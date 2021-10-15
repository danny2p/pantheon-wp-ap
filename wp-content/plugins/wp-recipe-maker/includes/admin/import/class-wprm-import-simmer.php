<?php
/**
 * Responsible for importing Simmer recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing Simmer recipes.
 *
 * @since      4.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Simmer extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    4.2.0
	 */
	public function get_uid() {
		return 'simmer';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    4.2.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    4.2.0
	 */
	public function get_name() {
		return 'Recipes by Simmer';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    4.2.0
	 */
	public function get_settings_html() {
		$html = '<h4>Import Type</h4>';
		$html = '<p>Not sure what to choose? Please contact us!</p>';
		$html .= '<input type="radio" name="simmer-import-type" value="convert" id="simmer-import-type-convert" checked> <label for="simmer-import-type-convert">Convert to posts</label>';
		$html .= "<p>The recipe post type will be converted to a regular post that includes a WP Recipe Maker recipe. Simmer shortcodes will not work anymore.</p>";
		$html .= '<input type="radio" name="simmer-import-type" value="hide" id="simmer-import-type-hide" /> <label for="simmer-import-type-hide">No conversion to posts needed</label>';
		$html .= '<p>The new WP Recipe Maker recipes will only show up wherever you used the Simmer recipe shortcode.</p>';

		return $html;
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    4.2.0
	 */
	public function get_recipe_count() {
		// Don't show Simmer option if WP Ultimate Recipe is active.
		if ( class_exists( 'WPUltimateRecipe' ) ) {
			return 0;
		}

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
	 * @since    4.2.0
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
	 * @since    4.2.0
	 * @param		 mixed $id ID of the recipe we want to import.
	 * @param		 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$post = get_post( $id );
		$post_meta = get_post_custom( $id );
		$import_type = isset( $post_data['simmer-import-type'] ) ? $post_data['simmer-import-type'] : '';

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
				'simmer_recipe_id' => $id,
				'simmer_import_type' => $import_type,
			),
		);

		// Simple matching.
		$recipe['image_id'] = get_post_thumbnail_id( $id );
		$recipe['name'] = $post->post_title;
		$recipe['servings'] = isset( $post_meta['_recipe_servings'] ) ? $post_meta['_recipe_servings'][0] : '';
		$recipe['servings_unit'] = isset( $post_meta['_recipe_servings_label'] ) ? $post_meta['_recipe_servings_label'][0] : '';
		$recipe['prep_time'] = isset( $post_meta['_recipe_prep_time'] ) ? $post_meta['_recipe_prep_time'][0] : 0;
		$recipe['cook_time'] = isset( $post_meta['_recipe_cook_time'] ) ? $post_meta['_recipe_cook_time'][0] : 0;
		$recipe['total_time'] = isset( $post_meta['_recipe_total_time'] ) ? $post_meta['_recipe_total_time'][0] : 0;

		// Author.
		$recipe['author_name'] = isset( $post_meta['_recipe_source_text'] ) ? $post_meta['_recipe_source_text'][0] : '';

		if ( '' !== trim( $recipe['author_name'] ) ) {
			$recipe['author_display'] = 'custom';
			$recipe['author_link'] = isset( $post_meta['_recipe_source_url'] ) ? $post_meta['_recipe_source_url'][0] : '';
		}

		// Recipe Tags.
		$recipe['tags'] = array();

		$terms = get_the_terms( $id, 'recipe_category' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$recipe['tags'][ 'course' ][] = $term->name;
			}
		}

		// Recipe Ingredients.
		$ingredients = $this->get_simmer_items( $id, 'ingredient' );
		$recipe['ingredients'] = array();

		$current_group = array(
			'name' => '',
			'ingredients' => array(),
		);
		foreach ( $ingredients as $ingredient ) {
			if ( isset( $ingredient['is_heading'] ) && $ingredient['is_heading'] ) {
				$recipe['ingredients'][] = $current_group;
				$current_group = array(
					'name' => isset( $ingredient['description'] ) ? $ingredient['description'] : '',
					'ingredients' => array(),
				);
			} else {
				$current_group['ingredients'][] = array(
					'amount' => isset( $ingredient['amount'] ) ? $ingredient['amount'] : '',
					'unit' => isset( $ingredient['unit'] ) ? $ingredient['unit'] : '',
					'name' => isset( $ingredient['description'] ) ? $ingredient['description'] : '',
					'notes' => '',
				);
			}
		}
		$recipe['ingredients'][] = $current_group;

		// Recipe Instructions.
		$instructions = $this->get_simmer_items( $id, 'instruction' );
		$recipe['instructions'] = array();

		$current_group = array(
			'name' => '',
			'instructions' => array(),
		);
		foreach ( $instructions as $instruction ) {
			if ( isset( $instruction['is_heading'] ) && $instruction['is_heading'] ) {
				$recipe['instructions'][] = $current_group;
				$current_group = array(
					'name' => isset( $instruction['description'] ) ? $instruction['description'] : '',
					'instructions' => array(),
				);
			} else {
				$current_group['instructions'][] = array(
					'text' => isset( $instruction['description'] ) ? $instruction['description'] : '',
					'image' => '',
				);
			}
		}
		$recipe['instructions'][] = $current_group;

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
		$import_type = isset( $post_data['simmer-import-type'] ) ? $post_data['simmer-import-type'] : '';

		// If the import type is not set, redirect back.
		if ( ! in_array( $import_type, array( 'convert', 'hide' ), true ) ) {
			return new WP_Error( 'import_type', 'You need to select an import type.' );
			wp_safe_redirect( add_query_arg( array( 'from' => $this->get_uid(), 'error' => rawurlencode( 'You need to select an import type.' ) ), admin_url( 'admin.php?page=wprm_import' ) ) );
			exit();
		}

		// If import type is "hide" we count on the fallback shortcode.
		if ( 'convert' === $import_type ) {
			$post = get_post( $id );
			$content = $post->post_content . ' [wprm-recipe id="' . $wprm_id . '"]';

			$update_content = array(
				'ID' => $id,
				'post_type' => 'post',
				'post_content' => $content,
			);
			wp_update_post( $update_content );

			// Store reference to WPRM recipe.
			add_post_meta( $id, '_simmer_wprm_migrated', $wprm_id );
		}
	}

	/**
	 * Get items from Simmer DB table.
	 *
	 * @since	4.2.0
	 * @param	mixed $id Recipe ID to get the items for.
	 */
	private function get_simmer_items( $id, $type ) {
		global $wpdb;
		$query = "
			SELECT   *
			FROM     {$wpdb->prefix}simmer_recipe_items
			WHERE    recipe_id = %d AND recipe_item_type = '%s'
			ORDER BY recipe_item_type, recipe_item_order
		";

		$results = $wpdb->get_results( $wpdb->prepare( $query, array( $id, $type ) ) );

		$items = array();
		foreach ( $results as $index => $item ) {
			$query = "
				SELECT   *
				FROM     {$wpdb->prefix}simmer_recipe_itemmeta
				WHERE    recipe_item_id = %d
			";

			$meta = $wpdb->get_results( $wpdb->prepare( $query, array( $item->recipe_item_id ) ) );

			$items[ $index ] = array();

			foreach ( $meta as $meta_field ) {
				$items[ $index ][ $meta_field->meta_key ] = $meta_field->meta_value;
			}
		}
		
		return $items;
	}
}
