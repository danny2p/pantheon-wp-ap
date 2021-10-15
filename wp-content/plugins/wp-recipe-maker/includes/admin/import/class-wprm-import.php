<?php
/**
 * Abstract class for importing to WPRM.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Abstract class for importing to WPRM.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
abstract class WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    1.0.0
	 */
	abstract public function get_uid();

	/**
	 * Get the name of this import source.
	 *
	 * @since    1.0.0
	 */
	abstract public function get_name();

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    1.10.0
	 */
	abstract public function requires_search();

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    1.3.0
	 */
	abstract public function get_settings_html();

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    1.10.0
	 */
	abstract public function get_recipe_count();

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    1.0.0
	 * @param	 int $page Page of recipes to get.
	 */
	abstract public function get_recipes( $page = 0 );

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    1.0.0
	 * @param		 mixed $id ID of the recipe we want to import.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	abstract public function get_recipe( $id, $post_data );

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    1.0.0
	 * @param		 mixed $id ID of the recipe we want replace.
	 * @param		 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	abstract public function replace_recipe( $id, $wprm_id, $post_data );
}
