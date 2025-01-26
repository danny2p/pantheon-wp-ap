<?php
/**
 * Handle tooltips.
 *
 * @link       http://bootstrapped.ventures
 * @since      9.8.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle tooltips.
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tooltip {

	/**
	 * Get data for a tooltip.
	 *
	 * @since	9.8.0
	 */
	public static function get_tooltip_data( $text ) {
		// Check if $text contains HTML code.
		$has_html = preg_match( '/<[^<]+>/', $text );

		$data = ' data-tooltip="' . esc_attr( $text ) .  '"';

		if ( $has_html ) {
			$data .= ' data-tooltip-html="1"';
		}

		return $data;
	}
}
