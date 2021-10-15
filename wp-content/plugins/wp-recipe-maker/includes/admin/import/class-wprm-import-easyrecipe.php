<?php
/**
 * Responsible for importing EasyRecipe recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing EasyRecipe recipes.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Easyrecipe extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    1.0.0
	 */
	public function get_uid() {
		return 'easyrecipe';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    1.10.0
	 */
	public function requires_search() {
		return true;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    1.0.0
	 */
	public function get_name() {
		return 'EasyRecipe';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    1.3.0
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
		$recipes_found = get_option( 'wprm_import_easyrecipe_recipes', array() );
		return count( $recipes_found );
	}

	/**
	 * Search for recipes to import.
	 *
	 * @since    1.10.0
	 * @param	 int $page Page of recipes to import.
	 */
	public function search_recipes( $page = 0 ) {
		if ( ! class_exists( 'simple_html_dom' ) && ! class_exists( 'simple_html_dom_node' ) ) {
			require_once( WPRM_DIR . 'vendor/simple_html_dom/simple_html_dom.php' );
			libxml_use_internal_errors( true );
		}

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
				$recipes_html = $this->get_easyrecipe_recipes( $post->post_content );

				if ( count( $recipes_html ) > 0 ) {
					foreach ( $recipes_html as $index => $recipe_html ) {
						$name = $recipe_html->find( 'div[class=ERName]', 0 );
						$name = is_object( $name ) ? $this->strip_easyrecipe_tags( $name->plaintext ) : false;

						if ( false === $name ) {
							$name = $recipe_html->find( 'span[class=ERName]', 0 );
							$name = is_object( $name ) ? $this->strip_easyrecipe_tags( $name->plaintext ) : __( 'Unknown', 'wp-recipe-maker' );
						}

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

		$found_recipes = 0 === $page ? array() : get_option( 'wprm_import_easyrecipe_recipes', array() );
		$found_recipes = array_merge( $found_recipes, $recipes );

		update_option( 'wprm_import_easyrecipe_recipes', $found_recipes, false );

		$search_result = array(
			'finished' => $finished,
			'recipes' => count( $found_recipes ),
		);

		return $search_result;
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    1.0.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$found_recipes = get_option( 'wprm_import_easyrecipe_recipes', array() );

		$limit = 100;
		$offset = $limit * $page;

		return array_slice( $found_recipes, $offset, $limit );
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    1.0.0
	 * @param		 mixed $id ID of the recipe we want to import.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		if ( ! class_exists( 'simple_html_dom' ) && ! class_exists( 'simple_html_dom_node' ) ) {
			require_once( WPRM_DIR . 'vendor/simple_html_dom/simple_html_dom.php' );
			libxml_use_internal_errors( true );
		}

		$id_parts = explode( '-', $id, 2 );
		$post_id = intval( $id_parts[0] );
		$recipe_index = intval( $id_parts[1] );

		$post = get_post( $post_id );
		$recipes = $this->get_easyrecipe_recipes( $post->post_content );
		$recipe_html = isset( $recipes[ $recipe_index ] ) ? $recipes[ $recipe_index ] : false;

		if ( $recipe_html ) {
			$recipe = array(
				'import_id' => 0, // Set to 0 because we need to create a new recipe post.
				'import_backup' => array(
					'easyrecipe_content' => $post->post_content,
				),
			);

			// Featured Image.
			// Check for image set on "Photo" tab.
			$easyrecipe_field = $recipe_html->find( 'link[itemprop=image]', 0 );
			$image_url = is_object( $easyrecipe_field ) ? $easyrecipe_field->attr['href'] : '';
			$image_id = WPRM_Import_Helper::get_or_upload_attachment( $post_id, $image_url );

			if ( $image_id ) {
				$recipe['image_id'] = $image_id;
			} else {
				// Use first image added to recipe.
				$images = $this->get_easyrecipe_images( $recipe_html->innertext );
				if ( isset( $images[0] ) ) {
					$recipe['image_id'] = $images[0]['id'];
				}
			}

			// Name.
			$easyrecipe_field = $recipe_html->find( 'div[class=ERName]', 0 );
			$wprm_field = is_object( $easyrecipe_field ) ? $this->strip_easyrecipe_tags( $easyrecipe_field->plaintext ) : false;

			if ( false === $wprm_field ) {
				$easyrecipe_field = $recipe_html->find( 'span[class=ERName]', 0 );
				$wprm_field = is_object( $easyrecipe_field ) ? $this->strip_easyrecipe_tags( $easyrecipe_field->plaintext ) : '';
			}

			$recipe['name'] = $wprm_field;

			// Simple matching.
			$easyrecipe_field = $recipe_html->find( 'div[class=ERSummary]', 0 );
			$wprm_field = is_object( $easyrecipe_field ) ? $this->replace_easyrecipe_tags( $easyrecipe_field->plaintext ) : '';
			$recipe['summary'] = $wprm_field;

			$easyrecipe_field = $recipe_html->find( 'div[class=ERNotes]', 0 );
			$wprm_field = is_object( $easyrecipe_field ) ? $this->replace_easyrecipe_tags( $easyrecipe_field->plaintext, true ) : '';
			$recipe['notes'] = $wprm_field;

			// Author.
			$easyrecipe_field = $recipe_html->find( 'span[class=author]', 0 );
			$wprm_field = is_object( $easyrecipe_field ) ? $this->replace_easyrecipe_tags( $easyrecipe_field->plaintext, true ) : '';
			$recipe['author_name'] = $wprm_field;

			if ( '' !== trim( $recipe['author_name'] ) ) {
				$recipe['author_display'] = 'custom';
			}

			// Servings.
			$servings = $recipe_html->find( 'span[class=yield]', 0 );
			$easyrecipe_servings = is_object( $servings ) ? trim( $servings->plaintext ) : '';

			$match = preg_match( '/^\s*\d+/', $easyrecipe_servings, $servings_array );
			if ( 1 === $match ) {
					$servings = str_replace( ' ','', $servings_array[0] );
			} else {
					$servings = '';
			}

			$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $easyrecipe_servings );

			$recipe['servings'] = $servings;
			$recipe['servings_unit'] = $servings_unit;

			// Cook times.
			$easyrecipe_times = array();
			$times = $recipe_html->find( 'time' );
			foreach ( $times as $time ) {
				$easyrecipe_times[ $time->itemprop ] = $this->easyrecipe_time_to_minutes( $time->datetime );
			}

			$recipe['prep_time'] = isset( $easyrecipe_times['prepTime'] ) ? $easyrecipe_times['prepTime'] : 0;
			$recipe['cook_time'] = isset( $easyrecipe_times['cookTime'] ) ? $easyrecipe_times['cookTime'] : 0;
			$recipe['total_time'] = isset( $easyrecipe_times['totalTime'] ) ? $easyrecipe_times['totalTime'] : 0;

			// Recipe Tags.
			$easyrecipe_field = $recipe_html->find( 'span[class=type]', 0 );
			$wprm_field = is_object( $easyrecipe_field ) ? trim( $easyrecipe_field->plaintext ) : '';
			$wprm_field = str_replace( ';', ',', $wprm_field );
			$courses = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $wprm_field );
			$courses = '' === $courses[0] ? array() : $courses;

			$easyrecipe_field = $recipe_html->find( 'span[class=cuisine]', 0 );
			$wprm_field = is_object( $easyrecipe_field ) ? trim( $easyrecipe_field->plaintext ) : '';
			$wprm_field = str_replace( ';', ',', $wprm_field );
			$cuisines = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $wprm_field );
			$cuisines = '' === $cuisines[0] ? array() : $cuisines;

			$recipe['tags'] = array(
				'course' => $courses,
				'cuisine' => $cuisines,
			);

			// Ingredients.
			$ingredients = array();
			$ingredient_lists = $recipe_html->find( 'ul[class=ingredients]' );
			$ingredient_elements = array();

			foreach ( $ingredient_lists as $ingredient_list ) {
				if ( is_object( $ingredient_list ) ) {
					$ingredient_elements = array_merge( $ingredient_elements, $ingredient_list->children() );
				}
			}

			$group = array(
				'ingredients' => array(),
				'name' => '',
			);
			foreach ( $ingredient_elements as $ingredient_element ) {
				if ( strpos( $ingredient_element->class, 'ERSeparator' ) !== false ) {
					// Ingredient group.
					$ingredients[] = $group;

					$group = array(
						'ingredients' => array(),
						'name' => $this->replace_easyrecipe_tags( $ingredient_element->plaintext, false ),
					);
				} else {
					// Ingredient.
					$text = trim( $this->replace_easyrecipe_tags( $ingredient_element->plaintext, false ) );

					if ( strlen( $text ) > 0 ) {
						$group['ingredients'][] = array(
							'raw' => $text,
						);
					}
				}
			}
			$ingredients[] = $group;
			$recipe['ingredients'] = $ingredients;

			// Instructions.
			$instructions = array();
			$instruction_div = $recipe_html->find( 'div[class=instructions]' );
			$instruction_children = isset( $instruction_div[0] ) && is_object( $instruction_div[0] ) ? $instruction_div[0]->children() : array();

			$group = array(
				'instructions' => array(),
				'name' => '',
			);
			foreach ( $instruction_children as $instruction_child ) {
				if ( 'div' === $instruction_child->tag && false !== strpos( $instruction_child->class, 'ERSeparator' ) ) {
						// Instruction Group.
						$instructions[] = $group;

						$group = array(
							'instructions' => array(),
							'name' => $this->strip_easyrecipe_tags( $instruction_child->plaintext ),
						);
				} elseif ( 'ol' === $instruction_child->tag ) {
					$instruction_steps = $instruction_child->find( '[class=instruction]' );

					foreach ( $instruction_steps as $instruction_step ) {
						$text = $this->replace_easyrecipe_tags( $instruction_step->plaintext );
						$images = $this->get_easyrecipe_images( $instruction_step->plaintext );

						if ( count( $images ) === 0 ) {
							// Create an instruction step without image.
							$group['instructions'][] = array(
									'text' => $text,
									'image' => '',
							);
						} else {
							// We have at least 1 image, create an instruction step for each image.
							foreach ( $images as $image ) {
								$group['instructions'][] = array(
									'text' => $text,
									'image' => $image['id'],
								);
								$text = ''; // Only use description for first step.
							}
						}
					}
				}
			}
			$instructions[] = $group;
			$recipe['instructions'] = $instructions;

			// Nutrition.
			$recipe['nutrition'] = array();

			$nutrition_mapping = array(
				'servingSize'           => 'serving_size',
				'calories'              => 'calories',
				'carbohydrates'         => 'carbohydrates',
				'protein'               => 'protein',
				'fat'                   => 'fat',
				'saturatedFat'          => 'saturated_fat',
				'unsaturatedFat'        => 'polyunsaturated_fat',
				'transFat'              => 'trans_fat',
				'cholesterol'           => 'cholesterol',
				'sodium'                => 'sodium',
				'fiber'                 => 'fiber',
				'sugar'                 => 'sugar',
			);

			foreach ( $nutrition_mapping as $easyrecipe_field => $wprm_field ) {
				$er_nutrition_data = $recipe_html->find( 'span[class=' . $easyrecipe_field . ']', 0 );

				if ( is_object( $er_nutrition_data ) ) {
					$value = trim( $er_nutrition_data->plaintext );
					$recipe['nutrition'][ $wprm_field ] = $value;
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
	 * @since    1.0.0
	 * @param		 mixed $id ID of the recipe we want replace.
	 * @param		 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		if ( ! class_exists( 'simple_html_dom' ) && ! class_exists( 'simple_html_dom_node' ) ) {
			require_once( WPRM_DIR . 'vendor/simple_html_dom/simple_html_dom.php' );
			libxml_use_internal_errors( true );
		}

		$id_parts = explode( '-', $id, 2 );
		$post_id = intval( $id_parts[0] );
		$recipe_index = intval( $id_parts[1] );

		$post = get_post( $post_id );
		$html = $this->get_html( $post->post_content );

		// Find EasyRecipe to replace with our shortcode.
		$easyrecipe = $html->find( 'div[class=easyrecipe]', $recipe_index );

		// If surrounded by wrapper we need to replace that as well.
		$parent = $easyrecipe->parent();
		if ( isset( $parent->class ) && false !== strpos( $parent->class, 'easyrecipeWrapper' ) ) {
			$easyrecipe = $parent;
		}
		$easyrecipe->outertext = '[wprm-recipe id="' . $wprm_id . '"]';

		$body = $html->find( 'body', 0 );
		$content = $body->innertext;

		$update_content = array(
			'ID' => $post_id,
			'post_content' => $content,
		);
		wp_update_post( $update_content );

		// Remove from found recipes.
		$found_recipes = get_option( 'wprm_import_easyrecipe_recipes', array() );
		unset( $found_recipes[ $id ] );
		update_option( 'wprm_import_easyrecipe_recipes', $found_recipes, false );

		// Migrate comment ratings.
		$comments = get_comments( array( 'post_id' => $post_id ) );

		foreach ( $comments as $comment ) {
			$comment_rating = intval( get_comment_meta( $comment->comment_ID, 'ERRating', true ) );
			if ( $comment_rating ) {
				WPRM_Comment_Rating::add_or_update_rating_for( $comment->comment_ID, $comment_rating );
			}
		}
	}

	/**
	 * Get EasyRecipe recipes that are used in this content.
	 *
	 * @since    1.0.0
	 * @param		 mixed $post_content Post content to find recipes in.
	 */
	private function get_easyrecipe_recipes( $post_content ) {
		$html = $this->get_html( $post_content );
		if ( $html ) {
			return $html->find( 'div[class=easyrecipe]' );
		} else {
			return array();
		}
	}

	/**
	 * Get post content as HTML.
	 *
	 * @since    1.0.0
	 * @param		 mixed $post_content Post content to get the HTML for.
	 */
	private function get_html( $post_content ) {
			$content = wpautop( $post_content );
			$html = new simple_html_dom();
			return $html->load( '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $content . '</body>' );
	}

	/**
	 * Get images that are used in the ER recipe.
	 *
	 * @since    1.0.0
	 * @param		 mixed $text Text to find images in.
	 */
	private function get_easyrecipe_images( $text ) {
		$images = array();

		preg_match_all( '/\[img[^\]]*]/i', $text, $easyrecipe_images );

		if ( isset( $easyrecipe_images[0] ) ) {
			foreach ( $easyrecipe_images[0] as $easyrecipe_image ) {
				preg_match( '/src=\"([^\"]*)\"/i', $easyrecipe_image, $image );

				if ( isset( $image[1] ) ) {
					$id = WPRM_Import_Helper::get_or_upload_attachment( $post_id, $image[1] );
					$image = wp_get_attachment_image_src( $id, array( 9999, 150 ) );

					$images[] = array(
						'id' => $id,
						'img' => $image[0],
					);
				}
			}
		}

		return $images;
	}

	/**
	 * Strip any EasyRecipe tags from the provided string.
	 *
	 * @since    1.0.0
	 * @param		 mixed $string Text to strip the tags from.
	 */
	private function strip_easyrecipe_tags( $string ) {
		$string = str_ireplace( '[b]', '', $string );
		$string = str_ireplace( '[/b]', '', $string );
		$string = str_ireplace( '[i]', '', $string );
		$string = str_ireplace( '[/i]', '', $string );
		$string = str_ireplace( '[u]', '', $string );
		$string = str_ireplace( '[/u]', '', $string );
		$string = str_ireplace( '[br]', '', $string );

		$string = preg_replace( '/\[img[^\]]*]/i', '', $string );

		$string = preg_replace( '/\[url[^\]]*]/i', '', $string );
		$string = str_ireplace( '[/url]', '', $string );

		return trim( $string );
	}

	/**
	 * Replace any EasyRecipe tags in the provided string with their HTML.
	 *
	 * @since    1.0.0
	 * @param		 mixed 	 $string Text to replace the tags in.
	 * @param		 boolean $images Allow images in the output.
	 */
	private function replace_easyrecipe_tags( $string, $images = false ) {
		$string = str_ireplace( '[b]', '<strong>', $string );
		$string = str_ireplace( '[/b]', '</strong>', $string );
		$string = str_ireplace( '[i]', '<em>', $string );
		$string = str_ireplace( '[/i]', '</em>', $string );
		$string = str_ireplace( '[u]', '<span style="text-decoration: underline;">', $string );
		$string = str_ireplace( '[/u]', '</span>', $string );
		$string = str_ireplace( '[br]', '<br/>', $string );

		if ( $images ) {
				$string = preg_replace( '/\[img([^\]]*)]/i', "<img$1 />", $string );
		} else {
				$string = preg_replace( '/\[img[^\]]*]/i', '', $string );
		}

		$string = preg_replace( '/\[url([^\]]*)]/i', "<a$1>", $string );
		$string = str_ireplace( '[/url]', '</a>', $string );

		return trim( $string );
	}

	/**
	 * Get time in minutes from ER time string.
	 *
	 * @since    1.0.0
	 * @param		 mixed $duration ER time string.
	 */
	private function easyrecipe_time_to_minutes( $duration = 'PT' ) {
		$date_abbr = array(
			'd' => 60*24,
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
