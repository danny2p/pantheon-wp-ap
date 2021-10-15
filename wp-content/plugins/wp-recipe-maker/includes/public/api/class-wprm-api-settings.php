<?php
/**
 * Open up plugin settings in the WordPress REST API.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Open up plugin settings in the WordPress REST API.
 *
 * @since      3.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Settings {

	/**
	 * Register actions and filters.
	 *
	 * @since    3.0.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    3.0.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_route( 'wp-recipe-maker/v1', '/setting', array(
				'callback' => array( __CLASS__, 'api_get_settings' ),
				'methods' => 'GET',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/setting', array(
				'callback' => array( __CLASS__, 'api_update_settings' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
		}
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since 3.0.0
	 */
	public static function api_required_permissions() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Handle get settings call to the REST API.
	 *
	 * @since 3.0.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_settings( $request ) {
		return WPRM_Settings::get_settings_with_defaults();
	}

	/**
	 * Handle update settings call to the REST API.
	 *
	 * @since 3.0.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_update_settings( $request ) {
		$params = $request->get_params();
		$settings = isset( $params['settings'] ) ? $params['settings'] : array();
		return WPRM_Settings::update_settings( $settings );
	}
}

WPRM_Api_Settings::init();
