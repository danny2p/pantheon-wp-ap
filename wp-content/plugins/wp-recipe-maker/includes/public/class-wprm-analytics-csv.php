<?php
/**
 * Responsible for the analytics CSV export.
 *
 * @link       http://bootstrapped.ventures
 * @since      9.8.1
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the analytics CSV export.
 *
 * @since      9.8.1
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Analytics_CSV {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.8.1
	 */
	public static function init() {
		add_action( 'wp_ajax_wprm_analytics_export_csv', array( __CLASS__, 'ajax_export_csv' ) );
	}
	
	/**
	 * Ajax callback for exporting analytics to CSV.
	 *
	 * @since    9.8.1
	 */
	public static function ajax_export_csv() {
		// Check capabilities.
		if ( ! current_user_can( WPRM_Settings::get( 'features_manage_access' ) ) ) {
			wp_die( 'You do not have the required capability to perform this action.' );
		}

		// Check nonce.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'wprm_analytics_export_csv' ) ) {
			wp_die( 'Invalid nonce.' );
		}

		// Get transient key from request.
		$export_key = isset( $_GET['export_key'] ) ? sanitize_key( $_GET['export_key'] ) : '';
		
		if ( empty( $export_key ) ) {
			wp_die( 'Invalid export key.' );
		}
		
		// Get IDs from transient.
		$ids = get_transient( $export_key );
		
		if ( false === $ids || ! is_array( $ids ) ) {
			wp_die( 'Export data expired or is invalid. Please try creating a new export.' );
		}
		
		// Delete transient after retrieving it to prevent reuse
		delete_transient( $export_key );

		if ( empty( $ids ) ) {
			wp_die( 'No analytics actions selected.' );
		}

		// Generate CSV content.
		$csv_content = self::generate_csv( $ids );

		if ( ! $csv_content ) {
			wp_die( 'Could not generate CSV data.' );
		}

		// Output CSV headers.
		$filename = 'wprm-analytics-export-' . date( 'Y-m-d' ) . '.csv';
		
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		
		echo $csv_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit();
	}

	/**
	 * Generate CSV content for analytics export.
	 *
	 * @since    9.8.1
	 * @param    array $ids IDs of the analytics actions to export.
	 */
	public static function generate_csv( $ids ) {
		global $wpdb;
		
		if ( empty( $ids ) ) {
			return false;
		}
		
		$args = array(
			'where' => 'id IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')',
			'orderby' => 'created_at',
			'order' => 'DESC',
		);
		
		$query = WPRM_Analytics_Database::get( $args );
		$actions = isset( $query['actions'] ) ? $query['actions'] : array();
		
		if ( empty( $actions ) ) {
			return false;
		}
		
		// Prepare data for CSV.
		$csv_data = array();
		$csv_data[] = array(
			'ID',
			'Action',
			'Action Name',
			'Post ID',
			'Post Title',
			'Recipe ID',
			'Recipe Name',
			'User ID',
			'User',
			'IP',
			'User Agent',
			'Created At',
			'Meta',
		);
		
		$types = WPRM_Analytics::get_types();
		
		foreach ( $actions as $action ) {
			$post_id = $action->post_id ? $action->post_id : 0;
			$post_title = $post_id ? get_the_title( $post_id ) : '';
			
			$recipe_id = $action->recipe_id ? $action->recipe_id : 0;
			$recipe_name = '';
			if ( $recipe_id ) {
				$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );
				if ( $recipe ) {
					$recipe_name = $recipe->name();
				}
			}
			
			$user_id = $action->user_id ? $action->user_id : 0;
			$user_name = '';
			if ( $user_id ) {
				$user = get_userdata( $user_id );
				if ( $user ) {
					$user_name = $user->display_name;
				}
			}
			
			$action_name = isset( $types[$action->type] ) ? $types[$action->type] : '';
			$meta = is_array( $action->meta ) ? wp_json_encode( $action->meta ) : '';
			
			$ip = '';
			$user_agent = '';
			if ( is_array( $action->visitor ) ) {
				$ip = isset( $action->visitor['ip'] ) ? $action->visitor['ip'] : '';
				$user_agent = isset( $action->visitor['user_agent'] ) ? $action->visitor['user_agent'] : '';
			}
			
			$csv_data[] = array(
				$action->id,
				$action->type,
				$action_name,
				$post_id,
				$post_title,
				$recipe_id,
				$recipe_name,
				$user_id,
				$user_name,
				$ip,
				$user_agent,
				$action->created_at,
				$meta,
			);
		}
		
		// Generate CSV.
		$csv = fopen( 'php://temp', 'r+' );
		foreach ( $csv_data as $row ) {
			fputcsv( $csv, $row );
		}
		rewind( $csv );
		$content = stream_get_contents( $csv );
		fclose( $csv );
		
		return $content;
	}
}

WPRM_Analytics_CSV::init();