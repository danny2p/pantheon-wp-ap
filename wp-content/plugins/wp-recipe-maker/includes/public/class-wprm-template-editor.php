<?php
/**
 * Responsible for the recipe template editor.
 *
 * @link       https://bootstrapped.ventures
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
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_hubbub_preview_styles' ), 20 );
	}

	/**
	 * Add the template editor submenu to the WPRM menu.
	 *
	 * @since	4.0.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( 'wprecipemaker', __( 'WPRM Template Editor', 'wp-recipe-maker' ), __( 'Template Editor', 'wp-recipe-maker' ), 'manage_options', 'wprm_template_editor', array( __CLASS__, 'template_editor_page_template' ) );
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
	 * Enqueue Hubbub frontend styles on the template editor page so Save This and Action Buttons previews are styled.
	 *
	 * @since 10.4.0
	 */
	public static function enqueue_hubbub_preview_styles() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( ! $screen || 'wp-recipe-maker_page_wprm_template_editor' !== $screen->id ) {
			return;
		}
		if ( ! self::is_hubbub_active() ) {
			return;
		}

		// Action Buttons are block-based and rely on core block styling as well.
		wp_enqueue_style( 'wp-block-library' );
		wp_enqueue_style( 'wp-block-library-theme' );

		// Load theme preset CSS and only the global button rules needed for Action Buttons.
		// Avoid full global styles because those can interfere with the template editor admin UI.
		if ( function_exists( 'wp_get_global_stylesheet' ) ) {
			$preset_css = wp_get_global_stylesheet( array( 'variables', 'presets' ) );
			$button_css = self::get_global_button_css_for_preview();

			if ( $preset_css ) {
				wp_register_style( 'wprm-template-editor-global-presets', false, array(), WPRM_VERSION );
				wp_enqueue_style( 'wprm-template-editor-global-presets' );
				wp_add_inline_style( 'wprm-template-editor-global-presets', $preset_css );
			}

			if ( $button_css ) {
				wp_register_style( 'wprm-template-editor-global-button-styles', false, array(), WPRM_VERSION );
				wp_enqueue_style( 'wprm-template-editor-global-button-styles' );
				wp_add_inline_style( 'wprm-template-editor-global-button-styles', $button_css );
			}
		}

		// Use Hubbub's registered style if available.
		if ( function_exists( 'wp_styles' ) && isset( wp_styles()->registered['dpsp-frontend-style-pro'] ) ) {
			wp_enqueue_style( 'dpsp-frontend-style-pro' );
		} else {
			$url = self::get_hubbub_frontend_style_url();
			if ( '' !== $url ) {
				wp_enqueue_style( 'wprm-hubbub-preview', $url, array(), WPRM_VERSION );
			}
		}

		// Action Buttons use a separate block style file in Hubbub.
		if ( function_exists( 'wp_styles' ) && isset( wp_styles()->registered['social-pug-action-button-style'] ) ) {
			wp_enqueue_style( 'social-pug-action-button-style' );
		} else {
			$url = self::get_hubbub_action_buttons_style_url();
			if ( '' !== $url ) {
				wp_enqueue_style( 'wprm-hubbub-action-buttons-preview', $url, array(), WPRM_VERSION );
			}
		}
	}

	/**
	 * Get the Hubbub (Social Pug) frontend stylesheet URL for template editor preview.
	 *
	 * @since 10.4.0
	 * @return string Style URL or empty string if not found.
	 */
	private static function get_hubbub_frontend_style_url() {
		$url   = '';
		$base  = self::get_hubbub_plugin_base_url();
		if ( '' !== $base ) {
			$url = $base . 'assets/dist/style-frontend-pro.css';
		}

		return apply_filters( 'wprm_template_editor_hubbub_preview_style_url', $url );
	}

	/**
	 * Get the Hubbub Action Buttons block stylesheet URL for template editor preview.
	 *
	 * @since 10.4.0
	 * @return string Style URL or empty string if not found.
	 */
	private static function get_hubbub_action_buttons_style_url() {
		$url  = '';
		$base = self::get_hubbub_plugin_base_url();
		if ( '' !== $base ) {
			$url = $base . 'inc/admin/block-action-buttons/style-index.css';
		}

		return apply_filters( 'wprm_template_editor_hubbub_action_buttons_style_url', $url );
	}

	/**
	 * Get only button-related global styles for template preview.
	 *
	 * @since 10.4.0
	 * @return string Button-related CSS.
	 */
	private static function get_global_button_css_for_preview() {
		if ( ! function_exists( 'wp_get_global_stylesheet' ) ) {
			return '';
		}

		$css = wp_get_global_stylesheet( array( 'styles' ) );
		if ( ! $css ) {
			return '';
		}

		$button_css = '';
		$patterns = array(
			'/[^{}]*\.wp-element-button[^{}]*\{[^{}]*\}/m',
			'/[^{}]*\.wp-block-button__link[^{}]*\{[^{}]*\}/m',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match_all( $pattern, $css, $matches ) ) {
				$button_css .= implode( "\n", $matches[0] ) . "\n";
			}
		}

		return trim( $button_css );
	}

	/**
	 * Get the Hubbub plugin base URL.
	 *
	 * @since 10.4.0
	 * @return string Plugin base URL or empty string if not found.
	 */
	private static function get_hubbub_plugin_base_url() {
		$base   = '';
		$active = get_option( 'active_plugins', array() );

		foreach ( $active as $plugin ) {
			if ( false !== strpos( $plugin, 'social-pug' ) || false !== strpos( $plugin, 'hubbub' ) ) {
				$base = plugin_dir_url( WP_PLUGIN_DIR . '/' . $plugin );
				break;
			}
		}

		return $base;
	}

	/**
	 * Check if Hubbub (Social Pug) is active.
	 *
	 * @since 10.4.0
	 * @return bool
	 */
	private static function is_hubbub_active() {
		// Main Hubbub plugin class.
		if ( class_exists( 'Social_Pug' ) ) {
			return true;
		}

		// Namespaced classes used by recent versions.
		if ( class_exists( 'Mediavine\\Grow\\Asset_Loader' ) || class_exists( 'Mediavine\\Grow\\Activation' ) ) {
			return true;
		}

		// Fallback check for active plugins list.
		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $active_plugins as $plugin ) {
			if ( false !== strpos( $plugin, 'social-pug' ) || false !== strpos( $plugin, 'hubbub' ) ) {
				return true;
			}
		}

		return false;
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
			'default_template_usages' => self::get_default_template_usages( $modern_templates ),
			'shortcodes' => WPRM_Template_Shortcodes::get_shortcodes(),
			'icons' => WPRM_Icon::get_all(),
			'thumbnail_sizes' => get_intermediate_image_sizes(),
			'preview_recipe' => WPRM_Settings::get( 'template_editor_preview_recipe' ),
			'undo_redo_history' => WPRM_Settings::get( 'template_editor_undo_redo_history' ),
		) );
	}

	/**
	 * Get a map of template slug to default usage labels.
	 *
	 * @since	10.5.0
	 * @param	array $modern_templates Modern templates available in the editor.
	 */
	private static function get_default_template_usages( $modern_templates ) {
		$usages = array();

		// Contexts resolved through template manager.
		self::add_template_usage_by_type( $usages, $modern_templates, 'single', 'food', __( 'Food Recipe Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'single', 'howto', __( 'How-to Instructions Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'single', 'other', __( 'Other Recipe Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'archive', 'food', __( 'Archive Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'amp', 'food', __( 'AMP Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'feed', 'food', __( 'RSS Feed Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'print', 'food', __( 'Food Recipe Print Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'print', 'howto', __( 'How-to Instructions Print Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'print', 'other', __( 'Other Recipe Print Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'pdf', 'food', __( 'Food Recipe PDF Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'pdf', 'howto', __( 'How-to Instructions PDF Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'pdf', 'other', __( 'Other Recipe PDF Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'snippet', 'food', __( 'Food Recipe Snippet Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'snippet', 'howto', __( 'How-to Instructions Snippet Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'snippet', 'other', __( 'Other Recipe Snippet Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'roundup', 'food', __( 'Food Recipe Roundup Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'roundup', 'howto', __( 'How-to Instructions Roundup Template', 'wp-recipe-maker' ) );
		self::add_template_usage_by_type( $usages, $modern_templates, 'roundup', 'other', __( 'Other Recipe Roundup Template', 'wp-recipe-maker' ) );

		$collections_active = WPRM_Addons::is_active( 'elite' );
		if ( $collections_active ) {
			self::add_template_usage_by_type( $usages, $modern_templates, 'print-collection', 'food', __( 'Recipe Collections Print Template (Food)', 'wp-recipe-maker' ) );
			self::add_template_usage_by_type( $usages, $modern_templates, 'print-collection', 'howto', __( 'Recipe Collections Print Template (How-to)', 'wp-recipe-maker' ) );
			self::add_template_usage_by_type( $usages, $modern_templates, 'print-collection', 'other', __( 'Recipe Collections Print Template (Other)', 'wp-recipe-maker' ) );
		}

		// Contexts resolved directly from setting values.
		$manual_contexts = array(
			array(
				'setting' => 'default_print_template_admin',
				'label' => __( 'Admin Print Template', 'wp-recipe-maker' ),
				'aliases' => array(
					'default_recipe_template' => 'default_recipe_template_modern',
				),
			),
			array(
				'setting' => 'post_type_archive_output_template',
				'label' => __( 'Archive Pages Template', 'wp-recipe-maker' ),
				'aliases' => array(),
			),
		);

		if ( $collections_active ) {
			$manual_contexts[] = array(
				'setting' => 'recipe_collections_template_modern',
				'label' => __( 'Recipe Collections Template (Food)', 'wp-recipe-maker' ),
				'aliases' => array(
					'default_recipe_template' => 'default_recipe_template_modern',
				),
			);
			$manual_contexts[] = array(
				'setting' => 'recipe_collections_howto_template_modern',
				'label' => __( 'Recipe Collections Template (How-to)', 'wp-recipe-maker' ),
				'aliases' => array(
					'default_recipe_template' => 'default_howto_recipe_template_modern',
				),
			);
			$manual_contexts[] = array(
				'setting' => 'recipe_collections_other_template_modern',
				'label' => __( 'Recipe Collections Template (Other)', 'wp-recipe-maker' ),
				'aliases' => array(
					'default_recipe_template' => 'default_other_recipe_template_modern',
				),
			);
		}

		foreach ( $manual_contexts as $context ) {
			$slug = self::get_template_slug_for_setting( $context['setting'], $modern_templates, $context['aliases'] );
			self::add_template_usage( $usages, $modern_templates, $slug, $context['label'] );
		}

		return $usages;
	}

	/**
	 * Add usage label for template resolved by manager type.
	 *
	 * @since	10.5.0
	 * @param	array  $usages           Map of slug to labels.
	 * @param	array  $modern_templates Modern templates available in the editor.
	 * @param	string $type             Template type.
	 * @param	string $recipe_type      Recipe type.
	 * @param	string $label            Usage label.
	 */
	private static function add_template_usage_by_type( &$usages, $modern_templates, $type, $recipe_type, $label ) {
		$template = WPRM_Template_Manager::get_template_by_type( $type, $recipe_type );
		$slug = $template && isset( $template['slug'] ) ? $template['slug'] : false;

		self::add_template_usage( $usages, $modern_templates, $slug, $label );
	}

	/**
	 * Add usage label for a template slug.
	 *
	 * @since	10.5.0
	 * @param	array  $usages           Map of slug to labels.
	 * @param	array  $modern_templates Modern templates available in the editor.
	 * @param	mixed  $slug             Template slug.
	 * @param	string $label            Usage label.
	 */
	private static function add_template_usage( &$usages, $modern_templates, $slug, $label ) {
		if ( ! self::is_valid_usage_slug( $slug, $modern_templates ) ) {
			return;
		}

		if ( ! isset( $usages[ $slug ] ) ) {
			$usages[ $slug ] = array();
		}

		if ( ! in_array( $label, $usages[ $slug ], true ) ) {
			$usages[ $slug ][] = $label;
		}
	}

	/**
	 * Resolve setting value to a valid template slug.
	 *
	 * @since	10.5.0
	 * @param	string $setting_id       Setting ID to resolve.
	 * @param	array  $modern_templates Modern templates available in the editor.
	 * @param	array  $aliases          Map of alias value to setting IDs.
	 * @param	array  $visited          Already visited setting IDs.
	 */
	private static function get_template_slug_for_setting( $setting_id, $modern_templates, $aliases = array(), $visited = array() ) {
		if ( in_array( $setting_id, $visited, true ) ) {
			return false;
		}

		$visited[] = $setting_id;

		$value = WPRM_Settings::get( $setting_id );
		$slug = self::resolve_template_slug_value( $value, $modern_templates, $aliases, $visited );
		if ( $slug ) {
			return $slug;
		}

		$default_value = WPRM_Settings::get_default( $setting_id );
		if ( $default_value !== $value ) {
			$slug = self::resolve_template_slug_value( $default_value, $modern_templates, $aliases, $visited );
			if ( $slug ) {
				return $slug;
			}
		}

		return false;
	}

	/**
	 * Resolve a setting value to a valid template slug.
	 *
	 * @since	10.5.0
	 * @param	mixed $value             Setting value.
	 * @param	array $modern_templates  Modern templates available in the editor.
	 * @param	array $aliases           Map of alias value to setting IDs.
	 * @param	array $visited           Already visited setting IDs.
	 */
	private static function resolve_template_slug_value( $value, $modern_templates, $aliases = array(), $visited = array() ) {
		if ( ! $value ) {
			return false;
		}

		if ( isset( $aliases[ $value ] ) ) {
			return self::get_template_slug_for_setting( $aliases[ $value ], $modern_templates, $aliases, $visited );
		}

		return self::is_valid_usage_slug( $value, $modern_templates ) ? $value : false;
	}

	/**
	 * Check if slug exists as an available modern template.
	 *
	 * @since	10.5.0
	 * @param	mixed $slug             Template slug.
	 * @param	array $modern_templates Modern templates available in the editor.
	 */
	private static function is_valid_usage_slug( $slug, $modern_templates ) {
		if ( ! $slug || ! isset( $modern_templates[ $slug ] ) ) {
			return false;
		}

		$premium_active = WPRM_Addons::is_active( 'premium' ) || WPRM_Addons::is_active( 'pro' ) || WPRM_Addons::is_active( 'elite' );
		if ( $modern_templates[ $slug ]['premium'] && ! $premium_active ) {
			return false;
		}

		return true;
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
		$css = WPRM_Template_Manager::get_template_css( $template, false );

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
