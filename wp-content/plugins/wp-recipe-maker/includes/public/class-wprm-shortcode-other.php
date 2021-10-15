<?php
/**
 * Handle the other shortcodes.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the other shortcodes.
 *
 * @since      5.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Other {

	/**
	 * Register actions and filters.
	 *
	 * @since	5.6.0
	 */
	public static function init() {
		add_shortcode( 'adjustable', array( __CLASS__, 'adjustable_shortcode' ) );
		add_shortcode( 'timer', array( __CLASS__, 'timer_shortcode' ) );
	}

	/**
	 * Output for the adjustable shortcode.
	 *
	 * @since	1.5.0
	 * @param	array $atts 		Shortcode attributes.
	 * @param	mixed $content Content in between the shortcodes.
	 */
	public static function adjustable_shortcode( $atts, $content ) {
		return '<span class="wprm-dynamic-quantity">' . $content . '</span>';
	}

	/**
	 * Output for the timer shortcode.
	 *
	 * @since	1.5.0
	 * @param	array $atts 	Shortcode attributes.
	 * @param	mixed $content Content in between the shortcodes.
	 */
	public static function timer_shortcode( $atts, $content ) {
		$atts = shortcode_atts( array(
			'seconds' => '0',
			'minutes' => '0',
			'hours' => '0',
		), $atts, 'wprm_timer' );

		$seconds = intval( $atts['seconds'] );
		$minutes = intval( $atts['minutes'] );
		$hours = intval( $atts['hours'] );

		$seconds = $seconds + (60 * $minutes) + (60 * 60 * $hours);

		if ( $seconds > 0 ) {
			return '<span class="wprm-timer" data-seconds="' . esc_attr( $seconds ) . '">' . $content . '</span>';
		} else {
			return $content;
		}
	}
}

WPRM_Shortcode_Other::init();
