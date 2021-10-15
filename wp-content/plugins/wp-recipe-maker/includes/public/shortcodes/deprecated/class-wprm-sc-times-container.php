<?php
/**
 * Handle the recipe times container shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/deprecated
 */

/**
 * Handle the recipe times container shortcode.
 *
 * @since      4.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/deprecated
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Times_Container extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-times-container';

	/**
	 * Output for the shortcode.
	 *
	 * @since	4.0.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = is_array( $atts ) ? $atts : array();
		$atts['fields'] = 'times';

		if ( isset( $atts['shorthand'] ) ) { $atts['time_shorthand'] = $atts['shorthand']; }
		if ( isset( $atts['icon_prep'] ) ) { $atts['icon_prep_time'] = $atts['icon_prep']; }
		if ( isset( $atts['icon_cook'] ) ) { $atts['icon_cook_time'] = $atts['icon_cook']; }
		if ( isset( $atts['icon_custom'] ) ) { $atts['icon_custom_time'] = $atts['icon_custom']; }
		if ( isset( $atts['icon_total'] ) ) { $atts['icon_total_time'] = $atts['icon_total']; }
		if ( isset( $atts['label_prep'] ) ) { $atts['label_prep_time'] = $atts['label_prep']; }
		if ( isset( $atts['label_cook'] ) ) { $atts['label_cook_time'] = $atts['label_cook']; }
		if ( isset( $atts['label_total'] ) ) { $atts['label_total_time'] = $atts['label_total']; }

		return WPRM_SC_Meta_Container::shortcode( $atts );
	}
}

WPRM_SC_Times_Container::init_deprecated();