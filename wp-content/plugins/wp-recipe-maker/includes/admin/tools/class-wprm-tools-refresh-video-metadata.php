<?php
/**
 * Responsible for handling the refresh video metadata tool.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the refresh video metadata tool.
 *
 * @since      6.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Refresh_Video_Metadata {

	/**
	 * Register actions and filters.
	 *
	 * @since	6.0.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_refresh_video_metadata', array( __CLASS__, 'ajax_refresh_video_metadata' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	6.0.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Refresh Video Metadata', 'wp-recipe-maker' ), __( 'Refresh Video Metadata', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_refresh_video_metadata', array( __CLASS__, 'refresh_video_metadata' ) );
	}

	/**
	 * Get the template for the refresh video metadata page.
	 *
	 * @since    6.0.0
	 */
	public static function refresh_video_metadata() {
		$args = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => 'all',
			'posts_per_page' => -1,
			'fields' => 'ids',
		);

		$posts = get_posts( $args );

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::refreshing_video_metadata( $posts ); // Input var okay.
			var_dump( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'refresh_video_metadata',
			'posts' => $posts,
			'args' => array(),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/refresh-video-metadata.php' );
	}

	/**
	 * Refresh video metadata through AJAX.
	 *
	 * @since    6.0.0
	 */
	public static function ajax_refresh_video_metadata() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

			$posts_left = array();
			$posts_processed = array();

			if ( count( $posts ) > 0 ) {
				$posts_left = $posts;
				$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

				$result = self::refreshing_video_metadata( $posts_processed );

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
	 * Refresh the video metadata for these posts.
	 *
	 * @since	6.0.0
	 * @param	array $posts IDs of posts to search.
	 */
	public static function refreshing_video_metadata( $posts ) {
		foreach ( $posts as $post_id ) {
			$recipe = WPRM_Recipe_Manager::get_recipe( $post_id );

			if ( $recipe ) {
				delete_post_meta( $post_id, 'wprm_video_metadata_updated' );
			}
		}
	}
}

WPRM_Tools_Refresh_Video_Metadata::init();