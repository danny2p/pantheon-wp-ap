<?php
/**
 * Responsible for handling the find ingredient units tool.
 *
 * @link       http://bootstrapped.ventures
 * @since      7.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the find ingredient units tool.
 *
 * @since      7.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Find_Ingredient_Units {

	/**
	 * Register actions and filters.
	 *
	 * @since	7.6.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_find_ingredient_units', array( __CLASS__, 'ajax_find_ingredient_units' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	7.6.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Find Ingredient Units', 'wp-recipe-maker' ), __( 'Find Ingredient Units', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_find_ingredient_units', array( __CLASS__, 'find_ingredient_units' ) );
	}

	/**
	 * Get the template for the Find Ingredient Units page.
	 *
	 * @since    7.6.0
	 */
	public static function find_ingredient_units() {
		$args = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => 'all',
			'posts_per_page' => -1,
			'fields' => 'ids',
		);

		$posts = get_posts( $args );

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::finding_ingredient_units( $posts ); // Input var okay.
			var_dump( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'find_ingredient_units',
			'posts' => $posts,
			'args' => array(),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/find-ingredient-units.php' );
	}

	/**
	 * Find Ingredient Units through AJAX.
	 *
	 * @since    7.6.0
	 */
	public static function ajax_find_ingredient_units() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

			$posts_left = array();
			$posts_processed = array();

			if ( count( $posts ) > 0 ) {
				$posts_left = $posts;
				$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

				$result = self::finding_ingredient_units( $posts_processed );

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
	 * @since	7.6.0
	 * @param	array $posts IDs of posts to search.
	 */
	public static function finding_ingredient_units( $posts ) {
		foreach ( $posts as $post_id ) {
			$recipe = WPRM_Recipe_Manager::get_recipe( $post_id );

			if ( $recipe ) {
				$sanitized = WPRM_Recipe_Sanitizer::sanitize(
					array(
						'ingredients' => $recipe->ingredients(),
					)
				);
				WPRM_Recipe_Saver::update_recipe( $recipe->id(), $sanitized );
			}
		}
	}
}

WPRM_Tools_Find_Ingredient_Units::init();