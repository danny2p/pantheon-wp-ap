<?php
/**
 * Responsible for handling the health check tool.
 *
 * @link       https://bootstrapped.ventures
 * @since      8.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the health check tool.
 *
 * @since      8.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Health_Check {

	/**
	 * Register actions and filters.
	 *
	 * @since	8.0.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_health_check', array( __CLASS__, 'ajax_health_check' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	8.0.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Health Check', 'wp-recipe-maker' ), __( 'Health Check', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_health_check', array( __CLASS__, 'health_check' ) );
	}

	/**
	 * Get the template for the health check page.
	 *
	 * @since    8.0.0
	 */
	public static function health_check() {
		// Post types to search during health check, filterable.
		$post_types = apply_filters( 'wprm_health_check_post_types', array( 'post', 'page', WPRM_POST_TYPE ) );

		$args = array(
			'post_type' => $post_types,
			'post_status' => array( 'publish', 'future', 'draft', 'private' ),
			'posts_per_page' => -1,
			'fields' => 'ids',
		);

		$posts = get_posts( $args );

		// Starting health check.
		WPRM_Health_Check::start();

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::run_health_check( $posts ); // Input var okay.
			var_dump( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'health_check',
			'posts' => $posts,
			'args' => array(),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/health-check.php' );
	}

	/**
	 * Run health check through AJAX.
	 *
	 * @since	8.0.0
	 */
	public static function ajax_health_check() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

			$posts_left = array();
			$posts_processed = array();

			if ( count( $posts ) > 0 ) {
				$posts_left = $posts;
				$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

				$result = self::run_health_check( $posts_processed );

				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array(
						'redirect' => add_query_arg( array( 'sub' => 'advanced' ), admin_url( 'admin.php?page=wprm_tools' ) ),
					) );
				}
			}
			
			// Indicate health check was finished.
			if ( ! $posts_left ) {
				WPRM_Health_Check::stop();
			}

			wp_send_json_success( array(
				'posts_processed' => $posts_processed,
				'posts_left' => $posts_left,
			) );
		}

		wp_die();
	}

	/**
	 * Run health check on posts.
	 *
	 * @since	8.0.0
	 * @param	array $posts IDs of posts to run the health check on.
	 */
	public static function run_health_check( $posts ) {
		foreach ( $posts as $post_id ) {
			WPRM_Health_Check::check( $post_id );
		}
	}
}

WPRM_Tools_Health_Check::init();
