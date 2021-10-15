<?php
/**
 * Show a FAQ in the backend menu.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Show a FAQ in the backend menu.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Admin_Menu_Faq {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'admin_head-wp-recipe-maker_page_wprm_faq', array( __CLASS__, 'add_support_widget' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 22 );

		add_action( 'current_screen', array( __CLASS__, 'redirect_to_onboarding' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_ajax_wprm_finished_onboarding', array( __CLASS__, 'ajax_finished_onboarding' ) );
	}

	/**
	 * Add our support widget to the page.
	 *
	 * @since    1.0.0
	 */
	public static function add_support_widget() {
		require_once( WPRM_DIR . 'templates/admin/menu/support-widget.php' );
	}

	/**
	 * Add the FAQ & Support submenu to the WPRM menu.
	 *
	 * @since    1.0.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( 'wprecipemaker', __( 'FAQ & Support', 'wp-recipe-maker' ), __( 'FAQ & Support', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_faq_access' ), 'wprm_faq', array( __CLASS__, 'page_template' ) );
	}

	/**
	 * Get the template for this submenu.
	 *
	 * @since    1.0.0
	 */
	public static function page_template() {
		echo '<div class="wrap wprm-wrap"><div id="wprm-admin-faq">Loading...</div></div>';
	}

	/**
	 * Check if we should redirect to onboarding.
	 *
	 * @since    5.8.0
	 */
	public static function redirect_to_onboarding() {
		$screen = get_current_screen();

		// Redirect from Manage page if not onboarded yet.
		if ( 'toplevel_page_wprecipemaker' === $screen->id || 'wp-recipe-maker_page_wprm_manage' === $screen->id ) {
			if ( ! self::is_onboarded() ) {
				if ( isset( $_GET['skip_onboarding'] ) && '1' === $_GET['skip_onboarding'] ) {
					update_option( 'wprm_onboarded', time(), 'no' );
				} else {
					wp_redirect( admin_url( 'admin.php?page=wprm_faq' ) );
				}
			}
		}
	}

	/**
	 * Check if someone is already onboarded.
	 *
	 * @since    5.8.0
	 */
	public static function is_onboarded() {
		$count = wp_count_posts( WPRM_POST_TYPE )->publish;
		$already_onboarded = get_option( 'wprm_onboarded' );

		// Already gone through the steps or has more than 3 recipes.
		return $already_onboarded || 3 < intval( $count );
	}

	/**
	 * Enqueue stylesheets and scripts.
	 *
	 * @since    5.8.0
	 */
	public static function enqueue() {
		$screen = get_current_screen();

		// Only load on onboarding page.
		if ( 'wp-recipe-maker_page_wprm_faq' === $screen->id ) {
			wp_enqueue_style( 'wprm-admin-faq', WPRM_URL . 'dist/admin-faq.css', array(), WPRM_VERSION, 'all' );
			wp_enqueue_script( 'wprm-admin-faq', WPRM_URL . 'dist/admin-faq.js', array( 'wprm-admin', 'wprm-admin-modal', 'wprm-admin-template' ), WPRM_VERSION, true );

			$current_user = wp_get_current_user();
			wp_localize_script( 'wprm-admin-faq', 'wprm_faq', array(
				'onboarded' => self::is_onboarded(),
				'user' => array(
					'email' => $current_user->user_email,
					'website' => get_site_url(),
				),
			) );

			WPRM_Template_Editor::localize_admin_template();
		}
	}

	/**
	 * Finish the onboarding through AJAX.
	 *
	 * @since	5.8.0
	 */
	public static function ajax_finished_onboarding() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			update_option( 'wprm_onboarded', time(), 'no' );
			wp_send_json_success();
		}
		wp_die();
	}
}

WPRM_Admin_Menu_Faq::init();
