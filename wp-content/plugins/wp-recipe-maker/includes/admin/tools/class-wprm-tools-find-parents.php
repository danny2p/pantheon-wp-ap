<?php
/**
 * Responsible for handling the find parents tool.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the find parents tool.
 *
 * @since      5.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Find_Parents {

	/**
	 * Register actions and filters.
	 *
	 * @since	5.6.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_finding_parents', array( __CLASS__, 'ajax_finding_parents' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	5.6.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Finding Parents', 'wp-recipe-maker' ), __( 'Finding Parents', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_finding_parents', array( __CLASS__, 'finding_parents' ) );
	}

	/**
	 * Get the template for the finding parents page.
	 *
	 * @since    2.1.0
	 */
	public static function finding_parents() {
		$args = array(
			'post_type' => array( 'post', 'page' ),
			'post_status' => array( 'publish', 'future', 'draft', 'private' ),
			'posts_per_page' => -1,
			'fields' => 'ids',
		);

		$posts = get_posts( $args );

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::find_parents( $posts ); // Input var okay.
			var_dump( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'finding_parents',
			'posts' => $posts,
			'args' => array(),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/finding-parents.php' );
	}

	/**
	 * Find parents through AJAX.
	 *
	 * @since    2.1.0
	 */
	public static function ajax_finding_parents() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

			$posts_left = array();
			$posts_processed = array();

			if ( count( $posts ) > 0 ) {
				$posts_left = $posts;
				$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

				$result = self::find_parents( $posts_processed );

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
	 * Find recipes in posts to link parents.
	 *
	 * @since	2.1.0
	 * @param	array $posts IDs of posts to search.
	 */
	public static function find_parents( $posts ) {
		foreach ( $posts as $post_id ) {
			$post = get_post( $post_id );
			$content = WPRM_Shortcode::replace_imported_shortcodes( $post->post_content );

			if ( $content !== $post->post_content ) {
				$update_content = array(
					'ID' => $post_id,
					'post_content' => $content,
				);
				wp_update_post( $update_content );
			} else {
				$recipe_ids = WPRM_Recipe_Manager::get_recipe_ids_from_content( $content );

				foreach ( $recipe_ids as $recipe_id ) {
					$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

					if ( $recipe ) {
						if ( $recipe->parent_post_id() !== $post_id ) {
							update_post_meta( $recipe_id, 'wprm_parent_post_id', $post_id );
						}
					}
				}

				// Fix recipes that have this post as a parent post when they aren't actually inside anymore.
				$args = array(
					'post_type' => WPRM_POST_TYPE,
					'post_status' => 'any',
					'posts_per_page' => -1,
					'meta_query' => array(
						array(
							'key'     => 'wprm_parent_post_id',
							'compare' => '=',
							'value' => $post_id,
						),
					),
					'fields' => 'ids',
				);

				$query = new WP_Query( $args );
				
				if ( $query->have_posts() ) {
					$ids = $query->posts;

					foreach ( $ids as $id ) {
						if ( ! in_array( $id, $recipe_ids ) ) {
							delete_post_meta( $id, 'wprm_parent_post_id' );
						}
					}
				}
			}
		}
	}
}

WPRM_Tools_Find_Parents::init();
