<?php
/**
 * Responsible for importing ZipList recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.8.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing ZipList recipes.
 *
 * @since      1.8.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Ziplist extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    1.8.0
	 */
	public function get_uid() {
		return 'ziplist';
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
	 * @since    1.8.0
	 */
	public function get_name() {
		return 'ZipList and Zip Recipes';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    1.8.0
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
		return count( $this->get_recipes() );
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    1.8.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$recipes = array();

		global $wpdb;
		$table = $wpdb->prefix . 'amd_zlrecipe_recipes';

		$zl_recipes = array();
		if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$zl_recipes = $wpdb->get_results( 'SELECT recipe_id, post_id, recipe_title FROM ' . $table );
		}

		foreach ( $zl_recipes as $zl_recipe ) {
			if ( WPRM_POST_TYPE !== get_post_type( $zl_recipe->post_id ) ) {
				$recipes[ $zl_recipe->recipe_id ] = array(
					'name' => $zl_recipe->recipe_title,
					'url' => get_edit_post_link( $zl_recipe->post_id ),
				);
			}
		}

		return $recipes;
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    1.8.0
	 * @param	 mixed $id ID of the recipe we want to import.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		global $wpdb;
		$zl_recipe = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'amd_zlrecipe_recipes WHERE recipe_id=' . $id );
		$post_id = $zl_recipe->post_id;

		$recipe = array(
			'import_id' => 0, // Set to 0 because we need to create a new recipe post.
			'import_backup' => array(
				'zl_recipe_id' => $id,
				'zl_post_id' => $post_id,
			),
		);

		// Featured Image.
		if ( $zl_recipe->recipe_image ) {
			$image_id = WPRM_Import_Helper::get_or_upload_attachment( $post_id, $zl_recipe->recipe_image );

			if ( $image_id ) {
				$recipe['image_id'] = $image_id;
			}
		}

		// Video embed.
		if ( $zl_recipe->video_url ) {
			$recipe['video_embed'] = $zl_recipe->video_url;
		}

		// Simple Matching.
		$recipe['name'] = $zl_recipe->recipe_title;
		$recipe['summary'] = $this->richify( $zl_recipe->summary );
		$recipe['notes'] = $this->richify( $zl_recipe->notes );

		// Servings.
		$match = preg_match( '/^\s*\d+/', $zl_recipe->yield, $servings_array );
		if ( 1 === $match ) {
			$servings = str_replace( ' ','', $servings_array[0] );
		} else {
			$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $zl_recipe->yield );

		$recipe['servings'] = $servings;
		$recipe['servings_unit'] = $servings_unit;

		// Recipe Times.
		$recipe['prep_time'] = $zl_recipe->prep_time ? $this->time_to_minutes( $zl_recipe->prep_time ) : 0;
		$recipe['cook_time'] = $zl_recipe->cook_time ? $this->time_to_minutes( $zl_recipe->cook_time ) : 0;
		$total_time = $zl_recipe->total_time ? $this->time_to_minutes( $zl_recipe->total_time ) : 0;
		
		// Recalculate total time if not set.
		if ( ! $total_time ) {
			$wait_time = $zl_recipe->wait_time ? $this->time_to_minutes( $zl_recipe->wait_time ) : 0;
			$total_time = $recipe['prep_time'] + $recipe['cook_time'] + $wait_time;
		}
		$recipe['total_time'] = $total_time;

		// Recipe Tags.
		$ziplist_courses = isset( $zl_recipe->category ) && $zl_recipe->category ? $zl_recipe->category : '';
		$wprm_field = str_replace( ';', ',', $ziplist_courses );
		$courses = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $wprm_field );
		$courses = '' === $courses[0] ? array() : $courses;

		$ziplist_cuisines = isset( $zl_recipe->cuisine ) && $zl_recipe->cuisine ? $zl_recipe->cuisine : '';
		$wprm_field = str_replace( ';', ',', $ziplist_cuisines );
		$cuisines = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $wprm_field );
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

		$zl_ingredients = preg_split( '/$\R?^/m', $zl_recipe->ingredients );

		foreach ( $zl_ingredients as $zl_ingredient ) {
			$zl_ingredient = trim( $this->derichify( $zl_ingredient ) );

			if ( '!' === substr( $zl_ingredient, 0, 1 ) ) {
				$ingredients[] = $group;
				$group = array(
					'ingredients' => array(),
					'name' => substr( $zl_ingredient, 1 ),
				);
			} elseif ( '%' !== substr( $zl_ingredient, 0, 1 ) ) {
				$group['ingredients'][] = array(
					'raw' => $zl_ingredient,
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

		$zl_instructions = preg_split( '/$\R?^/m', $zl_recipe->instructions );

		foreach ( $zl_instructions as $zl_instruction ) {
			$zl_instruction = trim( str_replace( array( "\n", "\t", "\r" ), '', $zl_instruction ) );

			if ( '!' === substr( $zl_instruction, 0, 1 ) ) {
				$instructions[] = $group;
				$group = array(
					'instructions' => array(),
					'name' => $this->derichify( substr( $zl_instruction, 1 ) ),
				);
			} elseif ( '%' === substr( $zl_instruction, 0, 1 ) ) {
				$image_id = WPRM_Import_Helper::get_or_upload_attachment( $post_id, substr( $zl_instruction, 1 ) );

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
					'text' => $this->richify( $zl_instruction ),
				);
			}
		}
		$instructions[] = $group;
		$recipe['instructions'] = $instructions;

		// Nutrition Facts.
		$recipe['nutrition'] = array();

		$nutrition_mapping = array(
			'serving_size'  => 'serving_size',
			'calories'      => 'calories',
			'carbs'         => 'carbohydrates',
			'protein'       => 'protein',
			'fat'           => 'fat',
			'saturated_fat' => 'saturated_fat',
			'trans_fat'		=> 'trans_fat',
			'cholesterol' 	=> 'cholesterol',
			'sodium'        => 'sodium',
			'fiber'         => 'fiber',
			'sugar'         => 'sugar',
			'vitamin_c'     => 'vitamin_c',
			'vitamin_a'     => 'vitamin_a',
			'iron'        	=> 'iron',
			'calcium'       => 'calcium',
		);

		foreach ( $nutrition_mapping as $zl_field => $wprm_field ) {
			if ( $zl_recipe->$zl_field ) {
				$recipe['nutrition'][ $wprm_field ] = trim( $zl_recipe->$zl_field );
			}
		}

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    1.8.0
	 * @param	 mixed $id ID of the recipe we want replace.
	 * @param	 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		global $wpdb;
		$zl_recipe = $wpdb->get_row( 'SELECT post_id FROM ' . $wpdb->prefix . 'amd_zlrecipe_recipes WHERE recipe_id=' . $id );
		$post_id = $zl_recipe->post_id;

		// Migrate ratings.
		global $wpdb;
		$table = $wpdb->prefix . 'zrdn_visitor_ratings';

		$ratings = array();
		if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$ratings = $wpdb->get_results( 'SELECT rating, ip FROM ' . $table . ' WHERE recipe_id=' . $id );
		}

		foreach ( $ratings as $rating ) {
			$user_rating = array(
				'recipe_id' => $wprm_id,
				'user_id' => 0,
				'ip' => $rating->ip,
				'rating' => intval( $rating->rating ),
			);

			WPRM_Rating_Database::add_or_update_rating( $user_rating );
		}

		// Update post_id field to show that this recipe has been imported.
		$wpdb->update( $wpdb->prefix . 'amd_zlrecipe_recipes', array( 'post_id' => $wprm_id ), array( 'recipe_id' => $id ), array( '%d' ), array( '%d' ) );

		$post = get_post( $post_id );
		$content = $post->post_content;

		$content = $this->replace_shortcode( $content, $id, $wprm_id );

		$update_content = array(
			'ID' => $post_id,
			'post_content' => $content,
		);
		wp_update_post( $update_content );
	}

	/**
	 * Helper function to replace the ZipList shortcode.
	 *
	 * @since    1.8.0
	 * @param	 mixed $post_text 	Text to find the shortcode in.
	 * @param	 mixed $id 			ID to replace.
	 * @param	 mixed $wprm_id 	New WPRM ID.
	 */
	private function replace_shortcode( $post_text, $id, $wprm_id ) {
		$output = $post_text;

		// Old.
		$replacement = '[wprm-recipe id="' . $wprm_id . '"]';

		$needle_old = 'id="amd-zlrecipe-recipe-';
		$preg_needle_old = '/(id)=("(amd-zlrecipe-recipe-)[0-9^"]*")/i';
		$needle = '[amd-zlrecipe-recipe:';
		$preg_needle = '/\[amd-zlrecipe-recipe:([0-9]+)\]/i';

		if ( strpos( $post_text, $needle_old ) !== false ) {
			preg_match_all( $preg_needle_old, $post_text, $matches );
			foreach ( $matches[0] as $match ) {
				$recipe_id = str_replace( 'id="amd-zlrecipe-recipe-', '', $match );
				$recipe_id = str_replace( '"', '', $recipe_id );
				$output = preg_replace( "/<img id=\"amd-zlrecipe-recipe-" . $recipe_id . "\" class=\"amd-zlrecipe-recipe\" src=\"[^\"]*\" alt=\"\" \/>/", $replacement, $output );
			}
		}

		if ( strpos( $post_text, $needle ) !== false ) {
			preg_match_all( $preg_needle, $post_text, $matches );
			foreach ( $matches[0] as $match ) {
				$recipe_id = str_replace( '[amd-zlrecipe-recipe:', '', $match );
				$recipe_id = str_replace( ']', '', $recipe_id );
				$output = str_replace( '[amd-zlrecipe-recipe:' . $recipe_id . ']', $replacement, $output );
			}
		}

		// New.
		// Gutenberg.
		$gutenberg_matches = array();
		$gutenberg_patern = '/<!--\s+wp:(zip\-recipes\/recipe\-block)(\s+(\{.*?\}))?\s+(\/)?-->/mis';
		preg_match_all( $gutenberg_patern, $post_text, $matches );

		if ( isset( $matches[3] ) ) {
			foreach ( $matches[3] as $index => $block_attributes_json ) {
				if ( ! empty( $block_attributes_json ) ) {
					$attributes = json_decode( $block_attributes_json, true );

					if ( ! is_null( $attributes ) ) {
						if ( isset( $attributes['id'] ) && intval( $id ) === intval( $attributes['id'] ) ) {
							$output = str_ireplace( $matches[0][ $index ], '<!-- wp:wp-recipe-maker/recipe {"id":' . $wprm_id . ',"updated":' . time() . '} -->[wprm-recipe id="' . $wprm_id . '"]<!-- /wp:wp-recipe-maker/recipe -->', $output );
						}
					}
				}
			}
		}

		// Classic Editor.
		$classic_pattern = '/\[zrdn-recipe\s.*?id=\"?\'?(\d+)\"?\'?.*?\]/mi';
		preg_match_all( $classic_pattern, $post_text, $classic_matches );

		if ( isset( $classic_matches[1] ) ) {
			foreach ( $classic_matches[1] as $index => $mv_id ) {
				if ( $id === $mv_id ) {
					$output = str_ireplace( $classic_matches[0][ $index ], '[wprm-recipe id="' . $wprm_id . '"]', $output );
				}
			}
		}

		return $output;
	}

	/**
	 * Richify text by adding links and styling.
	 * Source: ZipList.
	 *
	 * @since    1.8.0
	 * @param	 mixed $text Text to richify.
	 */
	private function richify( $text ) {
		$text = preg_replace( '/(^|\s)\*([^\s\*][^\*]*[^\s\*]|[^\s\*])\*(\W|$)/', '\\1<strong>\\2</strong>\\3', $text );
		$text = preg_replace( '/(^|\s)_([^\s_][^_]*[^\s_]|[^\s_])_(\W|$)/', '\\1<em>\\2</em>\\3', $text );
		$text = preg_replace( '/\[([^\]\|\[]*)\|([^\]\|\[]*)\]/', '<a href="\\2" target="_blank">\\1</a>', $text );

		return $text;
	}

	/**
	 * Derichify text by removing links and styling.
	 *
	 * @since    1.8.0
	 * @param	 mixed $text Text to derichify.
	 */
	private function derichify( $text ) {
		$text = preg_replace( '/(^|\s)\*([^\s\*][^\*]*[^\s\*]|[^\s\*])\*(\W|$)/', '\\1\\2\\3', $text );
		$text = preg_replace( '/(^|\s)_([^\s_][^_]*[^\s_]|[^\s_])_(\W|$)/', '\\1\\2\\3', $text );
		$text = preg_replace( '/\[([^\]\|\[]*)\|([^\]\|\[]*)\]/', '\\1', $text );

		return $text;
	}

	/**
	 * Convert time metadata to minutes.
	 *
	 * @since    1.8.0
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
