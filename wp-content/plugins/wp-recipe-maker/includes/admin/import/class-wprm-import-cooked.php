<?php
/**
 * Responsible for importing Cooked recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.26.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing Cooked recipes.
 *
 * @since      1.26.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Cooked extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    1.26.0
	 */
	public function get_uid() {
		return 'cooked';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    1.26.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    1.26.0
	 */
	public function get_name() {
		return 'Cooked';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    1.26.0
	 */
	public function get_settings_html() {
		$html = '<h4>Import Type</h4>';
		$html .= '<input type="radio" name="cooked-import-type" value="convert" id="cooked-import-type-convert" checked> <label for="cooked-import-type-convert">Convert to posts</label>';
		$html .= "<p>Most people should use this option. The recipe post type will be converted to a regular post that includes a WP Recipe Maker recipe. Every occurrence of the Cooked shortcode will be replaced as well.</p>";
		$html .= '<input type="radio" name="cooked-import-type" value="hide" id="cooked-import-type-hide" /> <label for="cooked-import-type-hide">No conversion to posts needed</label>';
		$html .= '<p>Only use this if you had the advanced "Disable Public Recipes" setting enabled in Cooked. The new WP Recipe Maker recipes will only show up wherever you used the Cooked recipe shortcode. Do not use this option if you are unsure. Contact us instead to confirm.</p>';
		$html .= '<h4>Recipe Tags</h4>';

		$cooked_taxonomies = array(
			'cp_recipe_category' => 'Categories',
			'cp_recipe_tags' => 'Tags',
			'cp_recipe_cuisine' => 'Cuisines',
			'cp_recipe_cooking_method' => 'Cooking Methods',
		);

		$wprm_taxonomies = WPRM_Taxonomies::get_taxonomies();

		foreach ( $wprm_taxonomies as $wprm_taxonomy => $options ) {
			$wprm_key = substr( $wprm_taxonomy, 5 );

			$html .= '<label for="cooked-tags-' . $wprm_key . '">' . $options['name'] . ':</label> ';
			$html .= '<select name="cooked-tags-' . $wprm_key . '" id="cooked-tags-' . $wprm_key . '">';
			$html .= "<option value=\"\">Don't import anything for this tag</option>";
			foreach ( $cooked_taxonomies as $name => $label ) {
				$selected = $wprm_key === $name ? ' selected="selected"' : '';
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
	 * @since    1.26.0
	 */
	public function get_recipe_count() {
		$args = array(
			'post_type' => 'cp_recipe',
			'post_status' => 'any',
			'posts_per_page' => 1,
		);

		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    1.26.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$recipes = array();

		$limit = 100;
		$offset = $limit * $page;

		$args = array(
				'post_type' => 'cp_recipe',
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
	 * @since    1.26.0
	 * @param		 mixed $id ID of the recipe we want to import.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$post = get_post( $id );
		$import_type = isset( $post_data['cooked-import-type'] ) ? $post_data['cooked-import-type'] : '';

		// If the import type is not set, redirect back.
		if ( ! in_array( $import_type, array( 'convert', 'hide' ), true ) ) {
			return new WP_Error( 'import_type', 'You need to select an import type.' );
			wp_safe_redirect( add_query_arg( array( 'from' => $this->get_uid(), 'error' => rawurlencode( 'You need to select an import type.' ) ), admin_url( 'admin.php?page=wprm_import' ) ) );
			exit();
		}

		// If we're converting the Cooked recipe to a normal post we want the import ID to be 0.
		$import_id = 'convert' === $import_type ? 0 : $id;
		$cooked_recipe = get_post_meta( $id, '_recipe_settings', true );

		$recipe = array(
			'import_id' => $import_id,
			'import_backup' => array(
				'cooked_recipe_id' => $id,
				'cooked_import_type' => $import_type,
				'cooked_recipe_settings' => $cooked_recipe,
			),
		);

		$recipe['image_id'] = get_post_thumbnail_id( $id );
		$recipe['name'] = $post->post_title;
		$recipe['summary'] = isset( $cooked_recipe['excerpt'] ) ? $cooked_recipe['excerpt'] : '';
		$recipe['servings'] = isset( $cooked_recipe['nutrition']['servings'] ) ? $cooked_recipe['nutrition']['servings'] : '';

		// Recipe Times.
		$recipe['prep_time'] = isset( $cooked_recipe['prep_time'] ) ? $cooked_recipe['prep_time'] : '';
		$recipe['cook_time'] = isset( $cooked_recipe['cook_time'] ) ? $cooked_recipe['cook_time'] : '';

		$total_time = intval( $recipe['prep_time'] ) + intval( $recipe['cook_time'] );
		$recipe['total_time'] = $total_time ? $total_time : '';

		// Recipe Tags.
		$recipe['tags'] = array();

		$wprm_taxonomies = WPRM_Taxonomies::get_taxonomies();
		foreach ( $wprm_taxonomies as $wprm_taxonomy => $options ) {
			$wprm_key = substr( $wprm_taxonomy, 5 );
			$tag = isset( $post_data[ 'cooked-tags-' . $wprm_key ] ) ? $post_data[ 'cooked-tags-' . $wprm_key ] : false;

			if ( $tag ) {
				$terms = get_the_terms( $id, $tag );
				if ( $terms && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$recipe['tags'][ $wprm_key ][] = $term->name;
					}
				}
			}
		}

		// Recipe Ingredients.
		$ingredients = isset( $cooked_recipe['ingredients'] ) ? $cooked_recipe['ingredients'] : array();
		$recipe['ingredients'] = array();

		$current_group = array(
			'name' => '',
			'ingredients' => array(),
		);
		foreach ( $ingredients as $ingredient ) {
			if ( isset( $ingredient['section_heading_name'] ) ) {
				$recipe['ingredients'][] = $current_group;
				$current_group = array(
					'name' => $ingredient['section_heading_name'],
					'ingredients' => array(),
				);
			} else {
				$current_group['ingredients'][] = array(
					'amount' => $ingredient['amount'],
					'unit' => $ingredient['measurement'],
					'name' => $ingredient['name'],
					'notes' => '',
				);
			}
		}
		$recipe['ingredients'][] = $current_group;

		// Recipe Instructions.
		$instructions = isset( $cooked_recipe['directions'] ) ? $cooked_recipe['directions'] : array();
		$recipe['instructions'] = array();

		$current_group = array(
			'name' => '',
			'instructions' => array(),
		);
		foreach ( $instructions as $instruction ) {
			if ( isset( $instruction['section_heading_name'] ) ) {
				$recipe['instructions'][] = $current_group;
				$current_group = array(
					'name' => $instruction['section_heading_name'],
					'instructions' => array(),
				);
			} else {
				$current_group['instructions'][] = array(
					'text' => $instruction['content'],
					'image' => $instruction['image'],
				);
			}
		}
		$recipe['instructions'][] = $current_group;

		// Recipe Nutrition.
		$recipe['nutrition'] = array();

		$nutrition_mapping = array(
			'serving_size'          => 'serving_size',
			'calories'              => 'calories',
			'carbs'          		=> 'carbohydrates',
			'protein'               => 'protein',
			'fat'                   => 'fat',
			'sat_fat'		        => 'saturated_fat',
			'trans_fat'             => 'trans_fat',
			'cholesterol'           => 'cholesterol',
			'sodium'                => 'sodium',
			'potassium'             => 'potassium',
			'fiber'                 => 'fiber',
			'sugars'                => 'sugar',
			'vitamin_a'             => 'vitamin_a',
			'vitamin_c'             => 'vitamin_c',
			'calcium'               => 'calcium',
			'iron'                  => 'iron',
		);

		$migrate_values = array(
			'vitamin_a' => 5000,
			'vitamin_c' => 82.5,
			'calcium' => 1000,
			'iron' => 18,
		);

		$nutrition = isset( $cooked_recipe['nutrition'] ) ? $cooked_recipe['nutrition'] : array();

		foreach ( $nutrition_mapping as $cooked_field => $wprm_field ) {
			$recipe['nutrition'][ $wprm_field ] = isset( $nutrition[ $cooked_field ] ) ? $nutrition[ $cooked_field ] : '';

			if ( array_key_exists( $wprm_field, $migrate_values ) && $recipe['nutrition'][ $wprm_field ] ) {
				// Daily needs * currently saved as percentage, round to 1 decimal.
				$recipe['nutrition'][ $wprm_field ] = round( $migrate_values[ $nutrient ] * ( $recipe['nutrition'][ $wprm_field ] / 100 ), 1 );
			}
		}

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    1.26.0
	 * @param		 mixed $id ID of the recipe we want replace.
	 * @param		 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		$import_type = isset( $post_data['cooked-import-type'] ) ? $post_data['cooked-import-type'] : '';

		// If the import type is not set, redirect back.
		if ( ! in_array( $import_type, array( 'convert', 'hide' ), true ) ) {
			return new WP_Error( 'import_type', 'You need to select an import type.' );
			wp_safe_redirect( add_query_arg( array( 'from' => $this->get_uid(), 'error' => rawurlencode( 'You need to select an import type.' ) ), admin_url( 'admin.php?page=wprm_import' ) ) );
			exit();
		}

		// If import type is "hide" we count on the fallback shortcode.
		if ( 'convert' === $import_type ) {
			$post = get_post( $id );

			// Get template content.
			$cooked_recipe = get_post_meta( $id, '_recipe_settings', true );

			$content = '';
			if ( $cooked_recipe && isset( $cooked_recipe['content'] ) ) {
				$pattern = get_shortcode_regex( array(
					'cooked-browse',
					'cooked-search',
					'cooked-recipe',
					'cooked-categories',
					'cooked-recipe-list',
					'cooked-recipe-card',
					'cooked-timer',
					'cooked-title',
					'cooked-gallery',
					'cooked-image',
					'cooked-info',
					'cooked-excerpt',
					'cooked-ingredients',
					'cooked-directions',
					'cooked-nutrition',
				) );
				$content = preg_replace_callback( "/$pattern/", 'strip_shortcode_tag', $cooked_recipe['content'] );
				$content = apply_filters( 'wpautop', $content );
	
				// Remove blank lines.
				$content = str_ireplace( '<p></p>', '', $content );
				$content = str_ireplace( '<p><br></p>', '', $content );
				$content = str_ireplace( '<p><br/></p>', '', $content );
			}

			$content .= '[wprm-recipe id="' . $wprm_id . '"]';

			// Update content and convert to post.
			$update_content = array(
				'ID' => $id,
				'post_type' => 'post',
				'post_content' => $content,
			);
			wp_update_post( $update_content );

			// Append categories and tags to converted post.
			$terms = get_the_terms( $id, 'cp_recipe_category' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$categories = array();
				foreach ( $terms as $term ) {
					$categories[] = $term->name;
				}
				wp_set_object_terms( $id, $categories, 'category', true );
			}
			$terms = get_the_terms( $id, 'cp_recipe_tags' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$tags = array();
				foreach ( $terms as $term ) {
					$tags[] = $term->name;
				}
				wp_set_object_terms( $id, $tags, 'post_tag', true );
			}
		}
	}
}
