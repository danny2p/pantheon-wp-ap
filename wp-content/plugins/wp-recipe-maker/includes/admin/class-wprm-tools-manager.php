<?php
/**
 * Responsible for handling the WPRM tools.
 *
 * @link       http://bootstrapped.ventures
 * @since      2.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the WPRM tools.
 *
 * @since      2.1.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Manager {

	/**
	 * Only to be enabled when debugging the tools.
	 *
	 * @since    2.1.0
	 * @access   private
	 * @var      boolean    $debugging    Wether or not we are debugging the tools.
	 */
	public static $debugging = false;

	/**
	 * Register actions and filters.
	 *
	 * @since    2.1.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_reset_settings', array( __CLASS__, 'ajax_reset_settings' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since    2.1.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( 'wprecipemaker', __( 'WPRM Tools', 'wp-recipe-maker' ), __( 'Tools', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_tools', array( __CLASS__, 'tools_page_template' ) );
	}

	/**
	 * Get the template for the tools page.
	 *
	 * @since    3.0.0
	 */
	public static function tools_page_template() {
		require_once( WPRM_DIR . 'templates/admin/tools.php' );
	}

	/**
	 * Reset settings through AJAX.
	 *
	 * @since    4.0.3
	 */
	public static function ajax_reset_settings() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			// Clear all settings.
			delete_option( 'wprm_settings' );

			wp_send_json_success( array(
				'redirect' => admin_url( 'admin.php?page=wprm_settings' ),
			) );
		}

		wp_die();
	}
}

WPRM_Tools_Manager::init();
