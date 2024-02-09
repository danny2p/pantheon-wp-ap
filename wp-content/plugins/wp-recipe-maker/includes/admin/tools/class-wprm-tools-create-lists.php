<?php
/**
 * Responsible for handling the import MV Create Lists tool.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the import MV Create Lists tool.
 *
 * @since      9.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Create_Lists {

	/**
	 * Register actions and filters.
	 *
	 * @since	9.0.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_create_lists', array( __CLASS__, 'ajax_create_lists' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	9.0.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Creating Lists', 'wp-recipe-maker' ), __( 'Creating Lists', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_create_lists', array( __CLASS__, 'create_lists' ) );
	}

	/**
	 * Get the template for the create lists page.
	 *
	 * @since	9.0.0
	 */
	public static function create_lists() {
		global $wpdb;
		$table = $wpdb->prefix . 'mv_creations';

		$mv_lists = array();
		if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$mv_lists = $wpdb->get_results( 'SELECT id FROM ' . $table . ' WHERE type IN ("list")' );
		}

		$mv_lists = array_map( function ( $list ) { return intval( $list->id ); }, $mv_lists );

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::import_lists( $mv_lists ); // Input var okay.
			var_dump( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'create_lists',
			'posts' => $mv_lists,
			'args' => array(),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/create-lists.php' );
	}

	/**
	 * Import WP Ultimate Recipe ingredient nutrition through AJAX.
	 *
	 * @since    2.1.0
	 */
	public static function ajax_create_lists() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			if ( current_user_can( WPRM_Settings::get( 'features_tools_access' ) ) ) {
				$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

				$posts_left = array();
				$posts_processed = array();

				if ( count( $posts ) > 0 ) {
					$posts_left = $posts;
					$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 1 ) );

					$result = self::import_lists( $posts_processed );

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
	 * Import MV Create Lists.
	 *
	 * @since	9.0.0
	 * @param	array $mv_lists MV Create Lists to import.
	 */
	public static function import_lists( $mv_lists ) {
		foreach ( $mv_lists as $mv_list_id ) {
			$mv_list = false;

			// Get MV List from their database table.
			global $wpdb;
			$table = $wpdb->prefix . 'mv_creations';

			if ( $table === $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) {
				$rows = $wpdb->get_results( 'SELECT * FROM ' . $table . ' WHERE id=' . intval( $mv_list_id ) );

				if ( is_array( $rows ) && 1 === count( $rows ) ) {
					$mv_list = (array) $rows[0];
				}
			}

			// Get MV list data.
			$mv_data = json_decode( $mv_list['published'], true );

			if ( ! $mv_data ) {
				continue;
			}

			// Create new WPRM list.
			$wprm_list = array();

			$wprm_list['imported_from'] = 'mv_create' . '-' . $mv_list_id;

			$wprm_list['name'] = str_replace( '</p><p>', ' ', html_entity_decode( $mv_data['title'] ) );
			$wprm_list['note'] = str_replace( '</p><p>', ' ', html_entity_decode( $mv_data['description'] ) );

			$wprm_list['metadata_output'] = (bool) $mv_data['schema_display'];
			if ( $wprm_list['metadata_output'] ) {
				$wprm_list['metadata_name'] = $wprm_list['name'];
				$wprm_list['metadata_description'] = $wprm_list['note'];
			}

			// MV Create settings.
			$open_external_in_new_tab = true;
			$open_internal_in_new_tab = false;

			if ( class_exists( '\Mediavine\Settings' ) ) {
				$open_external_in_new_tab = \Mediavine\Settings::get_setting( 'mv_create_external_link_tab' );
				$open_internal_in_new_tab = \Mediavine\Settings::get_setting( 'mv_create_internal_link_tab' );
			}

			// List items.
			$wprm_items = array();

			foreach( $mv_data['list_items'] as $mv_item ) {
				if ( 'text' === $mv_item['content_type'] ) {
					$text = '';

					$mv_title = trim( $mv_item['title'] );
					if ( $mv_title ) {
						$text .= '<p><strong>' . $mv_title . '</strong></p>';
					}

					$description = trim( $mv_item['description'] );
					if ( $mv_title ) {
						$text .= $description;
					}

					if ( $text ) {
						$wprm_items[] = array(
							'type' => 'text',
							'data' => array(
								'text' => $text,
							),
						);
					}
				}

				if ( 'card' === $mv_item['content_type'] ) {
					$relation_id = intval( $mv_item['relation_id'] );

					$mv_recipe = false;
					$wprm_recipe_id = false;

					// Find related MV recipe card.
					if ( $relation_id ) {
						$rows = $wpdb->get_results( 'SELECT * FROM ' . $table . ' WHERE id=' . intval( $relation_id ) );

						if ( is_array( $rows ) && 1 === count( $rows ) ) {
							$mv_recipe = (array) $rows[0];
						}
					}

					// Find WPRM recipe card this was imported to.
					if ( $mv_recipe ) {
						$object_id = intval( $mv_recipe['object_id'] );

						if ( $object_id && WPRM_POST_TYPE === get_post_type( $object_id ) ) {
							$wprm_recipe_id = $object_id;
						}
					}

					// If a WPRM recipe card was found, add it to the list.
					if ( $wprm_recipe_id ) {
						$wprm_items[] = array(
							'type' => 'roundup',
							'data' => array(
								'type' => 'internal',
								'id' => $wprm_recipe_id,
								'link' => '',
								'nofollow' => isset( $mv_item['nofollow'] ) && $mv_item['nofollow'] ? '1' : '',
								'new_tab' => $open_internal_in_new_tab ? '1' : '',
								'image' => '',
								'image_url' => '',
								'credit' => '',
								'name' => isset( $mv_item['title'] ) && $mv_item['title'] ? $mv_item['title'] : '',
								'summary' => isset( $mv_item['description'] ) && $mv_item['description'] ? $mv_item['description'] : '',
								'button' => isset( $mv_item['link_text'] ) && $mv_item['link_text'] ? $mv_item['link_text'] : '',
							),
						);
					}
				}

				if ( 'post' === $mv_item['content_type'] ) {
					$wprm_items[] = array(
						'type' => 'roundup',
						'data' => array(
							'type' => 'post',
							'id' => isset( $mv_item['relation_id'] ) ? intval( $mv_item['relation_id'] ) : 0,
							'link' => '',
							'nofollow' => isset( $mv_item['nofollow'] ) && $mv_item['nofollow'] ? '1' : '',
							'new_tab' => $open_internal_in_new_tab ? '1' : '',
							'image' => '',
							'image_url' => '',
							'credit' => '',
							'name' => isset( $mv_item['title'] ) && $mv_item['title'] ? $mv_item['title'] : '',
							'summary' => isset( $mv_item['description'] ) && $mv_item['description'] ? $mv_item['description'] : '',
							'button' => isset( $mv_item['link_text'] ) && $mv_item['link_text'] ? $mv_item['link_text'] : '',
						),
					);
				}

				if ( 'external' === $mv_item['content_type'] ) {
					$wprm_items[] = array(
						'type' => 'roundup',
						'data' => array(
							'type' => 'external',
							'id' => 0,
							'link' => isset( $mv_item['url'] ) && $mv_item['url'] ? $mv_item['url'] : '',
							'nofollow' => isset( $mv_item['nofollow'] ) && $mv_item['nofollow'] ? '1' : '',
							'new_tab' => $open_external_in_new_tab ? '1' : '',
							'image' => isset( $mv_item['thumbnail_id'] ) ? intval( $mv_item['thumbnail_id'] ) : 0,
							'image_url' => isset( $mv_item['thumbnail_uri'] ) && $mv_item['thumbnail_uri'] ? $mv_item['thumbnail_uri'] : '',
							'credit' => isset( $mv_item['thumbnail_credit'] ) && $mv_item['thumbnail_credit'] ? $mv_item['thumbnail_credit'] : '',
							'name' => isset( $mv_item['title'] ) && $mv_item['title'] ? $mv_item['title'] : '',
							'summary' => isset( $mv_item['description'] ) && $mv_item['description'] ? $mv_item['description'] : '',
							'button' => isset( $mv_item['link_text'] ) && $mv_item['link_text'] ? $mv_item['link_text'] : '',
						),
					);
				}
			}

			$wprm_list['items'] = $wprm_items;

			

			// Sanitize list.
			$wprm_list = WPRM_List_Saver::sanitize( $wprm_list );

			// Check if this list was imported before.
			$lists = array();
			$args = array(
				'post_type' => WPRM_LIST_POST_TYPE,
				'post_status' => 'any',
				'posts_per_page' => -1,
				'offset' => 0,
				'suppress_filters' => true,
				'meta_query' => array(
					array(
						'key' => 'wprm_imported_from',
						'value' => $wprm_list['imported_from'],
						'compare' => '=',
					),
				),
				'fields' => 'ids',
			);

			$query = new WP_Query( $args );

			// Update existing or create new list.
			if ( $query->have_posts() ) {
				$ids = $query->posts;

				WPRM_List_Saver::update_list( $ids[0], $wprm_list );
			} else {
				WPRM_List_Saver::create_list( $wprm_list );
			}
		}
	}
}

WPRM_Tools_Create_Lists::init();
