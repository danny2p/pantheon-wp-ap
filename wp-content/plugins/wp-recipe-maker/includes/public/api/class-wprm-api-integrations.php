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

		$data = isset( $params['data'] ) ? $params['data'] : false;

		if ( $data ) {
			$link = WPRM_Instacart::get_link_for_recipe( $data );
			return rest_ensure_response( $link );
		}

		return rest_ensure_response( false );
	}
}

WPRM_Api_Integrations::init();
