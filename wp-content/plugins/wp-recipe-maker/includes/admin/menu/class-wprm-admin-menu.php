<?php
/**
 * Responsible for showing the WPRM menu in the WP backend.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/menu
 */

/**
 * Responsible for showing the WPRM menu in the WP backend.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/menu
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Admin_Menu {
	/**
	 * Base64 encoded svg menu icon.
	 *
	 * @since    7.2.0
	 * @access   private
	 * @var      string    $icon    Base64 encoded svg menu icon.
	 */
	private static $icon = 'PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjI0cHgiIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDI0IDI0Ij48ZyA+DQo8cGF0aCBmaWxsPSIjZmZmZmZmIiBkPSJNMTAsMEM5LjQsMCw5LDAuNCw5LDF2NEg3VjFjMC0wLjYtMC40LTEtMS0xUzUsMC40LDUsMXY0SDNWMWMwLTAuNi0wLjQtMS0xLTFTMSwwLjQsMSwxdjhjMCwxLjcsMS4zLDMsMywzDQp2MTBjMCwxLjEsMC45LDIsMiwyczItMC45LDItMlYxMmMxLjcsMCwzLTEuMywzLTNWMUMxMSwwLjQsMTAuNiwwLDEwLDB6Ii8+DQo8cGF0aCBkYXRhLWNvbG9yPSJjb2xvci0yIiBmaWxsPSIjZmZmZmZmIiBkPSJNMTksMGMtMy4zLDAtNiwyLjctNiw2djljMCwwLjYsMC40LDEsMSwxaDJ2NmMwLDEuMSwwLjksMiwyLDJzMi0wLjksMi0yVjENCkMyMCwwLjQsMTkuNiwwLDE5LDB6Ii8+DQo8L2c+PC9zdmc+';

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_taxonomy_menu_page' ) );

		add_filter( 'parent_file', array( __CLASS__, 'set_taxonomy_menu_parent_file' ) );
		add_filter( 'submenu_file', array( __CLASS__, 'set_taxonomy_menu_submenu_file' ) );
	}

	/**
	 * Add WPRM to the wordpress menu.
	 *
	 * @since    1.0.0
	 */
	public static function add_menu_page() {
		add_menu_page( 'WP Recipe Maker', 'WP Recipe Maker', WPRM_Settings::get( 'features_dashboard_access' ), 'wprecipemaker', array( 'WPRM_Dashboard', 'page_template' ), 'data:image/svg+xml;base64,' . self::$icon, '57.9' );
	}

	/**
	 * Add WPRM taxonomies to the wordpress menu.
	 *
	 * @since    7.2.0
	 */
	public static function add_taxonomy_menu_page() {
		if ( WPRM_Settings::get( 'taxonomies_show_default_ui' ) ) {
			$first_taxonomy_showing = false;
			$taxonomies = WPRM_Taxonomies::get_taxonomies_to_register();

			foreach ( $taxonomies as $taxonomy => $options ) {
				if ( $options['archive'] ) {
					$page = 'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=' . WPRM_POST_TYPE;
					add_submenu_page( 'wprm_taxonomies', $options['name'], $options['name'], WPRM_Settings::get( 'features_manage_access' ), $page, null );

					if ( false === $first_taxonomy_showing ) {
						$first_taxonomy_showing = $page;
					}
				}
			}

			if ( false !== $first_taxonomy_showing ) {
				add_menu_page( 'WPRM ' . __( 'Taxonomies', 'wp-recipe-maker' ), __( 'Taxonomies', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_manage_access' ), 'wprm_taxonomies', $first_taxonomy_showing, 'data:image/svg+xml;base64,' . self::$icon, '57.91' );
			}
		}
	}

	/**
	 * Set correct parent for taxonomy menu.
	 *
	 * @since    7.2.0
	 */
	public static function set_taxonomy_menu_parent_file( $parent_file ) {
		if ( WPRM_Settings::get( 'taxonomies_show_default_ui' ) ) {
			$current_screen = get_current_screen();

			if ( WPRM_POST_TYPE === $current_screen->post_type && in_array( $current_screen->base, array( 'edit-tags', 'term' ) ) ) {
				$parent_file = 'wprm_taxonomies';
			}
		}

		return $parent_file;
	}

	/**
	 * Set correct submenu for taxonomy menu.
	 *
	 * @since    7.2.0
	 */
	public static function set_taxonomy_menu_submenu_file( $submenu_file ) {
		if ( WPRM_Settings::get( 'taxonomies_show_default_ui' ) ) {
			$current_screen = get_current_screen();

			if ( WPRM_POST_TYPE === $current_screen->post_type && in_array( $current_screen->base, array( 'edit-tags', 'term' ) ) ) {
				$submenu_file = 'edit-tags.php?taxonomy=' . $current_screen->taxonomy . '&post_type=' . $current_screen->post_type;
			}
		}

		return $submenu_file;
	}
}

WPRM_Admin_Menu::init();
