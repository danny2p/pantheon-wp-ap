<?php
/**
 * Handle the recipe custom field container shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/deprecated
 */

/**
 * Handle the recipe custom field container shortcode.
 *
 * @since      5.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/deprecated
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Custom_Field_Container extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-custom-field-container';

	/**
	 * Output for the shortcode.
	 *
	 * @since	5.2.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = is_array( $atts ) ? $atts : array();
		$atts['label_container'] = '1';
		return WPRM_SC_Custom_Field::shortcode( $atts );
	}
}

WPRM_SC_Custom_Field_Container::init_deprecated();