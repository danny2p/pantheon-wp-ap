<?php
/**
 * Open up dashboard in the WordPress REST API.
 *
 * @link       http://bootstrapped.ventures
 * @since      7.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Open up dashboard in the WordPress REST API.
 *
 * @since      7.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Dashboard {

	/**
	 * Register actions and filters.
	 *
	 * @since    7.4.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    7.4.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_route( 'wp-recipe-maker/v1', '/dashboard/analytics', array(
				'callback' => array( __CLASS__, 'api_get_analytics' ),
				'methods' => 'GET',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
		}
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since 7.4.0
	 */
	public static function api_required_permissions() {
		return current_user_can( WPRM_Settings::get( 'features_dashboard_access' ) );
	}

	/**
	 * Handle get analytics call to the REST API.
	 *
	 * @since 7.4.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_analytics( $request ) {
		$data = WPRM_Analytics::get_dashboard_chart_data();

		return array(
			'data' => $data
		);
	}
}

WPRM_Api_Dashboard::init();
