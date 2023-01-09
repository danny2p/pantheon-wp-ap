<?php
/**
 * Responsible for handling the fix comment ratings tool.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.9.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the fix comment ratings tool.
 *
 * @since      5.9.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Fix_Comment_Ratings {

	/**
	 * Register actions and filters.
	 *
	 * @since	5.9.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_fix_comment_ratings', array( __CLASS__, 'ajax_fix_comment_ratings' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	5.9.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Fix Comment Ratings', 'wp-recipe-maker' ), __( 'Fix Comment Ratings', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_fixing_comment_ratings', array( __CLASS__, 'fixing_comment_ratings' ) );
	}

	/**
	 * Get the template for the finding ratings page.
	 *
	 * @since    5.9.0
	 */
	public static function fixing_comment_ratings() {
		// Make sure rating DB is on latest version.
		WPRM_Rating_Database::update_database( '0.0' );

		$comment_ratings = WPRM_Rating_Database::get_ratings( array(
			'where' => 'comment_id != 0',
		) );

		$ratings = array_map( 'intval', wp_list_pluck( $comment_ratings['ratings'], 'id' ) );

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::fix_comment_ratings( $ratings ); // Input var okay.
			var_dump( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'fix_comment_ratings',
			'posts' => $ratings,
			'args' => array(),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/fixing-comment-ratings.php' );
	}

	/**
	 * Fix comment ratings through AJAX.
	 *
	 * @since    5.9.0
	 */
	public static function ajax_fix_comment_ratings() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

			$posts_left = array();
			$posts_processed = array();

			if ( count( $posts ) > 0 ) {
				$posts_left = $posts;
				$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

				$result = self::fix_comment_ratings( $posts_processed );

				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array(
						'redirect' => add_query_arg( array( 'sub' => 'advanced' ), admin_url( 'admin.php?page=wprm_tools' ) ),
					) );
				}
			}

			wp_send_json_success( array(
				'posts_processed' => $posts_processed,
				'posts_left' => $posts_left,
			) );
		}

		wp_die();
	}

	/**
	 * Find recipes in posts to link parents.
	 *
	 * @since	5.9.0
	 * @param	array $posts IDs of posts to search.
	 */
	public static function fix_comment_ratings( $ratings ) {
		foreach ( $ratings as $rating_id ) {
			$rating = WPRM_Rating_Database::get_rating( array(
				'where' => 'ID = "' . intval( $rating_id ) . '"',
			) );

			if ( $rating ) {
				$comment_id = intval( $rating->comment_id );

				if ( $comment_id ) {
					$comment = get_comment( $comment_id );

					if ( ! $comment ) {
						// Comment is gone, remove rating as well.
						WPRM_Rating_Database::delete_rating( $rating_id );
					}
				}
			}
		}
	}
}

WPRM_Tools_Fix_Comment_Ratings::init();
