<?php
/**
 * Responsible for performing an SEO check on recipes.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.15.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for performing an SEO check on recipes.
 *
 * @since      1.15.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Seo_Checker {

	/**
	 * Keep track of the recipes we're updating the SEO for.
	 *
	 * @since	8.1.0
	 * @access  private
	 * @var     array $updating_seo_for Recipes we're updating the SEO for.
	 */
	private static $updating_seo_for = array();

	/**
	 * Update the SEO data for a recipe.
	 *
	 * @since    8.0.0
	 * @param    mixed $recipe_id Recipe ID to update the SEO for.
	 */
	public static function update_seo_for( $recipe_id ) {
		$result = false;

		// Prevent infinite loop.
		if ( ! array_key_exists( $recipe_id, self::$updating_seo_for ) ) {
			self::$updating_seo_for[ $recipe_id ] = true;

			// Make sure we get the latest version, with the latest ratings.
			WPRM_Recipe_Manager::invalidate_recipe( $recipe_id );
			$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

			if ( $recipe ) {
				$result = self::check_recipe( $recipe );

				update_post_meta( $recipe_id, 'wprm_seo', $result );
				update_post_meta( $recipe_id, 'wprm_seo_priority', $result['priority'] );
			}
			unset( self::$updating_seo_for[ $recipe_id ] );
		}

		return $result;
	}

	/**
	 * Perform an SEO check on a specific recipe.
	 *
	 * @since    1.15.0
	 * @param    mixed $recipe Recipe to check the SEO for.
	 */
	public static function check_recipe( $recipe ) {
		if ( 'food' === $recipe->type() ) {
			$result = self::check_food_recipe( $recipe );
		} else if ( 'howto' === $recipe->type() ) {
			$result = self::check_howto_recipe( $recipe );
		} else {
			$result = array(
				'type' => 'other',
				'message' => 'No metadata will be output as this recipe has been marked as type "Other"',
			);
		}

		// Convert type to priority number.
		switch ( $result['type'] ) {
			case 'bad':
				$result['priority'] = 5;
				break;
			case 'warning':
				$result['priority'] = 10;
				break;
			case 'rating':
				$result['priority'] = 15;
				break;
			case 'good':
				$result['priority'] = 20;
				break;
			default:
				$result['priority'] = 25;
		}

		return $result;
	}

	/**
	 * Get SEO data for recipe where it hasn't been generated for.
	 *
	 * @since	8.0.0
	 */
	public static function missing_seo_data() {
		return array(
			'priority' => 0,
			'type' => 'missing',
			'message' => __( 'Run a Health Check through the WP Recipe Maker > Dashboard page to get SEO data', 'wp-recipe-maker' ),
		);
	}

	/**
	 * Perform an SEO check on a food recipe.
	 *
	 * @since    5.2.0
	 * @param    mixed $recipe Recipe to check the SEO for.
	 */
	public static function check_food_recipe( $recipe ) {
		$type = 'good';
		$message = array();

		// Recipe ratings.
		$rating = $recipe->rating();
		if ( 0 === $rating['count'] ) {
			$type = 'rating';
			$message[] = 'There are no ratings for your recipe.';
		}

		// Recommended fields.
		$issues = array();

		if ( ! $recipe->summary() ) { $issues[] = 'Summary'; }
		if ( ! $recipe->servings() ) { $issues[] = 'Servings'; }

		if ( ! ( $recipe->total_time() || ( $recipe->prep_time() && $recipe->cook_time() ) ) ) { $issues[] = 'Times'; }

		if ( 0 === count( $recipe->ingredients_without_groups() ) ) { $issues[] = 'Ingredients'; }
		if ( 0 === count( $recipe->instructions_without_groups() ) ) { $issues[] = 'Instructions'; }
		if ( 0 === count( $recipe->tags( 'course' ) ) ) { $issues[] = 'Course'; }
		if ( 0 === count( $recipe->tags( 'cuisine' ) ) ) { $issues[] = 'Cuisine'; }
		if ( 0 === count( $recipe->tags( 'keyword' ) ) ) { $issues[] = 'Keyword'; }

		$nutrition = $recipe->nutrition();
		if ( ! isset( $nutrition['calories'] ) || ! $nutrition['calories'] ) { $issues[] = 'Calories'; }

		if ( count( $issues ) > 0 ) {
			$type = 'warning';
			$message[] = 'Recommended fields: ' . implode( ', ', $issues );
		}

		// Required fields.
		$issues = array();

		if ( ! $recipe->name() ) { $issues[] = 'Name'; }
		if ( ! $recipe->image_id() ) { $issues[] = 'Image'; }

		if ( count( $issues ) > 0 ) {
			$type = 'bad';
			$message[] = 'Required fields: ' . implode( ', ', $issues );
		}

		// Recipe video.
		if ( $recipe->video_id() ) {
			$issues = array();
			$metadata = $recipe->video_metadata();

			if ( ! isset( $metadata['name'] ) || ! $metadata['name'] ) { $issues[] = 'Name'; }
			if ( ! isset( $metadata['description'] ) || ! $metadata['description'] ) { $issues[] = 'Description'; }
			if ( ! isset( $metadata['thumbnailUrl'] ) || ! $metadata['thumbnailUrl'] ) { $issues[] = 'Image'; }

			if ( count( $issues ) > 0 ) {
				$type = 'bad';
				$message[] = 'Required video fields: ' . implode( ', ', $issues );
			}
		}

		$message = 'good' === $type ? 'Good job!' : implode( '<br/>', $message );
		return array(
			'type' => $type,
			'message' => $message,
		);
	}

	/**
	 * Perform an SEO check on a How-to recipe.
	 *
	 * @since    5.2.0
	 * @param    mixed $recipe Recipe to check the SEO for.
	 */
	public static function check_howto_recipe( $recipe ) {
		$type = 'good';
		$message = array();

		// Recommended fields.
		$issues = array();

		if ( ! $recipe->summary() ) { $issues[] = 'Description'; }
		if ( ! $recipe->cost() ) { $issues[] = 'Estimated Cost'; }
		if ( ! $recipe->total_time() ) { $issues[] = 'Total Time'; }
		if ( 0 === count( $recipe->equipment() ) ) { $issues[] = 'Equipment'; }
		if ( 0 === count( $recipe->ingredients_without_groups() ) ) { $issues[] = 'Materials'; }
		if ( ! $recipe->image_id() ) { $issues[] = 'Image'; }

		if ( count( $issues ) > 0 ) {
			$type = 'warning';
			$message[] = 'Recommended fields: ' . implode( ', ', $issues );
		}

		// Required fields.
		$issues = array();

		if ( ! $recipe->name() ) { $issues[] = 'Name'; }
		if ( 0 === count( $recipe->instructions_without_groups() ) ) { $issues[] = 'Instructions'; }

		if ( count( $issues ) > 0 ) {
			$type = 'bad';
			$message[] = 'Required fields: ' . implode( ', ', $issues );
		}

		// Recipe video.
		if ( $recipe->video_id() ) {
			$issues = array();
			$metadata = $recipe->video_metadata();

			if ( ! isset( $metadata['name'] ) || ! $metadata['name'] ) { $issues[] = 'Name'; }
			if ( ! isset( $metadata['description'] ) || ! $metadata['description'] ) { $issues[] = 'Description'; }
			if ( ! isset( $metadata['thumbnailUrl'] ) || ! $metadata['thumbnailUrl'] ) { $issues[] = 'Image'; }

			if ( count( $issues ) > 0 ) {
				$type = 'bad';
				$message[] = 'Required video fields: ' . implode( ', ', $issues );
			}
		}

		$message = 'good' === $type ? 'Good job!' : implode( '<br/>', $message );
		return array(
			'type' => $type,
			'message' => $message,
		);
	}
}
