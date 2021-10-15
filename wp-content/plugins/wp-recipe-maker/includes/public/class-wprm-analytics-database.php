<?php
/**
 * Responsible for the analytics database.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the analytics database.
 *
 * @since      6.5.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Analytics_Database {
	/**
	 * Current version of the analytics database structure.
	 *
	 * @since    6.5.0
	 * @access   private
	 * @var      mixed $database_version Current version of the analytics database structure.
	 */
	private static $database_version = '1.0';

	/**
	 * Register actions and filters.
	 *
	 * @since    6.5.0
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'check_database_version' ), 1 );
	}

	/**
	 * Check if the correct database version is present.
	 *
	 * @since    6.5.0
	 */
	public static function check_database_version() {
		$current_version = get_option( 'wprm_analytics_db_version', '0.0' );

		if ( version_compare( $current_version, self::$database_version ) < 0 ) {
			self::update_database( $current_version );
		}
	}

	/**
	 * Create or update the rating database.
	 *
	 * @since    6.5.0
	 * @param    mixed $from Database version to update from.
	 */
	public static function update_database( $from ) {
		global $wpdb;

		$table_name = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		type varchar(64) NOT NULL,
		meta longtext NULL,
		post_id bigint(20) unsigned NOT NULL,
		recipe_id bigint(20) unsigned NOT NULL,
		user_id bigint(20) unsigned NOT NULL DEFAULT '0',
		visitor_id varchar(64) NOT NULL,
		visitor longtext NULL,
		created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY (id),
		KEY created_at (created_at)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$result = dbDelta( $sql );

		update_option( 'wprm_analytics_db_version', self::$database_version );
	}

	/**
	 * Get the name of an analytics database table.
	 *
	 * @since    6.5.0
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wprm_analytics' ;
	}

	/**
	 * Sanitize an action.
	 *
	 * @since    6.5.0
	 * @param    mixed $unsanitized_action Action to sanitize.
	 */
	public static function sanitize( $unsanitized_action ) {
		// Date.
		$action['created_at'] = current_time( 'mysql' );

		// Keys.
		$action['type'] = isset( $unsanitized_action['type'] ) ? sanitize_key( $unsanitized_action['type'] ) : '';
		$action['visitor_id'] = isset( $unsanitized_action['visitor_id'] ) ? sanitize_key( $unsanitized_action['visitor_id'] ) : '';

		// Integers.
		$action['post_id'] = isset( $unsanitized_action['post_id'] ) ? intval( $unsanitized_action['post_id'] ) : 0;
		$action['recipe_id'] = isset( $unsanitized_action['recipe_id'] ) ? intval( $unsanitized_action['recipe_id'] ) : 0;
		$action['user_id'] = isset( $unsanitized_action['user_id'] ) ? intval( $unsanitized_action['user_id'] ) : get_current_user_id();

		// Arrays.
		$action['meta'] = isset( $unsanitized_action['meta'] ) ? $unsanitized_action['meta'] : array();
		$action['meta'] = maybe_serialize( $action['meta'] );

		$action['visitor'] = isset( $unsanitized_action['visitor'] ) ? $unsanitized_action['visitor'] : array();
		$action['visitor'] = maybe_serialize( $action['visitor'] );

		return $action;
	}

	/**
	 * Add an action to the database.
	 *
	 * @since    6.5.0
	 * @param    mixed $unsanitized_action Action to add to the database.
	 */
	public static function add( $unsanitized_action ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$action = self::sanitize( $unsanitized_action );
		return $wpdb->insert( $table_name, $action );
	}

	/**
	 * Purge old actions.
	 *
	 * @since    7.4.0
	 * @param    int $days Number of days to keep.
	 */
	public static function purge( $days ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$days = intval( $days );
		$datetime = new DateTime( $days . ' days ago' );
		$created_at = $datetime->format( 'Y-m-d H:i:s' );

		$wpdb->query( 'DELETE FROM ' . $table_name . ' WHERE created_at < "' . $created_at . '"' );

		return true;
	}

	/**
	 * Delete a single or array of actions.
	 *
	 * @since    6.5.0
	 * @param    mixed $id_or_ids Action IDs to delete.
	 */
	public static function delete( $id_or_ids ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$ids = is_array( $id_or_ids ) ? $id_or_ids : array( $id_or_ids );
		$ids = implode( ',', array_map( 'intval', $ids ) );

		$wpdb->query( 'DELETE FROM ' . $table_name . ' WHERE ID IN (' . $ids . ')' );

		return true;
	}

	/**
	 * Query actions.
	 *
	 * @since    6.5.0
	 * @param    mixed $args Arguments for the query.
	 */
	public static function get( $args ) {
		global $wpdb;
		$table_name = self::get_table_name();

		// Sanitize arguments.
		$order = isset( $args['order'] ) ? strtoupper( $args['order'] ) : '';
		$order = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';

		$orderby = isset( $args['orderby'] ) ? sanitize_key( $args['orderby'] ) : 'created_at';

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
		$query_actions = 'SELECT * FROM ' . $table_name . $query_where . $query_order . $query_limit;
		$actions = $wpdb->get_results( $query_actions );

		// Unserialize fields.
		foreach ( $actions as $action ) {
			$action->meta = maybe_unserialize( $action->meta );
			$action->visitor = maybe_unserialize( $action->visitor );
		}

		return array(
			'total' => intval( $count ),
			'actions' => $actions,
		);
	}

	/**
	 * Count all actions.
	 *
	 * @since    6.5.0
	 */
	public static function count() {
		global $wpdb;
		$table_name = self::get_table_name();

		$query = 'SELECT count(*) FROM ' . $table_name;
		$count = $wpdb->get_var( $query );

		return intval( $count );
	}

	/**
	 * Get actions data for a specific day.
	 *
	 * @since    7.4.0
	 */
	public static function get_aggregated_actions_for( $date ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$datetime = new DateTime( $date );
		if ( ! $datetime ) return false;

		$start = $datetime->format( 'Y-m-d 00:00:00' );
		$end = $datetime->format( 'Y-m-d 23:59:59' );

		$sql = "SELECT
					type,
					recipe_id,
					count(*) as total,
					count(distinct visitor_id) as total_unique
				FROM
					$table_name
				WHERE
					created_at >= '$start'
					AND created_at <= '$end'
				GROUP BY
					type,
					recipe_id";
		$actions = $wpdb->get_results( $sql );

		return $actions;
	}
}

WPRM_Analytics_Database::init();
