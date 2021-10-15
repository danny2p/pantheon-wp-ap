<?php
/**
 * Responsible for importing Meal Planner Pro recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.7.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing Meal Planner Pro recipes.
 *
 * @since      1.7.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Mealplannerpro extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    1.7.0
	 */
	public function get_uid() {
		return 'mealplannerpro';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    1.10.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    1.7.0
	 */
	public function get_name() {
		return 'Meal Planner Pro';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    1.7.0
	 */
	public function get_settings_html() {
		return '';
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    1.10.0
	 */
	public function get_recipe_count() {
		global $wpdb;
		$table = $wpdb->prefix . 'mpprecipe_recipes';

		$nbr_recipes = 0;
		if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$nbr_recipes = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table . ' WHERE (server_recipe_id IS NULL OR LEFT( server_recipe_id, 5 ) <> "wprm-")' );
		}

		return $nbr_recipes;
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    1.7.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$recipes = array();

		global $wpdb;
		$mpp_recipes = $wpdb->get_results( 'SELECT recipe_id, post_id, server_recipe_id, recipe_title FROM ' . $wpdb->prefix . 'mpprecipe_recipes' );

		foreach ( $mpp_recipes as $mpp_recipe ) {
			if ( 'wprm-' !== substr( $mpp_recipe->server_recipe_id, 0, 5 ) ) {
				$recipes[ $mpp_recipe->recipe_id ] = array(
					'name' => $mpp_recipe->recipe_title,
					'url' => get_edit_post_link( $mpp_recipe->post_id ),
				);
			}
		}

		return $recipes;
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    1.7.0
	 * @param	 mixed $id ID of the recipe we want to import.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$recipe = array(
			'import_id' => 0, // Set to 0 because we need to create a new recipe post.
			'import_backup' => array(
				'mpp_recipe_id' => $id,
			),
		);

		global $wpdb;
		$mpp_recipe = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'mpprecipe_recipes WHERE recipe_id=' . $id );
		$post_id = $mpp_recipe->post_id;

		// Featured Image.
		if ( $mpp_recipe->recipe_image ) {
			$image_id = WPRM_Import_Helper::get_or_upload_attachment( $post_id, $mpp_recipe->recipe_image );

			if ( $image_id ) {
				$recipe['image_id'] = $image_id;
			}
		}

		// Simple Matching.
		$recipe['name'] = $mpp_recipe->recipe_title;
		$recipe['summary'] = $this->richify( $mpp_recipe->summary );
		$recipe['notes'] = $this->richify( $mpp_recipe->notes );

		// Author.
		$recipe['author_name'] = $mpp_recipe->author;
		if ( '' !== trim( $recipe['author_name'] ) ) {
			$recipe['author_display'] = 'custom';
		}

		// Servings.
		$match = preg_match( '/^\s*\d+/', $mpp_recipe->yield, $servings_array );
		if ( 1 === $match ) {
				$servings = str_replace( ' ','', $servings_array[0] );
		} else {
				$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $mpp_recipe->yield );

		$recipe['servings'] = $servings;
		$recipe['servings_unit'] = $servings_unit;

		// Recipe Times.
		$recipe['prep_time'] = $mpp_recipe->prep_time ? $this->time_to_minutes( $mpp_recipe->prep_time ) : 0;
		$recipe['cook_time'] = $mpp_recipe->cook_time ? $this->time_to_minutes( $mpp_recipe->cook_time ) : 0;
		$recipe['total_time'] = $mpp_recipe->total_time ? $this->time_to_minutes( $mpp_recipe->total_time ) : 0;

		// Recipe Tags.
		$courses = str_replace( ';', ',', $mpp_recipe->type );
		$courses = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $courses );
		$courses = '' === $courses[0] ? array() : $courses;

		$cuisines = str_replace( ';', ',', $mpp_recipe->cuisine );
		$cuisines = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $cuisines );
		$cuisines = '' === $cuisines[0] ? array() : $cuisines;

		$recipe['tags'] = array(
			'course' => $courses,
			'cuisine' => $cuisines,
		);

		// Ingredients.
		$ingredients = array();
		$group = array(
			'ingredients' => array(),
			'name' => '',
		);

		$mpp_ingredients = preg_split( '/$\R?^/m', $mpp_recipe->ingredients );

		foreach ( $mpp_ingredients as $mpp_ingredient ) {
			$mpp_ingredient = trim( $this->derichify( $mpp_ingredient ) );

			if ( '!' === substr( $mpp_ingredient, 0, 1 ) ) { 
				$ingredients[] = $group;
				$group = array(
					'ingredients' => array(),
					'name' => substr( $mpp_ingredient, 1 ),
				);
			} else {
				$group['ingredients'][] = array(
					'raw' => $mpp_ingredient,
				);
			}
		}
		$ingredients[] = $group;
		$recipe['ingredients'] = $ingredients;

		// Instructions.
		$instructions = array();
		$group = array(
			'instructions' => array(),
			'name' => '',
		);

		$mpp_instructions = preg_split( '/$\R?^/m', $mpp_recipe->instructions );

		foreach ( $mpp_instructions as $mpp_instruction ) {
			if ( '!' === substr( $mpp_instruction, 0, 1 ) ) {
				$instructions[] = $group;
				$group = array(
					'instructions' => array(),
					'name' => $this->derichify( substr( $mpp_instruction, 1 ) ),
				);
			} elseif ( '%' === substr( $mpp_instruction, 0, 1 ) ) {
				$image_id = WPRM_Import_Helper::get_or_upload_attachment( $post_id, substr( $mpp_instruction, 1 ) );

				if ( $image_id ) {
					$last_instruction = array_pop( $group['instructions'] );

					if ( ! $last_instruction ) {
						$group['instructions'][] = array(
							'image' => $image_id,
						);
					} elseif ( isset( $last_instruction['image'] ) && $last_instruction['image'] ) {
						$group['instructions'][] = $last_instruction;
						$group['instructions'][] = array(
							'image' => $image_id,
						);
					} else {
						$group['instructions'][] = array(
							'text' => $last_instruction['text'],
							'image' => $image_id,
						);
					}
				}
			} else {
				$group['instructions'][] = array(
					'text' => trim( $this->richify( $mpp_instruction ) ),
				);
			}
		}
		$instructions[] = $group;
		$recipe['instructions'] = $instructions;

		// Nutrition Facts.
		$recipe['nutrition'] = array();

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    1.7.0
	 * @param	 mixed $id ID of the recipe we want replace.
	 * @param	 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		global $wpdb;
		$mpp_recipe = $wpdb->get_row( 'SELECT post_id, server_recipe_id FROM ' . $wpdb->prefix . 'mpprecipe_recipes WHERE recipe_id=' . $id );
		$post_id = $mpp_recipe->post_id;
		$server_recipe_id = $mpp_recipe->server_recipe_id;

		// Migrate comment ratings.
		global $wpdb;
		$table = $wpdb->prefix . 'mpprecipe_ratings';

		$ratings = array();
		if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$ratings = $wpdb->get_results( 'SELECT rating, comment_id FROM ' . $table . ' WHERE recipe_id=' . $id );
		}

		foreach ( $ratings as $rating ) {
			WPRM_Comment_Rating::add_or_update_rating_for( $rating->comment_id, $rating->rating );
		}

		// Update server_recipe_id field to show that this recipe has been imported.
		$wpdb->update( $wpdb->prefix . 'mpprecipe_recipes', array( 'server_recipe_id' => 'wprm-' . $server_recipe_id ), array( 'recipe_id' => $id ), array( '%s' ), array( '%d' ) );

		$post = get_post( $post_id );

		if ( $post ) {
			$content = $post->post_content;

			$content = str_ireplace( '[mpprecipe-recipe:' . $id . ']', '[wprm-recipe id="' . $wprm_id . '"]', $content );

			$update_content = array(
				'ID' => $post_id,
				'post_content' => $content,
			);
			wp_update_post( $update_content );
		}
	}

	/**
	 * Richify text by adding links and styling.
	 * Source: Meal Planner Pro.
	 *
	 * @since    1.7.0
	 * @param	 mixed $text Text to richify.
	 */
	private function richify( $text ) {
		$output = $text;

		$link_ptr = '#\[(.*?)\|(.*?)( (.*?))?\]#';
		preg_match_all(
			$link_ptr,
			$text,
			$matches
		);

		if ( isset( $matches[0] ) ) {

			$orig = $matches[0];
			$substitution = preg_replace(
				$link_ptr,
				'<a href="\\2" target="_blank" \\3>\\1</a>',
				str_replace( '"', '', $orig )
			);
			$output = str_replace( $orig, $substitution, $text );
		}

		$output = preg_replace( '/(^|\s)\*([^\s\*][^\*]*[^\s\*]|[^\s\*])\*(\W|$)/', '\\1<span class="bold">\\2</span>\\3', $output );
		$output = preg_replace( '#\[br\]#', '<br/>', $output );
		$output = preg_replace( '#\[b\](.*?)\[\/b\]#s', '<strong>\1</strong>', $output );
		$output = preg_replace( '#\[i\](.*?)\[\/i\]#s', '<em>\1</em>', $output );
		$output = preg_replace( '#\[u\](.*?)\[\/u\]#s', '<u>\1</u>', $output );
		return preg_replace( '/(^|\s)_([^\s_][^_]*[^\s_]|[^\s_])_(\W|$)/', '\\1<span class="italic">\\2</span>\\3', $output );
	}

	/**
	 * Derichify text by removing links and styling.
	 *
	 * @since    1.7.0
	 * @param	 mixed $text Text to derichify.
	 */
	private function derichify( $text ) {
		$output = $text;

		$link_ptr = '#\[(.*?)\|(.*?)( (.*?))?\]#';
		preg_match_all(
			$link_ptr,
			$text,
			$matches
		);

		if ( isset( $matches[0] ) ) {

			$orig = $matches[0];
			$substitution = preg_replace(
				$link_ptr,
				'\\1',
				str_replace( '"', '', $orig )
			);
			$output = str_replace( $orig, $substitution, $text );
		}

		$output = preg_replace( '/(^|\s)\*([^\s\*][^\*]*[^\s\*]|[^\s\*])\*(\W|$)/', '\\1\\2\\3', $output );
		$output = preg_replace( '#\[br\]#', '', $output );
		$output = preg_replace( '#\[b\](.*?)\[\/b\]#s', '\1', $output );
		$output = preg_replace( '#\[i\](.*?)\[\/i\]#s', '\1', $output );
		$output = preg_replace( '#\[u\](.*?)\[\/u\]#s', '\1', $output );
		return preg_replace( '/(^|\s)_([^\s_][^_]*[^\s_]|[^\s_])_(\W|$)/', '\\1\\2\\3', $output );
	}

	/**
	 * Convert time metadata to minutes.
	 *
	 * @since    1.7.0
	 * @param	 mixed $duration Time to convert.
	 */
	private function time_to_minutes( $duration = 'PT' ) {
		$date_abbr = array(
			'd' => 60 * 24,
			'h' => 60,
			'i' => 1,
		);
		$result = 0;

		$arr = explode( 'T', $duration );
		if ( isset( $arr[1] ) ) {
			$arr[1] = str_replace( 'M', 'I', $arr[1] );
		}
		$duration = implode( 'T', $arr );

		foreach ( $date_abbr as $abbr => $time ) {
			if ( preg_match( '/(\d+)' . $abbr . '/i', $duration, $val ) ) {
				$result += intval( $val[1] ) * $time;
			}
		}

		return $result;
	}
}
