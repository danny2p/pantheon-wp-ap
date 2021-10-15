<?php
/**
 * Responsible for importing Tasty recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.23.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing Tasty recipes.
 *
 * @since      1.23.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Tasty extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    1.23.0
	 */
	public function get_uid() {
		return 'tasty';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    1.23.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    1.23.0
	 */
	public function get_name() {
		return 'Tasty Recipes';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    1.23.0
	 */
	public function get_settings_html() {
		return '';
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    1.23.0
	 */
	public function get_recipe_count() {
		$args = array(
			'post_type' => 'tasty_recipe',
			'post_status' => 'any',
			'posts_per_page' => 1,
		);

		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    1.23.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$recipes = array();

		$limit = 100;
		$offset = $limit * $page;

		$args = array(
			'post_type' => 'tasty_recipe',
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
	 * @since    1.23.0
	 * @param	 mixed $id ID of the recipe we want to import.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$recipe = array(
			'import_id' => $id,
			'import_backup' => array(),
		);

		$post = get_post( $id );

		// Featured Image.
		$recipe['image_id'] = get_post_thumbnail_id( $id );

		// Simple Matching.
		$recipe['name'] = $post->post_title;
		$recipe['summary'] = get_post_meta( $id, 'description', true );
		$recipe['notes'] = get_post_meta( $id, 'notes', true );
		$recipe['author_name'] = get_post_meta( $id, 'author_name', true );

		if ( $recipe['author_name'] ) {
			$recipe['author_display'] = 'custom';
		}

		// Servings.
		$tasty_yield = get_post_meta( $id, 'yield', true );
		$match = preg_match( '/^\s*\d+/', $tasty_yield, $servings_array );
		if ( 1 === $match ) {
				$servings = str_replace( ' ','', $servings_array[0] );
		} else {
				$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $tasty_yield );

		$recipe['servings'] = $servings;
		$recipe['servings_unit'] = $servings_unit;

		// Recipe times.
		$recipe['prep_time'] = $this->get_minutes_for_time( get_post_meta( $id, 'prep_time', true ) );
		$recipe['cook_time'] = $this->get_minutes_for_time( get_post_meta( $id, 'cook_time', true ) );
		$recipe['total_time'] = $this->get_minutes_for_time( get_post_meta( $id, 'total_time', true ) );

		// Recipe tags.
		$recipe['tags'] = array();
		$recipe['tags']['course'] = array_map( 'trim', explode( ',', get_post_meta( $id, 'category', true ) ) );
		$recipe['tags']['cuisine'] = array_map( 'trim', explode( ',', get_post_meta( $id, 'cuisine', true ) ) );
		$recipe['tags']['keyword'] = array_map( 'trim', explode( ',', get_post_meta( $id, 'keywords', true ) ) );

		// Ingredients.
		$tasty_ingredients = $this->parse_recipe_component_list( get_post_meta( $id, 'ingredients', true ) );

		$ingredients = array();

		foreach ( $tasty_ingredients as $tasty_group ) {
			$group = array(
				'name' => $tasty_group['name'],
				'ingredients' => array(),
			);

			foreach ( $tasty_group['items'] as $tasty_item ) {
				$text = trim( strip_tags( $tasty_item, '<a>' ) );

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
		$tasty_instructions = $this->parse_recipe_component_list( get_post_meta( $id, 'instructions', true ) );

		$instructions = array();

		foreach ( $tasty_instructions as $tasty_group ) {
			$group = array(
				'name' => $tasty_group['name'],
				'instructions' => array(),
			);

			foreach ( $tasty_group['items'] as $tasty_item ) {
				$text = trim( strip_tags( $tasty_item, '<a><strong><b><em><i><u><sub><sup>' ) );

				// Find any images.
				preg_match_all( '/<img[^>]+>/i', $tasty_item, $img_tags );

				foreach ( $img_tags[0] as $img_tag ) {
					preg_match_all( '/src="([^"]*)"/i', $img_tag, $img );

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

				if ( ! empty( $text ) ) {
					$group['instructions'][] = array(
						'text' => $text,
					);
				}
			}

			$instructions[] = $group;
		}
		$recipe['instructions'] = $instructions;

		// Video.
		$recipe['video_embed'] = get_post_meta( $id, 'video_url', true );

		// Nutrition Facts.
		$recipe['nutrition'] = array();

		// Serving size.
		$tasty_serving_size = get_post_meta( $id, 'serving_size', true );
		$match = preg_match( '/^\s*\d+/', $tasty_serving_size, $servings_array );
		if ( 1 === $match ) {
				$servings = str_replace( ' ','', $servings_array[0] );
		} else {
				$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $tasty_serving_size );

		$recipe['nutrition']['serving_size'] = $servings;
		$recipe['nutrition']['serving_unit'] = $servings_unit;

		$recipe['nutrition']['calories'] = get_post_meta( $id, 'calories', true );
		$recipe['nutrition']['sugar'] = get_post_meta( $id, 'sugar', true );
		$recipe['nutrition']['sodium'] = get_post_meta( $id, 'sodium', true );
		$recipe['nutrition']['fat'] = get_post_meta( $id, 'fat', true );
		$recipe['nutrition']['saturated_fat'] = get_post_meta( $id, 'saturated_fat', true );
		$recipe['nutrition']['polyunsaturated_fat'] = get_post_meta( $id, 'unsaturated_fat', true );
		$recipe['nutrition']['trans_fat'] = get_post_meta( $id, 'trans_fat', true );
		$recipe['nutrition']['carbohydrates'] = get_post_meta( $id, 'carbohydrates', true );
		$recipe['nutrition']['fiber'] = get_post_meta( $id, 'fiber', true );
		$recipe['nutrition']['protein'] = get_post_meta( $id, 'protein', true );
		$recipe['nutrition']['cholesterol'] = get_post_meta( $id, 'cholesterol', true );

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    1.23.0
	 * @param	 mixed $id ID of the recipe we want replace.
	 * @param	 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		// We don't know which posts use this recipe so we rely on the fallback shortcode.
	}

	/**
	 * Custom strtotime function for Tasty format.
	 *
	 * @since    1.23.0
	 * @param	 mixed $time Time to get in minutes.
	 * @param	 mixed $now Time now.
	 */
	public static function strtotime( $time, $now = null ) {
		if ( null === $now ) {
			$now = time();
		}
		// Parse string to remove any info in parentheses.
		$time = preg_replace( '/\([^\)]+\)/' , '' , $time );
		return strtotime( $time, $now );
	}


	/**
	 * Get the time in minutes.
	 *
	 * @since    1.23.0
	 * @param	 mixed $time Time to get in minutes.
	 */
	private function get_minutes_for_time( $time ) {
		if ( ! $time ) {
			return 0;
		}

		// Assume a number is minutes.
		if ( is_numeric( $time ) ) {
			$time = "{$time} minutes";
		}
		$now = time();
		$time = $this->strtotime( $time, $now );

		return ( $time - $now ) / 60;
	}

	/**
	 * Blob to array.
	 *
	 * @since    1.23.0
	 * @param	 mixed $component Component to parse.
	 */
	private function parse_recipe_component_list( $component ) {
		$component_list = array();
		$component_group = array(
			'name' => '',
			'items' => array(),
		);

		// Make sure to split list items.
		$component = str_ireplace( '</p>', '</p>' . PHP_EOL, $component );
		$component = str_ireplace( '</li>', '</li>' . PHP_EOL, $component );
		$component = str_ireplace( '</ul>', '</ul>' . PHP_EOL, $component );
		$component = str_ireplace( '</ol>', '</ol>' . PHP_EOL, $component );

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
