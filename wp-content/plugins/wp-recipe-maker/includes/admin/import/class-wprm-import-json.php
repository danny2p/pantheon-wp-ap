<?php
/**
 * Responsible for importing JSON recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.8.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing JSON recipes.
 *
 * @since      5.8.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Json extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    5.8.0
	 */
	public function get_uid() {
		return 'json-ld';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    5.8.0
	 */
	public function requires_search() {
		return true;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    5.8.0
	 */
	public function get_name() {
		return 'JSON-LD HTML Script';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    5.8.0
	 */
	public function get_settings_html() {
		 return '';
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    5.8.0
	 */
	public function get_recipe_count() {
		$recipes_found = get_option( 'wprm_import_json_ld_recipes', array() );
		return count( $recipes_found );
	}

	/**
	 * Search for recipes to import.
	 *
	 * @since	5.8.0
	 * @param	int $page Page of recipes to import.
	 */
	public function search_recipes( $page = 0 ) {
		$recipes = array();
		$finished = false;

		$limit = 100;
		$offset = $limit * $page;

		$args = array(
			'post_type' => array( 'post', 'page' ),
			'post_status' => 'any',
			'orderby' => 'date',
			'order' => 'DESC',
			'posts_per_page' => $limit,
			'offset' => $offset,
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			$posts = $query->posts;

			foreach ( $posts as $post ) {
				$json_recipes = $this->get_json_ld_recipes( $post->post_content );

				foreach ( $json_recipes as $index => $json_recipe ) {
					$name = isset( $json_recipe['json']['name'] ) ? $json_recipe['json']['name'] : __( 'Unknown', 'wp-recipe-maker' );

					$recipe_id = $post->ID . '-' . $index;
					$recipes[ $recipe_id ] = array(
						'name' => $name,
						'url' => get_edit_post_link( $post->ID ),
					);
				}
			}
		} else {
			$finished = true;
		}

		$found_recipes = 0 === $page ? array() : get_option( 'wprm_import_json_ld_recipes', array() );
		$found_recipes = array_merge( $found_recipes, $recipes );

		update_option( 'wprm_import_json_ld_recipes', $found_recipes, false );

		$search_result = array(
			'finished' => $finished,
			'recipes' => count( $found_recipes ),
		);

		return $search_result;
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    5.8.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$found_recipes = get_option( 'wprm_import_json_ld_recipes', array() );

		$limit = 100;
		$offset = $limit * $page;

		return array_slice( $found_recipes, $offset, $limit );
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    5.8.0
	 * @param		 mixed $id ID of the recipe we want to import.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$id_parts = explode( '-', $id, 2 );
		$post_id = intval( $id_parts[0] );
		$recipe_index = intval( $id_parts[1] );

		$post = get_post( $post_id );
		$recipes = $this->get_json_ld_recipes( $post->post_content );
		$json_recipe = isset( $recipes[ $recipe_index ] ) ? $recipes[ $recipe_index ] : false;

		if ( $json_recipe ) {
			$recipe = array(
				'import_id' => 0, // Set to 0 because we need to create a new recipe post.
				'import_backup' => array(
					'json_ld_script' => $json_recipe['html'],
				),
			);

			$json_recipe = $json_recipe['json'];

			// Featured Image.
			$image_url = false;
			if ( isset( $json_recipe['image'] ) ) {
				$image_url = is_array( $json_recipe['image'] ) ? $json_recipe['image'][0] : $json_recipe['image'];
			}
			$recipe['image_id'] = WPRM_Import_Helper::get_or_upload_attachment( $post_id, $image_url );

			// Simple matching.
			$recipe['name'] = isset( $json_recipe['name'] ) ? $json_recipe['name'] : '';
			$recipe['summary'] = isset( $json_recipe['description'] ) ? $json_recipe['description'] : '';

			// Servings.
			$servings_attribute = isset( $json_recipe['recipeYield'] ) ? trim( $json_recipe['recipeYield'] ) : '';

			$match = preg_match( '/^\s*\d+/', $servings_attribute, $servings_array );
			if ( 1 === $match ) {
				$servings = str_replace( ' ','', $servings_array[0] );
			} else {
				$servings = '';
			}

			$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $servings_attribute );

			$recipe['servings'] = $servings;
			$recipe['servings_unit'] = $servings_unit;

			// Cook times.
			$recipe['prep_time'] = isset( $json_recipe['prepTime'] ) ? $this->time_to_minutes( $json_recipe['prepTime'] ) : 0;
			$recipe['cook_time'] = isset( $json_recipe['cookTime'] ) ? $this->time_to_minutes( $json_recipe['cookTime'] ) : 0;
			$recipe['total_time'] = isset( $json_recipe['totalTime'] ) ? $this->time_to_minutes( $json_recipe['totalTime'] ) : 0;

			// Recipe Tags.
			$json_courses = isset( $json_recipe['recipeCategory'] ) ? $json_recipe['recipeCategory'] : array();
			$courses = is_array( $json_courses ) ? $json_courses : array( $json_courses );

			$json_cuisines = isset( $json_recipe['recipeCuisine'] ) ? $json_recipe['recipeCuisine'] : array();
			$cuisines = is_array( $json_cuisines ) ? $json_cuisines : array( $json_cuisines );

			$json_keywords = isset( $json_recipe['keywords'] ) ? $json_recipe['keywords'] : array();
			$keywords = is_array( $json_keywords ) ? $json_keywords : array( $json_keywords );

			$recipe['tags'] = array(
				'course' => $courses,
				'cuisine' => $cuisines,
				'keyword' => $keywords,
			);

			// Ingredients.
			$json_ingredients = isset( $json_recipe['recipeIngredient'] ) ? $json_recipe['recipeIngredient'] : array();
			$ingredients = array();

			$group = array(
				'ingredients' => array(),
				'name' => '',
			);

			foreach ( $json_ingredients as $json_ingredient ) {
				$group['ingredients'][] = array(
					'raw' => $json_ingredient,
				);
			}
			$recipe['ingredients'] = array( $group );

			// Instructions.
			$json_instructions = isset( $json_recipe['recipeInstructions'] ) ? $json_recipe['recipeInstructions'] : array();
			$instructions = array();

			$group = array(
				'instructions' => array(),
				'name' => '',
			);

			foreach ( $json_instructions as $json_instruction ) {
				if ( is_array( $json_instruction ) ) {
					if ( isset( $json_instruction['@type'] ) && 'HowToSection' === $json_instruction['@type'] ) {
						$group['name'] = isset( $json_instruction['name'] ) ? $json_instruction['name'] : '';

						if ( isset( $json_instruction['itemListElement'] ) && is_array( $json_instruction['itemListElement'] ) ) {
							foreach( $json_instruction['itemListElement'] as $item ) {
								if ( isset( $item['text'] ) ) {
									$group['instructions'][] = array(
										'text' => $item['text'],
										'image' => '',
									);
								}
							}

							$instructions[] = $group;
							$group = array(
								'instructions' => array(),
								'name' => '',
							);
						}

					} else {
						if ( isset( $json_instruction['text'] ) ) {
							$group['instructions'][] = array(
								'text' => $json_instruction['text'],
								'image' => '',
							);
						}
					}
				} else {
					$group['instructions'][] = array(
						'text' => $json_instruction,
						'image' => '',
					);
				}
			}
			$instructions[] = $group;
			$recipe['instructions'] = $instructions;

			// Recipe Nutrition.
			$recipe['nutrition'] = array();

			$nutrition_mapping = array(
				'serving_size' => 'servingSize',
				'calories' => 'calories',
				'fat' => 'fatContent',
				'saturated_fat' => 'saturatedFatContent',
				'unsaturated_fat' => 'unsaturatedFatContent',
				'trans_fat' => 'transFatContent',
				'carbohydrates' => 'carbohydrateContent',
				'sugar' => 'sugarContent',
				'fiber' => 'fiberContent',
				'protein' => 'proteinContent',
				'cholesterol' => 'cholesterolContent',
				'sodium' => 'sodiumContent',
			);
			$nutrition = isset( $json_recipe['nutrition'] ) ? $json_recipe['nutrition'] : array();

			foreach ( $nutrition_mapping as $wprm_field => $json_field ) {
				$recipe['nutrition'][ $wprm_field ] = isset( $nutrition[ $json_field ] ) ? $nutrition[ $json_field ] : '';
			}
		} else {
			$recipe = false;
		}

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since	5.8.0
	 * @param	mixed $id ID of the recipe we want replace.
	 * @param	mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		$id_parts = explode( '-', $id, 2 );
		$post_id = intval( $id_parts[0] );
		$recipe_index = intval( $id_parts[1] );

		$post = get_post( $post_id );
		$content = $post->post_content;

		$recipes = $this->get_json_ld_recipes( $content );
		$json_recipe = isset( $recipes[ $recipe_index ] ) ? $recipes[ $recipe_index ] : false;

		$content = str_replace( $json_recipe['html'], '[wprm-recipe id="' . $wprm_id . '"]', $content );

		$update_content = array(
			'ID' => $post_id,
			'post_content' => $content,
		);
		wp_update_post( $update_content );

		// Remove from found recipes.
		$found_recipes = get_option( 'wprm_import_json_ld_recipes', array() );
		unset( $found_recipes[ $id ] );
		update_option( 'wprm_import_json_ld_recipes', $found_recipes, false );
	}

	/**
	 * Get WordPress.com recipes that are used in this content.
	 *
	 * @since	5.8.0
	 * @param	mixed $content Content to find recipes in.
	 */
	private function get_json_ld_recipes( $content ) {
		$found_recipes = array();

		if ( preg_match_all( '/<script([^>]*)>(.*?)<\/script>/msi', $content, $matches ) && array_key_exists( 1, $matches ) ) {
			foreach ( $matches[1] as $key => $value ) {
				if ( false !== stripos( $value, 'application/ld+json' ) ) {
					$script_inner = $matches[2][ $key ];

					if ( $script_inner ) {
						$json = json_decode( $script_inner, true );
				
						if ( $json && isset( $json['@type'] ) && 'recipe' === strtolower( $json['@type'] ) ) {
							$found_recipes[] = array(
								'html' => $matches[0][ $key ],
								'json' => $json,
							);
						}
					}
				}
			}
		}

		return $found_recipes;
	}

	/**
	 * Convert time metadata to minutes.
	 *
	 * @since    5.8.0
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
