<?php
/**
 * Responsible for importing Cookbook recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      2.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing Cookbook recipes.
 *
 * @since      2.1.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Cookbook extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since	2.1.0
	 */
	public function get_uid() {
		return 'cookbook';
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
		return 'Cookbook';
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
			'post_type' => 'cookbook_recipe',
			'post_status' => 'any',
			'posts_per_page' => 1,
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
			'post_type' => 'cookbook_recipe',
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
	 * @since	2.1.0
	 * @param	mixed $id ID of the recipe we want to import.
	 * @param	array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$post = get_post( $id );
		$post_meta = get_post_custom( $id );

		$recipe = array(
			'import_id' => $id,
			'import_backup' => array(),
		);

		// Take over fields.
		$recipe['name'] = $post->post_title;
		$recipe['summary'] = $post->post_content;
		$recipe['image_id'] = get_post_thumbnail_id( $id );
		$recipe['servings'] = isset( $post_meta['cookbook_servings'] ) ? $post_meta['cookbook_servings'][0] : '';
		$recipe['servings_unit'] = isset( $post_meta['cookbook_servings_unit'] ) ? $post_meta['cookbook_servings_unit'][0] : '';
		$recipe['notes'] = isset( $post_meta['cookbook_notes'] ) ? $post_meta['cookbook_notes'][0] : '';

		// Author.
		$recipe['author_name'] = isset( $post_meta['cookbook_author'] ) ? $post_meta['cookbook_author'][0] : '';

		if ( '' !== trim( $recipe['author_name'] ) ) {
			$recipe['author_display'] = 'custom';
		}

		// Recipe Times.
		$prep_time = isset( $post_meta['cookbook_prep_time'] ) ? maybe_unserialize( $post_meta['cookbook_prep_time'][0] ) : array( 'hours' => 0, 'minutes' => 0, 'seconds' => 0 );
		$recipe['prep_time'] = ceil( $prep_time['hours'] * 60 + $prep_time['minutes'] + $prep_time['seconds'] / 60 );

		$cook_time = isset( $post_meta['cookbook_cook_time'] ) ? maybe_unserialize( $post_meta['cookbook_cook_time'][0] ) : array( 'hours' => 0, 'minutes' => 0, 'seconds' => 0 );
		$recipe['cook_time'] = ceil( $cook_time['hours'] * 60 + $cook_time['minutes'] + $cook_time['seconds'] / 60 );

		$inactive_time = isset( $post_meta['cookbook_inactive_time'] ) ? maybe_unserialize( $post_meta['cookbook_inactive_time'][0] ) : array( 'hours' => 0, 'minutes' => 0, 'seconds' => 0 );
		$inactive_time_minutes = ceil( $inactive_time['hours'] * 60 + $inactive_time['minutes'] + $inactive_time['seconds'] / 60 );

		if ( $inactive_time_minutes ) {
			$recipe['custom_time_label'] = __( 'Inactive Time', 'wp-recipe-maker' );
			$recipe['custom_time'] = $inactive_time_minutes;
		}

		$total_time = isset( $post_meta['cookbook_total_time'] ) ? maybe_unserialize( $post_meta['cookbook_total_time'][0] ) : array( 'hours' => 0, 'minutes' => 0, 'seconds' => 0 );
		$recipe['total_time'] = ceil( $total_time['hours'] * 60 + $total_time['minutes'] + $total_time['seconds'] / 60 );

		// Recipe Tags.
		$cookbook_courses = isset( $post_meta['cookbook_course'] ) ? $post_meta['cookbook_course'][0] : '';
		$wprm_field = str_replace( ';', ',', $cookbook_courses );
		$courses = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $wprm_field );
		$courses = '' === $courses[0] ? array() : $courses;

		$cookbook_cuisines = isset( $post_meta['cookbook_cuisine'] ) ? $post_meta['cookbook_cuisine'][0] : '';
		$wprm_field = str_replace( ';', ',', $cookbook_cuisines );
		$cuisines = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $wprm_field );
		$cuisines = '' === $cuisines[0] ? array() : $cuisines;

		$recipe['tags'] = array(
			'course' => $courses,
			'cuisine' => $cuisines,
		);

		// Recipe Ingredients.
		$cookbook_ingredients = isset( $post_meta['cookbook_ingredients'] ) ? maybe_unserialize( $post_meta['cookbook_ingredients'][0] ) : array( 'raw' => '', 'parsed' => array() );
		$cookbook_ingredients = $this->parse_cookbook_structure( $cookbook_ingredients['parsed'] );

		$ingredients = array();

		foreach ( $cookbook_ingredients as $cookbook_group ) {
			$group = array(
				'name' => $cookbook_group['name'],
				'ingredients' => array(),
			);

			foreach ( $cookbook_group['items'] as $cookbook_item ) {
				$text = trim( strip_tags( $cookbook_item, '<a>' ) );

				if ( ! empty( $text ) ) {
					$group['ingredients'][] = array(
						'raw' => $text,
					);
				}
			}

			$ingredients[] = $group;
		}
		$recipe['ingredients'] = $ingredients;

		// Recipe Instructions.
		$cookbook_instructions = isset( $post_meta['cookbook_instructions'] ) ? maybe_unserialize( $post_meta['cookbook_instructions'][0] ) : array( 'raw' => '', 'parsed' => array() );
		$cookbook_instructions = $this->parse_cookbook_structure( $cookbook_instructions['parsed'] );

		$instructions = array();

		foreach ( $cookbook_instructions as $cookbook_group ) {
			$group = array(
				'name' => $cookbook_group['name'],
				'instructions' => array(),
			);

			foreach ( $cookbook_group['items'] as $cookbook_item ) {
				$text = trim( strip_tags( $cookbook_item, '<a><strong><b><em><i><u><sub><sup>' ) );

				// Prevent empty tag (because of linked image, for example).
				if ( '' === strip_tags( $text ) ) {
					$text = '';
				}

				// Find any images.
				preg_match_all( '/<img[^>]+>/i', $cookbook_item, $img_tags );

				foreach ( $img_tags[0] as $img_tag ) {
					preg_match_all( '/src="([^"]*)"/i', $img_tag, $img );

					if ( $img[1] ) {
						$img_src = $img[1][0];
						$image_id = WPRM_Import_Helper::get_or_upload_attachment( $id, $img_src );

						if ( $image_id ) {
							$prev_instruction_index = count( $group['instructions'] ) - 1;
							if ( ! $text && 0 <= $prev_instruction_index && ! $group['instructions'][ $prev_instruction_index ]['image'] ) {
								$group['instructions'][ $prev_instruction_index ]['image'] = $image_id;
							} else {
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

		// Recipe Nutrition.
		$cookbook_nutrition = isset( $post_meta['cookbook_nutrition'] ) ? maybe_unserialize( $post_meta['cookbook_nutrition'][0] ) : array();
		$recipe['nutrition'] = array();

		// Serving Size.
		$match = preg_match( '/^\s*\d+/', $cookbook_nutrition['serving_size'], $servings_array );
		if ( 1 === $match ) {
			$servings = str_replace( ' ','', $servings_array[0] );
		} else {
			$servings = '';
		}
		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $cookbook_nutrition['serving_size'] );

		$recipe['nutrition']['serving_size'] = $servings;
		$recipe['nutrition']['serving_unit'] = $servings_unit;

		// Other nutrition fields.
		$nutrition_mapping = array(
			'calories'              => 'calories',
			'sugar'                 => 'sugar',
			'sodium'                => 'sodium',
			'carbohydrates'         => 'carbohydrates',
			'fiber'                 => 'fiber',
			'protein'               => 'protein',
			'fat'                   => 'fat',
			'saturated_fat'         => 'saturated_fat',
			'unsaturated_fat'   	=> 'polyunsaturated_fat',
			'trans_fat'             => 'trans_fat',
			'cholesterol'           => 'cholesterol',
		);

		foreach ( $nutrition_mapping as $cookbook_field => $wprm_field ) {
			$recipe['nutrition'][ $wprm_field ] = isset( $cookbook_nutrition[ $cookbook_field ] ) ? $cookbook_nutrition[ $cookbook_field ] : '';
		}

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    2.1.0
	 * @param		 mixed $id ID of the recipe we want replace.
	 * @param		 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		$parent_post_ids = maybe_unserialize( get_post_meta( $id, 'cookbook_embedded_post_ids', true ) );

		if ( $parent_post_ids ) {
			// Reverse so first in array to becomes our parent post.
			$parent_post_ids = array_reverse( $parent_post_ids );

			foreach ( $parent_post_ids as $parent_post_id ) {
				$parent_post = get_post( $parent_post_id );
				$content = $parent_post->post_content;

				// Replace their shortcode with ours.
				$content = preg_replace( "/\[cookbook_recipe\s.*?id='?\"?" . $id . '.*?]/im', '[wprm-recipe id="' . $wprm_id . '"]', $content );

				// Replace their fallback with our shortcode.
				preg_match_all( '/<!--Cookbook Recipe (\d+)-->.+?<!--End Cookbook Recipe-->/ms', $content, $matches );
				foreach ( $matches[0] as $key => $match ) {
					$match_id = intval( $matches[1][ $key ] );

					if ( $match_id && $match_id === intval( $id ) ) {
						preg_match_all( '/<!--Cookbook Recipe ' . $id . '-->.?<!--(.+?)-->/ms', $match, $args );

						$shortcode_options = isset( $args[1][0] ) ? ' ' . $args[1][0] : '';
						$content = str_replace( $match, '[wprm-recipe id="' . $wprm_id . '"' . $shortcode_options . ']', $content );
					}
				}

				// Update the parent post content.
				$update_content = array(
					'ID' => $parent_post_id,
					'post_content' => $content,
				);
				wp_update_post( $update_content );

				// Migrate potential comment ratings.
				$comments = get_comments( array( 'post_id' => $parent_post_id ) );

				foreach ( $comments as $comment ) {
					$comment_rating = intval( get_comment_meta( $comment->comment_ID, 'cookbook_comment_rating', true ) );
					if ( $comment_rating ) {
						WPRM_Comment_Rating::add_or_update_rating_for( $comment->comment_ID, $comment_rating );
					}
				}
			}
		}
	}

	/**
	 * Parse cookbook structure.
	 *
	 * @since	2.1.0
	 * @param	mixed $structure Structure to parse.
	 */
	private function parse_cookbook_structure( $structure ) {
		return $this->parse_recipe_component_list( $this->flatten_cookbook_structure( $structure ) );
	}

	/**
	 * Flatten cookbook structure.
	 *
	 * @since	2.1.0
	 * @param	mixed $structure Structure to parse.
	 */
	private function flatten_cookbook_structure( $structure ) {
		$flat = '';

		foreach ( $structure as $item ) {
			if ( is_array( $item['content'] ) ) {
				$flat .= implode( PHP_EOL, $item['content'] );
			} elseif ( 'p' !== $item['tag'] ) {
				$flat .= '<' . $item['tag'] . '>' . $item['content'] . '</' . $item['tag'] . '>';
			} else {
				$flat .= $item['content'];
			}

			$flat .= PHP_EOL;
		}

		return $flat;
	}

	/**
	 * Blob to array.
	 *
	 * @since	2.1.0
	 * @param	mixed $component Component to parse.
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
