<?php
/**
 * Handle compabitility with My Emissions.
 *
 * @link       http://bootstrapped.ventures
 * @since      7.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle compabitility with My Emissions.
 *
 * @since      7.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Compatibility_My_Emissions {

	/**
	 * Register actions and filters.
	 *
	 * @since	7.0.0
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );

		add_filter( 'wprm_settings_update', array( __CLASS__, 'check_my_emissions_settings' ), 10, 2 );
	}

	/**
	 * Register the public assets.
	 *
	 * @since	7.0.0
	 */
	public static function register_assets() {
		wp_register_script( 'wprm-my-emissions', WPRM_URL . 'assets/js/other/my-emissions.js', array(), WPRM_VERSION, true );
	}

	/**
	 * Load the public assets.
	 *
	 * @since    7.0.0
	 */
	public static function load() {
		if ( self::is_active() ) {
			wp_enqueue_script( 'wprm-my-emissions' );
			WPRM_Assets::add_js_data( 'wprm_my_emissions', array(
				'identifier' => get_option( 'wprm_my_emissions_identifier', '' ),
			) );
		}
	}

	/**
	 * Check the My Emissions settings.
	 *
	 * @since    7.0.0
	 * @param    array $new_settings Settings after update.
	 * @param    array $old_settings Settings before update.
	 */
	public static function check_my_emissions_settings( $new_settings, $old_settings ) {
		$enabled_new = isset( $new_settings[ 'my_emissions_enable' ] ) ? $new_settings[ 'my_emissions_enable' ] : false;

		if ( $enabled_new ) {
			$api_old = isset( $old_settings[ 'my_emissions_api_key' ] ) ? $old_settings[ 'my_emissions_api_key' ] : '';
			$api_new = isset( $new_settings[ 'my_emissions_api_key' ] ) ? $new_settings[ 'my_emissions_api_key' ] : '';

			$api_status = get_option( 'wprm_my_emissions_status', 'inactive' );

			// API key changed, do something.
			if ( $api_new !== $api_old ) {
				if ( '' === $api_new ) {
					update_option( 'wprm_my_emissions_status', 'inactive', false );
					update_option( 'wprm_my_emissions_identifier', '', false );
				} else {
					// GET request to My Emissions Auth.
					$api_url = 'https://recipelabels.myemissions.green/auth/';
					$args = array(
						'headers' => array(
							'Authorization' => 'Api-Key ' . trim( $api_new ),
						),
					);

					$request = wp_remote_get( $api_url, $args );

					// Only valid if auth returns a 200 OK response and has valid body.
					$body = json_decode( wp_remote_retrieve_body( $request ), true );

					if ( ! is_wp_error( $request ) && isset( $request['response'] ) && 200 === $request['response']['code'] && is_array( $body ) && isset( $body['identifier'] ) && $body['identifier'] ) {
						update_option( 'wprm_my_emissions_status', 'valid', false );
						update_option( 'wprm_my_emissions_identifier', $body['identifier'], false );
					} else {
						update_option( 'wprm_my_emissions_status', 'invalid', false );
						update_option( 'wprm_my_emissions_identifier', '', false );
					}
				}
			}
		}

		return $new_settings;
	}

	/**
	 * Check if the My Emissions integration is active.
	 *
	 * @since    7.0.0
	 */
	public static function is_active() {
		$enabled = WPRM_Settings::get( 'my_emissions_enable' );

		if ( $enabled ) {
			$api_status = get_option( 'wprm_my_emissions_status', 'inactive' );
			$identifier = get_option( 'wprm_my_emissions_identifier', '' );

			if ( 'valid' === $api_status && $identifier ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if we should show the My Emissions checkbox in the recipe modal.
	 *
	 * @since    7.0.0
	 */
	public static function show_checkbox_in_modal() {
		$enabled = WPRM_Settings::get( 'my_emissions_enable' );

		if ( $enabled ) {
			// Only show checkbox when enabled but not for every single recipe.
			return ! WPRM_Settings::get( 'my_emissions_show_all' );
		}

		return false;
	}
}

WPRM_Compatibility_My_Emissions::init();
