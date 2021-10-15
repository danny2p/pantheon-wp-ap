<?php
/**
 * Handle the recipe time container shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/deprecated
 */

/**
 * Handle the recipe time container shortcode.
 *
 * @since      3.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/deprecated
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Time_Container extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-time-container';

	/**
	 * Output for the shortcode.
	 *
	 * @since	3.3.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = is_array( $atts ) ? $atts : array();
		$atts['label_container'] = '1';
		return WPRM_SC_Time::shortcode( $atts );
	}
}

WPRM_SC_Time_Container::init_deprecated();