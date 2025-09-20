<?php
/**
 * Responsible for the recipe template conditions.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the recipe template conditions.
 *
 * @since      10.1.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Template_Conditions {
	/**
	 * Register actions and filters.
	 *
	 * @since	10.1.0
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'size_conditions_js' ), 5 );
	}

	/**
	 * Load the size conditions JS.
	 *
	 * @since	10.1.0
	 */
	public static function size_conditions_js() {
		if ( WPRM_Settings::get( 'load_size_conditions_js' ) ) {
			echo '<script>';
			echo file_get_contents( WPRM_DIR . '/assets/js/other/size-conditions-min.js' );
			echo '</script>';
		}
	}
}

WPRM_Template_Conditions::init();
