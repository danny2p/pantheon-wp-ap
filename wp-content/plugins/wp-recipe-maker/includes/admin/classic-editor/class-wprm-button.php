<?php
/**
 * Add the "WP Recipe Maker" button to posts and pages.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/classic-editor
 */

/**
 * Add the "WP Recipe Maker" button to posts and pages.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/classic-editor
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Button {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'media_buttons', array( __CLASS__, 'add_shortcode_button' ) );

		add_filter( 'mce_external_plugins', array( __CLASS__, 'add_button' ) );
		add_filter( 'mce_buttons', array( __CLASS__, 'register_button' ) );
	}

	/**
	 * Add the "WP Recipe Maker" button as a media button for posts and pages.
	 *
	 * @since    1.0.0
	 * @param		 object $editor_id Name of the tinymce editor where this media button will be added.
	 */
	public static function add_shortcode_button( $editor_id ) {
		$screen = get_current_screen();

		if ( 'wprm_recipe_notes' !== $editor_id && in_array( $screen->base, array( 'post', 'page' ), true ) ) {
			$title = 'WP Recipe Maker';

			echo '<button type="button" class="button wprm-modal-menu-button" data-editor="' . esc_attr( $editor_id ) . '" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</button>';

			// Edit Recipe button.
			$recipe_ids = WPRM_Recipe_Manager::get_recipe_ids_from_post();

			if ( $recipe_ids && isset( $recipe_ids[0] ) ) {
				$recipe_id = $recipe_ids[0];
				$title = __( 'Edit Recipe', 'wp-recipe-maker' );
				echo '<button type="button" class="button wprm-modal-edit-button" data-recipe="' . esc_attr( $recipe_id ) . '" data-editor="' . esc_attr( $editor_id ) . '" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</button>';
			}
		}
	}

	/**
	 * Add the button to the TinyMCE editor.
	 *
	 * @since    1.9.1
	 * @param    mixed $plugin_array TinyMCE plugins.
	 */
	public static function add_button( $plugin_array ) {
		$plugin_array['wp_recipe_maker'] = WPRM_URL . 'assets/js/other/tinymce-toolbar-icon.js';
		return $plugin_array;
	}

	/**
	 * Register the button for the TinyMCE editor.
	 *
	 * @since    1.9.1
	 * @param    mixed $buttons TinyMCE buttons.
	 */
	public static function register_button( $buttons ) {
		array_push( $buttons, 'wp_recipe_maker' );
		return $buttons;
	}
}

WPRM_Button::init();
