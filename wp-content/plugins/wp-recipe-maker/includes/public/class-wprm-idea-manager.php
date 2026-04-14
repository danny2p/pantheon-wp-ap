<?php
/**
 * Responsible for returning ideas.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for returning ideas.
 *
 * @since      10.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Idea_Manager {

	/**
	 * Cached ideas.
	 *
	 * @since    10.4.0
	 * @access   private
	 * @var      array $ideas Cached idea objects.
	 */
	private static $ideas = array();

	/**
	 * Get idea object by ID.
	 *
	 * @since    10.4.0
	 * @param    mixed $post_or_idea_id Post object or idea ID.
	 */
	public static function get_idea( $post_or_idea_id ) {
		$idea_id = is_object( $post_or_idea_id ) && $post_or_idea_id instanceof WP_Post ? $post_or_idea_id->ID : intval( $post_or_idea_id );

		if ( ! array_key_exists( $idea_id, self::$ideas ) ) {
			$post = is_object( $post_or_idea_id ) && $post_or_idea_id instanceof WP_Post ? $post_or_idea_id : get_post( $idea_id );

			if ( $post instanceof WP_Post && WPRM_IDEA_POST_TYPE === $post->post_type ) {
				self::$ideas[ $idea_id ] = new WPRM_Idea( $post );
			} else {
				self::$ideas[ $idea_id ] = false;
			}
		}

		return self::$ideas[ $idea_id ];
	}

	/**
	 * Invalidate idea cache.
	 *
	 * @since    10.4.0
	 * @param    int $idea_id Idea ID.
	 */
	public static function invalidate_idea( $idea_id ) {
		unset( self::$ideas[ intval( $idea_id ) ] );
	}

	/**
	 * Find existing idea and recipe title matches for duplicate warnings.
	 *
	 * @since    10.4.0
	 * @param    string $title Title to compare.
	 */
	public static function find_title_matches( $title ) {
		$title = trim( $title );

		if ( ! $title ) {
			return array(
				'ideas'   => array(),
				'recipes' => array(),
			);
		}

		$matches = array(
			'ideas'   => array(),
			'recipes' => array(),
		);

		$queries = array(
			'ideas' => array(
				'post_type'      => WPRM_IDEA_POST_TYPE,
				'post_status'    => array( 'publish', 'future', 'draft', 'private', 'pending' ),
				'posts_per_page' => 20,
				's'              => $title,
			),
			'recipes' => array(
				'post_type'      => WPRM_POST_TYPE,
				'post_status'    => array( 'publish', 'future', 'draft', 'private', 'pending' ),
				'posts_per_page' => 20,
				's'              => $title,
			),
		);

		foreach ( $queries as $type => $args ) {
			$query = new WP_Query(
				array_merge(
					$args,
					array(
						'suppress_filters' => true,
						'lang'             => '',
					)
				)
			);

			foreach ( $query->posts as $post ) {
				if ( 0 !== strcasecmp( trim( $post->post_title ), $title ) ) {
					continue;
				}

				$matches[ $type ][] = array(
					'id'   => $post->ID,
					'name' => $post->post_title,
				);
			}
		}

		return $matches;
	}
}
