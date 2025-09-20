<?php
/**
 * Responsible for importing WP Delicious recipes.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing WP Delicious recipes.
 *
 * @since      9.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Wpdelicious extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    9.4.0
	 */
	public function get_uid() {
		return 'wpdelicious';
	}

	/**
	 * Whether or not this importer requires a manual search for recipes.
	 *
	 * @since    9.4.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    9.4.0
	 */
	public function get_name() {
		return 'WP Delicious (formerly Delicious Recipes)';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    9.4.0
	 */
	public function get_settings_html() {
		$html = '<p><strong>Important:</strong> Make sure to have the WP Delicious plugin active during the import. After import, <a href="https://help.bootstrapped.ventures/article/338-import-from-wp-delicious" target="_blank">set up redirects as mentioned in the documentation</a>.</p>';

		// Match recipe tags.
		$html .= '<h4>Recipe Tags</h4>';

		$delicious_taxonomies = array(
			'recipe-keyword' => 'Recipe Keywords',
			'recipe-difficulty' => 'Difficulty Level',
			'recipe-key' => 'Recipe Key',
			'recipe-tag' => 'Recipe Tag',
			'recipe-cooking-method' => 'Recipe Cooking Method',
			'recipe-cuisine' => 'Recipe Cuisine',
			'recipe-course' => 'Recipe Course',
			'recipe-badge' => 'Recipe Badge',
			'recipe-dietary' => 'Recipe Dietary',
		);

		$wprm_taxonomies = WPRM_Taxonomies::get_taxonomies();

		foreach ( $wprm_taxonomies as $wprm_taxonomy => $options ) {
			$wprm_key = substr( $wprm_taxonomy, 5 );

			$html .= '<label for="wpurp-tags-' . $wprm_key . '">' . $options['name'] . ':</label> ';
			$html .= '<select name="wpurp-tags-' . $wprm_key . '" id="wpurp-tags-' . $wprm_key . '">';
			$html .= "<option value=\"\">Don't import anything for this tag</option>";
			foreach ( $delicious_taxonomies as $key => $label ) {
				$selected = $wprm_key === $key || 'recipe-' . $wprm_key === $key ? ' selected="selected"' : '';
				$html .= '<option value="' . esc_attr( $key ) . '"' . esc_html( $selected ) . '>' . esc_html( $label ) . '</option>';
			}
			$html .= '</select>';
			$html .= '<br />';
		}

		return $html;
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    9.4.0
	 */
	public function get_recipe_count() {
		$args = array(
			'post_type' => 'recipe',
			'post_status' => 'any',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key'     => 'delicious_recipes_metadata',
					'compare' => 'EXISTS',
				),
			),
		);

		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    9.4.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$recipes = array();

		$limit = 100;
		$offset = $limit * $page;

		$args = array(
				'post_type' => 'recipe',
				'post_status' => 'any',
				'meta_query' => array(
					array(
						'key'     => 'delicious_recipes_metadata',
						'compare' => 'EXISTS',
					),
				),
				'orderby' => 'date',
				'order' => 'DESC',
				'posts_per_page' => $limit,
				'offset' => $offset,
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			$posts = $query->posts;

			foreach ( $posts as $post ) {
				$recipes[ $post->ID ] = array(
					'name' => $post->post_title,
					'url' => get_edit_post_link( $post->ID ),
				);
			}
		}

		return $recipes;
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    9.4.0
	 * @param		 mixed $id ID of the recipe we want to import.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$post = get_post( $id );
		$post_meta = get_post_custom( $id );
		$delicious_recipe = maybe_unserialize( $post_meta['delicious_recipes_metadata'][0] );

		$recipe = array(
			'import_id' => 0,
			'import_backup' => array(),
		);

		$recipe['image_id'] = get_post_thumbnail_id( $id );
		$recipe['name'] = $post->post_title;
		$recipe['summary'] = isset( $delicious_recipe['recipeDescription'] ) ? $delicious_recipe['recipeDescription'] : '';
		$recipe['notes'] = isset( $delicious_recipe['recipeNotes'] ) ? $delicious_recipe['recipeNotes'] : '';

		// Cost.
		$cost = isset( $delicious_recipe['estimatedCost'] ) ? $delicious_recipe['estimatedCost'] : '';
		$currency = isset( $delicious_recipe['estimatedCostCurr'] ) ? $delicious_recipe['estimatedCostCurr'] : '';

		$recipe['cost'] = trim( $currency . $cost );

		// Servings.
		$delicious_servings = isset( $delicious_recipe['noOfServings'] ) ? $delicious_recipe['noOfServings'] : '';
		$match = preg_match( '/^\s*\d+/', $delicious_servings, $servings_array );
		if ( 1 === $match ) {
				$servings = str_replace( ' ','', $servings_array[0] );
		} else {
				$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $delicious_servings );

		$recipe['servings'] = $servings;
		$recipe['servings_unit'] = $servings_unit;		

		// Recipe Times.
		$prep_time = isset( $delicious_recipe['prepTime'] ) ? $delicious_recipe['prepTime'] : '';
		$prep_time_unit = isset( $delicious_recipe['prepTimeUnit'] ) ? $delicious_recipe['prepTimeUnit'] : '';
		$recipe['prep_time'] = self::get_time_in_minutes( $prep_time, $prep_time_unit );

		$cook_time = isset( $delicious_recipe['cookTime'] ) ? $delicious_recipe['cookTime'] : '';
		$cook_time_unit = isset( $delicious_recipe['cookTimeUnit'] ) ? $delicious_recipe['cookTimeUnit'] : '';
		$recipe['cook_time'] = self::get_time_in_minutes( $cook_time, $cook_time_unit );

		$rest_time = isset( $delicious_recipe['restTime'] ) ? $delicious_recipe['restTime'] : '';
		$rest_time_unit = isset( $delicious_recipe['restTimeUnit'] ) ? $delicious_recipe['restTimeUnit'] : '';
		$rest_time_minutes = self::get_time_in_minutes( $rest_time, $rest_time_unit );

		if ( $rest_time_minutes ) {
			$recipe['custom_time_label'] = __( 'Rest Time', 'wp-recipe-maker' );
			$recipe['custom_time'] = $rest_time_minutes;
		}

		$recipe['total_time'] = $recipe['prep_time'] + $recipe['cook_time'] + $rest_time_minutes;

		// Recipe Tags.
		$recipe['tags'] = array();

		$wprm_taxonomies = WPRM_Taxonomies::get_taxonomies();
		foreach ( $wprm_taxonomies as $wprm_taxonomy => $options ) {
			$wprm_key = substr( $wprm_taxonomy, 5 );
			$tag = isset( $post_data[ 'wpurp-tags-' . $wprm_key ] ) ? $post_data[ 'wpurp-tags-' . $wprm_key ] : false;

			if ( $tag ) {
				switch ( $tag ) {
					case 'recipe-keyword':
						$keywords = isset( $delicious_recipe['recipeKeywords'] ) ? explode( ',', $delicious_recipe['recipeKeywords'] ) : '';

						if ( $keywords ) {
							$keywords = array_map( 'trim', $keywords );
							$recipe['tags'][ $wprm_key ] = $keywords;
						}
						break;
					case 'recipe-difficulty':
						$difficulty = isset( $delicious_recipe['difficultyLevel'] ) ? $delicious_recipe['difficultyLevel'] : '';

						if ( $difficulty ) {
							$recipe['tags'][ $wprm_key ] = array( $difficulty );
						}
						break;
					default:
						$terms = get_the_terms( $id, $tag );
						if ( $terms && ! is_wp_error( $terms ) ) {
							foreach ( $terms as $term ) {
								$recipe['tags'][ $wprm_key ][] = $term->name;
							}
						}
				}
			}
		}

		// Recipe Ingredients.
		$groups = isset( $delicious_recipe['recipeIngredients'] ) ? $delicious_recipe['recipeIngredients'] : array();
		$recipe['ingredients'] = array();

		$current_group = array(
			'name' => '',
			'ingredients' => array(),
		);
		foreach ( $groups as $group ) {
			if ( isset( $group['sectionTitle'] ) && $group['sectionTitle'] !== $current_group['name'] ) {
				$recipe['ingredients'][] = $current_group;
				$current_group = array(
					'name' => $group['sectionTitle'],
					'ingredients' => array(),
				);
			}

			if ( isset( $group['ingredients'] ) ) {
				foreach ( $group['ingredients'] as $ingredient ) {
					$current_group['ingredients'][] = array(
						'amount' => isset( $ingredient['quantity'] ) ? $ingredient['quantity'] : '',
						'unit' => isset( $ingredient['unit'] ) ? $ingredient['unit'] : '',
						'name' => isset( $ingredient['ingredient'] ) ? $ingredient['ingredient'] : '',
						'notes' => isset( $ingredient['notes'] ) ? $ingredient['notes'] : '',
					);
				}
			}
		}
		$recipe['ingredients'][] = $current_group;

		// Recipe Instructions.
		$groups = isset( $delicious_recipe['recipeInstructions'] ) ? $delicious_recipe['recipeInstructions'] : array();
		$recipe['instructions'] = array();

		$current_group = array(
			'name' => '',
			'instructions' => array(),
		);
		foreach ( $groups as $group ) {
			if ( isset( $group['sectionTitle'] ) && $group['sectionTitle'] !== $current_group['name'] ) {
				$recipe['instructions'][] = $current_group;
				$current_group = array(
					'name' => $group['sectionTitle'],
					'instructions' => array(),
				);
			}

			if ( isset( $group['instruction'] ) ) {
				foreach ( $group['instruction'] as $instruction ) {
					$current_group['instructions'][] = array(
						'text' => isset( $instruction['instruction'] ) ? $instruction['instruction'] : '',
						'image' => isset( $instruction['image'] ) ? intval( $instruction['image'] ) : 0,
					);
				}
			}
		}
		$recipe['instructions'][] = $current_group;

		// Recipe Nutrition.
		$recipe['nutrition'] = array();

		$nutrition_mapping = array(
			'servingSize'           => 'serving_size',
			'calories'              => 'calories',
			'totalCarbohydrate'		=> 'carbohydrates',
			'protein'               => 'protein',
			'totalFat'				=> 'fat',
			'saturatedFat'			=> 'saturated_fat',
			'transFat'				=> 'trans_fat',
			'cholesterol'			=> 'cholesterol',
			'sodium'                => 'sodium',
			'potassium'             => 'potassium',
			'dietaryFiber'			=> 'fiber',
			'sugars'				=> 'sugar',
			'vitaminA'				=> 'vitamin_a',
			'vitaminC'				=> 'vitamin_c',
			'vitaminD'				=> 'vitamin_d',
			'vitaminE'				=> 'vitamin_e',
			'vitaminK'				=> 'vitamin_k',
			'vitaminB6'				=> 'vitamin_b6',
			'vitaminB12'			=> 'vitamin_b12',
			'calcium'               => 'calcium',
			'iron'                  => 'iron',
			'folate'                => 'folate',
			'phosphorus'			=> 'phosphorus',
			'magnesium'				=> 'magnesium',
			'zinc'					=> 'zinc',
			'selenium'				=> 'selenium',
			'copper'				=> 'copper',
			'manganese'				=> 'manganese',
		);

		foreach ( $nutrition_mapping as $delicious_field => $wprm_field ) {
			$recipe['nutrition'][ $wprm_field ] = isset( $delicious_recipe[ $delicious_field ] ) ? $delicious_recipe[ $delicious_field ] : '';
		}

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    9.4.0
	 * @param		 mixed $id ID of the recipe we want replace.
	 * @param		 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		$post = get_post( $id );
		$content = $post->post_content;

		// Add recipe shortcode at the end.
		$content .= ' ' . WPRM_Shortcode::get_block_replacement( $wprm_id );

		$update_content = array(
			'ID' => $id,
			'post_type' => 'post',
			'post_content' => wp_slash( $content ),
		);
		wp_update_post( $update_content );

		// Store reference to WPRM recipe.
		add_post_meta( $id, '_delicious_wprm_migrated', $wprm_id );
	}

	/**
	 * Convert time string to minutes.
	 *
	 * @since	9.4.0
	 * @param	mixed $time Time string to convert to minutes.
	 * @param	mixed $unit Unit of the time string.
	 */
	private function get_time_in_minutes( $time, $unit ) {
		$minutes = intval( $time );

		if ( in_array( $unit, array( 'hour', 'hours' ) ) ) {
			$minutes = $minutes * 60;
		}

		return $minutes;
	}
}
