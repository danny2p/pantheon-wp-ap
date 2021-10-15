<?php
/**
 * Responsible for importing WP Ultimate Recipe recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing WP Ultimate Recipe recipes.
 *
 * @since      1.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Wpultimaterecipe extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    1.3.0
	 */
	public function get_uid() {
		return 'wpultimaterecipe';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    1.10.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    1.3.0
	 */
	public function get_name() {
		return 'WP Ultimate Recipe';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    1.3.0
	 */
	public function get_settings_html() {
		$html = '<p><strong>Important:</strong> Make sure to have the WP Ultimate Recipe (Premium) plugin active during the import. After import, <a href="https://help.bootstrapped.ventures/article/96-import-from-wp-ultimate-recipe" target="_blank">set up redirects as mentioned in the documentation</a>.</p>';
		$html .= '<h4>Import Type</h4>';
		$html .= '<input type="radio" name="wpurp-import-type" value="convert" id="wpurp-import-type-convert" checked> <label for="wpurp-import-type-convert">Convert to posts</label>';
		$html .= "<p>Most people should use this option. The recipe post type will be converted to a regular post that includes a WP Recipe Maker recipe. Every occurrence of the WP Ultimate Recipe recipe shortcode will be replaced as well.</p>";
		$html .= '<input type="radio" name="wpurp-import-type" value="hide" id="wpurp-import-type-hide" /> <label for="wpurp-import-type-hide">No conversion to posts needed</label>';
		$html .= '<p>Only use this if you manually disabled the advanced "Recipes act as Posts" setting in WP Ultimate Recipe. The new WP Recipe Maker recipes will only show up wherever you used the WP Ultimate Recipe recipe shortcode. Do not use this option if you are unsure. Contact us instead to confirm.</p>';

		// Match recipe tags.
		$html .= '<h4>Recipe Tags</h4>';

		$wpurp_taxonomies = get_option( 'wpurp_taxonomies', array() );
		unset( $wpurp_taxonomies['ingredient'] );

		$wpurp_taxonomies['post_tag'] = array( 'labels' => array( 'name' => __( 'Tags' ) ) );
		$wpurp_taxonomies['category'] = array( 'labels' => array( 'name' => __( 'Categories' ) ) );

		$wprm_taxonomies = WPRM_Taxonomies::get_taxonomies();

		foreach ( $wprm_taxonomies as $wprm_taxonomy => $options ) {
			$wprm_key = substr( $wprm_taxonomy, 5 );

			$html .= '<label for="wpurp-tags-' . $wprm_key . '">' . $options['name'] . ':</label> ';
			$html .= '<select name="wpurp-tags-' . $wprm_key . '" id="wpurp-tags-' . $wprm_key . '">';
			$html .= "<option value=\"\">Don't import anything for this tag</option>";
			foreach ( $wpurp_taxonomies as $name => $options ) {
				$selected = $wprm_key === $name || 'wpurp_' . $wprm_key === $name ? ' selected="selected"' : '';
				$html .= '<option value="' . esc_attr( $name ) . '"' . esc_html( $selected ) . '>' . esc_html( $options['labels']['name'] ) . '</option>';
			}
			$html .= '</select>';
			$html .= '<br />';
		}

		return $html;
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    1.10.0
	 */
	public function get_recipe_count() {
		$args = array(
			'post_type' => 'recipe',
			'post_status' => 'any',
			'posts_per_page' => 1,
		);

		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    1.3.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$recipes = array();

		$limit = 100;
		$offset = $limit * $page;

		$args = array(
				'post_type' => 'recipe',
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
	 * @since    1.3.0
	 * @param		 mixed $id ID of the recipe we want to import.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$post = get_post( $id );
		$post_meta = get_post_custom( $id );
		$import_type = isset( $post_data['wpurp-import-type'] ) ? $post_data['wpurp-import-type'] : '';

		// If the import type is not set, redirect back.
		if ( ! in_array( $import_type, array( 'convert', 'hide' ), true ) ) {
			return new WP_Error( 'import_type', 'You need to select an import type.' );
			wp_safe_redirect( add_query_arg( array( 'from' => $this->get_uid(), 'error' => rawurlencode( 'You need to select an import type.' ) ), admin_url( 'admin.php?page=wprm_import' ) ) );
			exit();
		}

		// If we're converting the WPURP recipe to a normal post we want the import ID to be 0.
		$import_id = 'convert' === $import_type ? 0 : $id;

		$recipe = array(
			'import_id' => $import_id,
			'import_backup' => array(
				'wpultimaterecipe_recipe_id' => $id,
				'wpultimaterecipe_import_type' => $import_type,
			),
		);

		$alternate_image = isset( $post_meta['recipe_alternate_image'] ) ? $post_meta['recipe_alternate_image'][0] : false;
		$recipe['image_id'] = $alternate_image ? $alternate_image : get_post_thumbnail_id( $id );

		$recipe_title = isset( $post_meta['recipe_title'] ) ? $post_meta['recipe_title'][0] : false;
		$recipe['name'] = $recipe_title ? $recipe_title : $post->post_title;

		$recipe['summary'] = isset( $post_meta['recipe_description'] ) ? $post_meta['recipe_description'][0] : '';
		$recipe['servings'] = isset( $post_meta['recipe_servings'] ) ? $post_meta['recipe_servings'][0] : '';
		$recipe['servings_unit'] = isset( $post_meta['recipe_servings_type'] ) ? $post_meta['recipe_servings_type'][0] : '';
		$recipe['notes'] = isset( $post_meta['recipe_notes'] ) ? $post_meta['recipe_notes'][0] : '';

		// Recipe Times.
		$prep_time = isset( $post_meta['recipe_prep_time'] ) ? $post_meta['recipe_prep_time'][0] : '';
		$prep_time_unit = isset( $post_meta['recipe_prep_time_text'] ) ? $post_meta['recipe_prep_time_text'][0] : '';
		$recipe['prep_time'] = self::get_time_in_minutes( $prep_time, $prep_time_unit );

		$cook_time = isset( $post_meta['recipe_cook_time'] ) ? $post_meta['recipe_cook_time'][0] : '';
		$cook_time_unit = isset( $post_meta['recipe_cook_time_text'] ) ? $post_meta['recipe_cook_time_text'][0] : '';
		$recipe['cook_time'] = self::get_time_in_minutes( $cook_time, $cook_time_unit );

		$passive_time = isset( $post_meta['recipe_passive_time'] ) ? $post_meta['recipe_passive_time'][0] : '';
		$passive_time_unit = isset( $post_meta['recipe_passive_time_text'] ) ? $post_meta['recipe_passive_time_text'][0] : '';
		$passive_time_minutes = self::get_time_in_minutes( $passive_time, $passive_time_unit );

		// Optionally remove "false && " to import passive time as a separate field.
		if ( false && $passive_time_minutes ) {
			$recipe['custom_time_label'] = __( 'Passive Time', 'wp-recipe-maker' );
			$recipe['custom_time'] = $passive_time_minutes;
		}

		$recipe['total_time'] = $recipe['prep_time'] + $recipe['cook_time'] + $passive_time_minutes;

		// Recipe Tags.
		$recipe['tags'] = array();

		$wprm_taxonomies = WPRM_Taxonomies::get_taxonomies();
		foreach ( $wprm_taxonomies as $wprm_taxonomy => $options ) {
			$wprm_key = substr( $wprm_taxonomy, 5 );
			$tag = isset( $post_data[ 'wpurp-tags-' . $wprm_key ] ) ? $post_data[ 'wpurp-tags-' . $wprm_key ] : false;

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
		$ingredients = isset( $post_meta['recipe_ingredients'] ) ? maybe_unserialize( $post_meta['recipe_ingredients'][0] ) : array();
		$recipe['ingredients'] = array();

		$current_group = array(
			'name' => '',
			'ingredients' => array(),
		);
		foreach ( $ingredients as $ingredient ) {
			if ( isset( $ingredient['group'] ) && $ingredient['group'] !== $current_group['name'] ) {
				$recipe['ingredients'][] = $current_group;
				$current_group = array(
					'name' => $ingredient['group'],
					'ingredients' => array(),
				);
			}

			$current_group['ingredients'][] = array(
				'amount' => $ingredient['amount'],
				'unit' => $ingredient['unit'],
				'name' => $ingredient['ingredient'],
				'notes' => $ingredient['notes'],
			);
		}
		$recipe['ingredients'][] = $current_group;

		// Recipe Instructions.
		$instructions = isset( $post_meta['recipe_instructions'] ) ? maybe_unserialize( $post_meta['recipe_instructions'][0] ) : array();
		$recipe['instructions'] = array();

		$current_group = array(
			'name' => '',
			'instructions' => array(),
		);
		foreach ( $instructions as $instruction ) {
			if ( isset( $instruction['group'] ) && $instruction['group'] !== $current_group['name'] ) {
				$recipe['instructions'][] = $current_group;
				$current_group = array(
					'name' => $instruction['group'],
					'instructions' => array(),
				);
			}

			$current_group['instructions'][] = array(
				'text' => $instruction['description'],
				'image' => $instruction['image'],
			);
		}
		$recipe['instructions'][] = $current_group;

		// Recipe Nutrition.
		$recipe['nutrition'] = array();

		$nutrition_mapping = array(
			'serving_size'          => 'serving_size',
			'calories'              => 'calories',
			'carbohydrate'          => 'carbohydrates',
			'protein'               => 'protein',
			'fat'                   => 'fat',
			'saturated_fat'         => 'saturated_fat',
			'polyunsaturated_fat'   => 'polyunsaturated_fat',
			'monounsaturated_fat'   => 'monounsaturated_fat',
			'trans_fat'             => 'trans_fat',
			'cholesterol'           => 'cholesterol',
			'sodium'                => 'sodium',
			'potassium'             => 'potassium',
			'fiber'                 => 'fiber',
			'sugar'                 => 'sugar',
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

		$nutrition = isset( $post_meta['recipe_nutritional'] ) ? maybe_unserialize( $post_meta['recipe_nutritional'][0] ) : array();

		foreach ( $nutrition_mapping as $wpurp_field => $wprm_field ) {
			$recipe['nutrition'][ $wprm_field ] = isset( $nutrition[ $wpurp_field ] ) ? $nutrition[ $wpurp_field ] : '';

			if ( array_key_exists( $wprm_field, $migrate_values ) && $recipe['nutrition'][ $wprm_field ] ) {
				// Daily needs * currently saved as percentage, round to 1 decimal.
				$recipe['nutrition'][ $wprm_field ] = round( $migrate_values[ $wprm_field ] * ( $recipe['nutrition'][ $wprm_field ] / 100 ), 1 );
			}
		}

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    1.3.0
	 * @param		 mixed $id ID of the recipe we want replace.
	 * @param		 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		$import_type = isset( $post_data['wpurp-import-type'] ) ? $post_data['wpurp-import-type'] : '';

		// If the import type is not set, redirect back.
		if ( ! in_array( $import_type, array( 'convert', 'hide' ), true ) ) {
			return new WP_Error( 'import_type', 'You need to select an import type.' );
			wp_safe_redirect( add_query_arg( array( 'from' => $this->get_uid(), 'error' => rawurlencode( 'You need to select an import type.' ) ), admin_url( 'admin.php?page=wprm_import' ) ) );
			exit();
		}

		// Migrate user ratings.
		$user_ratings = get_post_meta( $id, 'recipe_user_ratings' );

		foreach ( $user_ratings as $wpurp_rating ) {
			$user_rating = array(
				'recipe_id' => $wprm_id,
				'user_id' => $wpurp_rating['user'],
				'ip' => $wpurp_rating['ip'],
				'rating' => $wpurp_rating['rating'],
			);

			WPRM_Rating_Database::add_or_update_rating( $user_rating );
		}

		// If import type is "hide" we count on the fallback shortcode.
		if ( 'convert' === $import_type ) {
			$post = get_post( $id );
			$content = $post->post_content;

			if ( 0 === substr_count( $content, '[recipe]' ) ) {
				$content .= ' [wprm-recipe id="' . $wprm_id . '"]';
			} else {
				$content = str_ireplace( '[recipe]', '[wprm-recipe id="' . $wprm_id . '"]', $content );
			}

			$content = preg_replace( "/\[ultimate-recipe\s.*?id='?\"?" . $id . '.*?]/im', '[wprm-recipe id="' . $wprm_id . '"]', $content );

			// Remove searchable recipe part
			$content = preg_replace( '/\[wpurp-searchable-recipe\][^\[]*\[\/wpurp-searchable-recipe\]/', '', $content );

			$update_content = array(
				'ID' => $id,
				'post_type' => 'post',
				'post_content' => $content,
			);
			wp_update_post( $update_content );

			// Store reference to WPRM recipe.
			add_post_meta( $id, '_wpurp_wprm_migrated', $wprm_id );

			// Migrate potential ER comment ratings.
			$comments = get_comments( array( 'post_id' => $id ) );

			foreach ( $comments as $comment ) {
				$comment_rating = intval( get_comment_meta( $comment->comment_ID, 'ERRating', true ) );
				if ( $comment_rating ) {
					WPRM_Comment_Rating::add_or_update_rating_for( $comment->comment_ID, $comment_rating );
				}
			}
		}
	}

	/**
	 * Convert time string to minutes.
	 *
	 * @since    1.3.0
	 * @param		 mixed $time Time string to convert to minutes.
	 * @param		 mixed $unit Unit of the time string.
	 */
	private function get_time_in_minutes( $time, $unit ) {
		$minutes = intval( $time );

		if ( ! $unit ) {
			$text_in_time = preg_replace( '/[\d\s]/', '', $time );
			$unit = $text_in_time;
		}

		if ( strtolower( $unit ) === strtolower( __( 'hour', 'wp-ultimate-recipe' ) )
				|| strtolower( $unit ) === strtolower( __( 'hours', 'wp-ultimate-recipe' ) )
				|| strtolower( $unit ) === 'h'
				|| strtolower( $unit ) === 'hr'
				|| strtolower( $unit ) === 'hrs' ) {
				$minutes = $minutes * 60;
		}

		return $minutes;
	}
}
