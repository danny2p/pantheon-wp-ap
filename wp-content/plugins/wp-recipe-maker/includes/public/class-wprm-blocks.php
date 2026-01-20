<?php
/**
 * Handle Gutenberg Blocks.
 *
 * @link       https://bootstrapped.ventures
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
					'updated' => array(
						'type' => 'number',
						'default' => 0,
					),
				),
				'render_callback' => array( __CLASS__, 'render_list_block' ),
			);
			register_block_type( 'wp-recipe-maker/list', $block_settings );


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
					'credit' => array(
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
		if ( WPRM_Context::is_gutenberg_preview() ) {
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
	 * Render the list block.
	 *
	 * @since	3.1.2
	 * @param	mixed $atts Block attributes.
	 */
	public static function render_list_block( $atts ) {
		$atts = self::parse_atts( $atts );
		$output = '';

		// Only do this for the Gutenberg Preview.
		if ( WPRM_Context::is_gutenberg_preview() ) {
			$list = WPRM_List_Manager::get_list( $atts['id'] );

			// No list found? ID is incorrect => show warning.
			if ( ! $list ) {
				return '<div class="wprm-recipe-block-invalid">' . __( 'This is a "WPRM List" block with a non-existing recipe ID.', 'wp-recipe-maker' ) . '</div>';
			}

			if ( 'default' !== $list->template() ) {
				$template = WPRM_Template_Manager::get_template_by_slug( $list->template() );
			} else {
				// Use default single recipe template.
				$template = WPRM_Template_Manager::get_template_by_type( 'roundup' );
			}
			$output .= '<style type="text/css">' . WPRM_Template_Manager::get_template_css( $template ) . '</style>';
		}

		$output .= WPRM_List_Shortcode::shortcode( $atts );

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
		if ( WPRM_Context::is_gutenberg_preview() ) {
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

			// Calculate and store the position of this roundup item block for counter display.
			// This ensures the counter shows the correct number in the backend editor.
			self::calculate_roundup_block_position( $atts );
		}

		$output .= WPRM_Recipe_Roundup::shortcode( $atts );

		return $output;
	}

	/**
	 * Calculate and store the position of a roundup item block in the post content.
	 * This is used to display the correct counter number in the backend editor.
	 *
	 * @since	10.0.0
	 * @param	array $atts Block attributes.
	 */
	private static function calculate_roundup_block_position( $atts ) {
		// Initialize static variables to track block positions.
		static $position_cache = null;
		static $current_position = 0;

		// Get post content if we haven't cached positions yet.
		if ( null === $position_cache ) {
			$position_cache = array();
			$current_position = 0;

			// Try to get post content from various sources.
			$post_content = '';
			$post_id = 0;
			
			// Try to get from global post.
			if ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] ) {
				$post_id = $GLOBALS['post']->ID;
				$post_content = $GLOBALS['post']->post_content;
			}
			
			// Try to get from request parameters (REST API).
			if ( empty( $post_content ) ) {
				// Check various possible parameter names.
				$possible_params = array( 'post_id', 'postId', 'context[postId]' );
				foreach ( $possible_params as $param ) {
					if ( isset( $_REQUEST[ $param ] ) ) {
						$post_id = intval( $_REQUEST[ $param ] );
						break;
					}
				}
				
				// Also check JSON body for REST API requests.
				if ( ! $post_id && ! empty( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
					$json_input = file_get_contents( 'php://input' );
					if ( ! empty( $json_input ) ) {
						$json_data = json_decode( $json_input, true );
						if ( $json_data ) {
							if ( isset( $json_data['post_id'] ) ) {
								$post_id = intval( $json_data['post_id'] );
							} elseif ( isset( $json_data['context']['postId'] ) ) {
								$post_id = intval( $json_data['context']['postId'] );
							}
						}
					}
				}
				
				if ( $post_id ) {
					$post = get_post( $post_id );
					if ( $post ) {
						$post_content = $post->post_content;
					}
				}
			}

			// Parse blocks to find all roundup item blocks.
			if ( ! empty( $post_content ) && function_exists( 'parse_blocks' ) ) {
				$blocks = parse_blocks( $post_content );
				if ( ! empty( $blocks ) ) {
					self::extract_roundup_blocks( $blocks, $position_cache );
				}
			}
		}

		// Create a unique identifier for this block based on its attributes.
		// Use a combination of id, link, and other distinguishing attributes.
		$block_key = '';
		if ( ! empty( $atts['id'] ) ) {
			$block_key = 'id_' . $atts['id'];
		} elseif ( ! empty( $atts['link'] ) ) {
			$block_key = 'link_' . md5( $atts['link'] );
		} else {
			// Fallback: use a hash of all attributes.
			$block_key = 'hash_' . md5( serialize( $atts ) );
		}

		// If we have cached positions, use them.
		if ( isset( $position_cache[ $block_key ] ) ) {
			$GLOBALS['wprm_roundup_block_position'] = $position_cache[ $block_key ];
			return;
		}

		// Otherwise, increment position counter (for blocks not found in cache).
		$current_position++;
		$GLOBALS['wprm_roundup_block_position'] = $current_position;
	}

	/**
	 * Recursively extract roundup item blocks from parsed blocks.
	 *
	 * @since	10.0.0
	 * @param	array $blocks Parsed block list.
	 * @param	array $position_cache Reference to position cache array.
	 */
	private static function extract_roundup_blocks( $blocks, &$position_cache ) {
		$position = 0;

		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			// Check if this is a roundup item block.
			if ( isset( $block['blockName'] ) && 'wp-recipe-maker/recipe-roundup-item' === $block['blockName'] ) {
				$position++;
				$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();

				// Create a unique identifier for this block.
				$block_key = '';
				if ( ! empty( $attrs['id'] ) ) {
					$block_key = 'id_' . $attrs['id'];
				} elseif ( ! empty( $attrs['link'] ) ) {
					$block_key = 'link_' . md5( $attrs['link'] );
				} else {
					$block_key = 'hash_' . md5( serialize( $attrs ) );
				}

				// Store position for this block.
				$position_cache[ $block_key ] = $position;
			}

			// Recursively check inner blocks.
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				self::extract_roundup_blocks( $block['innerBlocks'], $position_cache );
			}
		}
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
		if ( WPRM_Context::is_gutenberg_preview() ) {
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
