<?php
/**
 * Show plugin preview in the admin.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Show plugin preview in the admin.
 *
 * @since      9.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Admin_Menu_Preview {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.2.0
	 */
	public static function init() {
		// This setting should be set by the plugin preview blueprint.
		$is_plugin_preview = get_option( 'wprm_is_plugin_preview' );

		if ( $is_plugin_preview ) {
			add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 22 );
		}
	}

	/**
	 * Add our support widget to the page.
	 *
	 * @since    9.2.0
	 */
	public static function add_support_widget() {
		require_once( WPRM_DIR . 'templates/admin/menu/support-widget.php' );
	}

	/**
	 * Add the preview submenu to the WPRM menu.
	 *
	 * @since    9.2.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( 'wprecipemaker', __( 'Preview', 'wp-recipe-maker' ), __( 'Preview', 'wp-recipe-maker' ), 'manage_options', 'wprm_preview', array( __CLASS__, 'page_template' ) );
	}

	/**
	 * Get the template for this submenu.
	 *
	 * @since    9.2.0
	 */
	public static function page_template() {
		$preview_post_id = get_option( 'wprm_plugin_preview_post_id' );

		if ( ! $preview_post_id ) {
			$preview_post_id = self::set_up_preview();

			update_option( 'wprm_onboarded', time(), 'no' ); // Skip onboarding.
			update_option( 'wprm_plugin_preview_post_id', $preview_post_id, 'no' ); // Don't set up preview again.
		}

		require_once( WPRM_DIR . 'templates/admin/menu/preview.php' );
	}

	/**
	 * Set up plugin preview.
	 *
	 * @since    9.2.0
	 */
	public static function set_up_preview() {
		// Get and sanitize demo recipe.
		ob_start();
		include( WPRM_DIR . 'templates/admin/demo-recipe.json' );
		$json = ob_get_contents();
		ob_end_clean();

		$json_recipe = json_decode( $json, true );
		$sanitized_recipe = WPRM_Recipe_Sanitizer::sanitize( $json_recipe );
		$recipe_id = WPRM_Recipe_Saver::create_recipe( $sanitized_recipe );

		// Add image to recipe.
		$image_id = WPRM_Import_Helper::get_or_upload_attachment( $recipe_id, WPRM_URL . 'assets/images/demo-recipe.jpg' );

		if ( $image_id ) {
            set_post_thumbnail( $recipe_id, $image_id );
        }

		// Create post to hold the recipe.
        $post_content = '';
        $post_content .= '<p>This is an example post with a recipe. It\'s a regular WordPress post like any other, but it has a WP Recipe Maker block added to it, presenting the recipe itself.</p>';
        $post_content .= '<p>You can add any other content to this post as well, like images, text, etc.</p>';
        $post_content .= '<p>Try clicking on "WP Recipe Maker" in the admin bar at the top for a preview of what the edit interface looks like.</p>';
        $post_content .= '<p>For even more examples of all the plugin features, <a href="https://demo.wprecipemaker.com">check out our demo site!</a></p>';
        $post_content .= '<p>The following is an example of what the default recipe template looks like, but everything you see can be customized to your liking:</p>';
        $post_content .= '[wprm-recipe id="' . $recipe_id . '"]';
        $post_content .= '<p>Take note that this is just a demo recipe for the preview. Not all of the advanced features might work in this demo environment. We recommend just testing the plugin on your own site!</p>';

        $preview_post_id = wp_insert_post( array(
			'post_status' => 'publish',
            'post_title' => 'Example Post With a Recipe',
            'post_content' => $post_content,
		) );

		return $preview_post_id;
	}
}

WPRM_Admin_Menu_Preview::init();
