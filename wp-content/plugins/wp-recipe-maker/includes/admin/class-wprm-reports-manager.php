<?php
/**
 * Responsible for handling the WPRM reports.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the WPRM reports.
 *
 * @since      9.5.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Reports_Manager {

	/**
	 * Only to be enabled when debugging the report generation.
	 *
	 * @since    9.5.0
	 * @access   private
	 * @var      boolean    $debugging    Whether or not we are debugging the report generation.
	 */
	public static $debugging = false;

	/**
	 * Register actions and filters.
	 *
	 * @since	9.5.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 13 );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	9.5.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( 'wprecipemaker', __( 'WPRM Reports', 'wp-recipe-maker' ), __( 'Reports', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_reports_access' ), 'wprm_reports', array( __CLASS__, 'reports_page_template' ) );
	}

	/**
	 * Get the template for the tools page.
	 *
	 * @since	9.5.0
	 */
	public static function reports_page_template() {
		// Maybe update database table.
		self::update_database();

		require_once( WPRM_DIR . 'templates/admin/reports.php' );
	}

	/**
	 * Reset the reports database.
	 *
	 * @since	9.5.0
	 */
	public static function update_database() {
		global $wpdb;

		$table_name = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		// Create/update if needed.
		$sql = "CREATE TABLE $table_name (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,		
		object_id bigint(20) unsigned NOT NULL,
		meta longtext NULL,
		PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$result = dbDelta( $sql );
	}

	/**
	 * Get the name of the database table.
	 *
	 * @since	9.5.0
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wprm_reports' ;
	}

	/**
	 * Clear data in the reports database.
	 *
	 * @since	9.5.0
	 */
	public static function clear_data() {
		global $wpdb;
		$table_name = self::get_table_name();

		$sql = "TRUNCATE TABLE $table_name";
		return $wpdb->query($sql);
	}

	/**
	 * Save data in the reports database.
	 *
	 * @since	9.5.0
	 */
	public static function save_data( $id, $meta ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$data = array(
			'object_id' => intval( $id ),
			'meta' => maybe_serialize( $meta ),
		);
		
		return $wpdb->insert( $table_name, $data );
	}

	/**
	 * Get all data from the reports database.
	 *
	 * @since	9.5.0
	 */
	public static function get_data() {
		global $wpdb;
		$table_name = self::get_table_name();

		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT object_id, meta FROM `%1s`", $table_name ) );

		// Construct data array.
		$data = array();
		foreach ( $rows as $row ) {
			$data[ $row->object_id ] = maybe_unserialize( $row->meta );
		}

		return $data;
	}
}

WPRM_Reports_Manager::init();
