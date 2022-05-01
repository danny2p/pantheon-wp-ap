<?php
/**
 * Responsible for the rating database.
 *
 * @link       http://bootstrapped.ventures
 * @since      2.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the rating database.
 *
 * @since      2.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Rating_Database {
	/**
	 * Current version of the rating database structure.
	 *
	 * @since    2.2.0
	 * @access   private
	 * @var      mixed $database_version Current version of the rating database structure.
	 */
	private static $database_version = '3.0';

	/**
	 * Fields in the rating database table.
	 *
	 * @since    2.2.0
	 * @access   private
	 * @var      mixed $fields Fields in the rating database table.
	 */
	private static $fields = array( 'id', 'date', 'recipe_id', 'comment_id', 'user_id', 'ip', 'rating', 'approved' );

	/**
	 * Cache for queries.
	 *
	 * @since    5.0.0
	 * @access   private
	 * @var      mixed $cache Cached queries.
	 */
	private static $cache = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    2.2.0
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'check_database_version' ), 1 );
	}

	/**
	 * Check if the correct database version is present.
	 *
	 * @since    2.2.0
	 */
	public static function check_database_version() {
		$current_version = get_option( 'wprm_rating_db_version', '0.0' );

		if ( version_compare( $current_version, self::$database_version ) < 0 ) {
			self::update_database( $current_version );
		}
	}

	/**
	 * Create or update the rating database.
	 *
	 * @since    2.2.0
	 * @param    mixed $from Database version to update from.
	 */
	public static function update_database( $from ) {
		global $wpdb;

		$table_name = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		recipe_id bigint(20) unsigned NOT NULL,
		post_id bigint(20) unsigned NOT NULL,
		comment_id bigint(20) unsigned NOT NULL,
		approved tinyint(1) DEFAULT '1' NOT NULL,
		user_id bigint(20) unsigned NOT NULL DEFAULT '0',
		ip varchar(39) DEFAULT '' NOT NULL,
		rating tinyint(1) DEFAULT '0' NOT NULL,
		PRIMARY KEY (id),
		KEY date (date),
		KEY recipe_id (recipe_id),
		KEY post_id (post_id),
		KEY comment_id (comment_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( 'wprm_rating_db_version', self::$database_version );
	}

	/**
	 * Get the name of the rating database table.
	 *
	 * @since    2.2.0
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wprm_ratings';
	}

	/**
	 * Add or update a rating in the database.
	 *
	 * @since    2.2.0
	 * @param    mixed $unsanitized_rating Rating to add to the database.
	 */
	public static function add_or_update_rating( $unsanitized_rating ) {
		// Sanitize Rating fields.
		$rating = array();
		$rating['date'] = isset( $unsanitized_rating['date'] ) && $unsanitized_rating['date'] ? $unsanitized_rating['date'] : current_time( 'mysql' );
		$rating['recipe_id'] = isset( $unsanitized_rating['recipe_id'] ) ? intval( $unsanitized_rating['recipe_id'] ) : 0;
		$rating['comment_id'] = isset( $unsanitized_rating['comment_id'] ) ? intval( $unsanitized_rating['comment_id'] ) : 0;
		$rating['user_id'] = isset( $unsanitized_rating['user_id'] ) ? intval( $unsanitized_rating['user_id'] ) : 0;
		$rating['ip'] = isset( $unsanitized_rating['ip'] ) && $unsanitized_rating['ip'] ? esc_attr( $unsanitized_rating['ip'] ) : '';
		$rating['rating'] = isset( $unsanitized_rating['rating'] ) ? intval( $unsanitized_rating['rating'] ) : 0;

		// Get post ID for comment.
		$rating['post_id'] = 0;
		$rating['approved'] = 1;
		if ( $rating['comment_id'] ) {
			$comment = get_comment( $rating['comment_id'] );

			if ( $comment ) {
				$rating['post_id'] = $comment->comment_post_ID;
				$rating['approved'] = '1' === $comment->comment_approved || 'approve' === $comment->comment_approved ? 1 : 0;
			} else {
				$rating['approved'] = 0;
			}
		}

		// Rating is only valid when between 0 and 5.
		if ( 0 < $rating['rating'] && 5 >= $rating['rating'] ) {
			global $wpdb;
			$table_name = self::get_table_name();

			$where = false;

			// Check for existing ratings from this user/ip for this recipe/comment.
			if ( $rating['recipe_id'] ) {
				if ( $rating['user_id'] ) {
					$where = 'recipe_id = ' . $rating['recipe_id'] . ' AND user_id = ' . $rating['user_id'];
				} elseif ( $rating['ip'] ) {
					$where = 'recipe_id = ' . $rating['recipe_id'] . ' AND ip = "' . $rating['ip'] . '"';
				}
			} elseif ( $rating['comment_id'] ) {
				$where = 'comment_id = ' . $rating['comment_id'];
			}

			// Only continue if it was a valid rating.
			if ( $where ) {
				// Delete existing ratings.
				$existing_ratings = self::get_ratings(array(
					'where' => $where,
				));
				$existing_ratings_ids = wp_list_pluck( $existing_ratings['ratings'], 'id' );

				if ( 0 < count( $existing_ratings_ids ) ) {
					self::delete_ratings( $existing_ratings_ids );
				} else {
					do_action( 'wprm_rating_first_time', $rating ); // Trigger action for gamification plugins.
				}

				// Insert new rating.
				$wpdb->insert( $table_name, $rating );

				// Clear any cached queries.
				self::$cache = array();

				// Update cached rating.
				if ( $rating['recipe_id'] ) {
					WPRM_Rating::update_recipe_rating( $rating['recipe_id'] );
				} else {
					WPRM_Rating::update_recipe_rating_for_comment( $rating['comment_id'] );
					WPRM_Comment_Rating::update_cached_rating( $rating['comment_id'], $rating['rating'] );
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Count all ratings.
	 *
	 * @since    5.0.0
	 */
	public static function count_ratings() {
		global $wpdb;
		$table_name = self::get_table_name();

		$query = 'SELECT count(*) FROM ' . $table_name;
		$count = $wpdb->get_var( $query );

		return intval( $count );
	}

	/**
	 * Query ratings.
	 *
	 * @since    2.2.0
	 * @param    mixed $args Arguments for the query.
	 */
	public static function get_ratings( $args ) {
		$cached_args = serialize( $args );

		if ( isset( $args['nocache'] ) || ! array_key_exists( $cached_args, self::$cache ) ) {
			global $wpdb;
			$table_name = self::get_table_name();

			// Sanitize arguments.
			$order = isset( $args['order'] ) ? strtoupper( $args['order'] ) : '';
			$order = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';

			$orderby = isset( $args['orderby'] ) ? strtolower( $args['orderby'] ) : '';
			$orderby = in_array( $orderby, self::$fields, true ) ? $orderby : 'date';

			$offset = isset( $args['offset'] ) ? intval( $args['offset'] ) : 0;
			$limit = isset( $args['limit'] ) ? intval( $args['limit'] ) : 0;

			$where = isset( $args['where'] ) ? trim( $args['where'] ) : '';

			// Query ratings.
			$query_where = $where ? ' WHERE ' . $where : '';
			$query_order = ' ORDER BY ' . $orderby . ' ' . $order;
			$query_limit = $limit ? ' LIMIT ' . $offset . ',' . $limit : '';

			// Count without limit.
			$query_count = 'SELECT count(*) FROM ' . $table_name . $query_where;
			$count = $wpdb->get_var( $query_count );

			// Query ratings.
			$query_ratings = 'SELECT * FROM ' . $table_name . $query_where . $query_order . $query_limit;
			$ratings = $wpdb->get_results( $query_ratings );

			self::$cache[ $cached_args ] = array(
				'total' => intval( $count ),
				'ratings' => $ratings,
			);
		}
		
		return self::$cache[ $cached_args ];
	}

	/**
	 * Query for 1 specific rating.
	 *
	 * @since    2.2.0
	 * @param    mixed $args Arguments for the query.
	 */
	public static function get_rating( $args ) {
		$ratings = self::get_ratings( $args );

		if ( 0 < $ratings['total'] ) {
			return $ratings['ratings'][0];
		} else {
			return false;
		}
	}

	/**
	 * Delete a single rating and update associated data.
	 *
	 * @since    5.0.0
	 * @param    array $ids Rating IDs to delete.
	 */
	public static function delete_rating( $id ) {
		$rating = self::get_rating( array(
			'where' => 'ID = "' . intval( $id ) . '"',
		) );

		if ( $rating ) {
			// Delete rating.
			self::delete_ratings( array( $id ) );

			// Clear any cached queries.
			self::$cache = array();
			
			// Update cached rating.
			$rating = (array) $rating;

			if ( $rating['recipe_id'] ) {
				WPRM_Rating::update_recipe_rating( $rating['recipe_id'] );
			} else {
				WPRM_Rating::update_recipe_rating_for_comment( $rating['comment_id'] );
				WPRM_Comment_Rating::update_cached_rating( $rating['comment_id'], 0 );
			}

			return true;
		}

		 return false;
	}

	/**
	 * Delete a set of ratings.
	 *
	 * @since    2.2.0
	 * @param    array $ids Rating IDs to delete.
	 */
	public static function delete_ratings( $ids ) {
		global $wpdb;
		$table_name = self::get_table_name();

		if ( is_array( $ids ) && $ids ) {
			$ids = implode( ',', array_map( 'intval', $ids ) );

			// We need to make sure the cached ratings are deleted as well, so get those first.
			$query = self::get_ratings(array(
				'where' => 'id IN (' . $ids . ')',
			));

			// Delete all these rating IDs.
			$wpdb->query( 'DELETE FROM ' . $table_name . ' WHERE id IN (' . $ids . ')' );

			// Clear any cached queries.
			self::$cache = array();

			// Update the cache for the ratings that were deleted.
			foreach ( $query['ratings'] as $rating ) {
				$rating = (array) $rating;

				if ( $rating['recipe_id'] ) {
					WPRM_Rating::update_recipe_rating( $rating['recipe_id'] );
				} else {
					WPRM_Rating::update_recipe_rating_for_comment( $rating['comment_id'] );
					WPRM_Comment_Rating::update_cached_rating( $rating['comment_id'], 0 );
				}
			}
		}
	}

	/**
	 * Delete ratings for a specific recipe.
	 *
	 * @since    2.2.0
	 * @param    int $recipe_id Recipe to delete the ratings for.
	 */
	public static function delete_ratings_for( $recipe_id ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$wpdb->delete( $table_name, array( 'recipe_id' => $recipe_id ), array( '%d' ) );

		// Clear any cached queries.
		self::$cache = array();

		// Update cached rating.
		WPRM_Rating::update_recipe_rating( $recipe_id );
	}

	/**
	 * Delete ratings for a specific comment.
	 *
	 * @since    2.4.0
	 * @param    int $comment_id Comment to delete the ratings for.
	 */
	public static function delete_ratings_for_comment( $comment_id ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$wpdb->delete( $table_name, array( 'comment_id' => $comment_id ), array( '%d' ) );

		// Clear any cached queries.
		self::$cache = array();

		// Update cached rating.
		WPRM_Rating::update_recipe_rating_for_comment( $comment_id );
		WPRM_Comment_Rating::update_cached_rating( $comment_id, 0 );
	}
}

WPRM_Rating_Database::init();
