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
	private static $icon = 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyBpZD0iTGF5ZXJfMiIgZGF0YS1uYW1lPSJMYXllciAyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyODEuMSIgaGVpZ2h0PSIyODcuOSIgdmlld0JveD0iMCAwIDI4MS4xIDI4Ny45Ij4KICA8ZyBpZD0iTGF5ZXJfMS0yIiBkYXRhLW5hbWU9IkxheWVyIDEiPgogICAgPGc+CiAgICAgIDxwYXRoIGQ9Im0yNzMuOSwyMDAuM2M1LDEuNiw3LjQsNy41LDQuOCwxMi4xLTM0LjIsNjAuNC0xMDcuMiw5MC40LTE3NS43LDY4LjFDMzQuNCwyNTguMy02LjksMTkxLjEsMSwxMjIuMWMuNi01LjMsNS45LTguNiwxMS03bDI2Miw4NS4yWiIgZmlsbD0iI2ZmZiIvPgogICAgICA8Y2lyY2xlIGN4PSIyMzkuNyIgY3k9IjExMS43IiByPSI0MS40IiBmaWxsPSIjZmZmIi8+CiAgICAgIDxwYXRoIGQ9Im0xNTIuOCwxMC40bDQ5LjYsOC44YzYuOSwxLjIsMTAuMSw5LjQsNS44LDE1bC0yOS41LDM4LjhjLTQuNCw1LjgtMTMuNCw0LjYtMTYuMi0ybC0yMC4xLTQ3LjZjLTIuOS02LjksMy0xNC4yLDEwLjMtMTIuOVoiIGZpbGw9IiNmZmYiLz4KICAgICAgPHBhdGggZD0ibTEzMywxMjUuOWMtMS4zLS4zLTIuNS0uNy0zLjctMS40bC02LjQtMy43Yy0xNy4xLTEwLTI4LTI4LjUtMjguNC00OC4zdi0uN2MtLjMtMTYuOC04LjItMzIuNC0yMS41LTQyLjdsLTkuMS03LjFjLTUuMy00LjEtNi4zLTExLjgtMi4yLTE3LjEsNC4xLTUuMywxMS44LTYuMywxNy4xLTIuMmw5LjEsNy4xYzE5LjIsMTQuOSwzMC41LDM3LjMsMzAuOSw2MS42di43Yy4yLDExLjMsNi41LDIyLDE2LjMsMjcuN2w2LjQsMy43YzUuOCwzLjQsNy44LDEwLjksNC40LDE2LjctMi43LDQuNy04LDYuOC0xMyw1LjhaIiBmaWxsPSIjZmZmIi8+CiAgICA8L2c+CiAgPC9nPgo8L3N2Zz4=';

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
		add_menu_page( 'WP Recipe Maker', 'WP Recipe Maker', WPRM_Settings::get( 'features_dashboard_access' ), 'wprecipemaker', array( 'WPRM_Dashboard', 'page_template' ), 'dashicons-food', '57.9' );
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
				if ( $options['archive'] || 'wprm_glossary_term' === $taxonomy ) {
					$page = 'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=' . WPRM_POST_TYPE;
					add_submenu_page( 'wprm_taxonomies', $options['name'], $options['name'], WPRM_Settings::get( 'features_manage_access' ), $page, null );

					if ( false === $first_taxonomy_showing ) {
						$first_taxonomy_showing = $page;
					}
				}
			}

			if ( false !== $first_taxonomy_showing ) {
				add_menu_page( 'WPRM ' . __( 'Taxonomies', 'wp-recipe-maker' ), __( 'Taxonomies', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_manage_access' ), 'wprm_taxonomies', $first_taxonomy_showing, 'dashicons-food', '57.91' );
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
