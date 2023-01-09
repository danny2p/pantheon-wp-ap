<?php
/**
 * Responsible for handling the anonymize ratings tool.
 *
 * @link       http://bootstrapped.ventures
 * @since      8.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the anonymize ratings tool.
 *
 * @since      8.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Anonymize_Ratings {

	/**
	 * Register actions and filters.
	 *
	 * @since	8.4.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_anonymize_ratings', array( __CLASS__, 'ajax_anonymize_ratings' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	8.4.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Anonymize Ratings', 'wp-recipe-maker' ), __( 'Anonymize Ratings', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_anonymize_ratings', array( __CLASS__, 'anonymize_ratings_template' ) );
	}

	/**
	 * Get the template for the anonymize ratings page.
	 *
	 * @since    8.4.0
	 */
	public static function anonymize_ratings_template() {
		// Make sure rating DB is on latest version.
		WPRM_Rating_Database::update_database( '0.0' );

		$all_ratings = WPRM_Rating_Database::get_ratings( array() );

		$ratings = array_map( 'intval', wp_list_pluck( $all_ratings['ratings'], 'id' ) );

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::anonymize_ratings( $ratings ); // Input var okay.
			var_dump( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'anonymize_ratings',
			'posts' => $ratings,
			'args' => array(),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/anonymize-ratings.php' );
	}

	/**
	 * Anonymize ratings through AJAX.
	 *
	 * @since    8.4.0
	 */
	public static function ajax_anonymize_ratings() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

			$posts_left = array();
			$posts_processed = array();

			if ( count( $posts ) > 0 ) {
				$posts_left = $posts;
				$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

				$result = self::anonymize_ratings( $posts_processed );

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
	 * Anonymize ratings.
	 *
	 * @since	8.4.0
	 * @param	array $posts IDs of posts to search.
	 */
	public static function anonymize_ratings( $ratings ) {
		foreach ( $ratings as $rating_id ) {
			$rating = WPRM_Rating_Database::get_rating( array(
				'where' => 'ID = "' . intval( $rating_id ) . '"',
			) );

			if ( $rating ) {
				$ip = $rating->ip;

				if ( $ip && filter_var( $ip, FILTER_VALIDATE_IP ) !== false ) {
					global $wpdb;
					$table_name = WPRM_Rating_Database::get_table_name();

					$wpdb->query( 'UPDATE ' . $table_name . ' SET ip = "" WHERE ID = "' . intval( $rating_id ) . '"' );
				}
			}
		}
	}
}

WPRM_Tools_Anonymize_Ratings::init();
