<?php
/**
 * Handle compabitility with other plugins/themes.
 *
 * @link       https://bootstrapped.ventures
 * @since      3.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle compabitility with other plugins/themes.
 *
 * @since      3.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Compatibility {

	/**
	 * Track whether the Divi 5 integration has been initialized.
	 *
	 * @var bool
	 */
	private static $divi5_initialized = false;

	/**
	 * Track whether we've localized Divi 5 builder data.
	 *
	 * @var bool
	 */
	private static $divi5_builder_data_localized = false;

	/**
	 * Track the language newly created WPRM terms should use.
	 *
	 * @var false|string
	 */
	private static $new_term_language = false;

	/**
	 * Register actions and filters.
	 *
	 * @since	3.2.0
	 */
	public static function init() {
		// Shortcode block in query loop.
		add_filter( 'render_block', array( __CLASS__, 'shortcode_block_query_loop' ), 10, 2 );

		// Rank Math.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'rank_math' ) );

		// Yoast SEO.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'yoast_seo' ) );
		add_filter( 'wpseo_video_index_content', array( __CLASS__, 'yoast_video_seo' ) );

		// Caching plugins.
		add_filter( 'litespeed_optimize_js_excludes', array( __CLASS__, 'cache_js_excludes' ) );
		add_filter( 'rocket_exclude_js', array( __CLASS__, 'cache_js_excludes' ) );
		add_filter( 'wp-optimize-minify-default-exclusions', array( __CLASS__, 'cache_js_excludes' ) );
		add_filter( 'perfmatters_minify_js_exclusions', array( __CLASS__, 'cache_js_excludes' ) );
		add_filter( 'sgo_js_minify_exclude', array( __CLASS__, 'cache_js_excludes' ) );
		add_filter( 'js_do_concat', array( __CLASS__, 'jetpack_boost_exclude' ), 10, 2 );

		// Jupiter.
		add_action( 'wp_footer', array( __CLASS__, 'jupiter_assets' ) );

		// Emeals.
		add_filter( 'wprm_recipe_ingredients_shortcode', array( __CLASS__, 'emeals_after_ingredients' ), 9 );
		add_action( 'wp_footer', array( __CLASS__, 'emeals_assets' ) );

		// Smart With Food.
		add_filter( 'wprm_recipe_ingredients_shortcode', array( __CLASS__, 'smartwithfood_after_ingredients' ), 9 );
		add_action( 'wp_footer', array( __CLASS__, 'smartwithfood_assets' ) );

		// Chicory.
		add_filter( 'wprm_recipe_ingredients_shortcode', array( __CLASS__, 'chicory_after_ingredients' ), 9 );
		add_action( 'wp_footer', array( __CLASS__, 'chicory_assets' ) );

		// My Shopping Help.
		add_filter( 'wprm_recipe_ingredients_shortcode', array( __CLASS__, 'myshoppinghelp_after_ingredients' ), 9 );

		// Divi.
		add_action( 'divi_extensions_init', array( __CLASS__, 'divi' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'divi_assets' ) );
		add_action( 'init', array( __CLASS__, 'divi5_init' ) );
		add_action( 'divi_visual_builder_assets_before_enqueue_scripts', array( __CLASS__, 'divi5_vb_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'divi5_frontend_assets' ) );

		// Elementor.
		add_action( 'elementor/editor/before_enqueue_scripts', array( __CLASS__, 'elementor_assets' ) );
		add_action( 'elementor/controls/register', array( __CLASS__, 'elementor_controls' ) );
		add_action( 'elementor/preview/enqueue_styles', array( __CLASS__, 'elementor_styles' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'elementor_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'elementor_categories' ) );
		add_action( 'ECS_after_render_post_footer', array( __CLASS__, 'wpupg_unset_recipe_id' ) );

		// WP Ultimate Post Grid.
		add_filter( 'wpupg_output_grid_post', array( __CLASS__, 'wpupg_set_recipe_id_legacy' ) );
		add_filter( 'wpupg_term_name', array( __CLASS__, 'wpupg_term_name' ), 10, 3 );

		add_filter( 'wpupg_set_current_item', array( __CLASS__, 'wpupg_set_recipe_id' ) );
		add_filter( 'wpupg_unset_current_item', array( __CLASS__, 'wpupg_unset_recipe_id' ) );
		add_filter( 'wpupg_template_editor_shortcodes', array( __CLASS__, 'wpupg_template_editor_shortcodes' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'wpupg_template_editor_styles' ) );

		// WP Ultimate Post Grid & WP Extended Search combination.
		add_filter( 'wpes_post_types', array( __CLASS__, 'wpupg_extended_search_post_types' ) );
		add_filter( 'wpes_tax', array( __CLASS__, 'wpupg_extended_search_taxonomies' ) );

		// Track created terms for multilingual plugins.
		add_action( 'created_term', array( __CLASS__, 'maybe_set_new_term_language' ), 10, 3 );
	}

	/**
	 * Render WPRM shortcode for query loop compatibility.
	 *
	 * @since	10.0.0
	 * @param	string $block_content	Current block content.
	 * @param	array $block			Current block.
	 */
	public static function shortcode_block_query_loop( $block_content, $block ) {
		if ( $block['blockName'] === 'core/shortcode' ) {
			// Check if it contains "[wprm-"
			if ( strpos( $block_content, '[wprm-' ) !== false ) {
				return do_shortcode( $block_content );
			}
		}
		return $block_content;
	}

	/**
	 * Yoast SEO Compatibility.
	 *
	 * @since	3.2.0
	 */
	public static function yoast_seo() {
		if ( defined( 'WPSEO_VERSION' ) ) {
			wp_enqueue_script( 'wprm-yoast-compatibility', WPRM_URL . 'assets/js/other/yoast-compatibility.js', array( 'jquery' ), WPRM_VERSION, true );
		}
	}

	/**
	 * Yoast Video SEO Compatibility.
	 *
	 * @since	7.2.0
	 */
	public static function yoast_video_seo( $post_content ) {
		$recipes = WPRM_Recipe_Manager::get_recipe_ids_from_content( $post_content );

		if ( $recipes ) {
			foreach ( $recipes as $recipe_id ) {
				$recipe_id = intval( $recipe_id );
				$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

				if ( $recipe ) { 
					// This makes sure recipes are parsed and their videos are included.
					$post_content .= ' ' . do_shortcode( '[wprm-recipe id="' . $recipe_id . '"]' );
					$post_content .= ' ' . $recipe->video_embed();
				}
			}
		}

		return $post_content;
	}

	/**
	 * Caching plugin compatibility.
	 *
	 * @since	9.7.0
	 */
	public static function cache_js_excludes( $excludes ) {
		if ( WPRM_Settings::get( 'assets_prevent_caching_optimization' ) && is_array( $excludes ) ) {
			$excludes[] = 'wp-recipe-maker/dist/public-modern.js';
			if ( defined( 'WPRMP_BUNDLE' ) ) {
				$excludes[] = 'wp-recipe-maker-premium/dist/public-' . strtolower( WPRMP_BUNDLE ) . '.js';
			}
		}

		return $excludes;
	}

/**
  * Excludes WPRM assets from Jetpack Boost's concatenation.
  *
  * @param bool   $do_concat Whether Jetpack Boost should concatenate the asset.
  * @param string $handle    The handle of the JavaScript asset.
  * @return bool  Whether the asset should be excluded from concatenation.
  */
 public static function jetpack_boost_exclude( $do_concat, $handle ) {
		if ( WPRM_Settings::get( 'assets_prevent_caching_optimization' ) ) {
			if ( 'wprm-public' === $handle || 'wprmp-public' === $handle ) {
				$do_concat = false;
			}
		}

		return $do_concat;
	}

	/**
	 * Rank Math Compatibility.
	 *
	 * @since	6.6.0
	 */
	public static function rank_math() {
		// wp_enqueue_script( 'wprm-rank-math-compatibility', WPRM_URL . 'assets/js/other/rank-math-compatibility.js', array( 'wp-hooks', 'rank-math-analyzer' ), WPRM_VERSION, true );
	}

	/**
	 * Divi Builder Compatibility.
	 *
	 * @since	5.1.0
	 */
	public static function divi() {
		require_once( WPRM_DIR . 'templates/divi/includes/extension.php' );
	}

	/**
	 * Divi Builder assets.
	 *
	 * @since	9.7.0
	 */
	public static function divi_assets() {
		if ( isset( $_GET['et_fb'] ) && '1' === $_GET['et_fb'] ) {
			WPRM_Assets::load();
		}
	}

	/**
	 * Determine if Divi 5 is active.
	 *
	 * @return bool
	 */
	private static function is_divi5_enabled() {
		return function_exists( 'et_builder_d5_enabled' ) && et_builder_d5_enabled();
	}

	/**
	 * Bootstrap Divi 5 module registration.
	 */
	public static function divi5_init() {
		if ( self::$divi5_initialized || ! self::is_divi5_enabled() ) {
			return;
		}

		if ( ! defined( 'WPRM_DIVI5_PATH' ) ) {
			define( 'WPRM_DIVI5_PATH', WPRM_DIR . 'templates/divi5/' );
			define( 'WPRM_DIVI5_URL', WPRM_URL . 'templates/divi5/' );
			define( 'WPRM_DIVI5_JSON_PATH', WPRM_DIVI5_PATH . 'modules-json/' );
		}

		$modules_bootstrap = WPRM_DIVI5_PATH . 'modules/Modules.php';

		if ( file_exists( $modules_bootstrap ) ) {
			require_once $modules_bootstrap;
		}

		self::$divi5_initialized = true;
	}

	/**
	 * Enqueue Divi 5 Visual Builder assets.
	 */
	public static function divi5_vb_assets() {
		if ( ! self::is_divi5_enabled() || ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
			return;
		}

		if ( ! class_exists( '\\ET\\Builder\\VisualBuilder\\Assets\\PackageBuildManager' ) ) {
			return;
		}

		self::divi5_init();

		// Ensure the recipe selection modal assets are available inside the builder iframe.
		if ( ! class_exists( 'WPRM_Modal' ) ) {
			require_once WPRM_DIR . 'includes/admin/class-wprm-modal.php';
		}

		if ( ! class_exists( 'WPRM_Assets' ) ) {
			require_once WPRM_DIR . 'includes/class-wprm-assets.php';
		}

		// Force admin assets to load for Divi 5.
		add_filter( 'wprm_should_load_admin_assets', '__return_true' );

		$GLOBALS['wprm_divi5_context'] = true;
		WPRM_Modal::add_modal_content();
		unset( $GLOBALS['wprm_divi5_context'] );
		
		WPRM_Assets::enqueue_admin();
		WPRM_Modal::enqueue();

		if ( ! class_exists( 'WPRMP_Assets' ) && defined( 'WPRMP_DIR' ) && file_exists( WPRMP_DIR . 'includes/class-wprmp-assets.php' ) ) {
			require_once WPRMP_DIR . 'includes/class-wprmp-assets.php';
		}

		if ( class_exists( 'WPRMP_Assets' ) ) {
			WPRMP_Assets::enqueue_admin();
		}

		remove_filter( 'wprm_should_load_admin_assets', '__return_true' );

		$base_url = defined( 'WPRM_DIVI5_URL' ) ? WPRM_DIVI5_URL : WPRM_URL . 'templates/divi5/';

		\ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
			array(
				'name'    => 'wprm-divi5-builder-bundle-script',
				'version' => class_exists( 'WPRM_Debug' ) && WPRM_Debug::debugging() ? time() : WPRM_VERSION,
				'script'  => array(
					'src'                => $base_url . 'scripts/bundle.js',
					'deps'               => array(
						'divi-module-library',
						'divi-vendor-wp-hooks',
					),
					'enqueue_top_window' => false,
					'enqueue_app_window' => true,
				),
			)
		);

		\ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
			array(
				'name'   => 'wprm-divi5-builder-style',
				'version'=> class_exists( 'WPRM_Debug' ) && WPRM_Debug::debugging() ? time() : WPRM_VERSION,
				'style'  => array(
					'src'                => $base_url . 'styles/vb-bundle.css',
					'deps'               => array(),
					'enqueue_top_window' => false,
					'enqueue_app_window' => true,
				),
			)
		);

		if ( ! self::$divi5_builder_data_localized ) {
			$builder_data = array(
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'endpoints' => array(
					'preview' => trailingslashit( rest_url( 'wp-recipe-maker/v1/utilities/preview' ) ),
				),
				'latestRecipes' => WPRM_Recipe_Manager::get_latest_recipes( 20, 'id' ),
			);

			$inline_script = 'window.WPRMDivi5Data = ' . wp_json_encode( $builder_data ) . ';';

			wp_add_inline_script(
				'divi-module-library',
				$inline_script,
				'before'
			);

			wp_add_inline_script(
				'divi-module-library',
				"(function() { if (typeof window !== 'undefined' && !window.WPRMDivi5Data) { " . $inline_script . " } })();",
				'after'
			);

			self::$divi5_builder_data_localized = true;
		}
	}

	/**
	 * Load Divi 5 front-end styles.
	 */
	public static function divi5_frontend_assets() {
		if ( ! self::is_divi5_enabled() ) {
			return;
		}

		self::divi5_init();

		$style_url = defined( 'WPRM_DIVI5_URL' ) ? WPRM_DIVI5_URL : WPRM_URL . 'templates/divi5/';

		wp_enqueue_style( 'wprm-divi5-modules', $style_url . 'styles/bundle.css', array(), WPRM_VERSION );
	}


	/**
	 * Elementor Compatibility.
	 *
	 * @since	5.0.0
	 */
	public static function elementor_assets() {
		WPRM_Modal::add_modal_content();
		WPRM_Assets::enqueue_admin();
		WPRM_Modal::enqueue();

		if ( class_exists( 'WPRMP_Assets' ) ) {
			WPRMP_Assets::enqueue_admin();
		}

		wp_enqueue_script( 'wprm-admin-elementor', WPRM_URL . 'assets/js/other/elementor.js', array( 'wprm-admin', 'wprm-admin-modal' ), WPRM_VERSION, true );
	}
	public static function elementor_controls( $controls_manager ) {
		include( WPRM_DIR . 'templates/elementor/control.php' );
		include( WPRM_DIR . 'templates/elementor/control-list.php' );

		$controls_manager->register( new WPRM_Elementor_Control() );
		$controls_manager->register( new WPRM_Elementor_Control_List() );
	}
	public static function elementor_styles() {
		// Make sure default assets load.
		WPRM_Assets::load();
	}
	public static function elementor_widgets( $widgets_manager ) {
		include( WPRM_DIR . 'templates/elementor/widget-recipe.php' );
		include( WPRM_DIR . 'templates/elementor/widget-list.php' );
		include( WPRM_DIR . 'templates/elementor/widget-roundup.php' );

		$widgets_manager->register( new WPRM_Elementor_Recipe_Widget() );
		$widgets_manager->register( new WPRM_Elementor_List_Widget() );
		$widgets_manager->register( new WPRM_Elementor_Roundup_Widget() );
	}

	/**
	 * Add custom widget categories to Elementor.
	 *
	 * @since 8.6.0
	 */
	public static function elementor_categories( $elements_manager ) {
		$elements_manager->add_category(
			'wp-recipe-maker',
			array(
				'title' => __( 'WP Recipe Maker', 'wp-recipe-maker' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Recipes in WP Ultimate Post Grid Compatibility (after 3.0.0).
	 *
	 * @since	5.9.0
	 * @param	mixed $post Post getting shown in the grid.
	 */
	public static function wpupg_set_recipe_id( $item ) {
		if ( WPRM_POST_TYPE === $item->post_type() ) {
			WPRM_Template_Shortcodes::set_current_recipe_id( $item->id() );
		} else {
			$recipes = WPRM_Recipe_Manager::get_recipe_ids_from_post( $item->id() );

			if ( isset( $recipes[0] ) ) {
				WPRM_Template_Shortcodes::set_current_recipe_id( $recipes[0] );
			}
		}

		return $item;
	}
	public static function wpupg_unset_recipe_id( $item ) {
		WPRM_Template_Shortcodes::set_current_recipe_id( false );
		return $item;
	}

	/**
	 * Recipes in WP Ultimate Post Grid Compatibility (before 3.0.0).
	 *
	 * @since	4.2.0
	 * @param	mixed $post Post getting shown in the grid.
	 */
	public static function wpupg_set_recipe_id_legacy( $post ) {
		if ( WPRM_POST_TYPE === $post->post_type ) {
			WPRM_Template_Shortcodes::set_current_recipe_id( $post->ID );
		}

		return $post;
	}

	/**
	 * Alter term names in WP Ultimate Post Grid.
	 *
	 * @since	7.3.0
	 * @param	mixed $name Name for the term.
	 * @param	mixed $id Term ID.
	 * @param	mixed $taxonomy Taxonomy of the term.
	 */
	public static function wpupg_term_name( $name, $id, $taxonomy ) {
		if ( 'wprm_suitablefordiet' === $taxonomy ) {
			$diet_label = get_term_meta( $id, 'wprm_term_label', true );
			
			if ( $diet_label ) {
				$name = $diet_label;
			}
		}

		return $name;
	}

	/**
	 * Add recipe shortcodes to grid template editor.
	 *
	 * @since	5.9.0
	 * @param	mixed $shortcodes Current template editor shortcodes.
	 */
	public static function wpupg_template_editor_shortcodes( $shortcodes ) {
		$shortcodes = array_merge( $shortcodes, WPRM_Template_Shortcodes::get_shortcodes() );
		return $shortcodes;
	}
	
	/**
	 * Add recipe shortcode styles to grid template editor.
	 *
	 * @since	5.9.0
	 */
	public static function wpupg_template_editor_styles( $shortcodes ) {
		$screen = get_current_screen();
		if ( 'grids_page_wpupg_template_editor' === $screen->id  ) {
			wp_enqueue_style( 'wprm-admin-template', WPRM_URL . 'dist/admin-template.css', array(), WPRM_VERSION, 'all' );
		}
	}
	
	/**
	 * Compatibility with WP Extended Search when WP Ultimate Post Grid is activated.
	 *
	 * @since	8.0.0
	 */
	public static function wpupg_extended_search_post_types( $post_types ) {
		if ( class_exists( 'WP_Ultimate_Post_Grid' ) ) {
			$post_types[ WPRM_POST_TYPE ] = get_post_type_object( WPRM_POST_TYPE );
		}

		return $post_types;
	}

	/**
	 * Compatibility with WP Extended Search when WP Ultimate Post Grid is activated.
	 *
	 * @since	8.0.0
	 */
	public static function wpupg_extended_search_taxonomies( $taxonomies ) {
		if ( class_exists( 'WP_Ultimate_Post_Grid' ) ) {
			$wprm_taxonomies = get_object_taxonomies( WPRM_POST_TYPE, 'objects' );
			$taxonomies = array_merge( $taxonomies, $wprm_taxonomies );
		}

		return $taxonomies;
	}

	/**
	 * Jupiter assets in footer.
	 *
	 * @since    8.2.0
	 */
	public static function jupiter_assets() {
		if ( WPRM_Settings::get( 'integration_jupiter' ) ) {
			$data = '';

			// Jupiter Handle, empty by default.
			$handle = trim( WPRM_Settings::get( 'jupiter_handle' ) );
			$data .= ' data-handle="' . esc_attr( $handle ) . '"';

			// Options.
			$data .= WPRM_Settings::get( 'jupiter_print_button' ) ? ' data-enable-print-link="true"' : ' data-enable-print-link="false"';
			$data .= WPRM_Settings::get( 'jupiter_shop_ingredients_button' ) ? ' data-enable-shop-ingredients-button="true"' : ' data-enable-shop-ingredients-button="false"';

			echo '<script defer src="https://scripts.jupiter.shop/wp-recipe-maker/bundle.min.js"' . $data . '></script>';
		}
	}

	/**
	 * Add eMeals Walmart button after the ingredients.
	 *
	 * @since	9.4.0
	 * @param	mixed $output Current ingredients output.
	 */
	public static function emeals_after_ingredients( $output ) {
		if ( WPRM_Settings::get( 'emeals_walmart_button' ) ) {
			$output = $output . do_shortcode( '[wprm-spacer][wprm-recipe-emeals]' );
		}

		return $output;
	}
	
	/**
	 * Emeals assets in footer.
	 *
	 * @since    9.0.0
	 */
	public static function emeals_assets() {
		if ( apply_filters( 'wprm_load_emeals', false ) ) {
			// Optional partner ID.
			$partner_id = WPRM_Settings::get( 'emeals_partner_id' );

			if ( ! $partner_id ) {
				$partner_id = 'wprecipemaker';
			}
			
			echo '<script type="text/javascript" src="https://emeals.com/shopping/button/bundle.min.js" partner="' . esc_attr( $partner_id ) . '"></script>';
		}
	}

	/**
	 * Add Smart with Food button after the ingredients.
	 *
	 * @since	8.10.0
	 * @param	mixed $output Current ingredients output.
	 */
	public static function smartwithfood_after_ingredients( $output ) {
		if ( WPRM_Settings::get( 'integration_smartwithfood_token' ) && WPRM_Settings::get( 'integration_smartwithfood' ) ) {
			$output = $output . do_shortcode( '[wprm-spacer][wprm-recipe-smart-with-food]' );
		}

		return $output;
	}

	/**
	 * Smart with Food assets in footer.
	 *
	 * @since    8.10.0
	 */
	public static function smartwithfood_assets() {
		if ( apply_filters( 'wprm_load_smartwithfood', false ) ) {
			// Make sure to only load JS if a token is set up.
			if ( WPRM_Settings::get( 'integration_smartwithfood_token' ) ) {
				echo '<script src="https://unpkg.com/@smartwithfood/js-sdk@2.0.0/dist/index.min.js"></script>';
				echo '<script src="' . WPRM_URL . 'assets/js/other/smart-with-food.js?ver=' . WPRM_VERSION .'"></script>';
				echo '<script>window.wprm_smartwithfood_token = "' . esc_attr( WPRM_Settings::get( 'integration_smartwithfood_token' ) ).'";</script>';
			}
		}
	}

	/**
	 * Add Chicory button after the ingredients.
	 *
	 * @since	9.3.0
	 * @param	mixed $output Current ingredients output.
	 */
	public static function chicory_after_ingredients( $output ) {
		if ( WPRM_Settings::get( 'integration_chicory_activate' ) && WPRM_Settings::get( 'integration_chicory_shoppable_button' ) ) {
			$output = $output . do_shortcode( '[wprm-spacer][wprm-recipe-chicory]' );
		}

		return $output;
	}

	/**
	 * Chicory assets in footer.
	 *
	 * @since    9.3.0
	 */
	public static function chicory_assets() {
		if ( apply_filters( 'wprm_load_chicory', false ) ) {
			// Make sure to only load JS if they actually agree to the terms.
			if ( WPRM_Settings::get( 'integration_chicory_activate' ) ) {
				$ads_enabled = WPRM_Settings::get( 'integration_chicory_premium_ads' );
				$chicory_config = array(
					'desktop' => array(
						'pairingAdsEnabled' => $ads_enabled,
						'inlineAdsEnabled' => $ads_enabled,
						'inlineAdsRefresh' => $ads_enabled,
						'pairingAdsRefresh' => $ads_enabled,
					),
					'mobile' => array(
						'pairingAdsEnabled' => $ads_enabled,
						'inlineAdsEnabled' => $ads_enabled,
						'inlineAdsRefresh' => $ads_enabled,
						'pairingAdsRefresh' => $ads_enabled,
					),
				);

				echo '<script>window.ChicoryConfig = ' . json_encode( $chicory_config ) . ';</script>';
				echo '<script defer src="//www.chicoryapp.com/widget_v2/"></script>';
			}
		}
	}

	/**
	 * Add My Shopping Help button after the ingredients.
	 *
	 * @since	10.3.0
	 * @param	mixed $output Current ingredients output.
	 */
	public static function myshoppinghelp_after_ingredients( $output ) {
		if ( WPRM_Settings::get( 'integration_myshoppinghelp_add' ) && class_exists( 'Nakko\Msh\MshPlugin' ) ) {
			$output = $output . do_shortcode( '[wprm-spacer][wprm-recipe-my-shopping-help]' );
		}

		return $output;
	}

	/**
	 * Check if and what multilingual plugin is getting used.
	 *
	 * @since	6.9.0
	 */
	public static function multilingual() {
		$plugin = false;
		$languages = array();
		$current_language = false;
		$default_language = false;

		// WPML.
		$wpml_languages = apply_filters( 'wpml_active_languages', false );

		if ( $wpml_languages ) {
			$plugin = 'wpml';

			foreach ( $wpml_languages as $code => $options ) {
				$languages[ $code ] = array(
					'value' => $code,
					'label' => $options['native_name'],
				);
			}

			$current_language = ICL_LANGUAGE_CODE;
			$default_language = apply_filters( 'wpml_default_language', false );
		}

		// Polylang.
		if ( function_exists( 'pll_home_url' ) ) {
			$plugin = 'polylang';
			$slugs = pll_languages_list( array(
				'fields' => 'slug',
			) );

			$names = pll_languages_list( array(
				'fields' => 'name',
			) );

			$languages = array();
			foreach ( $slugs as $index => $slug ) {
				$languages[ $slug ] = array(
					'value' => $slug,
					'label' => isset( $names[ $index ] ) ? $names[ $index ] : $slug,
				);
			}

			if ( function_exists( 'pll_current_language' ) ) {
				$current_language = pll_current_language( 'slug' );
			}

			if ( function_exists( 'pll_default_language' ) ) {
				$default_language = pll_default_language( 'slug' );
			}
		}

		// Return either false (no multilingual plugin) or an array with the plugin and activated languages.
		return ! $plugin ? false : array(
			'plugin' => $plugin,
			'languages' => $languages,
			'current' => $current_language,
			'default' => $default_language,
		);
	}

	/**
	 * Get the language of a specific post ID.
	 *
	 * @since	7.7.0
	 * @param	int $post_id Post ID to get the language for.
	 */
	public static function get_language_for( $post_id ) {
		$language = false;

		if ( $post_id ) {
			$multilingual = self::multilingual();

			if ( $multilingual ) {
				// WPML.
				if ( 'wpml' === $multilingual['plugin'] ) {
					$wpml = apply_filters( 'wpml_post_language_details', false, $post_id );

					if ( $wpml && is_array( $wpml ) ) {
						$language = $wpml['language_code'];
					}
				}

				// Polylang.
				if ( 'polylang' === $multilingual['plugin'] ) {
					$polylang = pll_get_post_language( $post_id, 'slug' );

					if ( $polylang && ! is_wp_error( $polylang ) ) {
						$language = $polylang;
					}
				}
			}
		}

		// Use false instead of null.
		if ( ! $language ) {
			$language = false;
		}

		return $language;
	}

	/**
	 * Set the language for a specific recipe ID.
	 *
	 * @since	7.7.0
	 * @param	int 	$recipe_id	Recipe ID to set the language for.
	 * @param	mixed 	$language	Language to set the recipe to.
	 */
	public static function set_language_for( $recipe_id, $language ) {
		if ( $recipe_id ) {
			$multilingual = self::multilingual();

			if ( $multilingual ) {
				// WPML.
				if ( 'wpml' === $multilingual['plugin'] ) {
					$element_type = 'post_' . WPRM_POST_TYPE;
					$translation_group_id = apply_filters( 'wpml_element_trid', NULL, $recipe_id, $element_type );

					do_action( 'wpml_set_element_language_details', array(
						'element_id' => $recipe_id,
						'trid' => $translation_group_id ? $translation_group_id : false,
						'element_type' => $element_type,
						'language_code' => $language ? $language : null,
					) );
				}

				// Polylang.
				if ( 'polylang' === $multilingual['plugin'] && function_exists( 'pll_set_post_language' ) ) {
					pll_set_post_language( $recipe_id, $language );
				}
			}
		}
	}

	/**
	 * Set the language for a specific term ID.
	 *
	 * @since	10.8.0
	 * @param	int         $term_id   Term ID to set the language for.
	 * @param	string      $taxonomy  Taxonomy the term belongs to.
	 * @param	false|mixed $language  Language code to set.
	 */
	public static function set_term_language( $term_id, $taxonomy, $language ) {
		$multilingual = self::multilingual();

		if ( ! $term_id || ! $taxonomy || ! $multilingual || ! $language ) {
			return false;
		}

		$language = sanitize_key( $language );

		// WPML.
		if ( 'wpml' === $multilingual['plugin'] ) {
			$element_type = 'tax_' . $taxonomy;
			$translation_group_id = apply_filters( 'wpml_element_trid', null, $term_id, $element_type );

			do_action( 'wpml_set_element_language_details', array(
				'element_id'   => $term_id,
				'trid'         => $translation_group_id ? $translation_group_id : false,
				'element_type' => $element_type,
				'language_code'=> $language,
			) );

			return true;
		}

		// Polylang.
		if ( 'polylang' === $multilingual['plugin'] && function_exists( 'pll_set_term_language' ) ) {
			pll_set_term_language( $term_id, $language );
			return true;
		}

		return false;
	}

	/**
	 * Get the language for a specific term ID.
	 *
	 * @since	10.8.0
	 * @param	int    $term_id  Term ID to get the language for.
	 * @param	string $taxonomy Taxonomy for the term.
	 */
	public static function get_term_language( $term_id, $taxonomy = '' ) {
		$multilingual = self::multilingual();

		if ( ! $term_id || ! $multilingual ) {
			return false;
		}

		// WPML.
		if ( 'wpml' === $multilingual['plugin'] ) {
			$element_type = 'tax_' . ( $taxonomy ? $taxonomy : 'term' );
			$details = apply_filters( 'wpml_element_language_details', null, array(
				'element_id'   => $term_id,
				'element_type' => $element_type,
			) );

			if ( $details ) {
				// WPML may return an array or stdClass depending on version.
				if ( is_object( $details ) ) {
					$details = (array) $details;
				}

				if ( is_array( $details ) && isset( $details['language_code'] ) ) {
					return $details['language_code'];
				}
			}
		}

		// Polylang.
		if ( 'polylang' === $multilingual['plugin'] && function_exists( 'pll_get_term_language' ) ) {
			$language = pll_get_term_language( $term_id, 'slug' );

			if ( $language && ! is_wp_error( $language ) ) {
				return $language;
			}
		}

		return false;
	}

	/**
	 * Get the current admin language.
	 *
	 * @since	10.8.0
	 */
	public static function get_current_admin_language() {
		$multilingual = self::multilingual();

		if ( ! $multilingual ) {
			return false;
		}

		// WPML.
		if ( 'wpml' === $multilingual['plugin'] ) {
			$current = apply_filters( 'wpml_current_language', null );

			if ( ! $current && defined( 'ICL_LANGUAGE_CODE' ) ) {
				$current = ICL_LANGUAGE_CODE;
			}

			return $current ? sanitize_key( $current ) : false;
		}

		// Polylang.
		if ( 'polylang' === $multilingual['plugin'] && function_exists( 'pll_current_language' ) ) {
			$current = pll_current_language( 'slug' );
			return $current ? sanitize_key( $current ) : false;
		}

		return false;
	}

	/**
	 * Set the language context for newly created terms.
	 *
	 * @since	10.8.0
	 * @param	false|string $language Language to assign to new terms.
	 */
	public static function set_new_term_language_context( $language ) {
		self::$new_term_language = $language ? sanitize_key( $language ) : false;
	}

	/**
	 * Reset the language context for newly created terms.
	 *
	 * @since	10.8.0
	 */
	public static function clear_new_term_language_context() {
		self::$new_term_language = false;
	}

	/**
	 * Maybe set the language whenever a term gets created.
	 *
	 * @since	10.8.0
	 * @param	int    $term_id  Term ID that was created.
	 * @param	int    $tt_id    Term taxonomy ID.
	 * @param	string $taxonomy Taxonomy of the created term.
	 */
	public static function maybe_set_new_term_language( $term_id, $tt_id, $taxonomy ) {
		// Only handle WPRM taxonomies.
		if ( 0 !== strpos( $taxonomy, 'wprm_' ) ) {
			return;
		}

		$language = self::$new_term_language ? self::$new_term_language : self::get_current_admin_language();

		if ( $language ) {
			self::set_term_language( $term_id, $taxonomy, $language );
		}
	}

	/**
	 * Compatibility with multilingual plugins for home URL.
	 *
	 * @since	5.7.0
	 */
	public static function get_home_url() {
		$home_url = home_url();

		// Polylang Compatibility.
		if ( function_exists( 'pll_home_url' ) ) {
			$home_url = pll_home_url();
		}

		// Add trailing slash unless there are query parameters.
		if ( false === strpos( $home_url, '?' ) ) {
			$home_url = trailingslashit( $home_url );
		}

		// Add index.php if that's used in the permalinks.
		$structure = get_option( 'permalink_structure' );
		if ( '/index.php' === substr( $structure, 0, 10 ) && false === strpos( $home_url, '?' ) ) {
			$home_url .= 'index.php/';
		}

		return $home_url;
	}
}

WPRM_Compatibility::init();
