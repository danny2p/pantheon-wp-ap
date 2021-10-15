<?php
/**
 * Responsible for importing WPZOOM recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      7.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing WPZOOM recipes.
 *
 * @since      7.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Wpzoom extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    7.4.0
	 */
	public function get_uid() {
		return 'wpzoom';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since   7.4.0
	 */
	public function requires_search() {
		return true;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    7.4.0
	 */
	public function get_name() {
		return 'Recipe Card Blocks by WPZOOM';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    7.4.0
	 */
	public function get_settings_html() {
		// Match recipe tags.
		$html = '<h4>Recipe Tags</h4>';

		$wpzoom_taxonomies = array(
			'course' => __( 'Courses', 'wp-recipe-maker' ),
			'cuisine' => __( 'Cuisines', 'wp-recipe-maker' ),
			'difficulty' => __( 'Difficulties', 'wp-recipe-maker' ),
			'keyword' => __( 'Keywords', 'wp-recipe-maker' ),
			'foodLabels' => __( 'Food Labels', 'wp-recipe-maker' ),
		);

		$wprm_taxonomies = WPRM_Taxonomies::get_taxonomies();

		foreach ( $wprm_taxonomies as $wprm_taxonomy => $options ) {
			$wprm_key = substr( $wprm_taxonomy, 5 );

			$html .= '<label for="wpzoom-tags-' . $wprm_key . '">' . $options['name'] . ':</label> ';
			$html .= '<select name="wpzoom-tags-' . $wprm_key . '" id="wpzoom-tags-' . $wprm_key . '">';
			$html .= "<option value=\"\">Don't import anything for this tag</option>";
			foreach ( $wpzoom_taxonomies as $name => $label ) {
				$selected = $wprm_key === $name || 'wpurp_' . $wprm_key === $name ? ' selected="selected"' : '';
				$html .= '<option value="' . esc_attr( $name ) . '"' . esc_html( $selected ) . '>' . esc_html( $label ) . '</option>';
			}
			$html .= '</select>';
			$html .= '<br />';
		}

		return $html;
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    7.4.0
	 */
	public function get_recipe_count() {
		$recipes_found = get_option( 'wprm_import_wpzoom_recipes', array() );
		return count( $recipes_found );
	}

	/**
	 * Search for recipes to import.
	 *
	 * @since    7.4.0
	 * @param	 int $page Page of recipes to import.
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
				$blocks = parse_blocks( $post->post_content );

				foreach ( $blocks as $index => $block ) {
 					if ( 'wpzoom-recipe-card/block-recipe-card' === $block['blockName'] ) {
						$name = isset( $block['attrs']['recipeTitle'] ) && $block['attrs']['recipeTitle'] ? $block['attrs']['recipeTitle'] : __( 'Unknown', 'wp-recipe-maker' );

						$recipe_id = $post->ID . '-' . $index;
						$recipes[ $recipe_id ] = array(
							'name' => $name,
							'url' => get_edit_post_link( $post->ID ),
						);
					}
				}
			}
		} else {
			$finished = true;
		}

		$found_recipes = 0 === $page ? array() : get_option( 'wprm_import_wpzoom_recipes', array() );
		$found_recipes = array_merge( $found_recipes, $recipes );

		update_option( 'wprm_import_wpzoom_recipes', $found_recipes, false );

		$search_result = array(
			'finished' => $finished,
			'recipes' => count( $found_recipes ),
		);

		return $search_result;
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    7.4.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$found_recipes = get_option( 'wprm_import_wpzoom_recipes', array() );

		$limit = 100;
		$offset = $limit * $page;

		return array_slice( $found_recipes, $offset, $limit );
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    7.4.0
	 * @param		 mixed $id ID of the recipe we want to import.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$id_parts = explode( '-', $id, 2 );
		$post_id = intval( $id_parts[0] );
		$block_index = intval( $id_parts[1] );

		$post = get_post( $post_id );
		
		$blocks = parse_blocks( $post->post_content );
		$recipe_block = isset( $blocks[ $block_index ] ) ? $blocks[ $block_index ] : false;

		if ( $recipe_block && 'wpzoom-recipe-card/block-recipe-card' === $recipe_block['blockName'] ) {
			$recipe = array(
				'import_id' => 0, // Set to 0 because we need to create a new recipe post.
				'import_backup' => array(
					'wpzoom_block' => $recipe_block,
				),
			);

			$atts = $recipe_block['attrs'];

			// Recipe Image.
			if ( ! empty( $atts['image'] ) && isset( $atts['hasImage'] ) && $atts['hasImage'] ) {
				$recipe['image_id'] = isset( $atts['image']['id'] ) ? $atts['image']['id'] : 0;
			}

			// Recipe Video.
			if ( ! empty( $atts['video'] ) && isset( $atts['hasVideo'] ) && $atts['hasVideo'] ) {
				if ( 'embed' === $atts['video']['type'] ) {
					$recipe['video_embed'] = $atts['video']['url'];
				} elseif ( 'self-hosted' === $atts['video']['type'] ) {
					$recipe['video_id'] = $atts['video']['id'];
				}
			}
			
			// Recipe Title.
			$recipe_title = isset( $atts['recipeTitle'] ) ? $atts['recipeTitle'] : false;
			$recipe['name'] = $recipe_title ? $recipe_title : $post->post_title;

			// Simple text fields.
			$recipe['summary'] = isset( $atts['summary'] ) ? $atts['summary'] : '';
			$recipe['notes'] = isset( $atts['notes'] ) ? $atts['notes'] : '';

			// Make sure nutrition exists.
			$recipe['nutrition'] = array();

			// WPZOOM Details
			if ( isset( $atts['details' ] ) && $atts['details'] ) {
				foreach ( $atts['details'] as $index => $detail ) {
					if ( ! is_array( $detail ) || ! isset( $detail['value'] ) || ! $detail['value'] ) {
						continue;
					}

					if ( 0 === $index ) {
						// Recipe servings.
						$servings = intval( $detail['value'] );

						if ( $servings ) {
							$recipe['servings'] = $servings;
							$recipe['servings_unit'] = isset( $detail['unit'] ) ? $detail['unit'] : '';
						}
					} elseif ( 1 === $index ) {
						$recipe['prep_time'] = intval( $detail['value'] );
					} elseif ( 2 === $index ) {
						$recipe['cook_time'] = intval( $detail['value'] );
					} elseif ( 8 === $index ) {
						$recipe['total_time'] = intval( $detail['value'] );
					} elseif ( 3 === $index ) {
						$recipe['nutrition']['calories'] = intval( $detail['value'] );
					}
				}
			}

			// Recipe Tags.
			$recipe['tags'] = array();

			$wprm_taxonomies = WPRM_Taxonomies::get_taxonomies();
			foreach ( $wprm_taxonomies as $wprm_taxonomy => $options ) {
				$wprm_key = substr( $wprm_taxonomy, 5 );
				$tag = isset( $post_data[ 'wpzoom-tags-' . $wprm_key ] ) ? $post_data[ 'wpzoom-tags-' . $wprm_key ] : false;

				if ( 'foodLabels' === $tag ) {
					if ( isset( $atts['settings'][1] ) && isset( $atts['settings'][1]['foodLabels'] ) ) {
						$recipe['tags'][ $wprm_key ] = $atts['settings'][1]['foodLabels'];
					}
				} elseif ( $tag && isset( $atts[ $tag ] ) ) {
					$recipe['tags'][ $wprm_key ] = $atts[ $tag ];
				}
			}

			// Recipe Ingredients.
			$ingredients = isset( $atts['ingredients'] ) ? $atts['ingredients'] : array();
			$recipe['ingredients'] = array();

			$current_group = array(
				'name' => '',
				'ingredients' => array(),
			);
			foreach ( $ingredients as $ingredient ) {
				if ( isset( $ingredient['isGroup'] ) && $ingredient['isGroup'] ) {
					$group_name = $this->parse_rich_text( $ingredient['name'], true );

					if ( $group_name ) {
						$recipe['ingredients'][] = $current_group;
						$current_group = array(
							'name' => $group_name,
							'ingredients' => array(),
						);
					}
				} else {
					$amount = isset( $ingredient['parse'] ) && isset( $ingredient['parse']['amount'] ) ? $ingredient['parse']['amount'] : '';
					$unit = isset( $ingredient['parse'] ) && isset( $ingredient['parse']['unit'] ) ? $ingredient['parse']['unit'] : '';
					$name = $this->parse_rich_text( $ingredient['name'], true );

					$current_group['ingredients'][] = array(
						'amount' => $amount,
						'unit' => $unit,
						'name' => $name,
						'notes' => '',
					);
				}
			}
			$recipe['ingredients'][] = $current_group;

			// Recipe Instructions.
			$instructions = isset( $atts['steps'] ) ? $atts['steps'] : array();
			$recipe['instructions'] = array();

			$current_group = array(
				'name' => '',
				'instructions' => array(),
			);
			foreach ( $instructions as $instruction ) {
				if ( isset( $instruction['isGroup'] ) && $instruction['isGroup'] ) {
					$group_name = $this->parse_rich_text( $instruction['text'], true );

					if ( $group_name ) {
						$recipe['instructions'][] = $current_group;
						$current_group = array(
							'name' => $group_name,
							'instructions' => array(),
						);
					}
				} else {
					$parsed = $this->parse_rich_text( $instruction['text'], false, $post_id );

					if ( isset( $instruction['gallery'] ) && $instruction['gallery']['ids'] ) {
						$parsed = array_merge( $parsed, $instruction['gallery']['ids'] );
					}

					$current_instruction = array(
						'text' => '',
						'image' => '',
					);

					foreach ( $parsed as $part ) {
						if ( is_int( $part ) ) {
							$current_instruction['image'] = $part;

							// Always push images.
							$current_group['instructions'][] = $current_instruction;
							$current_instruction = array(
								'text' => '',
								'image' => '',
							);
						} else {
							// If text already set, push to instructions.
							if ( $current_instruction['text'] ) {
								$current_group['instructions'][] = $current_instruction;
								$current_instruction = array(
									'text' => '',
									'image' => '',
								);
							}

							$current_instruction['text'] = $part;
						}
					}

					// Push last instruction.
					if ( $current_instruction['text'] ) {
						$current_group['instructions'][] = $current_instruction;
					}
				}
			}
			$recipe['instructions'][] = $current_group;

			// Check if there's a nutrition label.
			$wpzoom_nutrition = false;
			foreach ( $blocks as $index => $block ) {
				if ( 'wpzoom-recipe-card/block-nutrition' === $block['blockName'] ) {
					$wpzoom_nutrition = $block['attrs']['data'];
					break;
			   	}
		   	}
			
			if ( $wpzoom_nutrition ) {
				$nutrition_mapping = array(
					'serving-size'          => 'serving_size',
					'serving-size-unit'     => 'serving_unit',
					'calories'              => 'calories',
					'total-carbohydrate'	=> 'carbohydrates',
					'protein'               => 'protein',
					'total-fat'             => 'fat',
					'saturated-fat'         => 'saturated_fat',
					'trans-fat'             => 'trans_fat',
					'cholesterol'           => 'cholesterol',
					'sodium'                => 'sodium',
					'potassium'             => 'potassium',
					'dietary-fiber'			=> 'fiber',
					'sugars'				=> 'sugar',
				);
			
				foreach ( $nutrition_mapping as $wpzoom_field => $wprm_field ) {
					$recipe['nutrition'][ $wprm_field ] = isset( $wpzoom_nutrition[ $wpzoom_field ] ) ? $wpzoom_nutrition[ $wpzoom_field ] : '';
				}
			}
		} else {
			$recipe = false;
		}

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    7.4.0
	 * @param		 mixed $id ID of the recipe we want replace.
	 * @param		 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		$id_parts = explode( '-', $id, 2 );
		$post_id = intval( $id_parts[0] );
		$block_index = intval( $id_parts[1] );

		$post = get_post( $post_id );
		
		$blocks = parse_blocks( $post->post_content );
		$recipe_block = isset( $blocks[ $block_index ] ) ? $blocks[ $block_index ] : false;

		if ( $recipe_block && 'wpzoom-recipe-card/block-recipe-card' === $recipe_block['blockName'] ) {
			// Replace with WPRM block.
			$blocks[ $block_index ] = array(
				'blockName' => 'wp-recipe-maker/recipe',
				'attrs' => array(
					'id' => $wprm_id,
					'updated' => time(),
				),
				'innerBlocks' => array(),
				'innerHTML' => '[wprm-recipe id="' . $wprm_id . '"]',
				'innerContent' => array(
					'[wprm-recipe id="' . $wprm_id . '"]',
				),
			);

			$content = serialize_blocks( $blocks );

			$update_content = array(
				'ID' => $post_id,
				'post_content' => $content,
			);
			wp_update_post( $update_content );

			// Remove from found recipes.
			$found_recipes = get_option( 'wprm_import_wpzoom_recipes', array() );
			unset( $found_recipes[ $id ] );
			update_option( 'wprm_import_wpzoom_recipes', $found_recipes, false );

			// Migrate ratings.
			global $wpdb;
        	$table_name = $wpdb->prefix . 'wpzoom_rating_stars';

			$query_ratings = "SELECT * FROM $table_name WHERE recipe_id = $post_id OR post_id = $post_id";
        	$ratings = $wpdb->get_results( $query_ratings );

			foreach ( $ratings as $rating ) {
				if ( '1' === $rating->approved ) {
					$comment_id = intval( $rating->comment_id );
					$user_id = intval( $rating->user_id );
					$rating_value = intval( $rating->rating );

					// Only use recipe ID if there is no comment ID.
					$recipe_id = 0 < $comment_id ? 0 : $wprm_id;

					$wprm_rating = array(
						'date' => $rating->rate_date,
						'recipe_id' => $recipe_id,
						'comment_id' => $comment_id,
						'user_id' => $user_id,
						'ip' => $rating->ip,
						'rating' => $rating_value,
					);

					WPRM_Rating_Database::add_or_update_rating( $wprm_rating );
				}
			}
		}
	}

	/**
	 * Parse the rich text that WPZOOM stores.
	 *
	 * @since	7.4.0
	 * @param	mixed	$parts Parts of text to parse.
	 * @param	boolean $flat Wether to return as a flat string.
	 * @param	mixed	$post_id Optional post ID to assign the image to.
	 */
	private function parse_rich_text( $parts, $flat = false, $post_id = false ) {
		$parsed = array();
		$text = '';

		foreach ( $parts as $part ) {
			if ( is_string( $part ) ) {
				$text .= $part;
			} elseif ( is_array( $part ) && isset( $part['type'] ) ) {
				switch( $part['type'] ) {
					case 'br':
						$text .= ' ';
						break;
					case 'img':
						// Can't add images when it's a flat string.
						if ( ! $flat && isset( $part['props']['src'] ) ) {
							$image_id = WPRM_Import_Helper::get_or_upload_attachment( $post_id, $part['props']['src'] );

							if ( $image_id ) {
								// Add existing text before the image.
								if ( $text ) {
									$parsed[] = $text;
									$text = '';
								}
	
								// Add image after.
								$parsed[] = intval( $image_id );
							}
						}
						break;
				}
			}
		}

		// Add remaining text.
		if ( $text ) {
			$parsed[] = $text;
		}

		if ( $flat ) {
			return isset( $parsed[0] ) ? $parsed[0] : '';
		} else {
			return $parsed;
		}
	}
}
