<?php
/**
 * Responsible for the changelog database.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the changelog database.
 *
 * @since      9.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Changelog_Database {

	/**
	 * Current version of the database structure.
	 *
	 * @since    9.2.0
	 * @access   private
	 * @var      mixed $database_version Current version of the database structure.
	 */
	private static $database_version = '1.0';

	/**
	 * Register actions and filters.
	 *
	 * @since    9.2.0
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'check_database_version' ), 1 );
	}

	/**
	 * Check if the correct database version is present.
	 *
	 * @since    9.2.0
	 */
	public static function check_database_version() {
		$current_version = get_option( 'wprm_changelog_db_version', '0.0' );

		if ( version_compare( $current_version, self::$database_version ) < 0 ) {
			self::update_database( $current_version );
		}
	}

	/**
	 * Create or update the rating database.
	 *
	 * @since    9.2.0
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
		object_id bigint(20) unsigned NOT NULL,
		object_meta longtext NULL,
		user_id bigint(20) unsigned NOT NULL DEFAULT '0',
		user_meta longtext NULL,
		created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY (id),
		KEY created_at (created_at)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$result = dbDelta( $sql );

		update_option( 'wprm_changelog_db_version', self::$database_version );
	}

	/**
	 * Get the name of an analytics database table.
	 *
	 * @since    9.2.0
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wprm_changelog' ;
	}

	/**
	 * Sanitize a change.
	 *
	 * @since    9.2.0
	 * @param    mixed $unsanitized_change Change to sanitize.
	 */
	public static function sanitize( $unsanitized_change ) {
		// Date.
		$change['created_at'] = current_time( 'mysql' );

		// Keys.
		$change['type'] = isset( $unsanitized_change['type'] ) ? sanitize_key( $unsanitized_change['type'] ) : '';

		// Integers.
		$change['object_id'] = isset( $unsanitized_change['object_id'] ) ? intval( $unsanitized_change['object_id'] ) : 0;
		$change['user_id'] = isset( $unsanitized_change['user_id'] ) ? intval( $unsanitized_change['user_id'] ) : get_current_user_id();

		// Arrays.
		$change['meta'] = isset( $unsanitized_change['meta'] ) ? $unsanitized_change['meta'] : array();
		$change['meta'] = maybe_serialize( $change['meta'] );

		$change['object_meta'] = isset( $unsanitized_change['object_meta'] ) ? $unsanitized_change['object_meta'] : array();
		$change['object_meta'] = maybe_serialize( $change['object_meta'] );

		$change['user_meta'] = isset( $unsanitized_change['user_meta'] ) ? $unsanitized_change['user_meta'] : array();
		$change['user_meta'] = maybe_serialize( $change['user_meta'] );

		return $change;
	}

	/**
	 * Add a change to the database.
	 *
	 * @since    9.2.0
	 * @param    mixed $unsanitized_change Change to add to the database.
	 */
	public static function add( $unsanitized_change ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$change = self::sanitize( $unsanitized_change );
		return $wpdb->insert( $table_name, $change );
	}

	/**
	 * Purge old changes.
	 *
	 * @since    9.2.0
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
	 * Delete a single or array of changes.
	 *
	 * @since    9.2.0
	 * @param    mixed $id_or_ids Change IDs to delete.
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
	 * @since    9.2.0
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
		$changes = $wpdb->get_results( $query_actions );

		// Unserialize fields.
		foreach ( $changes as $change ) {
			$change->meta = maybe_unserialize( $change->meta );
			$change->object_meta = maybe_unserialize( $change->object_meta );
			$change->user_meta = maybe_unserialize( $change->user_meta );
		}

		return array(
			'total' => intval( $count ),
			'rows' => $changes,
		);
	}

	/**
	 * Count all actions.
	 *
	 * @since    9.2.0
	 */
	public static function count() {
		global $wpdb;
		$table_name = self::get_table_name();

		$query = 'SELECT count(*) FROM ' . $table_name;
		$count = $wpdb->get_var( $query );

		return intval( $count );
	}
}

WPRM_Changelog_Database::init();
