<?php
/**
 * Responsible for handling the import MV Create Reviews tool.
 *
 * @link       https://bootstrapped.ventures
 * @since      7.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the import MV Create Reviews tool.
 *
 * @since      7.5.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Create_Reviews {

	/**
	 * Register actions and filters.
	 *
	 * @since	7.5.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_create_reviews', array( __CLASS__, 'ajax_create_reviews' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	7.5.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Importing Reviews', 'wp-recipe-maker' ), __( 'Importing Reviews', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_create_reviews', array( __CLASS__, 'create_reviews' ) );
	}

	/**
	 * Get the template for the import ingredient nutrition from WP Ultimate Recipe page.
	 *
	 * @since	7.5.0
	 */
	public static function create_reviews() {
		$args = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => 'all',
			'posts_per_page' => -1,
			'fields' => 'ids',
		);

		$posts = get_posts( $args );

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::import_reviews( $posts ); // Input var okay.
			var_dump( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'create_reviews',
			'posts' => $posts,
			'args' => array(),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/create-reviews.php' );
	}

	/**
	 * Import WP Ultimate Recipe ingredient nutrition through AJAX.
	 *
	 * @since    2.1.0
	 */
	public static function ajax_create_reviews() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

			$posts_left = array();
			$posts_processed = array();

			if ( count( $posts ) > 0 ) {
				$posts_left = $posts;
				$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

				$result = self::import_reviews( $posts_processed );

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
	 * Import MV Create Reviews.
	 *
	 * @since	7.5.0
	 * @param	array $recipes Recipes to import the reviews for.
	 */
	public static function import_reviews( $recipes ) {
		global $wpdb;
		$mv_reviews_table = $wpdb->prefix . 'mv_reviews';

		foreach ( $recipes as $recipe_id ) {
			$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

			if ( $recipe ) {
				$import_source = get_post_meta( $recipe_id, 'wprm_import_source', true );
				$import_backup = get_post_meta( $recipe_id, 'wprm_import_backup', true );

				$parent_post_id = $recipe->parent_post_id();

				if ( $parent_post_id && 'create' === substr( $import_source, 0, 6 ) && isset( $import_backup['mv_creation_id'] ) ) {
					$mv_creation_id = intval( $import_backup['mv_creation_id'] );
					
					// Get reviews for this MV ID.
					$reviews = $wpdb->get_results( 'SELECT * FROM ' . $mv_reviews_table . ' WHERE review_content IS NOT NULL AND creation = ' . $mv_creation_id );

					if ( $reviews ) {
						foreach ( $reviews as $review ) {
							// Delete any existing imported comments for this review ID.
							$args = array( 
								'meta_key' => 'wprm_create_review_import',
								'meta_value' => $review->id,
							); 

							$comments_query = new WP_Comment_Query( $args ); 
							$comments = $comments_query->comments;

							foreach ( $comments as $comment ) {
								WPRM_Rating_Database::delete_ratings_for_comment( $comment->comment_ID );
								wp_delete_comment( $comment->comment_ID, true );
							}

							// Create comment associated with parent post.
							$comment_id = wp_insert_comment( array(
								'comment_author' => $review->author_name,
								'comment_author_email' => $review->author_email,
								'comment_content' => $review->review_content,
								'comment_date' => $review->created,
								'comment_date_gmt' => $review->created,
								'comment_post_ID' => $parent_post_id,
								'comment_meta' => array(
									'wprm_create_review_import' => $review->id,
								),
							) );

							if ( $comment_id ) {
								// Changing to comment rating, so remove from user ratings.
								$existing_user_ratings = WPRM_Rating_Database::get_ratings( array(
									'where' => 'ip = "mv-create-' . intval( $review->id ) . '"',
								) );

								$existing_user_ratings_ids = wp_list_pluck( $existing_user_ratings['ratings'], 'id' );

								if ( $existing_user_ratings_ids ) {
									WPRM_Rating_Database::delete_ratings( $existing_user_ratings_ids );
								}

								// Create comment rating.
								WPRM_Comment_Rating::add_or_update_rating_for( $comment_id, ceil( floatval( $review->rating ) ) );
							}
						}
					}
				}
			}
		}
	}
}

WPRM_Tools_Create_Reviews::init();
