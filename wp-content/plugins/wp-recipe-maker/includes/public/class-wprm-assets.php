<?php
/**
 * Responsible for loading the WPRM assets.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.22.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for loading the WPRM assets.
 *
 * @since      1.22.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Assets {

	/**
	 * Data to pass along to JS.
	 *
	 * @since    8.1.0
	 * @access   private
	 * @var      array $js_data Data to pass along to JS.
	 */
	private static $js_data = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    1.22.0
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 1 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin' ), 1 );
		add_action( 'amp_post_template_css', array( __CLASS__, 'amp_style' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'block_assets' ) );

		add_action( 'wp_head', array( __CLASS__, 'custom_css' ) );
		add_action( 'wp_footer', array( __CLASS__, 'footer_assets' ) );
		add_action( 'wp_footer', array( __CLASS__, 'output_js_data' ) );
	}

	/**
	 * Enqueue stylesheets and scripts.
	 *
	 * @since    1.22.0
	 */
	public static function enqueue() {
		$template_mode = WPRM_Settings::get( 'recipe_template_mode' );

		wp_register_style( 'wprm-public', WPRM_URL . 'dist/public-' . $template_mode . '.css', array(), WPRM_VERSION, 'all' );

		// Only include scripts when not AMP page.
		if ( ! function_exists( 'is_amp_endpoint' ) || ! is_amp_endpoint() ) {
			wp_register_script( 'wprm-public', WPRM_URL . 'dist/public-' . $template_mode . '.js', array(), WPRM_VERSION, true );
			wp_localize_script( 'wprm-public', 'wprm_public', self::localize_public() );
		}
		
		if ( false === WPRM_Settings::get( 'only_load_assets_when_needed' ) ) {
			self::load();
		}
	}

	/**
	 * Actually load assets.
	 *
	 * @since	5.5.0
	 */
	public static function load() {
		wp_enqueue_style( 'wprm-public' );

		if ( ! function_exists( 'is_amp_endpoint' ) || ! is_amp_endpoint() ) {
			wp_enqueue_script( 'wprm-public' );
		}

		do_action( 'wprm_load_assets' );
	}

	/**
	 * Array for public JS file.
	 *
	 * @since    4.1.0
	 */
	public static function localize_public() {
		return array(
			'endpoints' => array(
				'analytics' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/analytics' ), '/' ),
			),
			'settings' => array(
				'features_comment_ratings' => WPRM_Settings::get( 'features_comment_ratings' ),
				'template_color_comment_rating' => WPRM_Settings::get( 'template_color_comment_rating' ),
				'instruction_media_toggle_default' => WPRM_Settings::get( 'instruction_media_toggle_default' ),
				'video_force_ratio' => WPRM_Settings::get( 'video_force_ratio' ),
				'analytics_enabled' => WPRM_Settings::get( 'analytics_enabled' ),
				'print_new_tab' => WPRM_Settings::get( 'print_new_tab' ),
			),
			'post_id' => get_the_ID(),
			'home_url' => WPRM_Compatibility::get_home_url(),
			'print_slug' => WPRM_Print::slug(),
			'permalinks' => get_option( 'permalink_structure' ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wprm' ),
			'api_nonce' => wp_create_nonce( 'wp_rest' ),
			'translations' => self::get_translations( 'public' ),
		);
	}

	/**
	 * Check if admin assets should get loaded.
	 *
	 * @since    6.7.0
	 */
	public static function should_load_admin_assets() {
		if ( WPRM_Settings::get( 'load_admin_assets_everywhere' ) ) {
			return true;
		}

		$screen = get_current_screen();
		if ( 'toplevel_page_wprecipemaker' === $screen->id
			|| 'wp-recipe-maker' === substr( $screen->id, 0, 15 ) ) {
			return true;
		}

		if ( ( method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) || ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) ) {
			return true;
		}

		if ( false !== strpos( $screen->id, 'wprm' ) ) {
			return true;
		}

		global $pagenow;
		if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow || 'comment.php' === $pagenow || 'edit-comments.php' === $pagenow ) {
			return true;
		}

		return apply_filters( 'wprm_should_load_admin_assets', false );
	}

	/**
	 * Enqueue stylesheets and scripts.
	 *
	 * @since    2.0.0
	 */
	public static function enqueue_admin() {
		if ( ! self::should_load_admin_assets() ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'wprm-admin', WPRM_URL . 'dist/admin.css', array(), WPRM_VERSION, 'all' );

		$screen = get_current_screen();
		if ( 'wp-recipe-maker_page_wprm_settings' === $screen->id ) {
			wp_enqueue_style( 'wprm-admin-settings', WPRM_URL . 'dist/admin-settings.css', array(), WPRM_VERSION, 'all' );
			wp_enqueue_script( 'wprm-admin-settings', WPRM_URL . 'dist/admin-settings.js', array( 'wprm-admin' ), WPRM_VERSION, true );
		}

		if ( 'admin_page_wprm_template_editor' === $screen->id || 'wp-recipe-maker_page_wprm_faq' === $screen->id ) {
			wp_enqueue_media();
			wp_enqueue_style( 'wprm-admin-template', WPRM_URL . 'dist/admin-template.css', array(), WPRM_VERSION, 'all' );
			wp_enqueue_script( 'wprm-admin-template', WPRM_URL . 'dist/admin-template.js', array( 'wprm-admin' ), WPRM_VERSION, true );
		}

		// Load shared JS first.
		wp_enqueue_script( 'wprm-shared', WPRM_URL . 'dist/shared.js', array(), WPRM_VERSION, true );

		// Add Premium JS to dependencies when active.
		$dependencies = array( 'wprm-shared', 'jquery' );
		if ( WPRM_Addons::is_active( 'premium' ) ) {
			$dependencies[] = 'wprmp-admin';
		}
		wp_enqueue_script( 'wprm-admin', WPRM_URL . 'dist/admin.js', $dependencies, WPRM_VERSION, true );

		$wprm_admin = array(
			'wprm_url' => WPRM_URL,
			'home_url' => WPRM_Compatibility::get_home_url(),
			'print_slug' => WPRM_Print::slug(),
			'permalinks' => get_option( 'permalink_structure' ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'manage_url' => admin_url( 'admin.php?page=wprm_manage' ),
			'nonce' => wp_create_nonce( 'wprm' ),
			'api_nonce' => wp_create_nonce( 'wp_rest' ),
			'endpoints' => array(
				'recipe' => rtrim( get_rest_url( null, 'wp/v2/' . WPRM_POST_TYPE ), '/' ),
				'taxonomy' => rtrim( get_rest_url( null, 'wp/v2/wprm_' ), '/' ),
				'manage' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/manage' ), '/' ),
				'modal' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/modal' ), '/' ),
				'notices' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/notice' ), '/' ),
				'analytics' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/analytics' ), '/' ),
				'custom_taxonomies' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/custom-taxonomies' ), '/' ),
				'rating' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/rating' ), '/' ),
				'setting' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/setting' ), '/' ),
				'template' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/template' ), '/' ),
				'utilities' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/utilities' ), '/' ),
				'dashboard' => rtrim( get_rest_url( null, 'wp-recipe-maker/v1/dashboard' ), '/' ),
			),
			'eol' => PHP_EOL,
			'latest_recipes' => WPRM_Recipe_Manager::get_latest_recipes( 20, 'id' ),
			'recipe_templates' => WPRM_Template_Manager::get_templates(),
			'addons' => array(
				'premium' => WPRM_Addons::is_active( 'premium' ),
				'pro' => WPRM_Addons::is_active( 'pro' ),
				'elite' => WPRM_Addons::is_active( 'elite' ),
			),
			'settings' => array(
				'nutrition_default_serving_unit' => WPRM_Settings::get( 'nutrition_default_serving_unit' ),
				'metadata_instruction_name' => WPRM_Settings::get( 'metadata_instruction_name' ),
				'pinterest_use_for_image' => WPRM_Settings::get( 'pinterest_use_for_image' ),
				'features_comment_ratings' => WPRM_Settings::get( 'features_comment_ratings' ),
				'recipe_name_from_post_title' => WPRM_Settings::get( 'recipe_name_from_post_title' ),
				'recipe_use_author' => WPRM_Settings::get( 'recipe_use_author' ),
				'post_type_structure' => WPRM_Settings::get( 'post_type_structure' ),
			),
			'manage' => array(
				'tooltip' => array(
					'recipes' => apply_filters( 'wprm_manage_datatable_tooltip', '<div class="tooltip-header">&nbsp;</div><a href="#" class="wprm-manage-recipes-actions-edit">Edit Recipe</a><a href="#" class="wprm-manage-recipes-actions-delete">Delete Recipe</a>', 'recipes' ),
					'ingredients' => apply_filters( 'wprm_manage_datatable_tooltip', '<div class="tooltip-header">&nbsp;</div><a href="#" class="wprm-manage-ingredients-actions-rename">Rename Ingredient</a><a href="#" class="wprm-manage-ingredients-actions-link">Edit Ingredient Link</a><a href="#" class="wprm-manage-ingredients-actions-merge">Merge into Another Ingredient</a><a href="#" class="wprm-manage-ingredients-actions-delete">Delete Ingredient</a>', 'ingredients' ),
					'taxonomies' => apply_filters( 'wprm_manage_datatable_tooltip', '<div class="tooltip-header">&nbsp;</div><a href="#" class="wprm-manage-taxonomies-actions-rename">Rename Term</a><a href="#" class="wprm-manage-taxonomies-actions-merge">Merge into Another Term</a><a href="#" class="wprm-manage-taxonomies-actions-delete">Delete Term</a>', 'taxonomies' ),
				),
			),
			'translations' => self::get_translations( 'admin' ),
			'text' => array(
				'shortcode_remove' => __( 'Are you sure you want to remove this recipe?', 'wp-recipe-maker' ),
				'nutrition_label_servings' => __( 'Amount per Serving', 'wp-recipe-maker-premium' ),
				'nutrition_label_100g' => __( 'Amount per 100g', 'wp-recipe-maker-premium' ),
			),
		);

		$wprm_admin = apply_filters( 'wprm_localize_admin', $wprm_admin );

		// Shared loads first, so localize then.
		wp_localize_script( 'wprm-shared', 'wprm_admin', $wprm_admin );
	}

	/**
	 * Enqueue Gutenberg block assets.
	 *
	 * @since    2.4.0
	 */
	public static function block_assets() {
		wp_enqueue_style( 'wprm-blocks', WPRM_URL . 'dist/blocks.css', array(), WPRM_VERSION, 'all' );
		wp_enqueue_script( 'wprm-blocks', WPRM_URL . 'dist/blocks.js', array( 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor' ), WPRM_VERSION );
		wp_enqueue_style( 'wprm-public', WPRM_URL . 'dist/public-' . WPRM_Settings::get( 'recipe_template_mode' ) . '.css', array(), WPRM_VERSION, 'all' );
	}

	/**
	 * Get JS translations.
	 *
	 * @since    5.9.0
	 */
	public static function get_translations( $type ) {
		$translations = array();

		switch ( $type ) {
			case 'public':
				break;
			case 'admin':
				include( WPRM_DIR . 'templates/admin/translations.php' );
				break;
			default:
				return $translations;
		}

		return apply_filters( 'wprm_translations_' . $type, $translations );
	}

	/**
	 * Enqueue template style on AMP pages.
	 *
	 * @since    2.1.0
	 */
	public static function amp_style() {
		// Get AMP specific CSS.
		ob_start();
		include( WPRM_DIR . 'dist/amp.css' );
		$css = ob_get_contents();
		ob_end_clean();

		// Get custom recipe styling.
		$css .= ' ' . self::get_custom_css( 'recipe' );

		// Get rid of !important flags.
		$css = str_ireplace( ' !important', '', $css );
		$css = str_ireplace( '!important', '', $css );

		echo $css;
	}

	/**
	 * Add data to pass along to JS.
	 *
	 * @since	8.1.0
	 * @param	mixed $variable	Variable to use in JS.
	 * @param	mixed $data		Data to pass along.
	 */
	public static function add_js_data( $variable, $data ) {
		$variable = sanitize_key( $variable );
		self::$js_data[ $variable ] = $data;
	}

	/**
	 * Output data to pass along to JS.
	 *
	 * @since	8.1.0
	 */
	public static function output_js_data() {
		$js = '';

		foreach ( self::$js_data as $variable => $data ) {
			if ( $data ) {
				$js .= 'var ' . $variable . ' = ' . wp_json_encode( $data ) . ';';
			}
		}

		if ( $js ) {
			echo '<script type="text/javascript">' . $js . '</script>';
		}
	}

	/**
	 * Assets to output in the footer.
	 *
	 * @since    8.0.0
	 */
	public static function footer_assets() {
		if ( apply_filters( 'wprm_load_pinit', false ) ) {
			if ( 'pinitjs' === WPRM_Settings::get( 'pinterest_pin_method' ) ) {
				// Source: https://developers.pinterest.com/docs/add-ons/getting-started/
				echo "<script type=\"text/javascript\">(function (d) {var f = d.getElementsByTagName('SCRIPT')[0],p = d.createElement('SCRIPT');p.type = 'text/javascript';p.async = true;p.src = '//assets.pinterest.com/js/pinit.js';f.parentNode.insertBefore(p, f);})(document);</script>";
			}
		}
	}

	/**
	 * Output custom CSS from the options.
	 *
	 * @since    1.10.0
	 * @param	 mixed $type Type of recipe to output the custom CSS for.
	 */
	public static function custom_css( $type = 'recipe' ) {
		if ( WPRM_Settings::get( 'features_custom_style' ) ) {

			$css = self::get_custom_css( $type );

			if ( $css ) {
				echo '<style type="text/css">' . $css . '</style>';
			}
		}
	}

	/**
	 * Get custom CSS from the options.
	 *
	 * @since    2.1.0
	 * @param	 mixed $type Type of recipe to get the custom CSS for.
	 */
	public static function get_custom_css( $type = 'recipe' ) {
		if ( ! WPRM_Settings::get( 'features_custom_style' ) ) {
			return '';
		}

		$output = '';
		$selector = 'print' === $type ? ' html body.wprm-print' : ' html body .wprm-recipe-container';

		// Layout styling for legacy templates.
		if ( 'legacy' === WPRM_Settings::get( 'recipe_template_mode' ) ) {
			// Recipe Snippets appearance.
			if ( WPRM_Settings::get( 'recipe_snippets_automatically_add' ) ) {
				$output .= ' .wprm-automatic-recipe-snippets a.wprm-jump-to-recipe-shortcode, .wprm-automatic-recipe-snippets a.wprm-jump-to-video-shortcode, .wprm-automatic-recipe-snippets a.wprm-print-recipe-shortcode {';
				$output .= ' background-color: ' . WPRM_Settings::get( 'recipe_snippets_background_color' ) . ';';
				$output .= ' color: ' . WPRM_Settings::get( 'recipe_snippets_text_color' ) . ' !important;';
				$output .= '}';
			}

			// Template Appearance.
			if ( WPRM_Settings::get( 'template_font_size' ) ) {
				$output .= $selector . ' .wprm-recipe { font-size: ' . WPRM_Settings::get( 'template_font_size' ) . 'px; }';
			}
			if ( WPRM_Settings::get( 'template_font_regular' ) ) {
				$output .= $selector . ' .wprm-recipe { font-family: ' . WPRM_Settings::get( 'template_font_regular' ) . '; }';
				$output .= $selector . ' .wprm-recipe p { font-family: ' . WPRM_Settings::get( 'template_font_regular' ) . '; }';
				$output .= $selector . ' .wprm-recipe li { font-family: ' . WPRM_Settings::get( 'template_font_regular' ) . '; }';
			}
			if ( WPRM_Settings::get( 'template_font_header' ) ) {
				$output .= $selector . ' .wprm-recipe .wprm-recipe-name { font-family: ' . WPRM_Settings::get( 'template_font_header' ) . '; }';
				$output .= $selector . ' .wprm-recipe .wprm-recipe-header { font-family: ' . WPRM_Settings::get( 'template_font_header' ) . '; }';
			}

			$output .= $selector . ' { color: ' . WPRM_Settings::get( 'template_color_text' ) . '; }';
			$output .= $selector . ' a.wprm-recipe-print { color: ' . WPRM_Settings::get( 'template_color_text' ) . '; }';
			$output .= $selector . ' a.wprm-recipe-print:hover { color: ' . WPRM_Settings::get( 'template_color_text' ) . '; }';
			$output .= $selector . ' .wprm-recipe { background-color: ' . WPRM_Settings::get( 'template_color_background' ) . '; }';
			$output .= $selector . ' .wprm-recipe { border-color: ' . WPRM_Settings::get( 'template_color_border' ) . '; }';
			$output .= $selector . ' .wprm-recipe-tastefully-simple .wprm-recipe-time-container { border-color: ' . WPRM_Settings::get( 'template_color_border' ) . '; }';
			$output .= $selector . ' .wprm-recipe .wprm-color-border { border-color: ' . WPRM_Settings::get( 'template_color_border' ) . '; }';
			$output .= $selector . ' a { color: ' . WPRM_Settings::get( 'template_color_link' ) . '; }';
			$output .= $selector . ' .wprm-recipe-tastefully-simple .wprm-recipe-name { color: ' . WPRM_Settings::get( 'template_color_header' ) . '; }';
			$output .= $selector . ' .wprm-recipe-tastefully-simple .wprm-recipe-header { color: ' . WPRM_Settings::get( 'template_color_header' ) . '; }';
			$output .= $selector . ' h1 { color: ' . WPRM_Settings::get( 'template_color_header' ) . '; }';
			$output .= $selector . ' h2 { color: ' . WPRM_Settings::get( 'template_color_header' ) . '; }';
			$output .= $selector . ' h3 { color: ' . WPRM_Settings::get( 'template_color_header' ) . '; }';
			$output .= $selector . ' h4 { color: ' . WPRM_Settings::get( 'template_color_header' ) . '; }';
			$output .= $selector . ' h5 { color: ' . WPRM_Settings::get( 'template_color_header' ) . '; }';
			$output .= $selector . ' h6 { color: ' . WPRM_Settings::get( 'template_color_header' ) . '; }';
			$output .= $selector . ' svg path { fill: ' . WPRM_Settings::get( 'template_color_icon' ) . '; }';
			$output .= $selector . ' svg rect { fill: ' . WPRM_Settings::get( 'template_color_icon' ) . '; }';
			$output .= $selector . ' svg polygon { stroke: ' . WPRM_Settings::get( 'template_color_icon' ) . '; }';
			$output .= $selector . ' .wprm-rating-star-full svg polygon { fill: ' . WPRM_Settings::get( 'template_color_icon' ) . '; }';
			$output .= $selector . ' .wprm-recipe .wprm-color-accent { background-color: ' . WPRM_Settings::get( 'template_color_accent' ) . '; }';
			$output .= $selector . ' .wprm-recipe .wprm-color-accent { color: ' . WPRM_Settings::get( 'template_color_accent_text' ) . '; }';
			$output .= $selector . ' .wprm-recipe .wprm-color-accent a.wprm-recipe-print { color: ' . WPRM_Settings::get( 'template_color_accent_text' ) . '; }';
			$output .= $selector . ' .wprm-recipe .wprm-color-accent a.wprm-recipe-print:hover { color: ' . WPRM_Settings::get( 'template_color_accent_text' ) . '; }';
			$output .= $selector . ' .wprm-recipe-colorful .wprm-recipe-header { background-color: ' . WPRM_Settings::get( 'template_color_accent' ) . '; }';
			$output .= $selector . ' .wprm-recipe-colorful .wprm-recipe-header { color: ' . WPRM_Settings::get( 'template_color_accent_text' ) . '; }';
			$output .= $selector . ' .wprm-recipe-colorful .wprm-recipe-meta > div { background-color: ' . WPRM_Settings::get( 'template_color_accent2' ) . '; }';
			$output .= $selector . ' .wprm-recipe-colorful .wprm-recipe-meta > div { color: ' . WPRM_Settings::get( 'template_color_accent2_text' ) . '; }';
			$output .= $selector . ' .wprm-recipe-colorful .wprm-recipe-meta > div a.wprm-recipe-print { color: ' . WPRM_Settings::get( 'template_color_accent2_text' ) . '; }';
			$output .= $selector . ' .wprm-recipe-colorful .wprm-recipe-meta > div a.wprm-recipe-print:hover { color: ' . WPRM_Settings::get( 'template_color_accent2_text' ) . '; }';

			// Rating stars outside recipe box.
			$output .= ' .wprm-rating-star svg polygon { stroke: ' . WPRM_Settings::get( 'template_color_icon' ) . '; }';
			$output .= ' .wprm-rating-star.wprm-rating-star-full svg polygon { fill: ' . WPRM_Settings::get( 'template_color_icon' ) . '; }';

			// Instruction image alignment.
			$output .= $selector . ' .wprm-recipe-instruction-image { text-align: ' . WPRM_Settings::get( 'template_instruction_image_alignment' ) . '; }';

			// List style.
			if ( 'checkbox' === WPRM_Settings::get( 'template_ingredient_list_style' ) ) {
				$output .= $selector . ' li.wprm-recipe-ingredient { list-style-type: none; }';
			} else {
				$output .= $selector . ' li.wprm-recipe-ingredient { list-style-type: ' . WPRM_Settings::get( 'template_ingredient_list_style' ) . '; }';
			}
			if ( 'checkbox' === WPRM_Settings::get( 'template_instruction_list_style' ) ) {
				$output .= $selector . ' li.wprm-recipe-instruction { list-style-type: none; }';
			} else {
				$output .= $selector . ' li.wprm-recipe-instruction { list-style-type: ' . WPRM_Settings::get( 'template_instruction_list_style' ) . '; }';
			}
		}

		// Comment ratings.
		$output .= ' .wprm-comment-rating svg { width: ' . WPRM_Settings::get( 'comment_rating_star_size' ) . 'px !important; height: ' . WPRM_Settings::get( 'comment_rating_star_size' ) . 'px !important; }';
		$output .= ' img.wprm-comment-rating { width: ' . ( 5 * WPRM_Settings::get( 'comment_rating_star_size' ) ) . 'px !important; height: ' . WPRM_Settings::get( 'comment_rating_star_size' ) . 'px !important; }';
		$output .= ' .wprm-comment-rating svg path { fill: ' . WPRM_Settings::get( 'template_color_comment_rating' ) . '; }';
		$output .= ' .wprm-comment-rating svg polygon { stroke: ' . WPRM_Settings::get( 'template_color_comment_rating' ) . '; }';
		
		$output .= ' .wprm-comment-ratings-container svg .wprm-star-full { fill: ' . WPRM_Settings::get( 'template_color_comment_rating' ) . '; }';
		$output .= ' .wprm-comment-ratings-container svg .wprm-star-empty { stroke: ' . WPRM_Settings::get( 'template_color_comment_rating' ) . '; }';

		// Allow add-ons to hook in.
		$output = apply_filters( 'wprm_custom_css', $output, $type, $selector );

		// Custom recipe CSS.
		if ( 'print' !== $type ) {
			$output .= WPRM_Settings::get( 'recipe_css' );
		}

		return $output;
	}
}

WPRM_Assets::init();
