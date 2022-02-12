<?php
/**
 * Handle the recipe manage page.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/modal
 */

/**
 * Handle the recipe manage page.
 *
 * @since      5.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/modal
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Manage {

	/**
	 * Register actions and filters.
	 *
	 * @since    5.0.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_manage_page' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Add the manage submenu to the WPRM menu.
	 *
	 * @since	5.0.0
	 */
	public static function add_manage_page() {
		add_submenu_page( 'wprecipemaker', __( 'Manage', 'wp-recipe-maker' ), __( 'Manage', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_manage_access' ), 'wprm_manage', array( __CLASS__, 'page_template' ) );
	}

	/**
	 * Get the template for this submenu.
	 *
	 * @since    1.9.0
	 */
	public static function page_template() {
		echo '<div class="wrap"><div id="wprm-admin-manage">Loading...</div></div>';
	}

	/**
	 * Enqueue stylesheets and scripts.
	 *
	 * @since    5.0.0
	 */
	public static function enqueue() {
		$screen = get_current_screen();

		// Only load on manage page.
		if ( 'wp-recipe-maker_page_wprm_manage' === $screen->id ) {
			wp_enqueue_style( 'wprm-admin-manage', WPRM_URL . 'dist/admin-manage.css', array(), WPRM_VERSION, 'all' );

			wp_enqueue_script( 'wprm-admin-manage', WPRM_URL . 'dist/admin-manage.js', array( 'wprm-admin', 'wprm-admin-modal' ), WPRM_VERSION, true );

			$args = array();

			// Prevent deprecation warning.
			if ( version_compare( $GLOBALS['wp_version'], '5.9', '<' ) ) {
				$args['who'] = 'authors';
			} else {
				$args['capability'] = array( 'edit_posts' );
			}

			// Get Authors.
			$authors = get_users( $args );

			$post_statuses = get_post_statuses();
			$post_statuses['future'] = __( 'Scheduled' );

			$revisions = defined( 'WP_POST_REVISIONS' ) && ! ! WP_POST_REVISIONS;
			$count_posts = wp_count_posts( WPRM_POST_TYPE );

			$localize_data = apply_filters( 'wprm_admin_manage_localize', array(
				'taxonomies' => WPRM_Taxonomies::get_taxonomies(),
				'authors' => $authors,
				'post_statuses' => $post_statuses,
				'trash' => $count_posts->trash,
				'revisions' => $revisions,
				'multilingual' => WPRM_Compatibility::multilingual(),
			) );
			wp_localize_script( 'wprm-admin-manage', 'wprm_admin_manage', $localize_data );
		}
	}
}

WPRM_Manage::init();
