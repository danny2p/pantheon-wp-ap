<?php
/**
 * Responsible for importing MV Create recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing MV Create recipes.
 *
 * @since      5.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Create extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    5.4.0
	 */
	public function get_uid() {
		return 'create';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    5.4.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    5.4.0
	 */
	public function get_name() {
		return 'MV Create';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    5.4.0
	 */
	public function get_settings_html() {
		return '';
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    5.4.0
	 */
	public function get_recipe_count() {
		return count( $this->get_recipes() );
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    5.4.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$recipes = array();

		global $wpdb;
		$table = $wpdb->prefix . 'mv_creations';

		$mv_recipes = array();
		if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$mv_recipes = $wpdb->get_results( 'SELECT id, object_id, title, type FROM ' . $table . ' WHERE type IN ("recipe","diy")' );
		}

		foreach ( $mv_recipes as $mv_recipe ) {
			if ( WPRM_POST_TYPE !== get_post_type( $mv_recipe->object_id ) ) {
				$recipes[ $mv_recipe->id ] = array(
					'name' => $mv_recipe->title,
					'url' => admin_url( 'post.php?action=edit&id=' . intval( $mv_recipe->id ) . '&post=' . intval( $mv_recipe->object_id ) . '&post_type=mv_create&type=' . urlencode( $mv_recipe->type ) ),
				);
			}
		}

		return $recipes;
	}

	/**
	 * Search the list of recipes that are available to import.
	 *
	 * @since    8.2.0
	 * @param	 string $search Search term to use.
	 */
	public function get_recipes_search( $search ) {
		$recipes = array();

		global $wpdb;
		$table = $wpdb->prefix . 'mv_creations';

		$mv_recipes = array();
		if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$mv_recipes = $wpdb->get_results( 'SELECT id, object_id, title, type FROM ' . $table . ' WHERE type IN ("recipe","diy") AND title LIKE \'%' . esc_sql( $search ) . '%\'' );
		}

		foreach ( $mv_recipes as $mv_recipe ) {
			if ( WPRM_POST_TYPE !== get_post_type( $mv_recipe->object_id ) ) {
				$recipes[ $mv_recipe->id ] = array(
					'name' => $mv_recipe->title,
					'url' => admin_url( 'post.php?action=edit&id=' . intval( $mv_recipe->id ) . '&post=' . intval( $mv_recipe->object_id ) . '&post_type=mv_create&type=' . urlencode( $mv_recipe->type ) ),
				);
			}
		}

		return $recipes;
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    5.4.0
	 * @param	 mixed $id ID of the recipe we want to import.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'mv_creations';

		$mv_recipe = false;
		if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$rows = $wpdb->get_results( 'SELECT * FROM ' . $table . ' WHERE id=' . intval( $id ) );

			if ( is_array( $rows ) && 1 === count( $rows ) ) {
				$mv_recipe = (array) $rows[0];
			}
		}

		// Make sure we found the corresponding recipe, die otherwise.
		if ( false === $mv_recipe ) {
			wp_die( 'Could not find the MV table or recipe.' );
		}

		$post_id = isset( $mv_recipe['object_id'] ) ? intval( $mv_recipe['object_id'] ) : 0;

		$recipe = array(
			'import_id' => $post_id,
			'import_backup' => array(
				'mv_creation_id' => $id,
			),
		);

		// Recipe type.
		$recipe['type'] = 'diy' === $mv_recipe['type'] ? 'howto' : 'food';

		// Featured Image.
		$recipe['image_id'] = $mv_recipe['thumbnail_id'];

		// Simple Matching.
		$recipe['name'] = $mv_recipe['title'];
		$recipe['summary'] = $mv_recipe['description'];
		$recipe['cost'] = $mv_recipe['estimated_cost'];
		$recipe['author_name'] = $mv_recipe['author'];

		if ( $recipe['author_name'] ) {
			$recipe['author_display'] = 'custom';
		}

		// Servings.
		$mv_yield = $mv_recipe['yield'];
		$match = preg_match( '/^\s*\d+/', $mv_yield, $servings_array );
		if ( 1 === $match ) {
				$servings = str_replace( ' ','', $servings_array[0] );
		} else {
				$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $mv_yield );

		$recipe['servings'] = $servings;
		$recipe['servings_unit'] = $servings_unit;

		// Recipe times. From seconds to minutes.
		$recipe['prep_time'] = intval( $mv_recipe['prep_time'] ) / 60;
		$recipe['cook_time'] = intval( $mv_recipe['active_time'] ) / 60;
		$recipe['custom_time'] = intval( $mv_recipe['additional_time'] ) / 60;
		$recipe['custom_time_label'] = $mv_recipe['additional_time_label'];
		$recipe['total_time'] = intval( $mv_recipe['total_time'] ) / 60;

		// Recipe tags.
		$recipe['tags'] = array();
		$recipe['tags']['keyword'] = $mv_recipe['keywords'] ? array_map( 'trim', explode( ',', $mv_recipe['keywords'] ) ) : array();
		$recipe['tags']['suitablefordiet'] = $mv_recipe['suitable_for_diet'] ? array_map( 'trim', explode( ',', $mv_recipe['suitable_for_diet'] ) ) : array();

		$taxonomies = array(
			'category' => 'course',
			'mv_cuisine' => 'cuisine',
		);

		foreach ( $taxonomies as $mv_tag => $wprm_tag ) {
			$terms = get_the_terms( $post_id, $mv_tag );
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$recipe['tags'][ $wprm_tag ][] = $term->name;
				}
			}
		}

		// Recipe video.
		if ( $mv_recipe['external_video'] ) {
			$mv_video = (array) json_decode( $mv_recipe['external_video'] );


			if ( isset( $mv_video['contentUrl'] ) ) {
				$recipe['video_embed'] = $mv_video['contentUrl'];
			}
		} else if ( $mv_recipe['video'] ) {
			$mv_video = (array) json_decode( $mv_recipe['video'] );

			if ( $mv_video['key'] ) {
				$key = esc_attr( $mv_video['key'] );
				$recipe['video_embed'] = '<div id="' . $key . '"></div><script type="text/javascript" src="//video.mediavine.com/videos/' . $key . '.js" async data-noptimize></script>';
			}
		}

		// Pinterest Image.
		if ( $mv_recipe['pinterest_img_id'] ) {
			$recipe['pin_image_id'] = intval( $mv_recipe['pinterest_img_id'] );
		}

		// Ingredients.
		$mv_published = (array) json_decode( $mv_recipe['published'] );
		$mv_ingredients = 'food' === $recipe['type'] ? (array) $mv_published['ingredients'] : (array) $mv_published['materials'];
		$ingredients = array();
		$has_ingredient_links = false;

		foreach ( $mv_ingredients as $mv_group_name => $mv_group_ingredients ) {
			$group = array(
				'name' => 'mv-has-no-group' === $mv_group_name ? '' : $mv_group_name,
				'ingredients' => array(),
			);

			foreach ( $mv_group_ingredients as $mv_ingredient ) {
				$mv_ingredient = (array) $mv_ingredient;
				$text = trim( $mv_ingredient['original_text'] );

				if ( ! empty( $text ) ) {
					$ingredient = array(
						'raw' => $text,
					);

					// Check for ingredient link.
					if ( $mv_ingredient['link'] ) {
						$ingredient['link'] = array(
							'url' => $mv_ingredient['link'],
							'nofollow' => '0' === $mv_ingredient['nofollow'] ? 'follow' : 'nofollow',
						);
						$has_ingredient_links = true;
					}

					$group['ingredients'][] = $ingredient;
				}
			}

			$ingredients[] = $group;
		}
		$recipe['ingredients'] = $ingredients;

		if ( $has_ingredient_links ) {
			$recipe['ingredient_links_type'] = 'custom';
		}

		// Equipment.
		$mv_equipment = (array) $mv_published['tools'];
		$equipment = array();

		foreach ( $mv_equipment as $mv_group_name => $mv_group_equipment ) {
			foreach ( $mv_group_equipment as $mv_item ) {
				$mv_item = (array) $mv_item;
				$text = trim( $mv_item['original_text'] );

				if ( ! empty( $text ) ) {
					$equipment[] = array(
						'name' => $text,
					);
				}
			}
		}

		// Products if tools are not set.
		if ( ! $equipment ) {
			$mv_products = (array) $mv_published['products'];

			foreach ( $mv_products as $mv_product ) {
				$text = trim( $mv_product->title );

				if ( ! empty( $text ) ) {
					$equipment[] = array(
						'name' => $text,
					);

					// Import link and image if not already set.
					$equipment_id = WPRM_Recipe_Sanitizer::get_equipment_id( $text );

					if ( $equipment_id ) {
						$link = $mv_product->link;
						$existing_link = get_term_meta( $equipment_id, 'wprmp_equipment_link', true );

						if ( ! $existing_link && $link ) {
							update_term_meta( $equipment_id, 'wprmp_equipment_link', $link );
						}

						$image_id = intval( $mv_product->thumbnail_id );
						$existing_image_id = get_term_meta( $equipment_id, 'wprmp_equipment_image_id', true );

						if ( ! $existing_image_id && $image_id ) {
							update_term_meta( $equipment_id, 'wprmp_equipment_image_id', $image_id );
						}
					}
				}
			}
		}

		$recipe['equipment'] = $equipment;

		// Instructions.
		$mv_instructions = $this->parse_blob( $mv_recipe['instructions'] );
		$instructions = array();

		foreach ( $mv_instructions as $mv_group ) {
			$group = array(
				'name' => trim( strip_tags( $mv_group['name'], '<a><strong><b><em><i><u><sub><sup>' ) ),
				'instructions' => array(),
			);

			foreach ( $mv_group['items'] as $mv_item ) {
				$text = trim( strip_tags( $mv_item, '<a><strong><b><em><i><u><sub><sup><br>' ) );

				// Find any images and remove from instructions.
				preg_match_all( '/\[mv_img[^\]]*\]/i', $mv_item, $img_shortcodes );
				$text = preg_replace( '/\[mv_img[^\]]*\]/i', '', $text );

				foreach ( $img_shortcodes[0] as $img_shortcode ) {
					$img_shortcode = html_entity_decode( $img_shortcode );
					preg_match( '/id="?\'?(\d+)/i', $img_shortcode, $img );

					if ( $img[1] ) {
						$image_id = intval( $img[1] );

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

		// Nutrition Facts.
		$recipe['nutrition'] = array();

		$mv_nutrition = (array) $mv_published['nutrition'];

		// Serving size.
		$mv_serving_size = isset( $mv_nutrition['serving_size'] ) ? trim( $mv_nutrition['serving_size'] ) : '';
		$match = preg_match( '/^\s*\d+/', $mv_serving_size, $servings_array );
		if ( 1 === $match ) {
			$servings = str_replace( ' ','', $servings_array[0] );
		} else {
			$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $mv_serving_size );

		$recipe['nutrition']['serving_size'] = $servings;
		$recipe['nutrition']['serving_unit'] = $servings_unit;

		// Other nutrients.
		$nutrition_mapping = array(
			'calories'              => 'calories',
			'carbohydrates'         => 'carbohydrates',
			'protein'               => 'protein',
			'total_fat'             => 'fat',
			'saturated_fat'         => 'saturated_fat',
			'unsaturated_fat'   	=> 'polyunsaturated_fat',
			'trans_fat'             => 'trans_fat',
			'cholesterol'           => 'cholesterol',
			'sodium'                => 'sodium',
			'fiber'                 => 'fiber',
			'sugar'                 => 'sugar',
			'sugar_alcohols'        => 'sugar_alcohols',
		);

		foreach ( $nutrition_mapping as $mv_field => $wprm_field ) {
			if ( isset( $mv_nutrition[ $mv_field ] ) && $mv_nutrition[ $mv_field ] ) {
				$recipe['nutrition'][ $wprm_field ] = $mv_nutrition[ $mv_field ];
			}
		}

		// Recipe Notes.
		$notes = $mv_recipe['notes'];

		// Find any images.
		preg_match_all( '/\[mv_img[^\]]*\]/i', $notes, $img_shortcodes );

		foreach ( $img_shortcodes[0] as $img_shortcode_encoded ) {
			$img_shortcode = html_entity_decode( $img_shortcode_encoded );
			preg_match( '/id="?\'?(\d+)/i', $img_shortcode, $img );

			if ( $img[1] ) {
				$image_id = intval( $img[1] );

				if ( $image_id ) {
					$image_html = wp_get_attachment_image( $image_id, 'medium' );

					if ( $image_html ) {
						$notes = str_replace( $img_shortcode_encoded, $image_html, $notes );
					}
				}
			}
		}

		$recipe['notes'] = $notes;

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    5.4.0
	 * @param	 mixed $id ID of the recipe we want replace.
	 * @param	 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'mv_creations';

		$mv_recipe = false;
		if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$rows = $wpdb->get_results( 'SELECT * FROM ' . $table . ' WHERE id=' . intval( $id ) );

			if ( is_array( $rows ) && 1 === count( $rows ) ) {
				$mv_recipe = (array) $rows[0];
			}
		}

		// Make sure we found the corresponding recipe, die otherwise.
		if ( false === $mv_recipe ) {
			wp_die( 'Could not find the MV table or recipe.' );
		}

		// Get all associated posts.
		$post_ids = json_decode( $mv_recipe['associated_posts'] );
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			$content = $post->post_content;

			// Gutenberg.
			$gutenberg_matches = array();
			if ( 'diy' === $mv_recipe['type'] ) {
				$gutenberg_patern = '/<!--\s+wp:(mv\/diy)(\s+(\{.*?\}))?\s+(\/)?-->.*?<!--\s+\/wp:mv\/diy\s+(\/)?-->/mis';
			} else {
				$gutenberg_patern = '/<!--\s+wp:(mv\/recipe)(\s+(\{.*?\}))?\s+(\/)?-->.*?<!--\s+\/wp:mv\/recipe\s+(\/)?-->/mis';
			}
			preg_match_all( $gutenberg_patern, $content, $matches );

			if ( isset( $matches[3] ) ) {
				foreach ( $matches[3] as $index => $block_attributes_json ) {
					if ( ! empty( $block_attributes_json ) ) {
						$attributes = json_decode( $block_attributes_json, true );

						if ( ! is_null( $attributes ) ) {
							if ( isset( $attributes['id'] ) && intval( $id ) === intval( $attributes['id'] ) ) {
								$content = str_ireplace( $matches[0][ $index ], '<!-- wp:wp-recipe-maker/recipe {"id":' . $wprm_id . ',"updated":' . time() . '} -->[wprm-recipe id="' . $wprm_id . '"]<!-- /wp:wp-recipe-maker/recipe -->', $content );
							}
						}
					}
				}
			}

			// Classic Editor.
			$classic_pattern = '/\[mv_create\s.*?key=\"?\'?(\d+)\"?\'?.*?\]/mi';
			preg_match_all( $classic_pattern, $content, $classic_matches );

			if ( isset( $classic_matches[1] ) ) {
				foreach ( $classic_matches[1] as $index => $mv_id ) {
					if ( $id === $mv_id ) {
						$content = str_ireplace( $classic_matches[0][ $index ], '[wprm-recipe id="' . $wprm_id . '"]', $content );
					}
				}
			}

			// Update post with new content including our shortcodes.
			if ( $content !== $post->post_content ) {
				$update_content = array(
					'ID' => $post_id,
					'post_content' => $content,
				);
				wp_update_post( $update_content );
			}
		}

		// Migrate ratings.
		$table = $wpdb->prefix . 'mv_reviews';

		$mv_ratings = false;
		if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$rows = $wpdb->get_results( 'SELECT * FROM ' . $table . ' WHERE creation=' . intval( $id ) );

			if ( is_array( $rows ) ) {
				$mv_ratings = (array) $rows;
			}
		}

		if ( $mv_ratings ) {
			foreach ( $mv_ratings as $mv_rating ) {
				$mv_rating = (array) $mv_rating;

 				$rating = array(
					'recipe_id' => $wprm_id,
					'user_id' => '',
					'ip' => 'mv-create-' . $mv_rating['id'],
					'rating' => ceil( floatval( $mv_rating['rating'] ) ),
				);

				WPRM_Rating_Database::add_or_update_rating( $rating );
			}
		}
	}

	/**
	 * Blob to array.
	 *
	 * @since    5.4.0
	 * @param	 mixed $blob Blob to parse.
	 */
	private function parse_blob( $blob ) {
		$component_list = array();
		$component_group = array(
			'name' => '',
			'items' => array(),
		);

		// Split in different parts.
		$blob = preg_replace( '/<ol(\s[^>]*>|>)/mi', '$0' . PHP_EOL, $blob );
		$blob = str_ireplace( '</li>', '</li>' . PHP_EOL, $blob );
		$blob = str_ireplace( '</ol>', '</ol>' . PHP_EOL, $blob );
		$blob = str_ireplace( '</h3>', '</h3>' . PHP_EOL, $blob );
		$blob = str_ireplace( '</p>', '</p>' . PHP_EOL, $blob );

		$bits = explode( PHP_EOL, $blob );
		foreach ( $bits as $bit ) {

			$test_bit = trim( $bit );
			if ( empty( $test_bit ) ) {
				continue;
			}

			if ( WPRM_Import_Helper::is_heading( $bit ) ) {
				$component_list[] = $component_group;

				$component_group = array(
					'name' => $bit,
					'items' => array(),
				);
			} else {
				$component_group['items'][] = $bit;
			}
		}

		$component_list[] = $component_group;

		return $component_list;
	}
}
