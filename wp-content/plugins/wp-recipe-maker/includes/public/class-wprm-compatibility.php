<?php
/**
 * Handle compabitility with other plugins/themes.
 *
 * @link       http://bootstrapped.ventures
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
	 * Register actions and filters.
	 *
	 * @since	3.2.0
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'yoast_seo' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'rank_math' ) );
		add_action( 'divi_extensions_init', array( __CLASS__, 'divi' ) );

		add_filter( 'wpseo_video_index_content', array( __CLASS__, 'yoast_video_seo' ) );

		// Instacart.
		add_filter( 'wprm_recipe_ingredients_shortcode', array( __CLASS__, 'instacart_after_ingredients' ), 9 );
		add_action( 'wp_footer', array( __CLASS__, 'instacart_assets' ) );

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
		// require_once( WPRM_DIR . 'templates/divi/includes/extension.php' );
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
		$controls_manager->register( new WPRM_Elementor_Control() );
	}
	public static function elementor_styles() {
		// Make sure default assets load.
		WPRM_Assets::load();
	}
	public static function elementor_widgets( $widgets_manager ) {
		include( WPRM_DIR . 'templates/elementor/widget-recipe.php' );
		include( WPRM_DIR . 'templates/elementor/widget-roundup.php' );

		$widgets_manager->register( new WPRM_Elementor_Recipe_Widget() );
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
	 * Add Instacart button after the ingredients.
	 *
	 * @since	8.2.0
	 * @param	mixed $output Current ingredients output.
	 */
	public static function instacart_after_ingredients( $output ) {
		if ( WPRM_Settings::get( 'integration_instacart_agree' ) && WPRM_Settings::get( 'integration_instacart' ) ) {
			$output = $output . do_shortcode( '[wprm-spacer][wprm-recipe-shop-instacart]' );
		}

		return $output;
	}

	/**
	 * Instacart assets in footer.
	 *
	 * @since    8.2.0
	 */
	public static function instacart_assets() {
		if ( apply_filters( 'wprm_load_instacart', false ) ) {
			// Make sure to only load JS if they actually agree to the terms.
			if ( WPRM_Settings::get( 'integration_instacart_agree' ) ) {
				echo '<script>(function (d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) { return; } js = d.createElement(s); js.id = id; js.src = "https://widgets.instacart.com/widget-bundle-v2.js"; js.async = true; js.dataset.source_origin = "recipemaker"; fjs.parentNode.insertBefore(js, fjs); })(document, "script", "standard-instacart-widget-v1");</script>';
			}
		}
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
					do_action( 'wpml_set_element_language_details', array(
						'element_id' => $recipe_id,
						'element_type' => 'post_' . WPRM_POST_TYPE,
						'language_code' => $language ? $language : null,
					) );
				}
			}
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
