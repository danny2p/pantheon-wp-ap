<?php
/**
 * Responsible for the recipe context.
 *
 * @link       http://bootstrapped.ventures
 * @since      7.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the recipe context.
 *
 * @since      7.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Context {

	/**
	 * Recipes that have already been requested for easy subsequent access.
	 *
	 * @since    7.0.0
	 * @access   private
	 * @var      array    $recipes    Array containing recipes that have already been requested for easy access.
	 */
	private static $context = array();

	/**
	 * Get the current context.
	 *
	 * @since    7.0.0
	 */
	public static function get() {
		$full_context = self::$context;

		// Return last element of array.
		if ( 0 < count( $full_context ) ) {
			return end( $full_context );
		}
		
		return false;
	}

	/**
	 * Set the current recipe context.
	 *
	 * @since	7.0.0
	 * @param	mixed $context Context to set.
	 */
	public static function set( $context ) {
		$full_context = self::$context;

		$full_context[] = $context;
		self::$context = $full_context;
	}
}
