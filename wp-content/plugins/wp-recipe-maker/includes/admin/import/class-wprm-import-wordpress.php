<?php
/**
 * Responsible for importing WordPress.com recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing WordPress.com recipes.
 *
 * @since      4.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Wordpress extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    4.2.0
	 */
	public function get_uid() {
		return 'wordpress';
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
	 * @since    4.2.0
	 */
	public function get_name() {
		return 'WordPress.com shortcode';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    4.2.0
	 */
	public function get_settings_html() {
		 return '';
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    4.2.0
	 */
	public function get_recipe_count() {
		$recipes_found = get_option( 'wprm_import_wordpress_recipes', array() );
		return count( $recipes_found );
	}

	/**
	 * Search for recipes to import.
	 *
	 * @since	4.2.0
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
				$recipe_shortcodes = $this->get_wordpress_recipes( $post->post_content );

				foreach ( $recipe_shortcodes as $index => $recipe_shortcode ) {
					$name = isset( $recipe_shortcode['attributes']['title'] ) ? $recipe_shortcode['attributes']['title'] : __( 'Unknown', 'wp-recipe-maker' );

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

		$found_recipes = 0 === $page ? array() : get_option( 'wprm_import_wordpress_recipes', array() );
		$found_recipes = array_merge( $found_recipes, $recipes );

		update_option( 'wprm_import_wordpress_recipes', $found_recipes, false );

		$search_result = array(
			'finished' => $finished,
			'recipes' => count( $found_recipes ),
		);

		return $search_result;
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    4.2.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$found_recipes = get_option( 'wprm_import_wordpress_recipes', array() );

		$limit = 100;
		$offset = $limit * $page;

		return array_slice( $found_recipes, $offset, $limit );
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    4.2.0
	 * @param		 mixed $id ID of the recipe we want to import.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$id_parts = explode( '-', $id, 2 );
		$post_id = intval( $id_parts[0] );
		$recipe_index = intval( $id_parts[1] );

		$post = get_post( $post_id );
		$recipes = $this->get_wordpress_recipes( $post->post_content );
		$recipe_shortcode = isset( $recipes[ $recipe_index ] ) ? $recipes[ $recipe_index ] : false;

		if ( $recipe_shortcode ) {
			$recipe = array(
				'import_id' => 0, // Set to 0 because we need to create a new recipe post.
				'import_backup' => array(
					'wordpress_shortcode' => $recipe_shortcode['shortcode'],
				),
			);

			$attributes = $recipe_shortcode['attributes'];

			// Featured Image.
			$image_url = isset( $attributes['image'] ) ? $attributes['image'] : false;
			$recipe['image_id'] = WPRM_Import_Helper::get_or_upload_attachment( $post_id, $image_url );

			// Simple matching.
			$recipe['name'] = isset( $attributes['title'] ) ? $attributes['title'] : '';
			$recipe['summary'] = isset( $attributes['description'] ) ? $attributes['description'] : '';

			// Servings.
			$servings_attribute = isset( $attributes['servings'] ) ? trim( $attributes['servings'] ) : '';

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
			$total_time = isset( $attributes['time'] ) ? trim( $attributes['time'] ) : '';
			$recipe['total_time'] = max( 0, $this->wordpress_read_time( $total_time ) ) / 60;

			// Preparation for notes content.
			$notes_content = $recipe_shortcode['content'];

			// Ingredients.
			$ingredient_shortcodes = $this->get_wordpress_shortcode( $recipe_shortcode['content'], 'recipe-ingredients' );
			$ingredients = array();

			foreach ( $ingredient_shortcodes as $ingredient_shortcode ) {
				// Remove ingredient shortcode from notes.
				$notes_content = str_replace( $ingredient_shortcode['shortcode'], '', $notes_content );
				
				// Group with optional name.
				$group = array(
					'ingredients' => array(),
					'name' => '',
				);
				
				if ( isset( $ingredient_shortcode['attributes']['title'] ) ) {
					$group['name'] = trim( $ingredient_shortcode['attributes']['title'] );
				}

				// Get ingredients.
				$ingredient_shortcode_lines = $this->parse_blob( $ingredient_shortcode['content'] );

				foreach( $ingredient_shortcode_lines as $ingredient_shortcode_line ) {
					$group['ingredients'][] = array(
						'raw' => $ingredient_shortcode_line,
					);
				}

				$ingredients[] = $group;
			}
			$recipe['ingredients'] = $ingredients;

			// Instructions.
			$instruction_shortcodes = $this->get_wordpress_shortcode( $recipe_shortcode['content'], 'recipe-directions' );
			$instructions = array();

			foreach ( $instruction_shortcodes as $instruction_shortcode ) {
				// Remove instruction shortcode from notes.
				$notes_content = str_replace( $instruction_shortcode['shortcode'], '', $notes_content );

				// Group with optional name.
				$group = array(
					'instructions' => array(),
					'name' => '',
				);

				if ( isset( $instruction_shortcode['attributes']['title'] ) ) {
					$group['name'] = trim( $instruction_shortcode['attributes']['title'] );
				}

				// Get instructions.
				$instruction_shortcode_lines = $this->parse_blob( $instruction_shortcode['content'] );

				foreach( $instruction_shortcode_lines as $instruction_shortcode_line ) {
					$group['instructions'][] = array(
						'text' => $instruction_shortcode_line,
						'image' => '',
					);
				}

				$instructions[] = $group;
			}
			$recipe['instructions'] = $instructions;

			// Notes.
			$notes_content = str_ireplace( '[recipe-notes]', '', $notes_content );
			$notes_content = str_ireplace( '[/recipe-notes]', '', $notes_content );

			$recipe['notes'] = trim( $notes_content );
		} else {
			$recipe = false;
		}

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since	4.2.0
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

		$recipes = $this->get_wordpress_recipes( $content );
		$recipe_shortcode = isset( $recipes[ $recipe_index ] ) ? $recipes[ $recipe_index ] : false;

		$content = str_replace( $recipe_shortcode['shortcode'], '[wprm-recipe id="' . $wprm_id . '"]', $content );

		$update_content = array(
			'ID' => $post_id,
			'post_content' => $content,
		);
		wp_update_post( $update_content );

		// Remove from found recipes.
		$found_recipes = get_option( 'wprm_import_wordpress_recipes', array() );
		unset( $found_recipes[ $id ] );
		update_option( 'wprm_import_wordpress_recipes', $found_recipes, false );
	}

	/**
	 * Get WordPress.com recipes that are used in this content.
	 *
	 * @since	4.2.0
	 * @param	mixed $content Content to find recipes in.
	 */
	private function get_wordpress_recipes( $content ) {
		return $this->get_wordpress_shortcode( $content, 'recipe' );
	}

	/**
	 * Get shortcodes with attributes in content.
	 *
	 * @since	4.2.0
	 * @param	mixed $content Content to find shortcodes in.
	 * @param	mixed $shortcode_name Shortcode to find.
	 */
	private function get_wordpress_shortcode( $content, $shortcode_name ) {
		$found_shortcodes = array();
		$pattern = get_shortcode_regex( array( $shortcode_name ) );

		if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) && array_key_exists( 2, $matches ) ) {
			foreach ( $matches[2] as $key => $value ) {
				if ( $shortcode_name === $value ) {
					$shortcode = $matches[0][ $key ];

					// Has to be a [recipe][/recipe] format.
					$closing_tag = '[/' . $shortcode_name . ']';
					if ( $closing_tag  === strtolower( substr( $shortcode, -1 * strlen( $closing_tag ) ) ) ) {
						$found_shortcodes[] = array(
							'shortcode' => $shortcode,
							'content' => trim( $matches[5][ $key ] ),
							'attributes' => shortcode_parse_atts( stripslashes( $matches[3][ $key ] ) ),
						);
					}
				}
			}
		}

		return $found_shortcodes;
	}

	/**
	 * Parse blob into individual lines.
	 *
	 * @since	4.2.0
	 * @param	mixed $blog Blob to parse.
	 */
	private function parse_blob( $blob ) {
		$lines = array();

		$bits = explode( PHP_EOL, $blob );
		foreach ( $bits as $bit ) {
			$test_bit = strip_tags( trim( $bit ) );
			// Skip empty lines.
			if ( ! $test_bit ) {
				continue;
			}

			$bit = trim( $bit );

			// Remove list indicators.
			$bit = preg_replace( '/^\p{Pd}\s*/u', '', $bit );
			$bit = preg_replace( '/^\d.\s+/u', '', $bit );
			$bit = str_ireplace( '<li>', '', $bit );
			$bit = str_ireplace( '</li>', '', $bit );

			$lines[] = $bit;
		}

		return $lines;
	}

	/**
	 * WordPress.com Read Time function.
	 *
	 * @since    4.2.0
	 * @param	 mixed $time  Time to parse.
	 */
	private function wordpress_read_time( $time ) {
		$time = str_replace( 'hrs', 'hour', $time );
		$time = str_replace( 'hr', 'hour', $time );
		$time = str_replace( 'minutes', 'min', $time );
		$time = str_replace( 'mins', 'min', $time );

		if ( is_numeric( $time ) ) {
			$time = "{$time} minutes";
		}

		return strtotime( $time, 0 );
	}
}
