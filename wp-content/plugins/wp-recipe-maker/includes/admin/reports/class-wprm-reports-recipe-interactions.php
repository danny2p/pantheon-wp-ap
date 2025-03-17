<?php
/**
 * Responsible for handling the recipe interactions report.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the recipe interactions report.
 *
 * @since      9.5.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Reports_Recipe_Interactions {

	/**
	 * Register actions and filters.
	 *
	 * @since	9.5.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_report_recipe_interactions', array( __CLASS__, 'ajax_report_recipe_interactions' ) );
	}

	/**
	 * Add the reports submenu to the WPRM menu.
	 *
	 * @since	9.5.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Recipe Interactions Report', 'wp-recipe-maker' ), __( 'Recipe Interactions Report', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_reports_access' ), 'wprm_report_recipe_interactions', array( __CLASS__, 'report_template' ) );
	}

	/**
	 * Get the template for the report.
	 *
	 * @since    9.5.0
	 */
	public static function report_template() {
		$report_finished = isset( $_GET['wprm_report_finished'] ) ? (bool) sanitize_key( $_GET['wprm_report_finished'] ) : false;

		if ( $report_finished ) {
			$data = WPRM_Reports_Manager::get_data();

			wp_localize_script( 'wprm-admin', 'wprm_reports_data', $data );
		} else {
			WPRM_Reports_Manager::clear_data();

			$args = array(
				'post_type' => WPRM_POST_TYPE,
				'post_status' => 'all',
				'posts_per_page' => -1,
				'fields' => 'ids',
			);
	
			$posts = get_posts( $args );
	
			// Only when debugging.
			if ( WPRM_Reports_Manager::$debugging ) {
				$result = self::report_recipe_interactions( $posts ); // Input var okay.
				WPRM_Debug::log( $result );
				die();
			}
	
			// Handle via AJAX.
			wp_localize_script( 'wprm-admin', 'wprm_reports', array(
				'action' => 'report_recipe_interactions',
				'posts' => $posts,
				'args' => array(),
			));
		}

		require_once( WPRM_DIR . 'templates/admin/menu/reports/recipe-interactions.php' );
	}

	/**
	 * Generate the report through AJAX.
	 *
	 * @since    9.5.0
	 */
	public static function ajax_report_recipe_interactions() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			if ( current_user_can( WPRM_Settings::get( 'features_reports_access' ) ) ) {
				$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

				$posts_left = array();
				$posts_processed = array();

				if ( count( $posts ) > 0 ) {
					$posts_left = $posts;
					$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

					$result = self::report_recipe_interactions( $posts_processed );

					if ( is_wp_error( $result ) ) {
						wp_send_json_error( array(
							'redirect' => add_query_arg( array( 'sub' => 'advanced' ), admin_url( 'admin.php?page=wprm_reports' ) ),
						) );
					}
				}

				wp_send_json_success( array(
					'posts_processed' => $posts_processed,
					'posts_left' => $posts_left,
				) );
			}
		}

		wp_die();
	}

	/**
	 * Generate the report.
	 *
	 * @since	9.5.0
	 * @param	array $posts IDs of posts to search.
	 */
	public static function report_recipe_interactions( $posts ) {
		foreach ( $posts as $recipe_id ) {
			$recipe_id = intval( $recipe_id );
			$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

			if ( $recipe ) {
				$data = array(
					'recipe_id' => $recipe_id,
					'recipe_name' => $recipe->name(),
					'recipe_permalink' => $recipe->permalink( true ),
					'recipe_date' => $recipe->date(),
				);

				// Days between now and recipe creation.
				$days_since_creation = ceil( ( time() - strtotime( $recipe->date() ) ) / ( 60 * 60 * 24 ) );
				$days_since_creation = max( 1, $days_since_creation );
				$data['days_since_creation'] = $days_since_creation;

				// Query analytics database.
				global $wpdb;
				$table_name = WPRM_Analytics_Database::get_table_name();

				$timeframes = array(
					'lifetime' => '1970-01-01 00:00:00',
					'365_days' => date( 'Y-m-d H:i:s', strtotime( '-365 days' ) ),
					'31_days' => date( 'Y-m-d H:i:s', strtotime( '-31 days' ) ),
					'7_days' => date( 'Y-m-d H:i:s', strtotime( '-7 days' ) ),
				);

				foreach ( $timeframes as $timeframe => $start ) {
					$actions = $wpdb->get_results( $wpdb->prepare(
						"SELECT
							count(*) as total,
							count(distinct visitor_id) as total_unique
						FROM `%1s`
						WHERE
							created_at >= %s
							AND recipe_id = %d",
						array(
							$table_name,
							$start,
							$recipe_id
						)
					) );

					// Count totals.
					$data[ 'total_' . $timeframe ] = $actions ? array_sum( array_map( function( $action ) {
						return intval( $action->total );
					}, $actions ) ) : 0;
					$data[ 'unique_' . $timeframe ] = $actions ? array_sum( array_map( function( $action ) {
						return intval( $action->total_unique );
					}, $actions ) ) : 0;

					$actions = $wpdb->get_results( $wpdb->prepare(
						"SELECT
							count(*) as total,
							count(distinct visitor_id) as total_unique
						FROM `%1s`
						WHERE
							created_at >= %s
							AND recipe_id = %d
							AND type = 'print'",
						array(
							$table_name,
							$start,
							$recipe_id
						)
					) );

					// Count totals.
					$data[ 'print_' . $timeframe ] = $actions ? array_sum( array_map( function( $action ) {
						return intval( $action->total );
					}, $actions ) ) : 0;
					$data[ 'print_unique_' . $timeframe ] = $actions ? array_sum( array_map( function( $action ) {
						return intval( $action->total_unique );
					}, $actions ) ) : 0;
					
				}

				// Daily average since creation.
				$data['total_daily'] = $data['total_lifetime'] / $days_since_creation;
				$data['unique_daily'] = $data['unique_lifetime'] / $days_since_creation;
				$data['print_daily'] = $data['print_lifetime'] / $days_since_creation;
				$data['print_unique_daily'] = $data['print_unique_lifetime'] / $days_since_creation;

				// Store in reports database.
				WPRM_Reports_Manager::save_data( $recipe_id, $data );
			}
		}
	}
}

WPRM_Reports_Recipe_Interactions::init();