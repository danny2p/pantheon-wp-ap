<?php
/**
 * Responsible for recipes revisions.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for recipe revisions.
 *
 * @since      5.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Recipe_Revisions {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'save_post', array( __CLASS__, 'save_revision' ), 20, 2 );

		add_filter( 'wp_save_post_revision_post_has_changed', array( __CLASS__, 'compare_revision' ), 10, 3 );
	}

	/**
	 * Save recipe data for revisions.
	 *
	 * @since	5.0.0
	 * @param	int    $id Post ID being saved.
	 * @param	object $post Post being saved.
	 */
	public static function save_revision( $id, $post ) {
		$parent_id = wp_is_post_revision( $post );

		if ( $parent_id && WPRM_POST_TYPE === get_post_type( $parent_id ) ) {
			$parent = WPRM_Recipe_Manager::get_recipe( $parent_id );

			if ( $parent ) {
				// Store current recipe values with revision.
				$data = $parent->get_data();
				add_metadata( 'post', $id, 'wprm_recipe', $data );
			}
		}
	}

	/**
	 * Compare revision changes.
	 *
	 * @since	5.0.0
	 * @param bool    $post_has_changed Whether the post has changed.
	 * @param WP_Post $last_revision    The last revision post object.
	 * @param WP_Post $post             The post object.
	 */
	public static function compare_revision( $post_has_changed, $last_revision, $post ) {
		if ( ! $post_has_changed && WPRM_POST_TYPE === $post->post_type ) {
			$recipe = WPRM_Recipe_Manager::get_recipe( $post );

			if ( $recipe ) {
				$data = $recipe->get_data();
				$revision_data = get_post_meta( $last_revision->ID, 'wprm_recipe', true );

				$post_has_changed = serialize( $data ) !== serialize( $revision_data );
			}
		}
		return $post_has_changed;
	}
}

WPRM_Recipe_Revisions::init();
