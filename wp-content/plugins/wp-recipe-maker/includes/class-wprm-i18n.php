<?php
/**
 * Define the internationalization functionality.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_i18n {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'load_plugin_textdomain' ) );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain(
			'wp-recipe-maker',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}

WPRM_i18n::init();
