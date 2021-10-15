<?php
/**
 * Fired during plugin activation.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Activator {

	/**
	 * Execute this on activation of the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Set up recipe taxonomies.
		WPRM_Post_Type::register_post_type();
		WPRM_Taxonomies::register_taxonomies();
		WPRM_Taxonomies::insert_default_taxonomy_terms();
	}
}
