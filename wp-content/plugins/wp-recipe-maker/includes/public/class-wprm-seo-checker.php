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

		return $result;
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
