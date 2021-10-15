<?php
/**
 * Responsible for importing BigOven recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.7.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing BigOven recipes.
 *
 * @since      1.7.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Bigoven extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    1.7.0
	 */
	public function get_uid() {
		return 'bigoven';
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
	 * @since    1.7.0
	 */
	public function get_name() {
		return 'BigOven';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    1.7.0
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
		$args = array(
			'post_type' => 'bo-recipe',
			'post_status' => 'any',
			'posts_per_page' => 1,
		);

		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    1.7.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$recipes = array();

		$limit = 100;
		$offset = $limit * $page;

		$args = array(
			'post_type' => 'bo-recipe',
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
	 * @since    1.7.0
	 * @param	 mixed $id ID of the recipe we want to import.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		$recipe = array(
			'import_id' => $id,
			'import_backup' => array(
				'bo_recipe_id' => $id,
			),
		);

		$post = get_post( $id );
		$bo_recipe = get_post_meta( $id, 'clc-recipe-attributes', true );

		// Featured Image.
		$recipe['image_id'] = get_post_thumbnail_id( $id );

		// Simple Matching.
		$recipe['name'] = $post->post_title;
		$recipe['summary'] = $post->post_content;

		// Servings.
		$match = preg_match( '/^\s*\d+/', $bo_recipe['yield'], $servings_array );
		if ( 1 === $match ) {
				$servings = str_replace( ' ','', $servings_array[0] );
		} else {
				$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $bo_recipe['yield'] );

		$recipe['servings'] = $servings;
		$recipe['servings_unit'] = $servings_unit;

		// Recipe Times.
		$recipe['prep_time'] = $bo_recipe['time-preparation'] ? $this->time_to_minutes( $bo_recipe['time-preparation'] ) : 0;
		$recipe['cook_time'] = $bo_recipe['time-cook'] ? $this->time_to_minutes( $bo_recipe['time-cook'] ) : 0;
		$recipe['total_time'] = $bo_recipe['time-total'] ? $this->time_to_minutes( $bo_recipe['time-total'] ) : 0;

		// Ingredients.
		$ingredients = array();
		$group = array(
			'ingredients' => array(),
			'name' => '',
		);

		$bo_ingredients = preg_split( '/$\R?^/m', $bo_recipe['ingredients'] );

		foreach ( $bo_ingredients as $bo_ingredient ) {
			$bo_ingredient = trim( $this->derichify( $bo_ingredient ) );

			if ( '!' === substr( $bo_ingredient, 0, 1 ) ) {
				$ingredients[] = $group;
				$group = array(
					'ingredients' => array(),
					'name' => substr( $bo_ingredient, 1 ),
				);
			} else {
				$group['ingredients'][] = array(
					'raw' => $bo_ingredient,
				);
			}
		}
		$ingredients[] = $group;
		$recipe['ingredients'] = $ingredients;

		// Instructions.
		$instructions = array();
		$group = array(
			'instructions' => array(),
			'name' => '',
		);

		$bo_instructions = preg_split( '/$\R?^/m', $bo_recipe['instructions'] );

		foreach ( $bo_instructions as $bo_instruction ) {
			if ( '!' === substr( $bo_instruction, 0, 1 ) ) {
				$instructions[] = $group;
				$group = array(
					'instructions' => array(),
					'name' => $this->derichify( substr( $bo_instruction, 1 ) ),
				);
			} else {
				$group['instructions'][] = array(
					'text' => trim( $this->richify( $bo_instruction ) ),
				);
			}
		}
		$instructions[] = $group;
		$recipe['instructions'] = $instructions;

		// Nutrition Facts.
		$recipe['nutrition'] = array();

		$recipe['nutrition']['serving_size'] = $bo_recipe['serving-size'];
		$recipe['nutrition']['calories'] = $bo_recipe['nutrition-calories'];
		$recipe['nutrition']['fat'] = $bo_recipe['nutrition-fat'];
		$recipe['nutrition']['carbohydrates'] = $bo_recipe['nutrition-carbohydrates'];
		$recipe['nutrition']['protein'] = $bo_recipe['nutrition-protein'];

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    1.7.0
	 * @param	 mixed $id ID of the recipe we want replace.
	 * @param	 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		// We don't know which posts use this recipe so we rely on the fallback shortcode.
	}

	/**
	 * Richify text by adding links and styling.
	 *
	 * @since    1.7.0
	 * @param	 mixed $text Text to richify.
	 */
	private function richify( $text ) {
		$output = $text;

		$link_ptr = '#\[(.*?)\]\((.*?)\)#';
		preg_match_all(
			$link_ptr,
			$text,
			$matches
		);

		if ( isset( $matches[0] ) ) {
			$orig = $matches[0];
			$substitution = preg_replace(
				$link_ptr,
				'<a href="\\2">\\1</a>',
				str_replace( '"', '', $orig )
			);
			$output = str_replace( $orig, $substitution, $text );
		}

		$output = preg_replace( '#\*\*(.*?)\*\*#s', '<strong>\1</strong>', $output );
		$output = preg_replace( '#\*(.*?)\*#s', '<em>\1</em>', $output );
		return $output;
	}

	/**
	 * Derichify text by removing links and styling.
	 *
	 * @since    1.7.0
	 * @param	 mixed $text Text to derichify.
	 */
	private function derichify( $text ) {
		$output = $text;

		$link_ptr = '#\[(.*?)\]\((.*?)\)#';
		preg_match_all(
			$link_ptr,
			$text,
			$matches
		);

		if ( isset( $matches[0] ) ) {
			$orig = $matches[0];
			$substitution = preg_replace(
				$link_ptr,
				'\\1',
				str_replace( '"', '', $orig )
			);
			$output = str_replace( $orig, $substitution, $text );
		}

		$output = preg_replace( '#\*\*(.*?)\*\*#s', '\1', $output );
		$output = preg_replace( '#\*(.*?)\*#s', '\1', $output );
		return $output;
	}

	/**
	 * Convert time field to minutes.
	 *
	 * @since    1.7.0
	 * @param	 mixed $time_string Time to convert.
	 */
	private function time_to_minutes( $time_string ) {
		$time = strtotime( $time_string, 0 );

		if ( $time ) {
			return intval( ceil( $time / 60 ) );
		} else {
			return 0;
		}
	}
}
