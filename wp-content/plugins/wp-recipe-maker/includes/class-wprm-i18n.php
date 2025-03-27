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
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );
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

	/**
	 * Maybe dynamically translate a user input string.
	 */
	public static function maybe_translate( $string ) {
		$predefined_translations = array(
			'Equipment' => __( 'Equipment', 'wp-recipe-maker' ),
			'Ingredients' => __( 'Ingredients', 'wp-recipe-maker' ),
			'Instructions' => __( 'Instructions', 'wp-recipe-maker' ),
			'Video' => __( 'Video', 'wp-recipe-maker' ),
			'Notes' => __( 'Notes', 'wp-recipe-maker' ),
			'Nutrition' => __( 'Nutrition', 'wp-recipe-maker' ),
			'Rate this Recipe' => __( 'Rate this Recipe', 'wp-recipe-maker' ),
			'Share on Bluesky' => __( 'Share on Bluesky', 'wp-recipe-maker' ),
			'Share by Email' => __( 'Share by Email', 'wp-recipe-maker' ),
			'Share on Facebook' => __( 'Share on Facebook', 'wp-recipe-maker' ),
			'Share with Messenger' => __( 'Share with Messenger', 'wp-recipe-maker' ),
			'Share by Text' => __( 'Share by Text', 'wp-recipe-maker' ),
			'Share on Twitter' => __( 'Share on Twitter', 'wp-recipe-maker' ),
			'Share with WhatsApp' => __( 'Share with WhatsApp', 'wp-recipe-maker' ),
			'Pin Recipe' => __( 'Pin Recipe', 'wp-recipe-maker' ),
			'Direct in je mandje bij' => __( 'Direct in je mandje bij', 'wp-recipe-maker' ),
			'Save' => __( 'Save', 'wp-recipe-maker' ),
			'Saved!' => __( 'Saved!', 'wp-recipe-maker' ),
			'Jump to Video' => __( 'Jump to Video', 'wp-recipe-maker' ),
			'Jump to Recipe' => __( 'Jump to Recipe', 'wp-recipe-maker' ),
			'Print Recipe' => __( 'Print Recipe', 'wp-recipe-maker' ),
			'Read More' => __( 'Read More', 'wp-recipe-maker' ),
			'Rate this Recipe' => __( 'Rate this Recipe', 'wp-recipe-maker' ),
		);

		if ( array_key_exists( $string, $predefined_translations ) ) {
			return $predefined_translations[ $string ];
		}

		return $string;
	}
}

WPRM_i18n::init();
