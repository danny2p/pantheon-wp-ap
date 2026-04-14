<?php
/**
 * Handle Divi compatibility.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.1
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle Divi compatibility.
 *
 * @since      10.4.1
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Compatibility_Divi {

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
	 * Register Divi compatibility hooks.
	 *
	 * @since	10.4.1
	 */
	public static function init() {
		add_action( 'divi_extensions_init', array( __CLASS__, 'divi' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'divi_assets' ) );
		add_action( 'init', array( __CLASS__, 'divi5_init' ), 1 );
		add_filter( 'divi_visual_builder_settings_data_post_content', array( __CLASS__, 'divi5_maybe_migrate_post_content' ), 20 );
		add_action( 'divi_visual_builder_assets_before_enqueue_scripts', array( __CLASS__, 'divi5_vb_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'divi5_frontend_assets' ) );
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
	 * Determine if Divi 5 is active.
	 *
	 * @return bool
	 */
	public static function is_divi5_active() {
		return self::is_divi5_enabled();
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
			define( 'WPRM_DIVI5_MODULES_PATH', WPRM_DIVI5_PATH . 'src/components/' );
		}

		$modules_bootstrap = WPRM_DIVI5_PATH . 'modules/Modules.php';

		if ( file_exists( $modules_bootstrap ) ) {
			require_once $modules_bootstrap;

			if ( function_exists( '\WPRM\Divi5\Modules\register_modules' ) ) {
				\WPRM\Divi5\Modules\register_modules();
				add_action( 'init', '\WPRM\Divi5\Modules\register_modules', 20 );
			}
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
				'nonce'         => wp_create_nonce( 'wp_rest' ),
				'endpoints'     => array(
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
	 * Repair legacy WPRM Divi modules before Divi 5 loads builder content.
	 *
	 * @since	10.4.1
	 * @param	string $content Post content loaded into the Visual Builder.
	 *
	 * @return string
	 */
	public static function divi5_maybe_migrate_post_content( $content ) {
		if ( ! self::is_divi5_enabled() || ! is_string( $content ) || false === strpos( $content, 'divi_wprm_recipe' ) ) {
			return $content;
		}

		$repair = self::repair_legacy_divi5_wprm_recipe_modules( $content );

		if ( ! empty( $repair['changed'] ) && ! empty( $repair['content'] ) && is_string( $repair['content'] ) ) {
			return $repair['content'];
		}

		return $content;
	}

	/**
	 * Check if content contains legacy Divi 4 WPRM recipe modules.
	 *
	 * @since	10.4.1
	 * @param	string $content Content to inspect.
	 *
	 * @return bool
	 */
	public static function has_legacy_divi5_wprm_recipe_modules( $content ) {
		return 0 < self::count_legacy_divi5_wprm_recipe_modules( $content );
	}

	/**
	 * Count legacy Divi 4 WPRM recipe modules in content.
	 *
	 * @since	10.4.1
	 * @param	string $content Content to inspect.
	 *
	 * @return int
	 */
	public static function count_legacy_divi5_wprm_recipe_modules( $content ) {
		return count( self::get_legacy_divi5_wprm_recipe_shortcodes( $content ) );
	}

	/**
	 * Repair legacy Divi 4 WPRM recipe modules inside Divi 5 content.
	 *
	 * @since	10.4.1
	 * @param	string $content Content to repair.
	 *
	 * @return array{
	 *     changed: bool,
	 *     content: string,
	 *     repaired: int,
	 *     recipe_ids: int[]
	 * }
	 */
	public static function repair_legacy_divi5_wprm_recipe_modules( $content ) {
		$result = array(
			'changed'    => false,
			'content'    => $content,
			'repaired'   => 0,
			'recipe_ids' => array(),
		);

		if ( ! is_string( $content ) || false === strpos( $content, 'divi_wprm_recipe' ) ) {
			return $result;
		}

		$repaired_recipe_ids = array();

		if ( function_exists( 'parse_blocks' ) && function_exists( 'serialize_blocks' ) ) {
			$blocks = parse_blocks( $content );

			if ( ! empty( $blocks ) ) {
				$block_repairs = 0;
				$blocks        = self::repair_legacy_divi5_wprm_recipe_blocks( $blocks, $block_repairs, $repaired_recipe_ids );

				if ( 0 < $block_repairs ) {
					$content             = serialize_blocks( $blocks );
					$result['changed']   = true;
					$result['repaired'] += $block_repairs;
				}
			}
		}

		$regex_repairs   = 0;
		$pattern         = get_shortcode_regex( array( 'divi_wprm_recipe' ) );
		$updated_content = preg_replace_callback(
			'/' . $pattern . '/s',
			function( $match ) use ( &$regex_repairs, &$repaired_recipe_ids ) {
				if ( ! isset( $match[2] ) || 'divi_wprm_recipe' !== $match[2] ) {
					return $match[0];
				}

				$recipe_id = self::get_legacy_divi5_wprm_recipe_id_from_shortcode_match( $match );

				if ( ! $recipe_id ) {
					return $match[0];
				}

				$regex_repairs++;
				$repaired_recipe_ids[] = $recipe_id;

				return self::serialize_divi5_wprm_recipe_block( $recipe_id );
			},
			$content
		);

		if ( is_string( $updated_content ) && $updated_content !== $content ) {
			$content = $updated_content;
		}

		if ( 0 < $regex_repairs ) {
			$result['changed']   = true;
			$result['repaired'] += $regex_repairs;
		}

		$result['content']    = $content;
		$result['recipe_ids'] = array_values( array_unique( array_map( 'absint', $repaired_recipe_ids ) ) );

		return $result;
	}

	/**
	 * Repair legacy WPRM Divi recipe modules inside parsed blocks.
	 *
	 * @since	10.4.1
	 * @param	array $blocks Parsed blocks.
	 * @param	int   $repaired_count Number of repaired blocks.
	 * @param	array $recipe_ids Repaired recipe IDs.
	 *
	 * @return array
	 */
	private static function repair_legacy_divi5_wprm_recipe_blocks( $blocks, &$repaired_count, &$recipe_ids ) {
		foreach ( $blocks as $index => $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			if ( isset( $block['blockName'] ) && 'wprm/recipe' === $block['blockName'] ) {
				continue;
			}

			$legacy_recipe_id = self::get_legacy_divi5_wprm_recipe_id_from_block( $block );

			if ( $legacy_recipe_id ) {
				$blocks[ $index ] = self::get_divi5_wprm_recipe_block( $legacy_recipe_id );
				$repaired_count++;
				$recipe_ids[] = $legacy_recipe_id;
				continue;
			}

			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = self::repair_legacy_divi5_wprm_recipe_blocks( $block['innerBlocks'], $repaired_count, $recipe_ids );
			}
		}

		return $blocks;
	}

	/**
	 * Get the recipe ID from a legacy Divi 5 wrapper block.
	 *
	 * @since	10.4.1
	 * @param	array $block Parsed block.
	 *
	 * @return int|false
	 */
	private static function get_legacy_divi5_wprm_recipe_id_from_block( $block ) {
		if ( ! empty( $block['innerBlocks'] ) ) {
			return false;
		}

		$candidates = array();

		if ( isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ) {
			$candidates[] = $block['innerHTML'];
		}

		if ( isset( $block['innerContent'] ) && is_array( $block['innerContent'] ) ) {
			$candidates[] = implode(
				'',
				array_filter(
					$block['innerContent'],
					'is_string'
				)
			);
		}

		$candidates = array_unique( array_filter( $candidates ) );

		foreach ( $candidates as $candidate ) {
			$shortcodes = self::get_legacy_divi5_wprm_recipe_shortcodes( $candidate );

			if ( empty( $shortcodes ) ) {
				continue;
			}

			foreach ( $shortcodes as $shortcode ) {
				$remaining_markup = str_replace( $shortcode['shortcode'], '', $candidate );
				$remaining_markup = preg_replace( '/<!--[\s\S]*?-->/', '', $remaining_markup );
				$remaining_markup = trim( wp_strip_all_tags( $remaining_markup ) );

				if ( '' === $remaining_markup ) {
					return $shortcode['recipe_id'];
				}
			}
		}

		return false;
	}

	/**
	 * Find legacy Divi 4 WPRM recipe shortcodes in content.
	 *
	 * @since	10.4.1
	 * @param	string $content Content to inspect.
	 *
	 * @return array
	 */
	private static function get_legacy_divi5_wprm_recipe_shortcodes( $content ) {
		if ( ! is_string( $content ) || false === strpos( $content, 'divi_wprm_recipe' ) ) {
			return array();
		}

		$pattern = get_shortcode_regex( array( 'divi_wprm_recipe' ) );
		$matches = array();
		$found   = array();

		if ( ! preg_match_all( '/' . $pattern . '/s', $content, $matches, PREG_SET_ORDER ) ) {
			return array();
		}

		foreach ( $matches as $match ) {
			if ( ! isset( $match[2] ) || 'divi_wprm_recipe' !== $match[2] ) {
				continue;
			}

			$recipe_id = self::get_legacy_divi5_wprm_recipe_id_from_shortcode_match( $match );

			if ( ! $recipe_id ) {
				continue;
			}

			$found[] = array(
				'shortcode' => $match[0],
				'recipe_id' => $recipe_id,
			);
		}

		return $found;
	}

	/**
	 * Get the recipe ID from a shortcode regex match.
	 *
	 * @since	10.4.1
	 * @param	array $match Shortcode regex match.
	 *
	 * @return int|false
	 */
	private static function get_legacy_divi5_wprm_recipe_id_from_shortcode_match( $match ) {
		if ( ! isset( $match[3] ) ) {
			return false;
		}

		$atts = shortcode_parse_atts( stripslashes( $match[3] ) );

		if ( ! isset( $atts['recipe_id'] ) ) {
			return false;
		}

		$recipe_id = absint( $atts['recipe_id'] );

		return $recipe_id > 0 ? $recipe_id : false;
	}

	/**
	 * Build a native Divi 5 WPRM recipe block structure.
	 *
	 * @since	10.4.1
	 * @param	int $recipe_id Recipe ID to set.
	 *
	 * @return array
	 */
	private static function get_divi5_wprm_recipe_block( $recipe_id ) {
		return array(
			'blockName'    => 'wprm/recipe',
			'attrs'        => array(
				'recipe' => array(
					'innerContent' => array(
						'desktop' => array(
							'value' => (string) absint( $recipe_id ),
						),
					),
				),
			),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
	}

	/**
	 * Serialize a native Divi 5 WPRM recipe block.
	 *
	 * @since	10.4.1
	 * @param	int $recipe_id Recipe ID to set.
	 *
	 * @return string
	 */
	private static function serialize_divi5_wprm_recipe_block( $recipe_id ) {
		if ( function_exists( 'serialize_blocks' ) ) {
			return serialize_blocks(
				array(
					self::get_divi5_wprm_recipe_block( $recipe_id ),
				)
			);
		}

		return '[wprm-recipe id="' . absint( $recipe_id ) . '"]';
	}
}

WPRM_Compatibility_Divi::init();