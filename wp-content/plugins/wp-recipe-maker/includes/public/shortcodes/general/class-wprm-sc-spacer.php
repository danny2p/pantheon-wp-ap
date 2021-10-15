<?php
/**
 * Handle the spacer shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 */

/**
 * Handle the spacer shortcode.
 *
 * @since      4.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Spacer extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-spacer';

	public static function init() {
		self::$attributes = array(
			'size' => array(
				'default' => '10px',
				'type' => 'size',
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	4.0.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$style = '10px' === $atts['size'] ? '' : ' style="height: ' . $atts['size'] . '"';
		$output = '<div class="wprm-spacer"' . $style . '></div>';

		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Spacer::init();