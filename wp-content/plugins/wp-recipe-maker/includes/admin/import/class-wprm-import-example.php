<?php
/**
 * Example importer.
 * The importer will automatically be loaded when placed in the /wp-recipe-maker/includes/admin/import/ folder.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.20.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Example importer.
 * The importer will automatically be loaded when placed in the /wp-recipe-maker/includes/admin/import/ folder.
 *
 * @since      1.20.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */

// Make sure the class name matches the file name.
class WPRM_Import_Example extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    1.20.0
	 */
	public function get_uid() {
		// This should return a uid (no spaces) representing the import source.
		// For example "wp-ultimate-recipe", "easyrecipe", ...

		return 'example';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    1.20.0
	 */
	public function requires_search() {
		// Set to true when you need to search through the post content (or somewhere else) to actually find recipes.
		// When set to true the "search_recipes" function is required.
		// Usually false is fine as you can find recipes as a custom post type or in a custom table.

		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    1.20.0
	 */
	public function get_name() {
		// Display name for this importer.

		return 'Example Recipe Plugin';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    1.20.0
	 */
	public function get_settings_html() {
		// Any HTML can be added here if input is required for doing the import.
		// Take a look at the WP Ultimate Recipe importer for an example.
		// Most importers will just need ''.

		return '';
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    1.20.0
	 */
	public function get_recipe_count() {
		// Return a count for the number of recipes left to import.
		// Don't include recipes that have already been imported.

		return 0;
	}

	/**
	 * Search for recipes to import.
	 *
	 * @since    1.20.0
	 * @param	 int $page Page of recipes to import.
	 */
	public function search_recipes( $page = 0 ) {
		// Only needed if "search_required" returns true.
		// Function will be called with increased $page number until finished is set to true.
		// Will need a custom way of storing the recipes.
		// Take a look at the Easy Recipe importer for an example.

		return array(
			'finished' => true,
			'recipes' => 0,
		);
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    1.20.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		// Return an array of recipes to be imported with name and edit URL.
		// If not the same number of recipes as in "get_recipe_count" are returned pagination will be used.

		$recipes = array();

		// $recipes[ $post_id ] = array(
		// 		'name' => $post_title,
		// 		'url' => get_edit_post_link( $post_id ),
		// 	);
		// }

		return $recipes;
	}

	/**
	 * Search the list of recipes that are available to import.
	 *
	 * @since    8.2.0
	 * @param	 string $search Search term to use.
	 */
	public function get_recipes_search( $search ) {
		// Optional function with same return as the get_recipes function, but searching by the $search string.

		return array();
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    1.20.0
	 * @param	 mixed $id ID of the recipe we want to import.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		// Get the recipe data in WPRM format for a specific ID, corresponding to the ID in the "get_recipes" array.
		// $post_data will contain any input fields set in the "get_settings_html" function.
		// Include any fields to backup in "import_backup".
		$recipe = array(
			'import_id' => 0, // Important! If set to 0 will create the WPRM recipe as a new post. If set to an ID it will update to post with that ID to become a WPRM post type.
			'import_backup' => array(
				'example_recipe_id' => $id,
			),
		);

		// Get and set all the WPRM recipe fields.
		$recipe['name'] = '';
		$recipe['summary'] = '';
		$recipe['author_name'] = '';
		$recipe['servings_unit'] = '';
		$recipe['notes'] = '';

		$recipe['image_id'] = 0;
		$recipe['servings'] = 0;
		$recipe['prep_time'] = 0;
		$recipe['cook_time'] = 0;
		$recipe['total_time'] = 0;

		// Set recipe options.
		$recipe['author_display'] = 'default'; // default, disabled, post_author, custom.
		$recipe['ingredient_links_type'] = 'global'; // global, custom.

		// Optionally update the GLOBAL ingredient links (Premium only).
		// Warning, this changes the link for ALL recipes using that ingredient.
		$recipe['global_ingredient_links'] = array(
			1 => array( // Term ID or name of the ingredient to update.
				'url' => '',
				'nofollow' => 'default', // default, follow, nofollow.
			),
		);

		// Set any recipe tags (custom ones need to be created on the WP Recipe Maker > Manage page first).
		$recipe['tags'] = array(
			'course' => array( 1, 2 ), // Use ID of existing terms
			'cuisine' => array( 'Italian' ), // ...or name of new terms.
		);

		// Ingredients have to follow this array structure consisting of groups first.
		$recipe['ingredients'] = array(
			array(
				'name' => '', // Group names can be empty.
				'ingredients' => array(
					array(
						'amount' => '1-2',
						'unit' => '',
						'name' => 'apples', // The name field is required.
						'notes' => '',
					),
					array(
						'raw' => '1 cl olive oil (extra virgin)', // Alternatively pass all ingredient data as raw to have it parsed automatically.
					),
				),
			),
			array(
				'name' => 'Another ingredient group',
				'ingredients' => array(
					array(
						'amount' => '',
						'unit' => '',
						'name' => 'sage',
						'notes' => '',
						'link' => array( // Possible to set a custom ingredient link to be used in this recipe only.
							'url' => '',
							'nofollow' => 'default',
						),
					),
				),
			),
		);

		// Instructions have to follow this array structure consisting of groups first.
		$recipe['instructions'] = array(
			array(
				'name' => '', // Group names can be empty.
				'instructions' => array(
					array(
						'text' => 'My first instruction',
					),
					array(
						'text' => '',
						'image' => 1, // Use the attachment ID of an image.
					),
				),
			),
			array(
				'name' => 'Another instruction group',
				'instructions' => array(
					array(
						'text' => 'Another instruction',
					),
				),
			),
		);

		// Nutrition Facts.
		$recipe['nutrition'] = array(
			'serving_size' => '',
			'calories' => '',
			'carbohydrates' => '',
			'protein' => '',
			'fat' => '',
			'saturated_fat' => '',
			'polyunsaturated_fat' => '',
			'monounsaturated_fat' => '',
			'trans_fat' => '',
			'cholesterol' => '',
			'sodium' => '',
			'potassium' => '',
			'fiber' => '',
			'sugar' => '',
			'vitamin_a' => '',
			'vitamin_c' => '',
			'calcium' => '',
			'iron' => '',
		);

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    1.20.0
	 * @param	 mixed $id ID of the recipe we want replace.
	 * @param	 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		// The recipe with ID $id has been imported and we now have a WPRM recipe with ID $wprm_id (can be the same ID).
		// $post_data will contain any input fields set in the "get_settings_html" function.
		// Use this function to do anything after the import, like replacing shortcodes.
	}
}
