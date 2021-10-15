<?php
/**
 * Handle the display of the shortcode in the TinyMCE editor.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/classic-editor
 */

/**
 * Handle the display of the shortcode in the TinyMCE editor.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/classic-editor
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Preview {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
			add_action( 'wp_ajax_wprm_shortcode_preview', array( __CLASS__, 'ajax_shortcode_preview' ) );
			add_filter( 'mce_external_plugins', array( __CLASS__, 'tinymce_shortcode_plugin' ) );
	}

	/**
	 * Return preview to be used for recipe shortcode.
	 *
	 * @since    1.0.0
	 */
	public static function ajax_shortcode_preview() {
		$preview = '';

		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$recipe_id = isset( $_POST['recipe_id'] ) ? intval( $_POST['recipe_id'] ) : 0; // Input var okay.

			$post = get_post( $recipe_id );

			$preview .= '<span contentEditable="false" style="font-weight: bold;" data-wprm-recipe="' . $recipe_id . '">WP Recipe Maker #' . $recipe_id . '</span>';
			$preview .= '<span contentEditable="false" style="float: right; color: darkred;" data-wprm-recipe-remove="' . $recipe_id . '">' . esc_html__( 'remove', 'wp-recipe-maker' ) . '</span>';
			$preview .= '<br/><br/>';

			if ( ! is_null( $post ) && WPRM_POST_TYPE === $post->post_type  ) {
					$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );
					ob_start();
					require( WPRM_DIR . 'templates/admin/shortcode-preview.php' );
					$preview .= ob_get_contents();
					ob_end_clean();
			}
		}

		echo $preview; // @codingStandardsIgnoreLine
		wp_die();
	}

	/**
	 * Load custom TinyMCE plugin for handling the recipe shortcode.
	 *
	 * @since    1.0.0
	 * @param		 array $plugin_array Plugins to be used by TinyMCE.
	 */
	public static function tinymce_shortcode_plugin( $plugin_array ) {
		 $plugin_array['wprecipemaker'] = WPRM_URL . 'assets/js/other/tinymce-shortcode-preview.js';
		 return $plugin_array;
	}
}

WPRM_Shortcode_Preview::init();
