<?php
/**
 * Purr importer.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Purr importer.
 *
 * @since      3.1.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Purr extends WPRM_Import {

	/**
	 * Get the UID of this import source.
	 *
	 * @since	3.1.0
	 */
	public function get_uid() {
		return 'purr';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since	3.1.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since	3.1.0
	 */
	public function get_name() {
		return 'Purr Recipes';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since	3.1.0
	 */
	public function get_settings_html() {
		return '';
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since	3.1.0
	 */
	public function get_recipe_count() {
		$args = array(
			'post_type' => 'post',
			'post_status' => 'any',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key'     => 'recipe_directions',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'recipe_title',
					'compare' => '!=',
					'value' => '',
				),
			),
		);

		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since	3.1.0
	 * @param	int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$recipes = array();

		$limit = 100;
		$offset = $limit * $page;

		$args = array(
				'post_type' => 'post',
				'post_status' => 'any',
				'meta_query' => array(
					array(
						'key'     => 'recipe_directions',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'recipe_title',
						'compare' => '!=',
						'value' => '',
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
	 * @since	3.1.0
	 * @param	mixed $id ID of the recipe we want to import.
	 * @param	array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$recipe = array(
			'import_id' => 0,
			'import_backup' => array(
			),
		);

		$post = get_post( $id );
		$post_meta = get_post_custom( $id );

		// Take over these fields.
		$recipe['name'] = isset( $post_meta['recipe_title'] ) ? $post_meta['recipe_title'][0] : '';
		$recipe['summary'] = isset( $post_meta['recipe_meta'] ) ? $post_meta['recipe_meta'][0] : '';
		$recipe['notes'] = isset( $post_meta['recipe_notes'] ) ? $post_meta['recipe_notes'][0] : '';
		$recipe['image_id'] = get_post_thumbnail_id( $id );

		// Servings.
		$purr_yield = isset( $post_meta['recipe_yield'] ) ? $post_meta['recipe_yield'][0] : '';

		$match = preg_match( '/^\s*\d+/', $purr_yield, $servings_array );
		if ( 1 === $match ) {
				$servings = str_replace( ' ','', $servings_array[0] );
		} else {
				$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $purr_yield );

		$recipe['servings'] = $servings;
		$recipe['servings_unit'] = $servings_unit;

		// Recipe Times.
		$purr_prep_time = isset( $post_meta['recipe_prep'] ) ? $post_meta['recipe_prep'][0] : '';
		$purr_cook_time = isset( $post_meta['recipe_cook'] ) ? $post_meta['recipe_cook'][0] : '';
		$purr_total_time = isset( $post_meta['recipe_time'] ) ? $post_meta['recipe_time'][0] : '';

		$recipe['prep_time'] = $this->time_to_minutes( $purr_prep_time );
		$recipe['cook_time'] = $this->time_to_minutes( $purr_cook_time );
		$recipe['total_time'] = $this->time_to_minutes( $purr_total_time );

		// Recipe Ingredients.
		$purr_ingredients = isset( $post_meta['recipe_ingredients'] ) ? $post_meta['recipe_ingredients'][0] : '';
		$purr_ingredients = $this->parse_recipe_component_list( $purr_ingredients );

		$ingredients = array();

		foreach ( $purr_ingredients as $purr_group ) {
			$group = array(
				'name' => $purr_group['name'],
				'ingredients' => array(),
			);

			foreach ( $purr_group['items'] as $purr_item ) {
				$text = trim( strip_tags( $purr_item, '<a>' ) );

				if ( ! empty( $text ) ) {
					$group['ingredients'][] = array(
						'raw' => $text,
					);
				}
			}

			$ingredients[] = $group;
		}
		$recipe['ingredients'] = $ingredients;

		// Instructions.
		$purr_instructions = isset( $post_meta['recipe_directions'] ) ? $post_meta['recipe_directions'][0] : '';
		$purr_instructions = $this->parse_recipe_component_list( $purr_instructions );

		$instructions = array();

		foreach ( $purr_instructions as $purr_group ) {
			$group = array(
				'name' => $purr_group['name'],
				'instructions' => array(),
			);

			foreach ( $purr_group['items'] as $purr_item ) {
				$text = trim( strip_tags( $purr_item, '<a><strong><b><em><i><u><sub><sup>' ) );

				// Find any images.
				preg_match_all( '/<img[^>]+>/i', $purr_item, $img_tags );

				foreach ( $img_tags[0] as $img_tag ) {
					if ( $img_tag ) {
						preg_match_all( '/src="([^"]*)"/i', $img_tag[0], $img );

						if ( $img[1] ) {
							$img_src = $img[1][0];
							$image_id = WPRM_Import_Helper::get_or_upload_attachment( $id, $img_src );

							if ( $image_id ) {
								$group['instructions'][] = array(
									'text' => $text,
									'image' => $image_id,
								);
								$text = ''; // Only add same text once.
							}
						}
					}
				}

				if ( ! empty( $text ) ) {
					$group['instructions'][] = array(
						'text' => $text,
					);
				}
			}

			$instructions[] = $group;
		}
		$recipe['instructions'] = $instructions;

		// Nutrition Facts.
		$recipe['nutrition'] = array();

		// Serving size.
		$purr_serving_size = isset( $post_meta['serving_size'] ) ? $post_meta['serving_size'][0] : '';
		$match = preg_match( '/^\s*\d+/', $purr_serving_size, $servings_array );
		if ( 1 === $match ) {
				$servings = str_replace( ' ','', $servings_array[0] );
		} else {
				$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $purr_serving_size );

		$recipe['nutrition']['serving_size'] = $servings;
		$recipe['nutrition']['serving_unit'] = $servings_unit;

		$recipe['nutrition']['calories'] = isset( $post_meta['calories'] ) ? $post_meta['calories'][0] : '';
		$recipe['nutrition']['sugar'] = isset( $post_meta['nutrition_sugar'] ) ? $post_meta['nutrition_sugar'][0] : '';
		$recipe['nutrition']['sodium'] = isset( $post_meta['nutrition_sodium'] ) ? $post_meta['nutrition_sodium'][0] : '';
		$recipe['nutrition']['fat'] = isset( $post_meta['nutrition_fat'] ) ? $post_meta['nutrition_fat'][0] : '';
		$recipe['nutrition']['saturated_fat'] = isset( $post_meta['nutrition_saturated'] ) ? $post_meta['nutrition_saturated'][0] : '';
		$recipe['nutrition']['carbohydrates'] = isset( $post_meta['nutrition_carbohydrates'] ) ? $post_meta['nutrition_carbohydrates'][0] : '';
		$recipe['nutrition']['fiber'] = isset( $post_meta['nutrition_fiber'] ) ? $post_meta['nutrition_fiber'][0] : '';
		$recipe['nutrition']['protein'] = isset( $post_meta['nutrition_protein'] ) ? $post_meta['nutrition_protein'][0] : '';
		$recipe['nutrition']['cholesterol'] = isset( $post_meta['nutrition_cholesterol'] ) ? $post_meta['nutrition_cholesterol'][0] : '';

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since	3.1.0
	 * @param	mixed $id ID of the recipe we want replace.
	 * @param	mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		$post = get_post( $id );

		// Hide Purr.
		$recipe_title = get_post_meta( $id, 'recipe_title', true );
		add_post_meta( $id, 'recipe_title_bkp', $recipe_title );
		delete_post_meta( $id, 'recipe_title' );

		// Update or add shortcode.
		$content = $post->post_content;

		if ( 0 === substr_count( $content, '[recipe]' ) ) {
			$content .= ' [wprm-recipe id="' . $wprm_id . '"]';
		} else {
			$content = str_ireplace( '[recipe]', '[wprm-recipe id="' . $wprm_id . '"]', $content );
		}

		$update_content = array(
			'ID' => $id,
			'post_content' => $content,
		);
		wp_update_post( $update_content );
	}

	/**
	 * Convert time field to minutes.
	 *
	 * @since    3.1.0
	 * @param	 mixed $time_string Time to convert.
	 */
	private function time_to_minutes( $time_string ) {
		$time = strtotime( $time_string, 0 );

		if ( $time ) {
			return intval( ceil( $time / 60 ) );
		} else {
			return 0;
		}
	}

	/**
	 * Blob to array.
	 *
	 * @since    3.1.0
	 * @param	 mixed $component Component to parse.
	 */
	private function parse_recipe_component_list( $component ) {
		$component_list = array();
		$component_group = array(
			'name' => '',
			'items' => array(),
		);

		$bits = explode( PHP_EOL, $component );
		foreach ( $bits as $bit ) {

			$test_bit = trim( $bit );
			if ( empty( $test_bit ) ) {
				continue;
			}
			if ( WPRM_Import_Helper::is_heading( $bit ) ) {
				$component_list[] = $component_group;

				$component_group = array(
					'name' => strip_tags( trim( $bit ) ),
					'items' => array(),
				);
			} else {
				$component_group['items'][] = trim( $bit );
			}
		}

		$component_list[] = $component_group;

		return $component_list;
	}
}
