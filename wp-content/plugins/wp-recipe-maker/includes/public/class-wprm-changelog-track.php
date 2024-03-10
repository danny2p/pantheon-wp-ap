<?php
/**
 * Track changes that need to get logged.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Track changes that need to get logged.
 *
 * @since      9.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Changelog_Track {
	/*
	 * Keep track of IDs that have just been created.
	 */
	private static $just_created = array();

	/**
	 * Register actions and filters.
	 *
	 * @since	9.2.0
	 */
	public static function init() {
		add_action( 'trashed_post', array( __CLASS__, 'trashed_post' ), 10, 2 );
		add_action( 'delete_post', array( __CLASS__, 'delete_post' ), 10, 2 );
	}

	/**
	 * Hook into trashed_post.
	 *
	 * @since	9.2.0
	 */
	public static function trashed_post( $id, $previous_status = false ) {
		if ( WPRM_POST_TYPE === get_post_type( $id ) ) {
			$data = array();

			if ( $previous_status ) {
				$data['previous_status'] = $previous_status;
			}

			WPRM_Changelog::log( 'recipe_trashed', $id, $data );
		}
	}

	/**
	 * Hook into delete_post.
	 *
	 * @since	9.2.0
	 */
	public static function delete_post( $id, $post = false ) {
		if ( $post && WPRM_POST_TYPE === $post->post_type ) {
			WPRM_Changelog::log( 'recipe_deleted', $id );
		}
	}
}

WPRM_Changelog_Track::init();
