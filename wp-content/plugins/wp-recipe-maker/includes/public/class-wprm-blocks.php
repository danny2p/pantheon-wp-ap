<?php
/**
 * Handle Gutenberg Blocks.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.1.2
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle Gutenberg Blocks.
 *
 * @since      3.1.2
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Blocks {

	/**
	 * Register actions and filters.
	 *
	 * @since	3.1.2
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_recipe_block' ) );

		// Deprecation notice after 5.8.0.
		global $wp_version;
		if ( $wp_version && version_compare( $wp_version, '5.8', '<' ) ) {
			add_filter( 'block_categories', array( __CLASS__, 'block_categories' ) );
		} else {
			add_filter( 'block_categories_all', array( __CLASS__, 'block_categories' ) );
		}
	}

	/**
	 * Register block categories.
	 *
	 * @since	3.2.0
	 * @param	array $categories Existing block categories.
	 */
	public static function block_categories( $categories ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug' => 'wp-recipe-maker',
					'title' => 'WP Recipe Maker',
				),
			)
		);
	}

	/**
	 * Register recipe block.
	 *
	 * @since	3.1.2
	 */
	public static function register_recipe_block() {
		if ( function_exists( 'register_block_type' ) ) {
			$block_settings = array(
				'attributes' => array(
					'id' => array(
						'type' => 'number',
						'default' => 0,
					),
					'align' => array(
						'type' => 'string',
						'default' => '',
					),
					'template' => array(
						'type' => 'string',
						'default' => '',
					),
					'updated' => array(
						'type' => 'number',
						'default' => 0,
					),
				),
				'render_callback' => array( __CLASS__, 'render_recipe_block' ),
			);
			register_block_type( 'wp-recipe-maker/recipe', $block_settings );

			$block_settings = array(
				'attributes' => array(
					'id' => array(
						'type' => 'number',
						'default' => 0,
					),
					'align' => array(
						'type' => 'string',
						'default' => '',
					),
					'link' => array(
						'type' => 'string',
						'default' => '',
					),
					'nofollow' => array(
						'type' => 'string',
						'default' => '',
					),
					'newtab' => array(
						'type' => 'string',
						'default' => '1',
					),
					'image' => array(
						'type' => 'number',
						'default' => 0,
					),
					'image_url' => array(
						'type' => 'string',
						'default' => '',
					),
					'name' => array(
						'type' => 'string',
						'default' => '',
					),
					'summary' => array(
						'type' => 'string',
						'default' => '',
					),
					'button' => array(
						'type' => 'string',
						'default' => '',
					),
					'template' => array(
						'type' => 'string',
						'default' => '',
					),
				),
				'render_callback' => array( __CLASS__, 'render_recipe_roundup_item_block' ),
			);
			register_block_type( 'wp-recipe-maker/recipe-roundup-item', $block_settings );

			$block_settings = array(
				'attributes' => array(
					'id' => array(
						'type' => 'number',
						'default' => 0,
					),
					'template' => array(
						'type' => 'string',
						'default' => '',
					),
				),
				'render_callback' => array( __CLASS__, 'render_recipe_snippet_block' ),
			);
			register_block_type( 'wp-recipe-maker/recipe-snippet', $block_settings );

			$block_settings = array(
				'attributes' => array(
					'id' => array(
						'type' => 'number',
						'default' => 0,
					),
					'part' => array(
						'type' => 'string',
						'default' => 'recipe-name',
					),
				),
				'render_callback' => array( __CLASS__, 'render_recipe_part_block' ),
			);
			register_block_type( 'wp-recipe-maker/recipe-part', $block_settings );

			$block_settings = array(
				'attributes' => array(
					'id' => array(
						'type' => 'number',
						'default' => 0,
					),
					'align' => array(
						'type' => 'string',
						'default' => 'left',
					),
				),
				'render_callback' => array( __CLASS__, 'render_nutrition_label_block' ),
			);
			register_block_type( 'wp-recipe-maker/nutrition-label', $block_settings );

			$block_settings = array(
				'attributes' => array(
					'id' => array(
						'type' => 'number',
						'default' => 0,
					),
					'text' => array(
						'type' => 'string',
						'default' => __( 'Jump to Recipe', 'wp-recipe-maker' ),
					),
				),
				'render_callback' => array( __CLASS__, 'render_jump_to_recipe_block' ),
			);
			register_block_type( 'wp-recipe-maker/jump-to-recipe', $block_settings );
			
			$block_settings = array(
				'attributes' => array(
					'id' => array(
						'type' => 'number',
						'default' => 0,
					),
					'text' => array(
						'type' => 'string',
						'default' => __( 'Jump to Video', 'wp-recipe-maker' ),
					),
				),
				'render_callback' => array( __CLASS__, 'render_jump_to_video_block' ),
			);
			register_block_type( 'wp-recipe-maker/jump-to-video', $block_settings );

			$block_settings = array(
				'attributes' => array(
					'id' => array(
						'type' => 'number',
						'default' => 0,
					),
					'text' => array(
						'type' => 'string',
						'default' => __( 'Print Recipe', 'wp-recipe-maker' ),
					),
				),
				'render_callback' => array( __CLASS__, 'render_print_recipe_block' ),
			);
			register_block_type( 'wp-recipe-maker/print-recipe', $block_settings );
		}
	}

	/**
	 * Parse the block attributes.
	 *
	 * @since	3.8.1
	 * @param	mixed $atts Block attributes.
	 */
	public static function parse_atts( $atts ) {
		// Account for demo recipe.
		if ( isset ( $atts['id'] ) ) {
			$atts['id'] = -1 == $atts['id'] ? 'demo' : intval( $atts['id'] );
		}

		return $atts;
	}

	/**
	 * Render the recipe block.
	 *
	 * @since	3.1.2
	 * @param	mixed $atts Block attributes.
	 */
	public static function render_recipe_block( $atts ) {
		$atts = self::parse_atts( $atts );
		$output = '';

		// Only do this for the Gutenberg Preview.
		if ( isset( $GLOBALS['wp']->query_vars['rest_route'] ) && '/wp/v2/block-renderer/wp-recipe-maker/recipe' === $GLOBALS['wp']->query_vars['rest_route'] ) {
			$recipe = WPRM_Recipe_Manager::get_recipe( $atts['id'] );

			// No recipe find? ID is incorrect => show warning.
			if ( ! $recipe ) {
				return '<div class="wprm-recipe-block-invalid">' . __( 'This is a "WPRM Recipe" block with a non-existing recipe ID.', 'wp-recipe-maker' ) . '</div>';
			}

			if ( isset( $atts['template'] ) && $atts['template'] ) {
				$template = WPRM_Template_Manager::get_template_by_slug( $atts['template'] );
			} else {
				// Get recipe type.
				$type = $recipe ? $recipe->type() : 'food';

				// Use default single recipe template.
				$template = WPRM_Template_Manager::get_template_by_type( 'single', $type );
				$atts['template'] = $template['slug'];
			}

			// Output style.
			if ( 'modern' === $template['mode'] ) {
				$output .= '<style type="text/css">' . WPRM_Template_Manager::get_template_css( $template ) . '</style>';
			} else {
				$output .= '<style type="text/css">' . WPRM_Assets::get_custom_css( 'recipe' ) . '</style>';
			}
		}

		$output .= WPRM_Shortcode::recipe_shortcode( $atts );

		return $output;
	}

	/**
	 * Render the recipe roundup item block.
	 *
	 * @since	4.3.0
	 * @param	mixed $atts Block attributes.
	 */
	public static function render_recipe_roundup_item_block( $atts ) {
		$atts = self::parse_atts( $atts );
		$output = '';

		// Only do this for the Gutenberg Preview.
		if ( isset( $GLOBALS['wp']->query_vars['rest_route'] ) && '/wp/v2/block-renderer/wp-recipe-maker/recipe-roundup-item' === $GLOBALS['wp']->query_vars['rest_route'] ) {
			if ( isset( $atts['template'] ) && $atts['template'] ) {
				$template = WPRM_Template_Manager::get_template_by_slug( $atts['template'] );
			} else {
				// Use default single recipe template.
				$template = WPRM_Template_Manager::get_template_by_type( 'roundup' );
				$atts['template'] = $template['slug'];
			}

			// Output style.
			if ( 'modern' === $template['mode'] ) {
				$output .= '<style type="text/css">' . WPRM_Template_Manager::get_template_css( $template ) . '</style>';
			} else {
				$output .= '<style type="text/css">' . WPRM_Assets::get_custom_css( 'recipe' ) . '</style>';
			}
		}

		$output .= WPRM_Recipe_Roundup::shortcode( $atts );

		return $output;
	}

	/**
	 * Render the recipe snippet block.
	 *
	 * @since	6.9.0
	 * @param	mixed $atts Block attributes.
	 */
	public static function render_recipe_snippet_block( $atts ) {
		$atts = self::parse_atts( $atts );
		$output = '';

		// Only do this for the Gutenberg Preview.
		if ( isset( $GLOBALS['wp']->query_vars['rest_route'] ) && '/wp/v2/block-renderer/wp-recipe-maker/recipe-snippet' === $GLOBALS['wp']->query_vars['rest_route'] ) {
			if ( isset( $atts['template'] ) && $atts['template'] ) {
				$template = WPRM_Template_Manager::get_template_by_slug( $atts['template'] );
			} else {
				// Use default single recipe template.
				$template = WPRM_Template_Manager::get_template_by_type( 'snippet' );
				$atts['template'] = $template['slug'];
			}

			// Output style.
			if ( 'modern' === $template['mode'] ) {
				$output .= '<style type="text/css">' . WPRM_Template_Manager::get_template_css( $template ) . '</style>';
			} else {
				$output .= '<style type="text/css">' . WPRM_Assets::get_custom_css( 'recipe' ) . '</style>';
			}
		}

		$output .= WPRM_Shortcode_Snippets::recipe_snippet_shortcode( $atts );

		return $output;
	}

	/**
	 * Render the recipe part block.
	 *
	 * @since	6.9.0
	 * @param	mixed $atts Block attributes.
	 */
	public static function render_recipe_part_block( $atts ) {
		$atts = self::parse_atts( $atts );
		$output = '';

		$shortcode_tag = 'wprm-' . $atts['part'];

		if ( shortcode_exists( $shortcode_tag ) ) {
			$output .= do_shortcode( '[' . $shortcode_tag . ' id="' . $atts['id'] . '"]' );
		}

		return $output;
	}

	/**
	 * Render the nutrition label block.
	 *
	 * @since	3.1.2
	 * @param	mixed $atts Block attributes.
	 */
	public static function render_nutrition_label_block( $atts ) {
		$atts = self::parse_atts( $atts );
		return WPRM_SC_Nutrition_Label::shortcode( $atts );
	}

	/**
	 * Render the jump to recipe block.
	 *
	 * @since	3.1.2
	 * @param	mixed $atts Block attributes.
	 */
	public static function render_jump_to_recipe_block( $atts ) {
		$atts = self::parse_atts( $atts );
		return WPRM_SC_Jump::shortcode( $atts );
	}

	/**
	 * Render the jump to video block.
	 *
	 * @since	3.2.0
	 * @param	mixed $atts Block attributes.
	 */
	public static function render_jump_to_video_block( $atts ) {
		$atts = self::parse_atts( $atts );
		return WPRM_SC_Jump_Video::shortcode( $atts );
	}

	/**
	 * Render the print recipe block.
	 *
	 * @since	3.1.2
	 * @param	mixed $atts Block attributes.
	 */
	public static function render_print_recipe_block( $atts ) {
		$atts = self::parse_atts( $atts );
		return WPRM_SC_Print::shortcode( $atts );
	}
}

WPRM_Blocks::init();
