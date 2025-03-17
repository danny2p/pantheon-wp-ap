<?php
/**
 * Handle marking comments as reviews.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle marking comments as reviews.
 *
 * @since      9.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Comment_Review {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.6.0
	 */
	public static function init() {
		add_filter( 'admin_comment_types_dropdown', array( __CLASS__, 'comment_types_dropdown' ) );
		add_filter( 'pre_get_comments', array( __CLASS__, 'comments_query' ) );

		add_filter( 'manage_edit-comments_columns', array( __CLASS__, 'comments_list_columns' ), 8 );
		add_filter( 'manage_edit-comments_sortable_columns', array( __CLASS__, 'comments_list_sortable_columns' ) );
		add_filter( 'pre_get_comments', array( __CLASS__, 'comments_list_sort' ) );
		add_filter( 'manage_comments_custom_column', array( __CLASS__, 'comments_list_column' ), 10, 2 );

		add_action( 'wprm_comment_rating_admin_form', array( __CLASS__, 'add_review_to_meta_box' ), 10, 2 );
		add_action( 'edit_comment', array( __CLASS__, 'save_admin_comment_rating' ) );
	}

	/**
	 * Add review statusses to comment types.
	 *
	 * @since	9.6.0
	 * @param	array $types Current comment types.
	 */
	public static function comment_types_dropdown( $types ) {
		if ( 'never' !== WPRM_Settings::get( 'metadata_review_include' ) ) {
			$types['wprm_review_featured'] = __( 'Featured Reviews', 'wp-recipe-maker' );
			$types['wprm_review_excluded'] = __( 'Excluded from Review Metadata', 'wp-recipe-maker' );
		}

		return $types;
	}

	/**
	 * Check for review statusses in the comments query.
	 *
	 * @since	9.5.0
	 */
	public static function comments_query( $wp_comment_query ) {
		if ( isset( $wp_comment_query->query_vars['type'] ) && ( 'wprm_review_featured' === $wp_comment_query->query_vars['type'] || 'wprm_review_excluded' === $wp_comment_query->query_vars['type'] ) ) {
			$value = 'wprm_review_featured' === $wp_comment_query->query_vars['type'] ? 'featured' : 'excluded';
			$wp_comment_query->query_vars['type'] = 'comment';

			// When looking for a count, don't add the meta query. This makes sure the "Flagged Comments" option shows up in the dropdown.
			if ( ! isset( $wp_comment_query->query_vars['count'] ) || false === $wp_comment_query->query_vars['count'] ) {
				$wp_comment_query->query_vars['meta_query'] = array(
					array(
						'key' => 'wprm-comment-review',
						'value' => $value,
					),
				);
			}
		}

		return $wp_comment_query;
	}

	/**
	 * Add a rating column to the admin comments list.
	 *
	 * @since	9.6.0
	 * @param	array $columns Current columns.
	 */
	public static function comments_list_columns( $columns ) {
		$new_columns = array();

		if ( 'never' !== WPRM_Settings::get( 'metadata_review_include' ) ) {
			// Try to add after comment column.
			$added = false;
			foreach ( $columns as $id => $label ) {
				$new_columns[ $id ] = $label;

				if ( ! $added && 'comment' === $id ) {
					$new_columns['wprm_review'] = __( 'Review Metadata', 'wp-recipe-maker' );
					$added = true;
				}
			}

			// Make sure the column is always added.
			if ( ! $added ) {
				$new_columns['wprm_review'] = __( 'Review Metadata', 'wp-recipe-maker' );
			}
		}

		return $new_columns;
	}

	/**
	 * Make the rating column sortable to the admin comments list.
	 *
	 * @since	9.6.0
	 * @param	array $columns Current columns.
	 */
	public static function comments_list_sortable_columns( $columns ) {
		$columns['wprm_review'] = 'by_wprm_review';

		return $columns;
	}

	/**
	 * Make the rating column sortable to the admin comments list.
	 *
	 * @since	9.6.0
	 * @param	mixed $comments_query Comment Query.
	 */
	public static function comments_list_sort( $comments_query ) {
		if( 'wprm_review' == $comments_query->query_vars['orderby'] ) {
			$comments_query->query_vars['meta_key'] = 'wprm-comment-review';
			$comments_query->query_vars['orderby'] = 'meta_value';
		}

		return $comments_query;
	}

	/**
	 * Add a rating column to the admin comments list.
	 *
	 * @since	9.6.0
	 * @param	array $column Current column.
	 * @param	array $comment_id Current comment ID.
	 */
	public static function comments_list_column( $column, $comment_id ) {
		if ( 'wprm_review' === $column ) {
			$rating = WPRM_Comment_Rating::get_rating_for( $comment_id );
			$comment_text = trim( get_comment_text( $comment_id ) );

			$statusses = array(
				'na' => 'n/a',
				'default' => __( 'Default', 'wp-recipe-maker' ),
				'featured' => __( 'Featured Review', 'wp-recipe-maker' ),
				'excluded' => __( 'Excluded', 'wp-recipe-maker' ),
			);

			$status = 'na';
			if ( 0 < $rating && $comment_text ) {
				$comment_review_status = get_comment_meta( $comment_id , 'wprm-comment-review', true );

				if ( isset( $statusses[ $comment_review_status ] ) ) {
					$status = $comment_review_status;
				} else {
					$status = 'default';
				}
			}
			
			echo '<span class="wprm-comment-review-status wprm-comment-review-status-' . esc_attr( $status ) . '">' . esc_html( $statusses[ $status ] ) . '</span>';
		}

		return $column;
	}

	public static function add_review_to_meta_box( $comment, $rating ) {
		if ( 'never' !== WPRM_Settings::get( 'metadata_review_include' ) ) {
			wp_nonce_field( 'wprm-comment-review-nonce', 'wprm-comment-review-nonce', false );
			$comment_text = trim( get_comment_text( $comment ) );
		
			if ( 0 < $rating && $comment_text ) {
				$comment_review_status = get_comment_meta( $comment->comment_ID , 'wprm-comment-review', true );
				?>
				<select name="wprm-comment-review" id="wprm-comment-review">
					<option value="default"><?php esc_html_e( 'This is a regular comment (default)', 'wp-recipe-maker' ); ?></option>
					<option value="featured"<?php if ( 'featured' === $comment_review_status ) { echo ' selected="selected"'; } ?>><?php esc_html_e( 'Mark as a "Featured Review" to always use in the metadata', 'wp-recipe-maker' ); ?></option>
					<option value="excluded"<?php if ( 'excluded' === $comment_review_status ) { echo ' selected="selected"'; } ?>><?php esc_html_e( 'Exclude this comment from the review metadata', 'wp-recipe-maker' ); ?></option>
				</select>
				<?php
			}
		}
	}

	/**
	 * Save the admin comment rating.
	 *
	 * @since	9.6.0
	 * @param	int $comment_id ID of the comment being saved.
	 */
	public static function save_admin_comment_rating( $comment_id ) {
		if ( isset( $_POST['wprm-comment-review-nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['wprm-comment-review-nonce'] ), 'wprm-comment-review-nonce' ) ) { // Input var okay.
			$comment_review_status = isset( $_POST['wprm-comment-review'] ) ? sanitize_key( $_POST['wprm-comment-review'] ) : 'default'; // Input var okay.
			update_comment_meta( $comment_id, 'wprm-comment-review', $comment_review_status );
		}
	}
}

WPRM_Comment_Review::init();
