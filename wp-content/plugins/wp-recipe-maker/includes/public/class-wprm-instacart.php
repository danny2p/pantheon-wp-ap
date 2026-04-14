<?php
/**
 * Handle integration with Instacart.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.8.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle integration with Instacart.
 *
 * @since      9.8.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Instacart {

	/**
	 * Register actions and filters.
	 *
	 * @since	9.8.0
	 */
	public static function init() {
		add_filter( 'wprm_recipe_ingredients_shortcode', array( __CLASS__, 'instacart_after_ingredients' ), 9 );
	}

	/**
	 * Add Instacart button after the ingredients.
	 *
	 * @since	9.8.0
	 * @param	mixed $output Current ingredients output.
	 */
	public static function instacart_after_ingredients( $output ) {
		if ( WPRM_Settings::get( 'integration_instacart_agree' ) && WPRM_Settings::get( 'integration_instacart' ) ) {
			$output = $output . do_shortcode( '[wprm-spacer][wprm-recipe-shop-instacart]' );
		}

		return $output;
	}

	/**
	 * Get the Instacart link for a recipe.
	 *
	 * @since	9.8.0
	 * @param	array $data Recipe data.
	 * @return	string|WP_Error Instacart URL or error on validation failure.
	 */
	public static function get_link_for_recipe( $data ) {
		$recipe_id = intval( $data['recipeId'] );

		// Validate recipeId: post must exist, be a published recipe.
		$post = get_post( $recipe_id );
		if ( ! $post ) {
			return new WP_Error( 'invalid_recipe', __( 'The specified recipe does not exist.', 'wp-recipe-maker' ), array( 'status' => 404 ) );
		}
		if ( WPRM_POST_TYPE !== get_post_type( $recipe_id ) ) {
			return new WP_Error( 'invalid_recipe', __( 'The specified recipe does not exist.', 'wp-recipe-maker' ), array( 'status' => 404 ) );
		}
		if ( 'publish' !== get_post_status( $recipe_id ) ) {
			return new WP_Error( 'invalid_recipe', __( 'The specified recipe does not exist.', 'wp-recipe-maker' ), array( 'status' => 404 ) );
		}

		$servings_system_combination = sanitize_key( $data['servingsSystemCombination'] );

		// Validate servingsSystemCombination: format must be servings-system, system 1 or 2, servings integer in range.
		$parts = explode( '-', $servings_system_combination, 2 );
		if ( 2 !== count( $parts ) ) {
			return new WP_Error( 'invalid_combination', __( 'Invalid servings and system combination.', 'wp-recipe-maker' ), array( 'status' => 400 ) );
		}
		$system = isset( $parts[1] ) ? $parts[1] : '';
		if ( ! in_array( $system, array( '1', '2' ), true ) ) {
			return new WP_Error( 'invalid_combination', __( 'Invalid servings and system combination.', 'wp-recipe-maker' ), array( 'status' => 400 ) );
		}
		$servings_raw = isset( $parts[0] ) ? $parts[0] : '';
		$requested_servings = WPRM_Recipe_Parser::parse_quantity( $servings_raw );
		if ( ! is_numeric( $requested_servings ) || $requested_servings != (int) $requested_servings || 1 > $requested_servings ) {
			return new WP_Error( 'invalid_combination', __( 'Invalid servings and system combination.', 'wp-recipe-maker' ), array( 'status' => 400 ) );
		}
		$requested_servings = (int) $requested_servings;
		$original_parsed = WPRM_Recipe_Parser::parse_quantity( get_post_meta( $recipe_id, 'wprm_servings', true ) );
		$original_parsed = is_numeric( $original_parsed ) && 0 < $original_parsed ? (int) $original_parsed : 1;
		$max_servings = max( 100, $original_parsed * 4 );
		$within_cache_range = ( $requested_servings <= $max_servings );

		// Look for existing combination first (only in-range combinations are cached).
		$existing_combinations = get_post_meta( $recipe_id, 'wprm_instacart_combinations', true );
		$existing_combinations = $existing_combinations ? maybe_unserialize( $existing_combinations ) : array();

		foreach ( $existing_combinations as $combination => $result ) {
			if ( $combination === $servings_system_combination ) {
				// Use cached result if it's less than a month old.
				if ( strtotime( '-1 month' ) < $result['timestamp'] ) {
					$link = self::get_link_from_response( $result['response'] );

					// Make sure the link is valid.
					if ( $link ) {
						return $link;
					}
				}
			}
		}
		
		// No cached result, get a new one through the Instacart API.
		$api_data = array(
			'title' => sanitize_text_field( $data['title'] ),
			'image_url' => esc_url( $data['image_url'] ),
			'ingredients' => array(),
		);

		foreach ( $data['ingredients'] as $ingredient ) {
			$name = trim( strip_tags( html_entity_decode( self::do_shortcode_safe( $ingredient['name'] ) ) ) );
			$quantity = WPRM_Recipe_Parser::parse_quantity( $ingredient['quantity'] );

			// Default to 1 if no quantity.
			if ( ! $quantity ) {
				$quantity = 1;
			}

			if ( $name && $quantity ) {
				$unit = trim( strip_tags( html_entity_decode( self::do_shortcode_safe( $ingredient['unit'] ) ) ) );
				$item = array(
					'name' => $name,
					'measurements' => array(
						'quantity' => $quantity,
						'unit' => $unit ? $unit : 'each',
					),
				);

				$api_data['ingredients'][] = self::filter_recipe_ingredient_payload( $item, $ingredient, $data, $recipe_id );
			}
		}

		$api_data = self::filter_recipe_payload( $api_data, $data, $recipe_id );

		// Call Instacart API.
		$instacart_response = self::call_instacart_api( 'recipe', $api_data );

		// Store result for future use only when within the logical range (out-of-range requests still get the link but are not cached).
		if ( $within_cache_range ) {
			$existing_combinations[ $servings_system_combination ] = array(
				'response' => $instacart_response,
				'timestamp' => time(),
			);
			update_post_meta( $recipe_id, 'wprm_instacart_combinations', $existing_combinations );
		}

		// Return Instacart URL.
		return self::get_link_from_response( $instacart_response );
	}

	/**
	 * Get the Instacart link for a shopping list.
	 *
	 * @since	9.8.0
	 * @param	array $data Shopping list data.
	 */
	public static function get_link_for_list( $data ) {
		// Check for existing link first.
		if ( isset( $data['meta'] ) && isset( $data['meta']['instacart'] ) ) {
			$response = $data['meta']['instacart']['response'];
			$timestamp = $data['meta']['instacart']['timestamp'];

			// Use cached result if it's less than a month old.
			if ( strtotime( '-1 month' ) < $timestamp ) {
				$link = self::get_link_from_response( $response );

				if ( $link ) {
					return $link;
				}
			}
		}

		// No cached result, get a new one through the Instacart API.
		$title = isset( $data['collection'] ) && isset( $data['collection']['name'] ) && $data['collection']['name'] ? $data['collection']['name'] : 'Shopping List';

		$api_data = array(
			'title' => sanitize_text_field( $title ),
			'line_items' => array(),
		);

		foreach ( $data['groups'] as $group ) {
			foreach ( $group['ingredients'] as $ingredient ) {
				$name = trim( strip_tags( html_entity_decode( self::do_shortcode_safe( $ingredient['name'] ) ) ) );

				// Exclude checked ingredients.
				if ( isset( $ingredient['checked'] ) && $ingredient['checked'] ) {
					continue;
				}

				foreach ( $ingredient['variations'] as $variation ) {
					$variation_amount = isset( $variation['amount'] ) ? $variation['amount'] : '';
					$variation_unit = isset( $variation['unit'] ) ? $variation['unit'] : '';

					$display = isset( $variation['display'] ) ? $variation['display'] : '';
					$display = trim( $display );

					if ( $display ) {
						$parsed = WPRM_Recipe_Parser::parse_ingredient( $display, true );
						$variation_amount = $parsed['amount'];

						// All the rest is the unit.
						$variation_unit = trim( $parsed['unit'] . $parsed['name'] . $parsed['notes'] );
					}

					$quantity = WPRM_Recipe_Parser::parse_quantity( $variation_amount );

					// Default to 1 if no quantity.
					if ( ! $quantity ) {
						$quantity = 1;
					}

					if ( $name && $quantity ) {
						$unit = trim( strip_tags( html_entity_decode( self::do_shortcode_safe( $variation_unit ) ) ) );
						$line_item = array(
							'name' => $name,
							'quantity' => $quantity,
							'unit' => $unit ? $unit : 'each',
						);

						$api_data['line_items'][] = self::filter_shopping_list_item_payload( $line_item, $variation, $ingredient, $group, $data );
					}
				}
			}
		}

		$api_data = self::filter_shopping_list_payload( $api_data, $data );

		// Call Instacart API and get link.
		$instacart_response = self::call_instacart_api( 'shopping_list', $api_data );
		$link = self::get_link_from_response( $instacart_response );

		// Found link, store in meta for future use.
		if ( $link ) {
			$meta = array(
				'instacart' => array(
					'response' => $instacart_response,
					'timestamp' => time(),
				),
			);
			WPRMPRC_Shopping_List::save_meta( $data['uid'], $meta );
		}

		return $link;
	}

	/**
	 * Call the Instacart API.
	 *
	 * @since	9.8.0
	 * @param	string $type Type of call.
	 * @param	array $data Data to send.
	 */
	public static function call_instacart_api( $type, $data ) {
		$data['link_type'] = $type;
		$data['landing_page_configuration'] = array(
			'partner_linkback_url' => WPRM_Compatibility::get_home_url(),
			'enable_pantry_items' => true,
		);

		return WPRM_Proxy::call( 'instacart', $data, array(
			'X-Instacart-Type' => $type,
		) );
	}

	/**
	 * Get the correct link to use from the API response.
	 *
	 * @since	9.8.0
	 * @param	mixed $response Instcart API response.
	 */
	public static function get_link_from_response( $response ) {
		$link = false;
		$response = is_object( $response ) ? (array) $response : $response;

		if ( $response && isset( $response['products_link_url'] ) ) {
			$link = $response['products_link_url'];

			$affiliate_id = WPRM_Settings::get( 'integration_instacart_affiliate_id' );
			if ( $affiliate_id ) {
				$link = add_query_arg( array(
					'utm_campaign' => 'instacart-idp',
					'utm_medium' => 'affiliate',
					'utm_source' => 'instacart_idp',
					'utm_term' => 'partnertype-mediapartner',
					'utm_content' => 'campaignid-20313_partnerid-' . $affiliate_id,
				), $link );
			}
		}

		return $link;
	}

	/**
	 * Filter a single Instacart recipe ingredient payload item.
	 *
	 * Add per-item Instacart filters through code, for example:
	 * array(
	 * 	'filters' => array(
	 * 		'brand_filters' => array(
	 * 			array(
	 * 				'type' => 'include',
	 * 				'brand' => 'Instacart',
	 * 			),
	 * 		),
	 * 		'health_filters' => array(
	 * 			array(
	 * 				'type' => 'include',
	 * 				'health_attribute' => 'GLUTEN_FREE',
	 * 			),
	 * 		),
	 * 	),
	 * )
	 *
	 * brand_filters values are case-sensitive. health_filters should use
	 * Instacart's documented health attribute constants.
	 *
	 * @since	10.1.1
	 * @param	array $item Instacart ingredient payload item.
	 * @param	array $ingredient Original recipe ingredient data.
	 * @param	array $data Original recipe request data.
	 * @param	int   $recipe_id Recipe post ID.
	 * @return	array
	 */
	public static function filter_recipe_ingredient_payload( $item, $ingredient, $data, $recipe_id ) {
		return self::apply_array_filter( 'wprm_instacart_recipe_ingredient', $item, $ingredient, $data, $recipe_id );
	}

	/**
	 * Filter the Instacart recipe payload before sending it to the proxy.
	 *
	 * Use this hook to add or adjust filters across multiple ingredients.
	 *
	 * @since	10.1.1
	 * @param	array $api_data Instacart recipe payload.
	 * @param	array $data Original recipe request data.
	 * @param	int   $recipe_id Recipe post ID.
	 * @return	array
	 */
	public static function filter_recipe_payload( $api_data, $data, $recipe_id ) {
		return self::apply_array_filter( 'wprm_instacart_recipe_payload', $api_data, $data, $recipe_id );
	}

	/**
	 * Filter a single Instacart shopping list line item payload item.
	 *
	 * Add per-item Instacart filters through code, for example:
	 * array(
	 * 	'filters' => array(
	 * 		'brand_filters' => array(
	 * 			array(
	 * 				'type' => 'exclude',
	 * 				'brand' => 'Example Brand',
	 * 			),
	 * 		),
	 * 		'health_filters' => array(
	 * 			array(
	 * 				'type' => 'include',
	 * 				'health_attribute' => 'VEGAN',
	 * 			),
	 * 		),
	 * 	),
	 * )
	 *
	 * brand_filters values are case-sensitive. health_filters should use
	 * Instacart's documented health attribute constants.
	 *
	 * @since	10.1.1
	 * @param	array $line_item Instacart shopping list line item payload item.
	 * @param	array $variation Original shopping list variation data.
	 * @param	array $ingredient Original shopping list ingredient data.
	 * @param	array $group Original shopping list group data.
	 * @param	array $data Original shopping list data.
	 * @return	array
	 */
	public static function filter_shopping_list_item_payload( $line_item, $variation, $ingredient, $group, $data ) {
		return self::apply_array_filter( 'wprm_instacart_shopping_list_item', $line_item, $variation, $ingredient, $group, $data );
	}

	/**
	 * Filter the Instacart shopping list payload before sending it to the proxy.
	 *
	 * Use this hook to add or adjust filters across multiple line items.
	 *
	 * @since	10.1.1
	 * @param	array $api_data Instacart shopping list payload.
	 * @param	array $data Original shopping list data.
	 * @return	array
	 */
	public static function filter_shopping_list_payload( $api_data, $data ) {
		return self::apply_array_filter( 'wprm_instacart_shopping_list_payload', $api_data, $data );
	}

	/**
	 * Apply a filter and only accept array results.
	 *
	 * @since	10.1.1
	 * @return	array
	 */
	public static function apply_array_filter() {
		$args = func_get_args();
		$hook = array_shift( $args );
		$default = isset( $args[0] ) && is_array( $args[0] ) ? $args[0] : array();
		$filtered = apply_filters_ref_array( $hook, $args );

		return is_array( $filtered ) ? $filtered : $default;
	}

	/**
	 * Execute safe shortcodes only.
	 *
	 * @since	10.1.0
	 */
	public static function do_shortcode_safe( $content ) {
		// No more executing of shortcodes for security reasons, only strip.
		global $shortcode_tags;

		if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) {
			return $content;
		}

		$pattern = get_shortcode_regex();

		return preg_replace_callback( "/$pattern/s", function( $matches ) {
			// $matches[5] is the content between opening/closing tags
			return isset( $matches[5] ) ? $matches[5] : '';
		}, $content );
	}
}

WPRM_Instacart::init();
