<?php
/**
 * Simple Recipe Pro importer.
 *
 * @link       http://bootstrapped.ventures
 * @since      2.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Simple Recipe Pro importer.
 *
 * @since      2.1.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Simplerecipepro extends WPRM_Import {

	/**
	 * Get the UID of this import source.
	 *
	 * @since	2.1.0
	 */
	public function get_uid() {
		return 'simplerecipepro';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since	2.1.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since	2.1.0
	 */
	public function get_name() {
		return 'Simple Recipe Pro';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since	2.1.0
	 */
	public function get_settings_html() {
		return '';
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since	2.1.0
	 */
	public function get_recipe_count() {
		$args = array(
			'post_type' => 'post',
			'post_status' => 'any',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key'     => '_simple_recipe_pro_recipename',
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
	 * @since	2.1.0
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
						'key'     => '_simple_recipe_pro_recipename',
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
					'name' => get_post_meta( $post->ID, '_simple_recipe_pro_recipename', true ),
					'url' => get_edit_post_link( $post->ID ),
				);
			}
		}

		return $recipes;
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    2.1.0
	 * @param	 mixed $id ID of the recipe we want to import.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$recipe = array(
			'import_id' => 0,
			'import_backup' => array(
				'simplerecipepro_post_id' => intval( $id ),
			),
		);

		$post = get_post( $id );
		$post_meta = get_post_custom( $id );

		// Take over these fields.
		$recipe['name'] = $post_meta['_simple_recipe_pro_recipename'][0];
		$recipe['summary'] = $post_meta['_simple_recipe_pro_description'][0];
		$recipe['notes'] = $post_meta['_simple_recipe_pro_notes'][0];

		// Recipe image.
		$image_url = $post_meta['_simple_recipe_pro_image'][0];
		if ( $image_url ) {
			$image_id = WPRM_Import_Helper::get_or_upload_attachment( $id, $image_url );

			if ( $image_id ) {
				$recipe['image_id'] = $image_id;
			}
		}

		// Author.
		$recipe['author_name'] = $post_meta['_simple_recipe_pro_author'][0];

		if ( '' !== trim( $recipe['author_name'] ) ) {
			$recipe['author_display'] = 'custom';
		}

		// Servings.
		$simplerecipepro_servings = $post_meta['_simple_recipe_pro_servings'][0];

		$match = preg_match( '/^\s*\d+/', $simplerecipepro_servings, $servings_array );
		if ( 1 === $match ) {
				$servings = str_replace( ' ','', $servings_array[0] );
		} else {
				$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $simplerecipepro_servings );

		$recipe['servings'] = $servings;
		$recipe['servings_unit'] = $servings_unit;

		// Recipe Tags.
		$simplerecipepro_cuisines = $post_meta['_simple_recipe_pro_cuisine'][0];
		$wprm_field = str_replace( ';', ',', $simplerecipepro_cuisines );
		$cuisines = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $wprm_field );
		$cuisines = '' === $cuisines[0] ? array() : $cuisines;

		$recipe['tags'] = array(
			'cuisine' => $cuisines,
		);

		// Recipe Times.
		$prep_time = max( 0, $this->simple_recipe_pro_read_time( $post_meta['_simple_recipe_pro_prep_time'][0], 0) ) / 60;
		$cook_time = max( 0, $this->simple_recipe_pro_read_time( $post_meta['_simple_recipe_pro_cook_time'][0], 0) ) / 60;
		$wait_time = max( 0, $this->simple_recipe_pro_read_time( $post_meta['_simple_recipe_pro_wait_time'][0], 0) ) / 60;
		$total_time = max( 0, $this->simple_recipe_pro_read_time( $post_meta['_simple_recipe_pro_total_time'][0], 0) ) / 60;

		$recipe['prep_time'] = $prep_time;
		$recipe['cook_time'] = $cook_time;

		if ( $total_time ) {
			$recipe['total_time'] = $total_time;
		} else {
			$recipe['total_time'] = $prep_time + $cook_time + $wait_time;
		}

		// Recipe Ingredients.
		$ingredients = maybe_unserialize( $post_meta['_simple_recipe_pro_ingredients'][0] );

		// Backwards compatibility fix.
		if ( is_string( $ingredients ) ) {
			$ingredients = str_replace( '\&quot;', '/quot/', $ingredients );
			$ingredients = array_map( array( $this, 'fix_ingredients' ), json_decode( $ingredients, true ) );
		}

		$recipe['ingredients'] = array();

		$current_group = array(
			'name' => '',
			'ingredients' => array(),
		);
		foreach ( $ingredients as $ingredient ) {
			if ( ':' === substr( $ingredient['ingredient'], -1, 1 ) ) {
				$recipe['ingredients'][] = $current_group;
				$current_group = array(
					'name' => substr( $ingredient['ingredient'], 0, strlen( $ingredient['ingredient'] ) -1 ),
					'ingredients' => array(),
				);
			} else {
				$current_group['ingredients'][] = array(
					'raw' => trim( $ingredient['portion'] ) . ' ' . trim( $ingredient['ingredient'] ),
				);
			}
		}
		$recipe['ingredients'][] = $current_group;

		// Instructions.
		$simplerecipepro_instructions = $this->parse_instructions( $post_meta['_simple_recipe_pro_directions'][0] );
		$instructions = array();

		foreach ( $simplerecipepro_instructions as $simplerecipepro_group ) {
			$group = array(
				'name' => $simplerecipepro_group['name'],
				'instructions' => array(),
			);

			foreach ( $simplerecipepro_group['items'] as $simplerecipepro_item ) {
				$text = trim( strip_tags( $simplerecipepro_item, '<a><strong><b><em><i><u><sub><sup>' ) );

				// Find any images.
				preg_match_all( '/<img[^>]+>/i', $simplerecipepro_item, $img_tags );

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
		$simplerecipepro_nutrition = $post_meta['_simple_recipe_pro_nutrition_data'][0];
		$simplerecipepro_nutrition = str_replace( '\&quot;', '/quot/', $simplerecipepro_nutrition );
		$simplerecipepro_nutrition = json_decode( $simplerecipepro_nutrition, true );

		// Serving Size.
		$match = preg_match( '/^\s*\d+/', $simplerecipepro_nutrition['servingsize'], $servings_array );
		if ( 1 === $match ) {
			$servings = str_replace( ' ','', $servings_array[0] );
		} else {
			$servings = '';
		}
		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $simplerecipepro_nutrition['servingsize'] );

		$recipe['nutrition']['serving_size'] = $servings;
		$recipe['nutrition']['serving_unit'] = $servings_unit;

		// Other nutrition fields.
		$nutrition_mapping = array(
			'calories'              => 'calories',
			'totalfat'              => 'fat',
			'saturatedfat'          => 'saturated_fat',
			'cholesterol'           => 'cholesterol',
			'sodium'                => 'sodium',
			'carbohydrate'          => 'carbohydrates',
			'dietaryfiber'          => 'fiber',
			'sugars'                => 'sugar',
			'protein'               => 'protein',
		);

		foreach ( $nutrition_mapping as $simplerecipepro_field => $wprm_field ) {
			$recipe['nutrition'][ $wprm_field ] = isset( $simplerecipepro_nutrition[ $simplerecipepro_field ] ) ? $simplerecipepro_nutrition[ $simplerecipepro_field ] : '';
		}

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    2.1.0
	 * @param	 mixed $id ID of the recipe we want replace.
	 * @param	 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		$post = get_post( $id );

		// Hide this post from to import list.
		$name = get_post_meta( $id, '_simple_recipe_pro_recipename', true );
		add_post_meta( $id, '_simple_recipe_pro_recipename_bkp', $name );
		delete_post_meta( $id, '_simple_recipe_pro_recipename' );

		// Store reference to WPRM recipe.
		add_post_meta( $id, '_simple_recipe_pro_wprm_migrated', $wprm_id );

		// Update or add shortcode.
		$content = $post->post_content;

		$content = str_ireplace( '[simple-recipe]', '[wprm-recipe id="' . $wprm_id . '"]', $content );
		$content = preg_replace( "/\[simple-recipe\s.*?id='?\"?" . $id . '.*?]/im', '[wprm-recipe id="' . $wprm_id . '"]', $content );

		$update_content = array(
			'ID' => $id,
			'post_content' => $content,
		);
		wp_update_post( $update_content );

		// Migrate user ratings.
		$user_ratings = get_post_meta( $id, '_ratings', true );

		if ( $user_ratings ) {
			$user_ratings = json_decode( $user_ratings, true );

			foreach ( $user_ratings as $user_or_ip => $rating_value ) {
				if ( '' . intval( $user_or_ip ) === '' . $user_or_ip ) {
					$rating = array(
						'recipe_id' => $wprm_id,
						'user_id' => $user_or_ip,
						'ip' => '',
						'rating' => $rating_value,
					);
				} else {
					$rating = array(
						'recipe_id' => $wprm_id,
						'user_id' => 0,
						'ip' => $user_or_ip,
						'rating' => $rating_value,
					);
				}

				WPRM_Rating_Database::add_or_update_rating( $rating );
			}
		}
	}

	/**
	 * Simple Recipe Pro string fix.
	 *
	 * @since	2.1.0
	 * @param	mixed $ingredients Ingredients to fix.
	 */
	private function fix_ingredients( $ingredients ) {
		$ingredients['ingredient'] = str_replace( 'u0027', "'", $ingredients['ingredient'] );
		$ingredients['ingredient'] = str_replace( '/quot/', '"', $ingredients['ingredient'] );
		return $ingredients;
	}

	/**
	 * Blob to array.
	 *
	 * @since	2.1.0
	 * @param	mixed $instructions Instructions to parse.
	 */
	private function parse_instructions( $instructions ) {
		// Introduce linebreaks.
		$instructions = str_ireplace( '<p>', '', $instructions );
		$instructions = str_ireplace( '</p>', PHP_EOL, $instructions );
		$instructions = str_ireplace( '<br>', PHP_EOL, $instructions );
		$instructions = str_ireplace( '<br/>', PHP_EOL, $instructions );
		$instructions = str_ireplace( '<ol>', '', $instructions );
		$instructions = str_ireplace( '</ol>', '', $instructions );
		$instructions = str_ireplace( '<ul>', '', $instructions );
		$instructions = str_ireplace( '</ul>', '', $instructions );
		$instructions = str_ireplace( '<li>', '', $instructions );
		$instructions = str_ireplace( '</li>', PHP_EOL, $instructions );
		$instructions = str_ireplace( '<div>', '', $instructions );
		$instructions = str_ireplace( '</div>', PHP_EOL, $instructions );
		$instructions = preg_replace('/<\/h[1-6]>/im', '$0' . PHP_EOL, $instructions);

		// Convert to array.
		$component_list = array();
		$component_group = array(
			'name' => '',
			'items' => array(),
		);

		$bits = explode( PHP_EOL, $instructions );
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

	/**
	 * Simple Recipe Pro Read Time function.
	 *
	 * @since    2.1.0
	 * @param	 mixed $time  Time to parse.
	 * @param	 mixed $start Start time.
	 */
	private function simple_recipe_pro_read_time( $time, $start ) {
		$time = str_replace( 'hrs', 'hour', $time );
		$time = str_replace( 'hr', 'hour', $time );
		$time = str_replace( 'minutes', 'min', $time );
		$time = str_replace( 'mins', 'min', $time );

		if ( is_numeric( $time ) ) {
			$time = "{$time} minutes";
		}

		return strtotime( $time, $start );
	}
}
