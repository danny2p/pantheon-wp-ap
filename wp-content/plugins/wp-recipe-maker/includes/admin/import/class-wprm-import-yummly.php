<?php
/**
 * Responsible for importing Yummly recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.12.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 */

/**
 * Responsible for importing Yummly recipes.
 *
 * @since      1.12.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/import
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Yummly extends WPRM_Import {
	/**
	 * Get the UID of this import source.
	 *
	 * @since    1.12.0
	 */
	public function get_uid() {
		return 'yummly';
	}

	/**
	 * Wether or not this importer requires a manual search for recipes.
	 *
	 * @since    1.12.0
	 */
	public function requires_search() {
		return false;
	}

	/**
	 * Get the name of this import source.
	 *
	 * @since    1.12.0
	 */
	public function get_name() {
		return 'Yummly';
	}

	/**
	 * Get HTML for the import settings.
	 *
	 * @since    1.12.0
	 */
	public function get_settings_html() {
		return '';
	}

	/**
	 * Get the total number of recipes to import.
	 *
	 * @since    1.12.0
	 */
	public function get_recipe_count() {
		return count( $this->get_recipes() );
	}

	/**
	 * Get a list of recipes that are available to import.
	 *
	 * @since    1.12.0
	 * @param	 int $page Page of recipes to get.
	 */
	public function get_recipes( $page = 0 ) {
		$recipes = array();

		global $wpdb;
		$table = $wpdb->prefix . 'amd_yrecipe_recipes';

		$yum_recipes = array();
		if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$yum_recipes = $wpdb->get_results( 'SELECT recipe_id, post_id, recipe_title FROM ' . $table );
		}

		foreach ( $yum_recipes as $yum_recipe ) {
			if ( WPRM_POST_TYPE !== get_post_type( $yum_recipe->post_id ) ) {
				$recipes[ $yum_recipe->recipe_id ] = array(
					'name' => $yum_recipe->recipe_title,
					'url' => get_edit_post_link( $yum_recipe->post_id ),
				);
			}
		}

		return $recipes;
	}

	/**
	 * Get recipe with the specified ID in the import format.
	 *
	 * @since    1.12.0
	 * @param	 mixed $id ID of the recipe we want to import.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function get_recipe( $id, $post_data ) {
		global $wpdb;
		$yum_recipe = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'amd_yrecipe_recipes WHERE recipe_id=' . $id );
		$post_id = $yum_recipe->post_id;

		$recipe = array(
			'import_id' => 0, // Set to 0 because we need to create a new recipe post.
			'import_backup' => array(
				'yum_recipe_id' => $id,
				'yum_post_id' => $post_id,
			),
		);

		// Featured Image.
		if ( $yum_recipe->recipe_image ) {
			$image_id = WPRM_Import_Helper::get_or_upload_attachment( $post_id, $yum_recipe->recipe_image );

			if ( $image_id ) {
				$recipe['image_id'] = $image_id;
			}
		}

		// Simple Matching.
		$recipe['name'] = $yum_recipe->recipe_title;
		$recipe['summary'] = $this->richify( $yum_recipe->summary );
		$recipe['notes'] = $this->richify( $yum_recipe->notes );

		// Servings.
		$match = preg_match( '/^\s*\d+/', $yum_recipe->yield, $servings_array );
		if ( 1 === $match ) {
			$servings = str_replace( ' ','', $servings_array[0] );
		} else {
			$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $yum_recipe->yield );

		$recipe['servings'] = $servings;
		$recipe['servings_unit'] = $servings_unit;

		// Recipe Times.
		$recipe['prep_time'] = $yum_recipe->prep_time ? $this->time_to_minutes( $yum_recipe->prep_time ) : 0;
		$recipe['cook_time'] = $yum_recipe->cook_time ? $this->time_to_minutes( $yum_recipe->cook_time ) : 0;
		$recipe['total_time'] = $yum_recipe->total_time ? $this->time_to_minutes( $yum_recipe->total_time ) : 0;

		// Ingredients.
		$ingredients = array();
		$group = array(
			'ingredients' => array(),
			'name' => '',
		);

		$yum_ingredients = preg_split( '/$\R?^/m', $yum_recipe->ingredients );

		foreach ( $yum_ingredients as $yum_ingredient ) {
			$yum_ingredient = trim( $this->derichify( $yum_ingredient ) );

			if ( '!' === substr( $yum_ingredient, 0, 1 ) ) {
				$ingredients[] = $group;
				$group = array(
					'ingredients' => array(),
					'name' => substr( $yum_ingredient, 1 ),
				);
			} elseif ( '%' !== substr( $yum_ingredient, 0, 1 ) ) {
				$group['ingredients'][] = array(
					'raw' => $yum_ingredient,
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

		$yum_instructions = preg_split( '/$\R?^/m', $yum_recipe->instructions );

		foreach ( $yum_instructions as $yum_instruction ) {
			$yum_instruction = trim( str_replace( array( "\n", "\t", "\r" ), '', $yum_instruction ) );

			if ( '!' === substr( $yum_instruction, 0, 1 ) ) {
				$instructions[] = $group;
				$group = array(
					'instructions' => array(),
					'name' => $this->derichify( substr( $yum_instruction, 1 ) ),
				);
			} elseif ( '%' === substr( $yum_instruction, 0, 1 ) ) {
				$image_id = WPRM_Import_Helper::get_or_upload_attachment( $post_id, substr( $yum_instruction, 1 ) );

				if ( $image_id ) {
					$last_instruction = array_pop( $group['instructions'] );

					if ( ! $last_instruction ) {
						$group['instructions'][] = array(
							'image' => $image_id,
						);
					} elseif ( isset( $last_instruction['image'] ) && $last_instruction['image'] ) {
						$group['instructions'][] = $last_instruction;
						$group['instructions'][] = array(
							'image' => $image_id,
						);
					} else {
						$group['instructions'][] = array(
							'text' => $last_instruction['text'],
							'image' => $image_id,
						);
					}
				}
			} else {
				$group['instructions'][] = array(
					'text' => $this->richify( $yum_instruction ),
				);
			}
		}
		$instructions[] = $group;
		$recipe['instructions'] = $instructions;

		// Nutrition Facts.
		$recipe['nutrition'] = array();

		$nutrition_mapping = array(
			'serving_size'  => 'serving_size',
			'calories'      => 'calories',
			'fat'           => 'fat',
		);

		foreach ( $nutrition_mapping as $yum_field => $wprm_field ) {
			if ( $yum_recipe->$yum_field ) {
				$recipe['nutrition'][ $wprm_field ] = trim( $yum_recipe->$yum_field );
			}
		}

		return $recipe;
	}

	/**
	 * Replace the original recipe with the newly imported WPRM one.
	 *
	 * @since    1.12.0
	 * @param	 mixed $id ID of the recipe we want replace.
	 * @param	 mixed $wprm_id ID of the WPRM recipe to replace with.
	 * @param	 array $post_data POST data passed along when submitting the form.
	 */
	public function replace_recipe( $id, $wprm_id, $post_data ) {
		global $wpdb;
		$yum_recipe = $wpdb->get_row( 'SELECT post_id FROM ' . $wpdb->prefix . 'amd_yrecipe_recipes WHERE recipe_id=' . $id );
		$post_id = $yum_recipe->post_id;

		// Update post_id field to show that this recipe has been imported.
		$wpdb->update( $wpdb->prefix . 'amd_yrecipe_recipes', array( 'post_id' => $wprm_id ), array( 'recipe_id' => $id ), array( '%d' ), array( '%d' ) );

		$post = get_post( $post_id );
		$content = $post->post_content;

		$content = $this->replace_shortcode( $content, '[wprm-recipe id="' . $wprm_id . '"]' );

		$update_content = array(
			'ID' => $post_id,
			'post_content' => $content,
		);
		wp_update_post( $update_content );
	}

	/**
	 * Helper function to replace the Yummly shortcode.
	 *
	 * @since    1.12.0
	 * @param	 mixed $post_text 	Text to find the shortcode in.
	 * @param	 mixed $replacement Text to replace the shortcode with.
	 */
	private function replace_shortcode( $post_text, $replacement ) {
		$output = $post_text;

		$needle_old = 'id="amd-yrecipe-recipe-';
		$preg_needle_old = '/(id)=("(amd-yrecipe-recipe-)[0-9^"]*")/i';
		$needle = '[amd-yrecipe-recipe:';
		$preg_needle = '/\[amd-yrecipe-recipe:([0-9]+)\]/i';

		if ( strpos( $post_text, $needle_old ) !== false ) {
			preg_match_all( $preg_needle_old, $post_text, $matches );
			foreach ( $matches[0] as $match ) {
				$recipe_id = str_replace( 'id="amd-yrecipe-recipe-', '', $match );
				$recipe_id = str_replace( '"', '', $recipe_id );
				$output = preg_replace( "/<img id=\"amd-yrecipe-recipe-" . $recipe_id . "\" class=\"amd-yrecipe-recipe\" src=\"[^\"]*\" alt=\"\" \/>/", $replacement, $output );
			}
		}

		if ( strpos( $post_text, $needle ) !== false ) {
			preg_match_all( $preg_needle, $post_text, $matches );
			foreach ( $matches[0] as $match ) {
				$recipe_id = str_replace( '[amd-yrecipe-recipe:', '', $match );
				$recipe_id = str_replace( ']', '', $recipe_id );
				$output = str_replace( '[amd-yrecipe-recipe:' . $recipe_id . ']', $replacement, $output );
			}
		}

		return $output;
	}

	/**
	 * Richify text by adding links and styling.
	 * Source: Yummly.
	 *
	 * @since    1.12.0
	 * @param	 mixed $text Text to richify.
	 */
	private function richify( $text ) {
		$text = preg_replace( '/(^|\s)\*([^\s\*][^\*]*[^\s\*]|[^\s\*])\*(\W|$)/', '\\1<strong>\\2</strong>\\3', $text );
		$text = preg_replace( '/(^|\s)_([^\s_][^_]*[^\s_]|[^\s_])_(\W|$)/', '\\1<em>\\2</em>\\3', $text );
		$text = preg_replace( '/\[([^\]\|\[]*)\|([^\]\|\[]*)\]/', '<a href="\\2" target="_blank">\\1</a>', $text );

		return $text;
	}

	/**
	 * Derichify text by removing links and styling.
	 *
	 * @since    1.12.0
	 * @param	 mixed $text Text to derichify.
	 */
	private function derichify( $text ) {
		$text = preg_replace( '/(^|\s)\*([^\s\*][^\*]*[^\s\*]|[^\s\*])\*(\W|$)/', '\\1\\2\\3', $text );
		$text = preg_replace( '/(^|\s)_([^\s_][^_]*[^\s_]|[^\s_])_(\W|$)/', '\\1\\2\\3', $text );
		$text = preg_replace( '/\[([^\]\|\[]*)\|([^\]\|\[]*)\]/', '\\1', $text );

		return $text;
	}

	/**
	 * Convert time metadata to minutes.
	 *
	 * @since    1.12.0
	 * @param	 mixed $duration Time to convert.
	 */
	private function time_to_minutes( $duration = 'PT' ) {
		$date_abbr = array(
			'd' => 60 * 24,
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
