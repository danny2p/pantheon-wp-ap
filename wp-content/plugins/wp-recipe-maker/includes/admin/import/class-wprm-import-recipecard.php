<?php
/**
 * Responsible for importing Recipe Card recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.12.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing Recipe Card recipes.
 *
 * @since      1.12.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Recipecard extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    1.12.0
	 */
	public function get_uid() {
		return 'recipecard';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    1.12.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    1.12.0
	 */
	public function get_name() {
		return 'Recipe Card';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    1.12.0
	 */
	public function get_settings_html() {
		return '';
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    1.12.0
	 */
	public function get_recipe_count() {
		return count( $this->get_recipes() );
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    1.12.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$recipes = array();

		global $wpdb;
		$table = $wpdb->prefix . 'yumprint_recipe_recipe';

		$rc_recipes = array();
		if ( strtolower( $table ) === strtolower( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) ) {
			$rc_recipes = $wpdb->get_results( 'SELECT id, recipe, post_id FROM ' . $table );
		}

		foreach ( $rc_recipes as $rc_recipe ) {
			if ( WPRM_POST_TYPE !== get_post_type( $rc_recipe->post_id ) ) {
				$recipe = json_decode( $rc_recipe->recipe );

				$recipes[ $rc_recipe->id ] = array(
					'name' => $recipe->title,
					'url' => get_edit_post_link( $rc_recipe->post_id ),
				);
			}
		}

		return $recipes;
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    1.12.0
	 * @param	 mixed $id ID of the recipe we want to import.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		global $wpdb;
		$rc_raw_recipe = $wpdb->get_row( 'SELECT id, recipe, nutrition, post_id FROM ' . $wpdb->prefix . 'yumprint_recipe_recipe WHERE id=' . $id );
		$post_id = $rc_raw_recipe->post_id;

		$rc_recipe = json_decode( $rc_raw_recipe->recipe );

		$recipe = array(
			'import_id' => 0, // Set to 0 because we need to create a new recipe post.
			'import_backup' => array(
				'rc_recipe_id' => $id,
				'rc_post_id' => $post_id,
			),
		);

		// Featured Image.
		if ( $rc_recipe->image ) {
			$image_id = WPRM_Import_Helper::get_or_upload_attachment( $post_id, $rc_recipe->image );

			if ( $image_id ) {
				$recipe['image_id'] = $image_id;
			}
		}

		// Simple Matching.
		$recipe['name'] = $rc_recipe->title;
		$recipe['summary'] = $rc_recipe->summary;
		$recipe['prep_time'] = $rc_recipe->prepTime;
		$recipe['cook_time'] = $rc_recipe->cookTime;
		$recipe['total_time'] = $rc_recipe->totalTime;
		$recipe['servings'] = $rc_recipe->servings;
		$recipe['servings_unit'] = '';

		// Recipe Author.
		$recipe['author_name'] = $rc_recipe->author;

		if ( '' !== trim( $recipe['author_name'] ) ) {
			$recipe['author_display'] = 'custom';
		}

		// Ingredients.
		$ingredients = array();

		if ( isset( $rc_recipe->ingredients ) && is_array( $rc_recipe->ingredients ) ) {
			foreach ( $rc_recipe->ingredients as $rc_ingredient_group ) {
				$group = array(
					'ingredients' => array(),
					'name' => $rc_ingredient_group->title,
				);

				foreach ( $rc_ingredient_group->lines as $rc_ingredient ) {
					$group['ingredients'][] = array(
						'raw' => $rc_ingredient,
					);
				}
				$ingredients[] = $group;
			}
		}

		$recipe['ingredients'] = $ingredients;

		// Instructions.
		$instructions = array();

		if ( isset( $rc_recipe->directions ) && is_array( $rc_recipe->directions ) ) {
			foreach ( $rc_recipe->directions as $rc_instruction_group ) {
				$group = array(
					'instructions' => array(),
					'name' => $rc_instruction_group->title,
				);

				foreach ( $rc_instruction_group->lines as $rc_instruction ) {
					$group['instructions'][] = array(
						'text' => $rc_instruction,
					);
				}
				$instructions[] = $group;
			}
		}

		$recipe['instructions'] = $instructions;

		// Recipe Notes.
		$notes = '';

		if ( isset( $rc_recipe->notes ) && is_array( $rc_recipe->notes ) ) {
			foreach ( $rc_recipe->notes as $rc_note_group ) {
				if ( $rc_note_group->title ) {
					if ( $notes ) {
						$notes .= '<br/>';
					}
					$notes .= $rc_note_group->title . ':<br/>';
				}

				foreach ( $rc_note_group->lines as $rc_note ) {
					$notes .= $rc_note . '<br/>';
				}
			}
		}

		// Adapted link in recipe notes.
		if ( isset( $rc_recipe->adapted ) && $rc_recipe->adapted ) {
			$link = isset( $rc_recipe->adaptedLink ) && $rc_recipe->adaptedLink ? $rc_recipe->adaptedLink : false;

			$notes .= '<br/>' . esc_html__( 'Adapted from', 'wp-recipe-maker' ) . ' ';
			if ( $link ) {
				 $notes .= '<a href="' . esc_attr( $link ) . '" target="_blank">' . esc_html( $rc_recipe->adapted ) . '</a>';
			} else {
				$notes .= esc_html( $rc_recipe->adapted );
			}
		}

		$recipe['notes'] = $notes;

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    1.12.0
	 * @param	 mixed $id ID of the recipe we want replace.
	 * @param	 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		global $wpdb;
		$rc_recipe = $wpdb->get_row( 'SELECT post_id FROM ' . $wpdb->prefix . 'yumprint_recipe_recipe WHERE id=' . $id );
		$post_id = $rc_recipe->post_id;

		// Update post_id field to show that this recipe has been imported.
		$wpdb->update( $wpdb->prefix . 'yumprint_recipe_recipe', array( 'post_id' => $wprm_id ), array( 'id' => $id ), array( '%d' ), array( '%d' ) );

		$post = get_post( $post_id );
		$content = $post->post_content;

		$content = preg_replace( "/\[yumprint-recipe\s.*?id='?\"?" . $id . '.*?]/im', '[wprm-recipe id="' . $wprm_id . '"]', $content );

		$update_content = array(
			'ID' => $post_id,
			'post_content' => $content,
		);
		wp_update_post( $update_content );
	}
}
