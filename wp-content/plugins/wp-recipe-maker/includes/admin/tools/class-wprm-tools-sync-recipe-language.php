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
		$posts = self::get_all_recipe_ids();

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
					'report' => isset( $result['report'] ) ? $result['report'] : false,
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
		$entries = array();

		foreach ( $posts as $post_id ) {
			$entries[] = self::sync_recipe_language_for_post( $post_id );
		}

		return array(
			'report' => array(
				'toggle' => __( 'Detailed Report', 'wp-recipe-maker' ),
				'title' => __( 'Sync Recipe Language Report', 'wp-recipe-maker' ),
				'columns' => array(
					array(
						'key' => 'recipe',
						'label' => __( 'Recipe', 'wp-recipe-maker' ),
					),
					array(
						'key' => 'parent_post',
						'label' => __( 'Parent Post', 'wp-recipe-maker' ),
					),
					array(
						'key' => 'language',
						'label' => __( 'Language', 'wp-recipe-maker' ),
					),
					array(
						'key' => 'status',
						'label' => __( 'Status', 'wp-recipe-maker' ),
					),
					array(
						'key' => 'details',
						'label' => __( 'Details', 'wp-recipe-maker' ),
					),
				),
				'entries' => $entries,
				'json_label' => __( 'Full Report JSON', 'wp-recipe-maker' ),
			),
		);
	}

	/**
	 * Get all recipe IDs across all languages.
	 *
	 * @since	10.8.1
	 */
	private static function get_all_recipe_ids() {
		$request_post_id = isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0;

		if ( $request_post_id && WPRM_POST_TYPE === get_post_type( $request_post_id ) ) {
			return array( $request_post_id );
		}

		$args = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'no_found_rows' => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters' => false,
		);

		global $wpml_query_filter;
		if ( $wpml_query_filter ) {
			remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
			remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );
		}

		$query = new WP_Query( $args );

		if ( $wpml_query_filter ) {
			add_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
			add_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );
		}

		return is_array( $query->posts ) ? $query->posts : array();
	}

	/**
	 * Sync recipe language for a single recipe and return debug info.
	 *
	 * @since	10.8.1
	 * @param	int $post_id Recipe ID.
	 */
	private static function sync_recipe_language_for_post( $post_id ) {
		$post_id = intval( $post_id );
		$entry = array(
			'post_id' => $post_id,
			'title' => get_the_title( $post_id ),
			'recipe' => '',
			'parent_post_id' => 0,
			'parent_post' => '-',
			'language' => false,
			'status' => __( 'Skipped: no parent language', 'wp-recipe-maker' ),
			'details' => __( 'No parent post language detected.', 'wp-recipe-maker' ),
		);

		$entry['recipe'] = $entry['title'] ? $entry['title'] . ' (#' . $post_id . ')' : '#' . $post_id;

		if ( ! $post_id || WPRM_POST_TYPE !== get_post_type( $post_id ) ) {
			$entry['status'] = __( 'Skipped: invalid recipe', 'wp-recipe-maker' );
			$entry['details'] = __( 'Recipe could not be loaded.', 'wp-recipe-maker' );
			return $entry;
		}

		$recipe = WPRM_Recipe_Manager::get_recipe( $post_id );

		if ( ! $recipe ) {
			$entry['status'] = __( 'Skipped: missing recipe', 'wp-recipe-maker' );
			$entry['details'] = __( 'Recipe object could not be loaded.', 'wp-recipe-maker' );
			return $entry;
		}

		$parent_post_id = $recipe->parent_post_id();
		$entry['parent_post_id'] = $parent_post_id ? intval( $parent_post_id ) : 0;
		$entry['parent_post'] = $entry['parent_post_id'] ? get_the_title( $entry['parent_post_id'] ) . ' (#' . $entry['parent_post_id'] . ')' : '-';

		if ( ! $entry['parent_post_id'] ) {
			$entry['status'] = __( 'Skipped: no parent post', 'wp-recipe-maker' );
			$entry['details'] = __( 'Recipe has no parent post assigned.', 'wp-recipe-maker' );
			return $entry;
		}

		$language_before = WPRM_Compatibility::get_language_for( $post_id );
		$parent_language = WPRM_Compatibility::get_language_for( $entry['parent_post_id'] );

		$entry['language'] = $parent_language;

		if ( ! $parent_language ) {
			return $entry;
		}

		WPRM_Compatibility::set_language_for( $post_id, $parent_language );
		$language_after = WPRM_Compatibility::get_language_for( $post_id );

		if ( $language_before !== $language_after ) {
			$entry['status'] = __( 'Updated', 'wp-recipe-maker' );
			$entry['details'] = sprintf(
				/* translators: 1: previous language 2: new language */
				__( 'Language changed from %1$s to %2$s.', 'wp-recipe-maker' ),
				$language_before ? $language_before : '-',
				$language_after ? $language_after : '-'
			);
		} else {
			$entry['status'] = __( 'No changes', 'wp-recipe-maker' );
			$entry['details'] = __( 'Recipe language already matched the parent post.', 'wp-recipe-maker' );
		}

		$entry['language'] = $language_after;

		return $entry;
	}
}

WPRM_Tools_Sync_Recipe_Language::init();
