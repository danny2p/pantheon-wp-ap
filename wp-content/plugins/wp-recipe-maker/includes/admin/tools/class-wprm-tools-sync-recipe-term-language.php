<?php
/**
 * Responsible for handling the sync recipe term language tool.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the sync recipe term language tool.
 *
 * @since      10.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Sync_Recipe_Term_Language {
	/**
	 * Register actions and filters.
	 *
	 * @since	10.4.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_sync_recipe_term_language', array( __CLASS__, 'ajax_sync_recipe_term_language' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	10.4.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Sync Recipe Term Language', 'wp-recipe-maker' ), __( 'Sync Recipe Term Language', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_sync_recipe_term_language', array( __CLASS__, 'sync_recipe_term_language' ) );
	}

	/**
	 * Get the template for the sync recipe term language page.
	 *
	 * @since    10.4.0
	 */
	public static function sync_recipe_term_language() {
		$posts = self::get_all_recipe_ids();

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::syncing_recipe_term_language( $posts ); // Input var okay.
			WPRM_Debug::log( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'sync_recipe_term_language',
			'posts' => $posts,
			'args' => array(),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/sync-recipe-term-language.php' );
	}

	/**
	 * Sync recipe term language through AJAX.
	 *
	 * @since    10.4.0
	 */
	public static function ajax_sync_recipe_term_language() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			if ( current_user_can( WPRM_Settings::get( 'features_tools_access' ) ) ) {
				$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

				$posts_left = array();
				$posts_processed = array();

				if ( count( $posts ) > 0 ) {
					$posts_left = $posts;
					$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

					$result = self::syncing_recipe_term_language( $posts_processed );

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
	 * Sync recipe term language for these posts.
	 *
	 * @since	10.4.0
	 * @param	array $posts IDs of posts to sync.
	 */
	public static function syncing_recipe_term_language( $posts ) {
		$entries = array();

		foreach ( $posts as $post_id ) {
			$entries[] = self::sync_recipe_term_language_for_post( $post_id );
		}

		return array(
			'report' => array(
				'toggle' => __( 'Detailed Report', 'wp-recipe-maker' ),
				'title' => __( 'Sync Recipe Term Language Report', 'wp-recipe-maker' ),
				'columns' => array(
					array(
						'key' => 'recipe',
						'label' => __( 'Recipe', 'wp-recipe-maker' ),
					),
					array(
						'key' => 'language',
						'label' => __( 'Language', 'wp-recipe-maker' ),
					),
					array(
						'key' => 'source',
						'label' => __( 'Source', 'wp-recipe-maker' ),
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
	 * Sync recipe term language for a single recipe and return debug info.
	 *
	 * @since	10.8.1
	 * @param	int $post_id Recipe ID.
	 */
	private static function sync_recipe_term_language_for_post( $post_id ) {
		$post_id = intval( $post_id );
		$entry = array(
			'post_id' => $post_id,
			'title' => get_the_title( $post_id ),
			'recipe' => '',
			'language' => false,
			'language_source' => 'missing',
			'source' => __( 'Missing', 'wp-recipe-maker' ),
			'status' => 'skipped_no_language',
			'details' => __( 'No recipe or parent language detected.', 'wp-recipe-maker' ),
			'changed_taxonomies' => array(),
		);

		$entry['recipe'] = $entry['title'] ? $entry['title'] . ' (#' . $post_id . ')' : '#' . $post_id;

		if ( ! $post_id || WPRM_POST_TYPE !== get_post_type( $post_id ) ) {
			$entry['status'] = __( 'Skipped: invalid recipe', 'wp-recipe-maker' );
			$entry['details'] = __( 'Recipe could not be loaded.', 'wp-recipe-maker' );
			return $entry;
		}

		$language = WPRM_Compatibility::get_language_for( $post_id );
		$language_source = 'recipe';

		if ( ! $language ) {
			$parent_post_id = get_post_meta( $post_id, 'wprm_parent_post_id', true );
			$parent_post_id = $parent_post_id ? intval( $parent_post_id ) : 0;

			if ( $parent_post_id ) {
				$language = WPRM_Compatibility::get_language_for( $parent_post_id );
				$language_source = $language ? 'parent' : 'missing';
				$entry['parent_post_id'] = $parent_post_id;
			}
		}

		$entry['language'] = $language;
		$entry['language_source'] = $language_source;
		$entry['source'] = self::get_language_source_label( $language_source );

		if ( ! $language ) {
			return $entry;
		}

		$before = self::get_sync_taxonomy_term_ids( $post_id );
		WPRM_Recipe_Saver::sync_recipe_term_languages( $post_id, $language );
		clean_object_term_cache( $post_id, WPRM_POST_TYPE );
		$after = self::get_sync_taxonomy_term_ids( $post_id );

		$changed_taxonomies = array();

		foreach ( $before as $taxonomy => $before_term_ids ) {
			$after_term_ids = isset( $after[ $taxonomy ] ) ? $after[ $taxonomy ] : array();

			if ( $before_term_ids !== $after_term_ids ) {
				$changed_taxonomies[] = array(
					'taxonomy' => $taxonomy,
					'before' => $before_term_ids,
					'after' => $after_term_ids,
				);
			}
		}

		$entry['changed_taxonomies'] = $changed_taxonomies;
		$entry['status'] = count( $changed_taxonomies ) > 0 ? __( 'Updated', 'wp-recipe-maker' ) : __( 'No changes', 'wp-recipe-maker' );
		$entry['details'] = count( $changed_taxonomies ) > 0 ? self::format_changed_taxonomies_details( $changed_taxonomies ) : self::format_no_changes_details( $before, $language );

		return $entry;
	}

	/**
	 * Get assigned term IDs for the recipe taxonomies that are synced.
	 *
	 * @since	10.8.1
	 * @param	int $post_id Recipe ID.
	 */
	private static function get_sync_taxonomy_term_ids( $post_id ) {
		$term_ids_per_taxonomy = array();
		$taxonomies = WPRM_Taxonomies::get_taxonomies();
		global $wpdb;

		foreach ( array_keys( $taxonomies ) as $taxonomy ) {
			$query = $wpdb->prepare(
				"SELECT DISTINCT tt.term_id
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE tr.object_id = %d AND tt.taxonomy = %s
				ORDER BY tt.term_id ASC",
				$post_id,
				$taxonomy
			);
			$term_ids = $wpdb->get_col( $query );
			$term_ids = is_array( $term_ids ) ? array_values( array_map( 'intval', $term_ids ) ) : array();
			sort( $term_ids );
			$term_ids_per_taxonomy[ $taxonomy ] = $term_ids;
		}

		return $term_ids_per_taxonomy;
	}

	/**
	 * Get a label for the detected language source.
	 *
	 * @since	10.8.1
	 * @param	string $language_source Raw language source.
	 */
	private static function get_language_source_label( $language_source ) {
		switch ( $language_source ) {
			case 'recipe':
				return __( 'Recipe', 'wp-recipe-maker' );
			case 'parent':
				return __( 'Parent', 'wp-recipe-maker' );
			default:
				return __( 'Missing', 'wp-recipe-maker' );
		}
	}

	/**
	 * Format changed taxonomies for a readable report.
	 *
	 * @since	10.8.1
	 * @param	array $changed_taxonomies Changed taxonomy data.
	 */
	private static function format_changed_taxonomies_details( $changed_taxonomies ) {
		if ( ! is_array( $changed_taxonomies ) || empty( $changed_taxonomies ) ) {
			return __( 'No taxonomy changes.', 'wp-recipe-maker' );
		}

		$details = array();

		foreach ( $changed_taxonomies as $change ) {
			if ( ! isset( $change['taxonomy'] ) ) {
				continue;
			}

			$before = isset( $change['before'] ) && is_array( $change['before'] ) && ! empty( $change['before'] ) ? implode( ', ', $change['before'] ) : '-';
			$after = isset( $change['after'] ) && is_array( $change['after'] ) && ! empty( $change['after'] ) ? implode( ', ', $change['after'] ) : '-';

			$details[] = $change['taxonomy'] . ': ' . $before . ' -> ' . $after;
		}

		return implode( '; ', $details );
	}

	/**
	 * Format "no changes" details for a readable report.
	 *
	 * @since	10.8.1
	 * @param	array  $term_ids_per_taxonomy Stored term IDs per taxonomy.
	 * @param	string $language              Recipe language.
	 */
	private static function format_no_changes_details( $term_ids_per_taxonomy, $language ) {
		if ( ! is_array( $term_ids_per_taxonomy ) || empty( $term_ids_per_taxonomy ) ) {
			return __( 'No synced recipe taxonomy terms assigned.', 'wp-recipe-maker' );
		}

		$has_terms = false;

		foreach ( $term_ids_per_taxonomy as $term_ids ) {
			if ( ! empty( $term_ids ) ) {
				$has_terms = true;
				break;
			}
		}

		if ( ! $has_terms ) {
			return __( 'No synced recipe taxonomy terms assigned.', 'wp-recipe-maker' );
		}

		return sprintf(
			/* translators: %s: language code */
			__( 'Stored recipe taxonomy relationships already matched %s.', 'wp-recipe-maker' ),
			$language
		);
	}
}

WPRM_Tools_Sync_Recipe_Term_Language::init();
