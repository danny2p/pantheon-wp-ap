<?php
/**
 * Responsible for the recipe template editor.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the recipe template editor.
 *
 * @since      4.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Template_Editor {
	/**
	 * Register actions and filters.
	 *
	 * @since	4.0.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
	}

	/**
	 * Add the template editor submenu to the WPRM menu.
	 *
	 * @since	4.0.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'WPRM Template Editor', 'wp-recipe-maker' ), __( 'Template Editor', 'wp-recipe-maker' ), 'manage_options', 'wprm_template_editor', array( __CLASS__, 'template_editor_page_template' ) );
	}

	/**
	 * Get the template for the template editor page.
	 *
	 * @since	4.0.0
	 */
	public static function template_editor_page_template() {
		self::localize_admin_template();
		echo '<div id="wprm-template" class="wrap">Loading...</div>';
	}

	/**
	 * Localize JS for the template editor page.
	 *
	 * @since	5.8.0
	 */
	public static function localize_admin_template() {
		// Get all modern templates.
		$modern_templates = array();
		$templates = WPRM_Template_Manager::get_templates();

		foreach ( $templates['modern'] as $template ) {
			$modern_templates[ $template['slug'] ] = self::prepare_template_for_editor( $template );
		}

		wp_localize_script( 'wprm-admin-template', 'wprm_admin_template', array(
			'templates' => $modern_templates,
			'shortcodes' => WPRM_Template_Shortcodes::get_shortcodes(),
			'icons' => WPRM_Icon::get_all(),
			'thumbnail_sizes' => get_intermediate_image_sizes(),
			'preview_recipe' => WPRM_Settings::get( 'template_editor_preview_recipe' ),
		) );
	}

	/**
	 * Prepare a template for the template editor.
	 *
	 * @since	4.0.0
	 * @param	mixed $template Template to prepare.
	 */
	public static function prepare_template_for_editor( $template ) {
		$template['style'] = self::extract_style_with_properties( $template );

		// Fix deprecated shortcodes.
		$template['html'] = str_ireplace( '[wprm-recipe-author-container', 			'[wprm-recipe-author label_container="1"', $template['html'] );
		$template['html'] = str_ireplace( '[wprm-recipe-cost-container', 			'[wprm-recipe-cost label_container="1"', $template['html'] );
		$template['html'] = str_ireplace( '[wprm-recipe-custom-field-container', 	'[wprm-recipe-custom-field label_container="1"', $template['html'] );
		$template['html'] = str_ireplace( '[wprm-recipe-nutrition-container', 		'[wprm-recipe-nutrition label_container="1"', $template['html'] );
		$template['html'] = str_ireplace( '[wprm-recipe-servings-container', 		'[wprm-recipe-servings label_container="1"', $template['html'] );
		$template['html'] = str_ireplace( '[wprm-recipe-tag-container', 			'[wprm-recipe-tag label_container="1"', $template['html'] );
		$template['html'] = str_ireplace( '[wprm-recipe-time-container', 			'[wprm-recipe-time label_container="1"', $template['html'] );

		// Migrate tags and times container.
		$pattern = get_shortcode_regex( array( 'wprm-recipe-tags-container' ) );
		if ( preg_match_all( '/' . $pattern . '/s', $template['html'], $matches ) && array_key_exists( 2, $matches ) ) {
			foreach ( $matches[2] as $key => $value ) {
				if ( 'wprm-recipe-tags-container' === $value ) {
					$old_shortcode = $matches[0][ $key ];
					
					$new_shortcode = $old_shortcode;
					$new_shortcode = str_ireplace( '[wprm-recipe-tags-container', '[wprm-recipe-meta-container fields="tags"', $new_shortcode );
					$new_shortcode = str_ireplace( ' separator=', ' tag_separator=', $new_shortcode );

					$template['html'] = str_ireplace( $old_shortcode, $new_shortcode, $template['html'] );
				}
			}
		}

		$pattern = get_shortcode_regex( array( 'wprm-recipe-times-container' ) );
		if ( preg_match_all( '/' . $pattern . '/s', $template['html'], $matches ) && array_key_exists( 2, $matches ) ) {
			foreach ( $matches[2] as $key => $value ) {
				if ( 'wprm-recipe-times-container' === $value ) {
					$old_shortcode = $matches[0][ $key ];
					
					$new_shortcode = $old_shortcode;
					$new_shortcode = str_ireplace( '[wprm-recipe-times-container', '[wprm-recipe-meta-container fields="times"', $new_shortcode );
					$new_shortcode = str_ireplace( ' shorthand=', ' time_shorthand=', $new_shortcode );
					$new_shortcode = str_ireplace( ' icon_prep=', ' icon_prep_time=', $new_shortcode );
					$new_shortcode = str_ireplace( ' icon_cook=', ' icon_cook_time=', $new_shortcode );
					$new_shortcode = str_ireplace( ' icon_custom=', ' icon_custom_time=', $new_shortcode );
					$new_shortcode = str_ireplace( ' icon_total=', ' icon_total_time=', $new_shortcode );
					$new_shortcode = str_ireplace( ' label_prep=', ' label_prep_time=', $new_shortcode );
					$new_shortcode = str_ireplace( ' label_cook=', ' label_cook_time=', $new_shortcode );
					$new_shortcode = str_ireplace( ' label_total=', ' label_total_time=', $new_shortcode );

					$template['html'] = str_ireplace( $old_shortcode, $new_shortcode, $template['html'] );
				}
			}
		}
		
		return $template;
	}

	/**
	 * Extract the style and optional properties from a template stylesheet.
	 *
	 * @since	4.0.0
	 * @param	mixed $template Template to extract from.
	 */
	private static function extract_style_with_properties( $template ) {
		$css = WPRM_Template_Manager::get_template_css( $template );

		// Find properties in CSS.
		$properties = array();

		preg_match_all( "/:([^:;]+);\s*\/\*([^*]*)\*+([^\/*][^*]*\*+)*\//im", $css, $matches );
		foreach ( $matches[2] as $index => $comment ) {
			$value = trim( $matches[1][ $index ] );
			$comment = trim( $comment );
			
			// Check if it's one of our comments.
			if ( 'wprm_' === substr( $comment, 0, 5 ) ) {
				$parts = explode( ' ', $comment );

				// First part should be variable name.
				$id = substr( $parts[0], 5 );
				unset( $parts[0] );

				if ( $id ) {
					$property = array(
						'id' => $id,
						'name' => ucwords( str_replace( '_', ' ', $id ) ),
						'default' => $value,
						'value' => $value,
					);

					// Check if there are any parts left.
					foreach ( $parts as $part ) {
						$pieces = explode( '=', $part );

						if ( 2 === count( $pieces ) ) {
							if ( ! array_key_exists( $pieces[0], $property ) ) {
								$property[ $pieces[0] ] = $pieces[1];
							}
						}
					}

					// Add to properties.
					$properties[ $id ] = $property;

					// Replace with variable in CSS.
					$css = str_ireplace( $matches[0][ $index ], ': %wprm_' . $id .'%;', $css );
				}
			}
		}

		return array(
			'properties' => $properties,
			'css' => $css,
		);
	}
}

WPRM_Template_Editor::init();
