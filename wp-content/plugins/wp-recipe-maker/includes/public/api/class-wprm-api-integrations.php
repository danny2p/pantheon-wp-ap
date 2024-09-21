<?php
/**
 * Open up integrations in the WordPress REST API.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Open up integrations in the WordPress REST API.
 *
 * @since      9.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Integrations {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.6.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    9.6.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_route( 'wp-recipe-maker/v1', '/integrations/instacart', array(
				'callback' => array( __CLASS__, 'api_instacart' ),
				'methods' => 'POST',
				'permission_callback' => '__return_true',
			));
		}
	}

	/**
	 * Handle Instacart Integration call to the REST API.
	 *
	 * @since	9.6.0
	 * @param	WP_REST_Request $request Current request.
	 */
	public static function api_instacart( $request ) {
		$params = $request->get_params();

		$instacart_url = 'https://connect.dev.instacart.tools/idp/v1/products/recipe'; // TODO Production.
		$instacart_key = 'keys.2EcdyxVp5ryyTXo8gR9sXVUi9V7s0XRLzW6x9pGtbdk';

		$data = isset( $params['data'] ) ? $params['data'] : false;

		if ( $data ) {
			$recipe_id = intval( $data['recipeId'] );
			$servings_system_combination = sanitize_key( $data['servingsSystemCombination'] );

			// Look for existing combination first.
			$existing_combinations = get_post_meta( $recipe_id, 'wprm_instacart_combinations', true );
			$existing_combinations = $existing_combinations ? maybe_unserialize( $existing_combinations ) : array();

			foreach ( $existing_combinations as $combination => $result ) {
				if ( $combination === $servings_system_combination ) {
					// Use cached result if it's less than a month old.
					if ( strtotime( '-1 month' ) < $result['timestamp'] ) {
						return rest_ensure_response( $result['response'] );
					}
				}
			}
			
			// No cached result, get a new one through the Instacart API.
			$sanitized_data = array(
				'title' => sanitize_text_field( $data['title'] ),
				'image_url' => esc_url( $data['image_url'] ),
				'link_type' => sanitize_key( $data['link_type'] ),
				'instructions' => array(),
				'landing_page_configuation' => array(
					'partner_linkback_url' => WPRM_Compatibility::get_home_url(), // TODO?
					'enable_pantry_items' => true, // TODO Setting?
				),
			);

			foreach ( $data['ingredients'] as $ingredient ) {
				$name = trim( strip_tags( html_entity_decode( do_shortcode( $ingredient['name'] ) ) ) );
				$quantity = WPRM_Recipe_Parser::parse_quantity( $ingredient['quantity'] );

				if ( $name && $quantity ) {
					$unit = trim( strip_tags( html_entity_decode( do_shortcode( $ingredient['unit'] ) ) ) );

					$sanitized_data['ingredients'][] = array(
						'name' => $name,
						'measurements' => array(
							'quantity' => $quantity,
							'unit' => $unit ? $unit : 'each',
						),
					);
				}
			}

			$response = wp_remote_post( $instacart_url, array(
				'timeout' => 60,
				'sslverify' => false,
				'headers' => array(
					'accept' => 'application/json',
					'content-type' => 'application/json',
					'authorization' => 'Bearer ' . $instacart_key,
				),
				'body' => json_encode( $sanitized_data ),
			) );

			if ( is_wp_error( $response ) ) {
				return rest_ensure_response( false );
			}

			$instacart_response = json_decode( wp_remote_retrieve_body( $response ) );

			// Store result for future use.
			$existing_combinations[ $servings_system_combination ] = array(
				'response' => $instacart_response,
				'timestamp' => time(),
			);
			update_post_meta( $recipe_id, 'wprm_instacart_combinations', $existing_combinations );

			// Return Instacart URL.
			return rest_ensure_response( $instacart_response );
		}

		return rest_ensure_response( false );
	}
}

WPRM_Api_Integrations::init();
