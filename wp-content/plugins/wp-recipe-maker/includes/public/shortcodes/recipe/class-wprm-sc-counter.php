<?php
/**
 * Handle the recipe counter shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      6.9.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe counter shortcode.
 *
 * @since      6.9.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Counter extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-counter';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'text' => array(
				'help' => 'Potential placeholders: %count%, %recipe_name%',
				'default' => '%count%. %recipe_name%',
				'type' => 'text',
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'tag' => array(
				'default' => 'p',
				'type' => 'dropdown',
				'options' => array(
					'p' => 'p',
					'span' => 'span',
					'div' => 'div',
					'h1' => 'h1',
					'h2' => 'h2',
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5',
					'h6' => 'h6',
				),
			),
			'link' => array(
				'default' => '0',
				'type' => 'toggle',
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	6.9.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		$text = $atts['text'];
		if ( ! $recipe || ! $text ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		// Output.
		$classes = array(
			'wprm-recipe-counter',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$text = $recipe->replace_placeholders( $text );

		// Check if %count% or %total% is used - replace with placeholders that will be replaced later.
		// This ensures we count only rendered elements, not items processed by other plugins.
		$needs_replacement = false;
		
		// In Gutenberg preview (backend editor), use stored block position if available.
		if ( WPRM_Context::is_gutenberg_preview() && isset( $GLOBALS['wprm_roundup_block_position'] ) ) {
			$block_position = intval( $GLOBALS['wprm_roundup_block_position'] );
			
			// Ensure we have a valid position (at least 1).
			if ( $block_position < 1 ) {
				$block_position = 1;
			}
			
			// Replace %count% with the actual position in backend.
			if ( false !== stripos( $text, '%count%' ) ) {
				$text = str_ireplace( '%count%', $block_position, $text );
			}
			
			// For %total%, we still need to calculate it from the post content.
			if ( false !== stripos( $text, '%total%' ) ) {
				$total_count = self::get_roundup_total_count();
				$text = str_ireplace( '%total%', $total_count, $text );
			}
		} else {
			// Frontend or backend without position: use placeholder replacement that happens later via filter.
			if ( false !== stripos( $text, '%count%' ) ) {
				$text = str_ireplace( '%count%', '<span class="wprm-recipe-counter-count">1</span>', $text );
				$needs_replacement = true;
			}
			if ( false !== stripos( $text, '%total%' ) ) {
				$text = str_ireplace( '%total%', '<span class="wprm-recipe-counter-total">1</span>', $text );
				$needs_replacement = true;
			}
			
			if ( $needs_replacement ) {
				$GLOBALS['wprm_recipe_counter_needs_replacement'] = true;
			}
		}

		if ( $atts['link'] && $recipe->permalink() ) {
			$target = $recipe->parent_url_new_tab() ? ' target="_blank"' : '';

			$rel = '';
			if ( $recipe->parent_url_nofollow() ) { $rel .= ' nofollow'; }
			if ( $recipe->parent_url_noopener() ) { $rel .= ' noopener'; }
			$rel = $rel ? ' rel="' . trim( $rel ) . '"' : '';

			$text = '<a href="' . esc_url( $recipe->permalink() ) . '"' . $target . $rel . '>' . WPRM_Shortcode_Helper::sanitize_html( $text ) . '</a>';
		}

		$tag = WPRM_Shortcode_Helper::sanitize_html_element( $atts['tag'] );
		$output = '<' . $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $text . '</' . $tag . '>';
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}

	/**
	 * Get the total count of roundup items in the post content.
	 * Used in backend editor to display correct total.
	 *
	 * @since	10.0.0
	 * @return	int Total count of roundup items.
	 */
	private static function get_roundup_total_count() {
		static $total_count_cache = null;

		if ( null !== $total_count_cache ) {
			return $total_count_cache;
		}

		$total_count = 0;

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

		// Count roundup item blocks in the content.
		if ( ! empty( $post_content ) && function_exists( 'parse_blocks' ) ) {
			$blocks = parse_blocks( $post_content );
			if ( ! empty( $blocks ) ) {
				$total_count = self::count_roundup_blocks( $blocks );
			}
		}

		// Fallback to 1 if no items found.
		if ( 0 === $total_count ) {
			$total_count = 1;
		}

		$total_count_cache = $total_count;
		return $total_count;
	}

	/**
	 * Recursively count roundup item blocks in parsed blocks.
	 * Also counts roundup items within list blocks.
	 *
	 * @since	10.0.0
	 * @param	array $blocks Parsed block list.
	 * @return	int Count of roundup item blocks.
	 */
	private static function count_roundup_blocks( $blocks ) {
		$count = 0;

		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			// Check if this is a roundup item block.
			if ( isset( $block['blockName'] ) && 'wp-recipe-maker/recipe-roundup-item' === $block['blockName'] ) {
				$count++;
			}

			// Check if this is a list block and count roundup items within it.
			if ( isset( $block['blockName'] ) && 'wp-recipe-maker/list' === $block['blockName'] ) {
				$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();
				$list_id = isset( $attrs['id'] ) ? intval( $attrs['id'] ) : 0;
				if ( $list_id ) {
					// Count roundup items in this list.
					if ( class_exists( 'WPRM_List_Manager' ) ) {
						$list = WPRM_List_Manager::get_list( $list_id );
						if ( $list && method_exists( $list, 'items' ) ) {
							$items = $list->items();
							if ( is_array( $items ) ) {
								foreach ( $items as $item ) {
									if ( is_array( $item ) && isset( $item['type'] ) && 'roundup' === $item['type'] ) {
										$count++;
									}
								}
							}
						}
					}
				}
			}

			// Recursively count inner blocks.
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$count += self::count_roundup_blocks( $block['innerBlocks'] );
			}
		}

		return $count;
	}
}

WPRM_SC_Counter::init();