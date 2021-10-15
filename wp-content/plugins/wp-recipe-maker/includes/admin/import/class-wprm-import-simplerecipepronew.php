<?php
/**
 * Simple Recipe Pro importer for new format.
 *
 * @link       http://bootstrapped.ventures
 * @since      2.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Simple Recipe Pro importer for new format.
 *
 * @since      2.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Simplerecipepronew extends WPRM_Import {

	/**
	 * Get the UID of this import source.
	 *
	 * @since	2.3.0
	 */
	public function get_uid() {
		return 'simplerecipepro-new';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since	2.3.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since	2.3.0
	 */
	public function get_name() {
		return 'Simple Recipe Pro (New format)';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since	2.3.0
	 */
	public function get_settings_html() {
		return '';
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since	2.3.0
	 */
	public function get_recipe_count() {
		$args = array(
			'post_type' => 'post',
			'post_status' => 'any',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key'     => 'simple_recipe_index',
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
	 * @since	2.3.0
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
						'key'     => 'simple_recipe_index',
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
				$srp_index = get_post_meta( $post->ID, 'simple_recipe_index', true );
				$srp_recipes = array_map( 'trim', explode( ',', $srp_index ) );

				foreach( $srp_recipes as $srp_recipe_id ) {
					if ( $srp_recipe_id ) {
						$srp_shortcode = '[simple-recipe:' . $post->ID . $srp_recipe_id . ']';
						$srp_recipe_data = get_post_meta( $post->ID, $srp_shortcode, true );

						if ( $srp_recipe_data ) {
							$srp_recipe = $this->srp_read_recipe( $srp_recipe_data );
							$recipes[ $post->ID . '|' . $srp_recipe_id ] = array(
								'name' => $srp_recipe['Name'],
								'url' => get_edit_post_link( $post->ID ),
							);
						}
					}
				} 
			}
		}

		return $recipes;
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    2.3.0
	 * @param	 mixed $id ID of the recipe we want to import.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$id_parts = explode( '|', $id );
		$post_id = intval( $id_parts[0] );
		$recipe_id = $id_parts[1];

		$recipe = array(
			'import_id' => 0,
			'import_backup' => array(
				'simplerecipepro_post_id' => $post_id,
				'simplerecipepro_recipe_id' => $recipe_id,
			),
		);

		$srp_shortcode = '[simple-recipe:' . $post_id . $recipe_id . ']';
		$srp_recipe_data = get_post_meta( $post_id, $srp_shortcode, true );
		$srp_recipe = $this->srp_read_recipe( $srp_recipe_data );

		// Take over these fields.
		$recipe['name'] = $srp_recipe['Name'];
		$recipe['summary'] = $srp_recipe['Description'];
		$recipe['notes'] = $srp_recipe['Notes'];

		// Recipe image.
		$image_url = $srp_recipe['Image'];
		if ( $image_url ) {
			$image_id = WPRM_Import_Helper::get_or_upload_attachment( $id, $image_url );

			if ( $image_id ) {
				$recipe['image_id'] = $image_id;
			}
		}

		// Author.
		$recipe['author_name'] = $srp_recipe['By'];

		if ( '' !== trim( $recipe['author_name'] ) ) {
			$recipe['author_display'] = 'custom';
		}

		// Servings.
		$simplerecipepro_servings = $srp_recipe['Total Servings'];

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
		$simplerecipepro_cuisines = $srp_recipe['Cuisine'];
		$wprm_field = str_replace( ';', ',', $simplerecipepro_cuisines );
		$cuisines = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $wprm_field );
		$cuisines = '' === $cuisines[0] ? array() : $cuisines;

		$simplerecipepro_courses = $srp_recipe['Recipe Type'];
		$wprm_field = str_replace( ';', ',', $simplerecipepro_courses );
		$courses = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $wprm_field );
		$courses = '' === $courses[0] ? array() : $courses;

		$recipe['tags'] = array(
			'cuisine' => $cuisines,
			'course' => $courses,
		);

		// Recipe Times.
		$prep_time = max( 0, $this->simple_recipe_pro_read_time( $srp_recipe['Prep Time'], 0) ) / 60;
		$cook_time = max( 0, $this->simple_recipe_pro_read_time( $srp_recipe['Cook Time'], 0) ) / 60;
		$wait_time = max( 0, $this->simple_recipe_pro_read_time( $srp_recipe['Wait Time'], 0) ) / 60;
		$total_time = max( 0, $this->simple_recipe_pro_read_time( $srp_recipe['Total Time'], 0) ) / 60;

		$recipe['prep_time'] = $prep_time;
		$recipe['cook_time'] = $cook_time;

		if ( $total_time ) {
			$recipe['total_time'] = $total_time;
		} else {
			$recipe['total_time'] = $prep_time + $cook_time + $wait_time;
		}

		// Recipe Ingredients.
		$simplerecipepro_ingredients = $this->parse_blob( $srp_recipe['Ingredients'] );
		$ingredients = array();

		foreach ( $simplerecipepro_ingredients as $simplerecipepro_group ) {
			$group = array(
				'name' => $simplerecipepro_group['name'],
				'ingredients' => array(),
			);

			foreach ( $simplerecipepro_group['items'] as $simplerecipepro_item ) {
				$text = trim( strip_tags( $simplerecipepro_item, '<a>' ) );

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
		$simplerecipepro_instructions = $this->parse_blob( $srp_recipe['Directions'] );
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

		// Serving Size.
		$match = preg_match( '/^\s*\d+/', $srp_recipe['Serving Size'], $servings_array );
		if ( 1 === $match ) {
			$servings = str_replace( ' ','', $servings_array[0] );
		} else {
			$servings = '';
		}
		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $srp_recipe['Serving Size'] );

		$recipe['nutrition']['serving_size'] = $servings;
		$recipe['nutrition']['serving_unit'] = $servings_unit;

		// Other nutrition fields.
		$nutrition_mapping = array(
			'Calories'				=> 'calories',
			'Total Fat'             => 'fat',
			'Saturated Fat'         => 'saturated_fat',
			'Cholesterol'           => 'cholesterol',
			'Sodium'                => 'sodium',
			'Carbohydrate'          => 'carbohydrates',
			'Dietary Fiber'         => 'fiber',
			'Sugars'                => 'sugar',
			'Protein'               => 'protein',
		);

		foreach ( $nutrition_mapping as $simplerecipepro_field => $wprm_field ) {
			$recipe['nutrition'][ $wprm_field ] = isset( $srp_recipe[ $simplerecipepro_field ] ) ? $srp_recipe[ $simplerecipepro_field ] : '';
		}

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    2.3.0
	 * @param	 mixed $id ID of the recipe we want replace.
	 * @param	 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		$id_parts = explode( '|', $id );
		$post_id = intval( $id_parts[0] );
		$recipe_id = $id_parts[1];

		$post = get_post( $post_id );
		$srp_shortcode = '[simple-recipe:' . $post_id . $recipe_id . ']';

		// Hide this post from to import list.
		$index = get_post_meta( $post_id, 'simple_recipe_index', true );
		add_post_meta( $post_id, 'simple_recipe_index_bkp', $index );
		delete_post_meta( $post_id, 'simple_recipe_index' );

		// Store reference to WPRM recipe.
		add_post_meta( $post_id, '_simple_recipe_pro_wprm_migrated', $wprm_id );

		// Update or add shortcode.
		$content = $post->post_content;

		$content = str_ireplace( $srp_shortcode, '[wprm-recipe id="' . $wprm_id . '"]', $content );
		$content = str_ireplace( '[simple-recipe]', '[wprm-recipe id="' . $wprm_id . '"]', $content );
		$content = preg_replace( "/\[simple-recipe\s.*?id='?\"?" . $post_id . '.*?]/im', '[wprm-recipe id="' . $wprm_id . '"]', $content );

		$update_content = array(
			'ID' => $post_id,
			'post_content' => $content,
		);
		wp_update_post( $update_content );

		// Migrate user ratings.
		$user_ratings = get_post_meta( $post_id, '_ratings', true );

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
	 * Simple Recipe Pro read recipe function.
	 *
	 * @since	2.3.0
	 * @param	mixed $recipe Recipe to read.
	 */
	private function srp_read_recipe( $recipe ) {
		$postdata = array();
		$recipe = preg_replace( '/\;([\w\s]*)\:/', '<field>$1<value>', $recipe );
		$fields = explode( '<field>', $recipe );

		foreach ( $fields as $field ) {
		    $part = explode( '<value>', $field );
		    if ( isset( $part[0] ) && ! empty( $part[0] ) ) {
				$postdata[ $part[0] ] = isset( $part[1] ) ? trim( $part[1] ) : '';
			}
		}

		return $postdata;
	}

	/**
	 * Blob to array.
	 *
	 * @since	2.3.0
	 * @param	mixed $blob Blog to parse.
	 */
	private function parse_blob( $blob ) {
		// Introduce linebreaks.
		$blob = str_ireplace( '<p>', '', $blob );
		$blob = str_ireplace( '</p>', PHP_EOL, $blob );
		$blob = str_ireplace( '<br>', PHP_EOL, $blob );
		$blob = str_ireplace( '<br/>', PHP_EOL, $blob );
		$blob = str_ireplace( '<ol>', '', $blob );
		$blob = str_ireplace( '</ol>', '', $blob );
		$blob = str_ireplace( '<ul>', '', $blob );
		$blob = str_ireplace( '</ul>', '', $blob );
		$blob = str_ireplace( '<li>', '', $blob );
		$blob = str_ireplace( '</li>', PHP_EOL, $blob );
		$blob = str_ireplace( '<div>', '', $blob );
		$blob = str_ireplace( '</div>', PHP_EOL, $blob );
		$blob = preg_replace('/<\/h[1-6]>/im', '$0' . PHP_EOL, $blob);

		// Convert to array.
		$component_list = array();
		$component_group = array(
			'name' => '',
			'items' => array(),
		);

		$bits = explode( PHP_EOL, $blob );
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
	 * @since    2.3.0
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
