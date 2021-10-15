<?php
/**
 * Provide information about WP Recipe Maker addons.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Provide information about WP Recipe Maker addons.
 *
 * @since      1.5.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Addons {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.5.0
	 */
	public static function init() {
	}

	/**
	 * Check if a particular addon is active.
	 *
	 * @since    1.5.0
	 * @param	 	 mixed $addon Addon to check.
	 */
	public static function is_active( $addon ) {
		return apply_filters( 'wprm_addon_active', false, $addon );
	}
}

WPRM_Addons::init();
