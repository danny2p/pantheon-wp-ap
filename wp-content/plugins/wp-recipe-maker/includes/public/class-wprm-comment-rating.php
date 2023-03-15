<?php
/**
 * Allow visitors to rate the recipe in the comment.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Allow visitors to rate the recipe in the comment.
 *
 * @since      1.1.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Comment_Rating {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.1.0
	 */
	public static function init() {
		add_filter( 'comment_text', array( __CLASS__, 'add_stars_to_comment' ), 10, 2 );
		add_filter( 'comment_form_field_comment', array( __CLASS__, 'add_rating_field_to_comment_form' ), 10, 2 );
		
		// Rating column on admin comments list.
		add_filter( 'manage_edit-comments_columns', array( __CLASS__, 'comments_list_columns' ) );
		add_filter( 'manage_edit-comments_sortable_columns', array( __CLASS__, 'comments_list_sortable_columns' ) );
		add_filter( 'pre_get_comments', array( __CLASS__, 'comments_list_sort' ) );
		add_filter( 'manage_comments_custom_column', array( __CLASS__, 'comments_list_column' ), 10, 2 );

		add_action( 'comment_form_after_fields', array( __CLASS__, 'add_rating_field_to_comments_legacy' ) );
		add_action( 'comment_form_logged_in_after', array( __CLASS__, 'add_rating_field_to_comments_legacy' ) );
		add_action( 'add_meta_boxes_comment', array( __CLASS__, 'add_rating_field_to_admin_comments' ) );

		add_action( 'comment_post', array( __CLASS__, 'save_comment_rating' ), 10, 3 );
		add_action( 'edit_comment', array( __CLASS__, 'save_admin_comment_rating' ) );

		add_action( 'trashed_comment', array( __CLASS__, 'update_comment_rating_on_change' ) );
		add_action( 'spammed_comment', array( __CLASS__, 'update_comment_rating_on_change' ) );
		add_action( 'unspammed_comment', array( __CLASS__, 'update_comment_rating_on_change' ) );

		add_action( 'deleted_comment', array( __CLASS__, 'delete_comment_rating' ) );

		// Different hooks before and after 5.5.
		add_action( 'comment_unapproved_', array( __CLASS__, 'update_comment_rating_on_change' ) );
		add_action( 'comment_approved_', array( __CLASS__, 'update_comment_rating_on_change' ) );
		add_action( 'comment_unapproved_comment', array( __CLASS__, 'update_comment_rating_on_change' ) );
		add_action( 'comment_approved_comment', array( __CLASS__, 'update_comment_rating_on_change' ) );

		add_action( 'handle_bulk_actions-edit-comments', array( __CLASS__, 'update_comment_ratings_bulk' ), 10, 3 );
		
		// WPDiscuz compatibility.
		add_action( 'init', array( __CLASS__, 'wpdiscuz_compatibility' ) );
		add_action( 'wpdiscuz_button', array( __CLASS__, 'add_rating_field_to_comments' ) );

		// Thrive Comments compatibility.
		add_action( 'tcm_comment_extra_fields', array( __CLASS__, 'add_rating_field_to_thrive_comments' ) );
	}

	/**
	 * Get ratings for a specific recipe.
	 *
	 * @since	2.2.0
	 * @param	int $recipe_id ID of the recipe.
	 */
	public static function get_ratings_for( $recipe_id ) {
		$ratings = array();
		$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

		if ( $recipe ) {
			$query_where = '';

			if ( WPRM_Migrations::is_migrated_to( 'ratings_db_post_id' ) ) {
				// Can be comment ratings both to recipe itself and its parent post.
				$post_ids = array();

				if ( 'public' === WPRM_Settings::get( 'post_type_structure' ) && WPRM_Settings::get( 'post_type_comments' ) ) {
					$post_ids[] = $recipe_id;
				}

				$parent_post_id = $recipe->parent_post_id();
				if ( $parent_post_id ) {
					$post_ids[] = $parent_post_id;
				}

				if ( $post_ids ) {
					$comment_ratings = WPRM_Rating_Database::get_ratings(array(
						'where' => 'approved = 1 AND post_id IN (' . implode( ', ', array_map( 'intval', $post_ids ) ) . ')',
					));
	
					$ratings = $comment_ratings['ratings'];
				}
			} else {
				$comments = get_approved_comments( $recipe->parent_post_id() );
				$comment_ids = array_map( 'intval', wp_list_pluck( $comments, 'comment_ID' ) );

				if ( count( $comment_ids ) ) {
					$comment_ratings = WPRM_Rating_Database::get_ratings(array(
						'where' => 'comment_id IN (' . implode( ',', $comment_ids ) . ')',
					));

					$ratings = $comment_ratings['ratings'];
				}
			}
		}

		return $ratings;
	}

	/**
	 * Get rating for a specific comment.
	 *
	 * @since	2.2.0
	 * @param	int $comment_id ID of the comment.
	 */
	public static function get_rating_for( $comment_id ) {
		$rating = 0;
		$comment_id = intval( $comment_id );

		if ( $comment_id ) {
			$rating_found = get_comment_meta( $comment_id, 'wprm-comment-rating', true );

			// Cache rating for this comment if none can be found.
			if ( '' === $rating_found ) {
				$rating_found = WPRM_Rating_Database::get_rating(array(
					'where' => 'comment_id = ' . $comment_id,
				));
	
				if ( $rating_found ) {
					$rating = intval( $rating_found->rating );
				} else {
					$rating = 0;
				}

				self::update_cached_rating( $comment_id, $rating );
			} else {
				$rating = intval( $rating_found );
			}
		}

		return $rating;
	}

	/**
	 * Add or update rating for a specific comment.
	 *
	 * @since	2.2.0
	 * @param	int $comment_id ID of the comment.
	 * @param	int $comment_rating Rating to add for this comment.
	 */
	public static function add_or_update_rating_for( $comment_id, $comment_rating ) {
		$comment_id = intval( $comment_id );
		$comment_rating = intval( $comment_rating );

		if ( $comment_id ) {
			$comment = get_comment( $comment_id );

			if ( $comment ) {
				if ( $comment_rating ) {
					$rating = array(
						'date' => $comment->comment_date,
						'comment_id' => $comment->comment_ID,
						'user_id' => $comment->user_id,
						'ip' => $comment->comment_author_IP,
						'rating' => $comment_rating,
					);

					WPRM_Rating_Database::add_or_update_rating( $rating );
				} else {
					WPRM_Rating_Database::delete_ratings_for_comment( $comment_id );
				}
			} else {
				WPRM_Rating_Database::delete_ratings_for_comment( $comment_id );
			}
		}
	}

	/**
	 * Update the comment rating meta that is used as a cache.
	 *
	 * @since	3.1.0
	 * @param	int $comment_id ID of the comment.
	 * @param	int $comment_rating Rating to set for this comment.
	 */
	public static function update_cached_rating( $comment_id, $comment_rating ) {
		$comment_id = intval( $comment_id );
		$comment_rating = intval( $comment_rating );

		if ( $comment_id ) {
			$comment = get_comment( $comment_id );

			if ( $comment ) {
				update_comment_meta( $comment_id, 'wprm-comment-rating', $comment_rating );

				// FlyingPress compatibility.
				if ( class_exists( 'FlyingPress\Purge' ) ) {
					$post_link = get_permalink( $comment->comment_post_ID );

					// FlyingPress changed API. Make sure there are no PHP errors.
					if( is_callable( 'FlyingPress\Purge::purge_url' ) ) {
						FlyingPress\Purge::purge_url( $post_link );
					} elseif ( is_callable( 'FlyingPress\Purge::purge_by_urls' ) ) {
						FlyingPress\Purge::purge_by_urls( array( $post_link ) );
					}
				}
			}
		}
	}

	/**
	 * Add field to the comment form.
	 *
	 * @since    1.1.0
	 * @param		 mixed  $text Comment text.
	 * @param		 object $comment Comment object.
	 */
	public static function add_stars_to_comment( $text, $comment = null ) {
		if ( null !== $comment ) {
			// Ratings are shown in separate column on admin comments list page.
			global $pagenow;
			if ( 'edit-comments.php' === $pagenow ) {
				return $text;
			}

			// Get rating and show stars.
			$rating = self::get_rating_for( $comment->comment_ID );

			$rating_html = '';
			if ( $rating ) {
				ob_start();
				$template = apply_filters( 'wprm_template_comment_rating', WPRM_DIR . 'templates/public/comment-rating.php' );
				require( $template );
				$rating_html = ob_get_contents();
				ob_end_clean();
			}

			$text = 'below' === WPRM_Settings::get( 'comment_rating_position' ) ? $text . $rating_html : $rating_html . $text;
		}

		return $text;
	}

	/**
	 * Compatibility with the wpDiscuz plugin.
	 *
	 * @since    1.3.0
	 */
	public static function wpdiscuz_compatibility() {
		if ( ! defined( 'WPDISCUZ_BOTTOM_TOOLBAR' ) ) {
			define( 'WPDISCUZ_BOTTOM_TOOLBAR', true );
		}
	}

	/**
	 * Add star rating option to the comment form.
	 *
	 * @param    mixed $comment_field HTML for the comment field.
	 * @since    4.2.1
	 */
	public static function add_rating_field_to_comment_form( $comment_field ) {
		if ( 'legacy' !== WPRM_Settings::get( 'comment_rating_form_position' ) ) {
			$rating = 0;
			$template = apply_filters( 'wprm_template_comment_rating_form', WPRM_DIR . 'templates/public/comment-rating-form.php' );

			ob_start();
			require( $template );
			$rating_form_html = ob_get_contents();
			ob_end_clean();

			if ( 'below' === WPRM_Settings::get( 'comment_rating_form_position' ) ) {
				$comment_field = $comment_field . $rating_form_html;
			} else {
				$comment_field = $rating_form_html . $comment_field;
			}
		}

		return $comment_field;
	}

	/**
	 * Add field to the comment form legacy option.
	 *
	 * @since    4.3.3
	 */
	public static function add_rating_field_to_comments_legacy() {
		if ( 'legacy' === WPRM_Settings::get( 'comment_rating_form_position' ) ) {
			self::add_rating_field_to_comments();
		}
	}

	/**
	 * Add field to the comment form legacy option.
	 *
	 * @since    4.3.3
	 */
	public static function add_rating_field_to_thrive_comments() {
		self::add_rating_field_to_comments();
	}

	/**
	 * Add field to the comment form.
	 *
	 * @since    1.1.0
	 */
	public static function add_rating_field_to_comments() {
		$rating = 0;
		$template = apply_filters( 'wprm_template_comment_rating_form', WPRM_DIR . 'templates/public/comment-rating-form.php' );
		require( $template );
	}

	/**
	 * Add field to the admin comment form.
	 *
	 * @since    1.1.0
	 */
	public static function add_rating_field_to_admin_comments() {
		add_meta_box( 'wprm-comment-rating', __( 'Recipe Rating', 'wp-recipe-maker' ), array( __CLASS__, 'add_rating_field_to_admin_comments_form' ), 'comment', 'normal', 'high' );
	}

	/**
	 * Callback for the admin comments meta box.
	 *
	 * @since    1.1.0
	 * @param		 object $comment Comment being edited.
	 */
	public static function add_rating_field_to_admin_comments_form( $comment ) {
		$rating = self::get_rating_for( $comment->comment_ID );

		wp_nonce_field( 'wprm-comment-rating-nonce', 'wprm-comment-rating-nonce', false );
		require( WPRM_DIR . 'templates/public/comment-rating-form.php' );
	}

	/**
	 * Add a rating column to the admin comments list.
	 *
	 * @since	7.6.0
	 * @param	array $columns Current columns.
	 */
	public static function comments_list_columns( $columns ) {
		$new_columns = array();

		// Try to add after comment column.
		$added = false;
		foreach ( $columns as $id => $label ) {
			$new_columns[ $id ] = $label;

			if ( ! $added && 'comment' === $id ) {
				$new_columns['wprm_rating'] = __( 'Recipe Rating', 'wp-recipe-maker' );
				$added = true;
			}
		}

		// Make sure the column is always added.
		if ( ! $added ) {
			$new_columns['wprm_rating'] = __( 'Recipe Rating', 'wp-recipe-maker' );
		}

		return $new_columns;
	}

	/**
	 * Make the rating column sortable to the admin comments list.
	 *
	 * @since	7.6.0
	 * @param	array $columns Current columns.
	 */
	public static function comments_list_sortable_columns( $columns ) {
		$columns['wprm_rating'] = 'by_wprm_rating';

		return $columns;
	}

	/**
	 * Make the rating column sortable to the admin comments list.
	 *
	 * @since	7.6.0
	 * @param	mixed $comments_query Comment Query.
	 */
	public static function comments_list_sort( $comments_query ) {
		if( 'by_wprm_rating' == $comments_query->query_vars['orderby'] ) {
			$comments_query->query_vars['meta_key'] = 'wprm-comment-rating';
			$comments_query->query_vars['orderby'] = 'meta_value_num';
		}

		return $comments_query;
	}

	/**
	 * Add a rating column to the admin comments list.
	 *
	 * @since	7.6.0
	 * @param	array $column Current column.
	 * @param	array $comment_id Current comment ID.
	 */
	public static function comments_list_column( $column, $comment_id ) {
		if ( 'wprm_rating' === $column ) {
			$rating = self::get_rating_for( $comment_id );

			// If no rating, check if this post actually contains a recipe.
			if ( ! $rating ) {
				$comment = get_comment( $comment_id ); 
				$recipe_ids = WPRM_Recipe_Manager::get_recipe_ids_from_post( $comment->comment_post_ID );

				if ( ! $recipe_ids ) {
					return $column;
				}
			}

			wp_nonce_field( 'wprm-comment-rating-nonce', 'wprm-comment-rating-nonce', false );
			require( WPRM_DIR . 'templates/public/comment-rating-form.php' );

			echo '<a href="#" class="wprm-rating-change-save" data-comment-id="' . esc_attr( $comment_id ) . '" role="button" onclick="WPRecipeMaker.comments.save(this)">' . __( 'Save changed rating', 'wp-recipe-maker' ) . '<span class="wprm-rating-change"></span></a>';
		}

		return $column;
	}

	/**
	 * Save the comment rating.
	 *
	 * @since	1.1.0
	 * @param	int $comment_id ID of the comment being saved.
	 * @param	mixed $comment_approved Approval state of the comment being saved.
	 * @param	mixed $commentdata Data of the comment being saved.
	 */
	public static function save_comment_rating( $comment_id, $comment_approved, $commentdata ) {
		$rating = isset( $_POST['wprm-comment-rating'] ) ? intval( $_POST['wprm-comment-rating'] ) : 0; // Input var okay.

		// Needed for editing through comments list overview.
		if ( ! $rating && isset( $_POST[ 'wprm-comment-rating-' . $comment_id ] ) ) {
			$rating = intval( $_POST[ 'wprm-comment-rating-' . $comment_id ] );
		}

		// Thrive comments compatibility.
		if ( ! $rating && isset( $_POST['tcm_extra_fields'] ) && isset( $_POST['tcm_extra_fields']['wprm-comment-rating'] ) ) {
			$rating = intval( $_POST['tcm_extra_fields']['wprm-comment-rating'] );
		}

		// Track in analytics.
		$post_id = isset( $commentdata['comment_post_ID'] ) ? intval( $commentdata['comment_post_ID'] ) : false;
		WPRM_Analytics::register_action( false, $post_id, 'comment', array(
			'comment_id' => $comment_id,
			'rating' => $rating,
		) );

		self::add_or_update_rating_for( $comment_id, $rating );
	}

	/**
	 * Delete recipe rating when comment gets deleted.
	 *
	 * @since	8.0.0
	 * @param	int $comment_id ID of the comment being deleted.
	 */
	public static function delete_comment_rating( $comment_id ) {
		$comment_id = intval( $comment_id );

		if ( $comment_id ) {
			WPRM_Rating_Database::delete_ratings_for_comment( $comment_id );
		}

		// Recalculate recipe rating.
		WPRM_Rating::update_recipe_rating_for_comment( $comment_id );
	}

	/**
	 * Update recipe rating when comment changes.
	 *
	 * @since	3.2.0
	 * @param	int $comment_id ID of the comment being changed.
	 */
	public static function update_comment_rating_on_change( $comment_id ) {
		// Force update in case approval state changed.
		$rating = self::get_rating_for( $comment_id );
		self::add_or_update_rating_for( $comment_id, $rating );

		// Recalculate recipe rating.
		WPRM_Rating::update_recipe_rating_for_comment( $comment_id );
	}

	/**
	 * Update recipe rating when comments are updated in bulk.
	 *
	 * @since	6.3.0
	 * @param	mixed $sendback The redirect URL.
	 * @param	mixed $doaction The action being taken.
	 * @param	array $items 	An array of IDs of comments.
	 */
	public static function update_comment_ratings_bulk( $sendback, $doaction, $items ) {
		foreach ( $items as $item ) {
			self::update_comment_rating_on_change( $item );
		}
	}

	/**
	 * Save the admin comment rating.
	 *
	 * @since	1.1.0
	 * @param	int $comment_id ID of the comment being saved.
	 */
	public static function save_admin_comment_rating( $comment_id ) {
		if ( isset( $_POST['wprm-comment-rating-nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['wprm-comment-rating-nonce'] ), 'wprm-comment-rating-nonce' ) ) { // Input var okay.
			$rating = isset( $_POST['wprm-comment-rating'] ) ? intval( $_POST['wprm-comment-rating'] ) : 0; // Input var okay.
			self::add_or_update_rating_for( $comment_id, $rating );
		}
	}
}

WPRM_Comment_Rating::init();
