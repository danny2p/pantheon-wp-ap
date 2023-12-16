<?php
/**
 * Handle the My Emissions label shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      7.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the My Emissions label shortcode.
 *
 * @since      7.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_My_Emissions extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-my-emissions-label';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
		);

		$atts = array_merge( WPRM_Shortcode_Helper::get_section_atts(), $atts );
		self::$attributes = $atts;

		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	3.2.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );
		return ''; // My Emissions shut down at the end of 2023.
	}
}

WPRM_SC_My_Emissions::init();