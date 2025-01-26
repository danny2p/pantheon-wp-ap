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
	private static $instacart_mode = 'prod';
	private static $instacart_server = array(
		'dev' => 'https://connect.dev.instacart.tools',
		'prod' => 'https://connect.instacart.com',
	);
	private static $instacart_key = array(
		'dev' => 'keys.2EcdyxVp5ryyTXo8gR9sXVUi9V7s0XRLzW6x9pGtbdk',
		'prod' => 'keys.SN6m6wbXGYEXuICxIG0_6JoGdkoxejfnyliFjtGMEW0',
	);

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
	 * Get the endpoint to use.
	 *
	 * @since	9.8.0
	 * @param	string $type Type of endpoing.
	 */
	public static function get_endpoint( $type ) {
		$endpoint = self::$instacart_server[ self::$instacart_mode ];
		$endpoint .= '/idp/v1/';

		switch ( $type ) {
			case 'recipe':
				$endpoint .= 'products/recipe';
				break;
			case 'shopping_list':
				$endpoint .= 'products/products_link';
				break;
		}

		return $endpoint;
	}

	/**
	 * Get the Instacart link for a recipe.
	 *
	 * @since	9.8.0
	 * @param	array $data Recipe data.
	 */
	public static function get_link_for_recipe( $data ) {
		$recipe_id = intval( $data['recipeId'] );
		$servings_system_combination = sanitize_key( $data['servingsSystemCombination'] );

		// Look for existing combination first.
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
			$name = trim( strip_tags( html_entity_decode( do_shortcode( $ingredient['name'] ) ) ) );
			$quantity = WPRM_Recipe_Parser::parse_quantity( $ingredient['quantity'] );

			// Default to 1 if no quantity.
			if ( ! $quantity ) {
				$quantity = 1;
			}

			if ( $name && $quantity ) {
				$unit = trim( strip_tags( html_entity_decode( do_shortcode( $ingredient['unit'] ) ) ) );

				$api_data['ingredients'][] = array(
					'name' => $name,
					'measurements' => array(
						'quantity' => $quantity,
						'unit' => $unit ? $unit : 'each',
					),
				);
			}
		}

		// Call Instacart API.
		$instacart_response = self::call_instacart_api( 'recipe', $api_data );

		// Store result for future use.
		$existing_combinations[ $servings_system_combination ] = array(
			'response' => $instacart_response,
			'timestamp' => time(),
		);
		update_post_meta( $recipe_id, 'wprm_instacart_combinations', $existing_combinations );

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
				$name = trim( strip_tags( html_entity_decode( do_shortcode( $ingredient['name'] ) ) ) );

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
						$unit = trim( strip_tags( html_entity_decode( do_shortcode( $variation_unit ) ) ) );
		
						$api_data['line_items'][] = array(
							'name' => $name,
							'quantity' => $quantity,
							'unit' => $unit ? $unit : 'each',
						);
					}
				}
			}
		}

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
		$endpoint = self::get_endpoint( $type );

		$data['link_type'] = $type;
		$data['landing_page_configuration'] = array(
			'partner_linkback_url' => WPRM_Compatibility::get_home_url(),
			'enable_pantry_items' => true,
		);

		$key = self::$instacart_key[ self::$instacart_mode ];

		$response = wp_remote_post( $endpoint, array(
			'timeout' => 60,
			'sslverify' => false,
			'headers' => array(
				'accept' => 'application/json',
				'content-type' => 'application/json',
				'authorization' => 'Bearer ' . $key,
			),
			'body' => json_encode( $data ),
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
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
}

WPRM_Instacart::init();
