<?php
/**
 * Responsible for handling the sync recipe language tool.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.1.1
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the sync recipe language tool.
 *
 * @since      10.1.1
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Sync_Recipe_Language {

	/**
	 * Register actions and filters.
	 *
	 * @since	10.1.1
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_sync_recipe_language', array( __CLASS__, 'ajax_sync_recipe_language' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	10.1.1
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Sync Recipe Language', 'wp-recipe-maker' ), __( 'Sync Recipe Language', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_sync_recipe_language', array( __CLASS__, 'sync_recipe_language' ) );
	}

	/**
	 * Get the template for the sync recipe language page.
	 *
	 * @since    10.1.1
	 */
	public static function sync_recipe_language() {
		$args = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => 'all',
			'posts_per_page' => -1,
			'fields' => 'ids',
		);

		$posts = get_posts( $args );

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::syncing_recipe_language( $posts ); // Input var okay.
			WPRM_Debug::log( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'sync_recipe_language',
			'posts' => $posts,
			'args' => array(),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/sync-recipe-language.php' );
	}

	/**
	 * Sync recipe language through AJAX.
	 *
	 * @since    10.1.1
	 */
	public static function ajax_sync_recipe_language() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			if ( current_user_can( WPRM_Settings::get( 'features_tools_access' ) ) ) {
				$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

				$posts_left = array();
				$posts_processed = array();

				if ( count( $posts ) > 0 ) {
					$posts_left = $posts;
					$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

					$result = self::syncing_recipe_language( $posts_processed );

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
		}

		wp_die();
	}

	/**
	 * Sync the recipe language for these posts.
	 *
	 * @since	10.1.1
	 * @param	array $posts IDs of posts to sync.
	 */
	public static function syncing_recipe_language( $posts ) {
		foreach ( $posts as $post_id ) {
			$recipe = WPRM_Recipe_Manager::get_recipe( $post_id );

			if ( $recipe ) {
				$parent_post_id = $recipe->parent_post_id();
				
				if ( $parent_post_id ) {
					$parent_language = WPRM_Compatibility::get_language_for( $parent_post_id );
					
					if ( $parent_language ) {
						WPRM_Compatibility::set_language_for( $post_id, $parent_language );
					}
				}
			}
		}
	}
}

WPRM_Tools_Sync_Recipe_Language::init();
