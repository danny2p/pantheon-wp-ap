<?php
/**
 * Handle comment moderation.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle comment moderation.
 *
 * @since      9.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Comment_Moderation {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.6.0
	 */
	public static function init() {
		add_filter( 'admin_comment_types_dropdown', array( __CLASS__, 'comment_types_dropdown' ) );
		add_filter( 'pre_get_comments', array( __CLASS__, 'comments_query' ) );
		add_filter( 'manage_comments_custom_column', array( __CLASS__, 'comments_list_column' ), 5, 2 );

		add_action( 'add_meta_boxes_comment', array( __CLASS__, 'add_meta_box' ) );

		add_action( 'edit_comment', array( __CLASS__, 'save_moderation' ) );
	}

	/**
	 * Add field to the admin comment form.
	 *
	 * @since	9.6.0
	 */
	public static function add_meta_box() {
		add_meta_box( 'wprm-comment-moderation', __( 'WP Recipe Maker Moderation Tools', 'wp-recipe-maker' ), array( __CLASS__, 'meta_box' ), 'comment', 'normal', 'core' );
	}

	/**
	 * Callback for the admin comments meta box.
	 *
	 * @since	9.6.0
	 * @param	object $comment Comment being edited.
	 */
	public static function meta_box( $comment ) {
		wp_nonce_field( 'wprm-comment-moderation-nonce', 'wprm-comment-moderation-nonce', false );
		require( WPRM_DIR . 'templates/admin/comment-moderation.php' );
	}

	/**
	 * Add flagged comments to comment types.
	 *
	 * @since	9.6.0
	 * @param	array $types Current comment types.
	 */
	public static function comment_types_dropdown( $types ) {
		$types['wprm_flagged'] = __( 'Flagged Comments', 'wp-recipe-maker' );
		$types['wprm_with_comment_text'] = __( 'Comments with Text', 'wp-recipe-maker' );
		$types['wprm_rating_with_comment_text'] = __( 'Comment Ratings with Text', 'wp-recipe-maker' );
		$types['wprm_rating_no_comment_text'] = __( 'Comment Ratings without Text', 'wp-recipe-maker' );

		return $types;
	}

	/**
	 * Check for flagged comments in the comments query.
	 *
	 * @since	9.5.0
	 */
	public static function comments_query( $wp_comment_query ) {
		if ( isset( $wp_comment_query->query_vars['type'] ) && 'wprm_flagged' === $wp_comment_query->query_vars['type'] ) {
			$wp_comment_query->query_vars['type'] = 'comment';

			// When looking for a count, don't add the meta query. This makes sure the "Flagged Comments" option shows up in the dropdown.
			if ( ! isset( $wp_comment_query->query_vars['count'] ) || false === $wp_comment_query->query_vars['count'] ) {
				$wp_comment_query->query_vars['meta_query'] = array(
					array(
						'key' => 'wprm_comment_moderation_flag',
						'value' => '1',
					),
				);
			}
		}

		if ( isset( $wp_comment_query->query_vars['type'] ) && 'wprm_rating_with_comment_text' === $wp_comment_query->query_vars['type'] ) {
			$wp_comment_query->query_vars['type'] = 'comment';

			// When looking for a count, don't add the query conditions. This makes sure the option shows up in the dropdown.
			if ( ! isset( $wp_comment_query->query_vars['count'] ) || false === $wp_comment_query->query_vars['count'] ) {
				// Find comments with empty content but with a WPRM rating
				add_filter( 'comments_clauses', function( $clauses ) {
					global $wpdb;
					
					// Join with comment meta table to find comments with ratings
					$clauses['join'] .= " INNER JOIN $wpdb->commentmeta AS cm ON $wpdb->comments.comment_ID = cm.comment_id AND cm.meta_key = 'wprm-comment-rating'";
					
					// Only get comments with empty content
					$clauses['where'] .= " AND $wpdb->comments.comment_content != '' AND $wpdb->comments.comment_content != ' ' AND $wpdb->comments.comment_content IS NOT NULL";

					// Only get comments with ratings not equal to 0
					$clauses['where'] .= " AND cm.meta_value != '0'";
					
					return $clauses;
				} );
			}
		}

		if ( isset( $wp_comment_query->query_vars['type'] ) && 'wprm_rating_no_comment_text' === $wp_comment_query->query_vars['type'] ) {
			$wp_comment_query->query_vars['type'] = 'comment';

			// When looking for a count, don't add the query conditions. This makes sure the option shows up in the dropdown.
			if ( ! isset( $wp_comment_query->query_vars['count'] ) || false === $wp_comment_query->query_vars['count'] ) {
				// Find comments with empty content but with a WPRM rating
				add_filter( 'comments_clauses', function( $clauses ) {
					global $wpdb;
					
					// Join with comment meta table to find comments with ratings
					$clauses['join'] .= " INNER JOIN $wpdb->commentmeta AS cm ON $wpdb->comments.comment_ID = cm.comment_id AND cm.meta_key = 'wprm-comment-rating'";
					
					// Only get comments with empty content
					$clauses['where'] .= " AND ($wpdb->comments.comment_content = '' OR $wpdb->comments.comment_content = ' ' OR $wpdb->comments.comment_content IS NULL)";

					// Only get comments with ratings not equal to 0
					$clauses['where'] .= " AND cm.meta_value != '0'";
					
					return $clauses;
				} );
			}
		}
		
		if ( isset( $wp_comment_query->query_vars['type'] ) && 'wprm_with_comment_text' === $wp_comment_query->query_vars['type'] ) {
			$wp_comment_query->query_vars['type'] = 'comment';

			// When looking for a count, don't add the query conditions. This makes sure the option shows up in the dropdown.
			if ( ! isset( $wp_comment_query->query_vars['count'] ) || false === $wp_comment_query->query_vars['count'] ) {
				// Find comments that have text content
				add_filter( 'comments_clauses', function( $clauses ) {
					global $wpdb;
					
					// Only get comments with non-empty content (more than whitespace)
					$clauses['where'] .= " AND $wpdb->comments.comment_content != '' AND $wpdb->comments.comment_content != ' ' AND $wpdb->comments.comment_content IS NOT NULL";
					
					return $clauses;
				} );
			}
		}

		return $wp_comment_query;
	}

	/**
	 * Add a rating column to the admin comments list.
	 *
	 * @since	9.6.0
	 * @param	array $column Current column.
	 * @param	array $comment_id Current comment ID.
	 */
	public static function comments_list_column( $column, $comment_id ) {
		if ( 'wprm_rating' === $column ) {
			$flag = get_comment_meta( $comment_id, 'wprm_comment_moderation_flag', true );
			
			$class = $flag ? 'wprm-comment-moderation-flagged' : 'wprm-comment-moderation-unflagged';
			$icon = $flag ? 'flag' : 'unflag';
			$alt = $flag ? __( 'Comment Flagged', 'wp-recipe-maker' ) : __( 'Comment Not Flagged', 'wp-recipe-maker' );

			echo '<span class="wprm-comment-moderation-flag ' . esc_attr( $class ) .'">';
			echo '<img src="' . WPRM_URL . 'assets/icons/comment-moderation/' . esc_attr( $icon ) . '.svg" alt="' . esc_attr( $alt ).  '" title="' . esc_attr( $alt ) . '" />';
			echo '</span>';
		}

		return $column;
	}

	/**
	 * Save the moderation fields.
	 *
	 * @since	9.6.0
	 * @param	int $comment_id ID of the comment being saved.
	 */
	public static function save_moderation( $comment_id ) {
		if ( isset( $_POST['wprm-comment-moderation-nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wprm-comment-moderation-nonce'] ) ), 'wprm-comment-moderation-nonce' ) ) {
			$note = isset( $_POST['wprm-comment-moderation-note'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['wprm-comment-moderation-note'] ) ) ) : false;
			$flag = isset( $_POST['wprm-comment-moderation-flag'] ) && '1' === $_POST['wprm-comment-moderation-flag'] ? 1 : 0;
			$email = isset( $_POST['wprm-comment-moderation-email'] ) && '1' === $_POST['wprm-comment-moderation-email'] ? true : false;

			$changed_flag = self::maybe_flag( $comment_id, $flag );
			if ( $note ) {
				self::log_change( $comment_id, 'note', $note );
			}

			if ( $changed_flag || $note ) {
				if ( $email ) {
					$comment = get_comment( $comment_id );
					$comment_post = get_post( $comment->comment_post_ID );

					// translators: %s: title of the post the comment was given to.
					$subject = sprintf( __( 'WPRM Comment Moderation: %s', 'wp-recipe-maker' ), $comment_post->post_title );
					$message = __( 'Comment', 'wp-recipe-maker' );
					$message .= ': ' . get_edit_comment_link( $comment_id );
					$message .= "\n";
					$message .= "\n";

					if ( $changed_flag ) {
						$message .= $flag ? __( 'Flagged for moderation', 'wp-recipe-maker' ) : __( 'Unflagged for moderation', 'wp-recipe-maker' );
					}

					if ( $note ) {
						if ( $changed_flag ) {
							$message .= "\n";
							$message .= "\n";
						}

						$message .= __( 'Note added', 'wp-recipe-maker' );
						$message .= ":\n";
						$message .= $note;
					}
					
					

					$admin_email = self::get_admin_email();
					foreach ( $admin_email as $email ) {
						wp_mail( $email, $subject, $message );
					}
				}
			}
		}
	}

	/**
	 * Check if a comment should be flagged.
	 *
	 * @since	9.6.0
	 * @param	int $comment_id ID of the comment to flag.
	 * @param	boolean $flag Flag status.
	 */
	public static function maybe_flag( $comment_id, $flag ) {
		$flag = $flag ? 1 : 0;

		$current_flag = get_comment_meta( $comment_id, 'wprm_comment_moderation_flag', true );
		$current_flag = $current_flag ? 1 : 0;

		if ( $flag !== $current_flag ) {
			self::log_change( $comment_id, $flag ? 'flag' : 'unflag' );

			if ( $flag ) {
				update_comment_meta( $comment_id, 'wprm_comment_moderation_flag', '1' );
			} else {
				delete_comment_meta( $comment_id, 'wprm_comment_moderation_flag' );
			}

			return true;
		}

		return false;
	}

	/**
	 * Log a change for this comment.
	 *
	 * @since	9.6.0
	 * @param	int $comment_id ID of the comment to log the change for.
	 * @param	string $type Type of change.
	 * @param	string $text Text for the change.
	 */
	public static function log_change( $comment_id, $type, $text = '' ) {
		if ( ! $comment_id ) {
			return;
		}

		$log = get_comment_meta( $comment_id, 'wprm_comment_moderation_log', true );

		if ( ! $log ) {
			$log = array();
		}

		// Check if we're logging the adding of stars.
		if ( 'stars-added' === $type ) {
			$new_rating = intval( $text );
			// translators: %d: number of stars for this recipe.
			$text = sprintf( esc_html( _n( '%d star', '%d stars', $new_rating, 'wp-recipe-maker' ) ), $new_rating );

			// Check if there was an old rating, if so, the stars changed.
			$old_rating = get_comment_meta( $comment_id, 'wprm-comment-rating', true );
			$old_rating = $old_rating ? intval( $old_rating ) : 0;
			if ( 0 < $old_rating ) {
				// Don't log if the rating didn't actually change.
				if ( $old_rating === $new_rating ) {
					return;
				}

				$type = 'stars-changed';
				// translators: %d: number of stars for this recipe.
				$text = sprintf( esc_html( _n( '%d star', '%d stars', $old_rating, 'wp-recipe-maker' ) ), $old_rating ) . ' &rArr; ' . $text;
			}
		}

		// Check if we're logging the removal of stars.
		if ( 'stars-removed' === $type ) {
			$old_rating = get_comment_meta( $comment_id, 'wprm-comment-rating', true );
			$old_rating = $old_rating ? intval( $old_rating ) : 0;

			if ( 0 < $old_rating ) {
				// translators: %d: number of stars for this recipe.
				$text = sprintf( esc_html( _n( '%d star', '%d stars', $old_rating, 'wp-recipe-maker' ) ), $old_rating );
			} else {
				return;
			}
		}

		// Add change to log.
		$log[] = array(
			'user' => get_current_user_id(),
			'datetime' => current_time( 'mysql' ),
			'type' => $type,
			'text' => $text,
		);

		update_comment_meta( $comment_id, 'wprm_comment_moderation_log', $log );
	}

	/**
	 * Get the admin email.
	 */
	public static function get_admin_email() {
		$admin_email = apply_filters( 'wprm_comment_moderation_email', get_option( 'admin_email' ) );

		if ( ! is_array( $admin_email ) ) {
			$admin_email = array( $admin_email );
		}

		// Make sure these are all actual email addresses.
		$admin_email = array_filter( $admin_email, 'is_email' );

		return $admin_email;
	}
}

WPRM_Comment_Moderation::init();