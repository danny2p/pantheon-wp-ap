<?php
/**
 * API for the admin notices.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * API for the admin notices.
 *
 * @since      5.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Notices {

	/**
	 * Register actions and filters.
	 *
	 * @since    5.0.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    5.0.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_route( 'wp-recipe-maker/v1', '/notice', array(
				'callback' => array( __CLASS__, 'api_dismiss_notice' ),
				'methods' => 'DELETE',
				'permission_callback' => '__return_true',
			));
		}
	}

	/**
	 * Handle dismiss notice call to the REST API.
	 *
	 * @since 5.0.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_dismiss_notice( $request ) {
		// Parameters.
		$params = $request->get_params();

		$id = isset( $params['id'] ) ? $params['id'] : '';
		$user_id = get_current_user_id();

		if ( $id && $user_id ) {
			add_user_meta( $user_id, 'wprm_dismissed_notices', $id );
			return true;
		}

		return false;
	}
}

WPRM_Api_Notices::init();
