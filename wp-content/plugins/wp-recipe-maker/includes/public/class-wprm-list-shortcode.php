<?php
/**
 * Responsible for the list shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the list shortcode.
 *
 * @since      9.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_List_Shortcode {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.0.0
	 */
	public static function init() {
		add_shortcode( 'wprm-list', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Output for the list shortcode.
	 *
	 * @since    9.0.0
	 * @param    array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => false,
				'align' => '',
			),
			$atts,
			'wprm_list'
		);

		$output = '';
		$list_id = intval( $atts['id'] );
		$list = WPRM_List_Manager::get_list( $list_id );

		if ( $list ) {
			$internal_post_ids = array();
			$items = $list->items();

			// In Gutenberg preview, calculate starting position for roundup items in this list.
			$roundup_position = 0;
			if ( WPRM_Context::is_gutenberg_preview() ) {
				$roundup_position = self::get_roundup_items_before_list( $list_id );
			}

			foreach ( $items as $item ) {
				if ( 'roundup' === $item['type'] ) {
					$data = $item['data'];

					// Get ID for metadata if internal recipe.
					if ( 'internal' === $data['type'] || 'post' === $data['type'] ) {
						$internal_post_ids[] = $data['id'];
					}

					// Set template for roundup item.
					if ( 'default' !== $list->template() ) {
						$data['template'] = $list->template();
					}

					// In Gutenberg preview, set position for this roundup item.
					if ( WPRM_Context::is_gutenberg_preview() ) {
						$roundup_position++;
						$GLOBALS['wprm_roundup_block_position'] = $roundup_position;
					}

					// Generate and process shortcode immediately to use the position context.
					$shortcode = '[wprm-recipe-roundup-item ';
					foreach ( $data as $key => $value ) {
						$shortcode .= $key . '="' . self::clean_up_shortcode_attribute( $value ) . '" ';
					}
					$shortcode = trim( $shortcode ) . ']';

					// Process shortcode immediately so position context is available.
					$output .= do_shortcode( $shortcode );
				}

				if ( 'text' === $item['type'] ) {					
					$output .= do_shortcode( $item['data']['text'] );
				}
			}

			// Maybe output itemList metadata.
			$metadata = '';
			if ( is_singular() && ( ! WPRM_Metadata::has_outputted_metadata() || false === WPRM_Settings::get( 'recipe_roundup_no_metadata_when_recipe' ) ) ) {
				$internal_post_ids = array_unique( $internal_post_ids );

				if ( 1 < count( $internal_post_ids ) && $list->metadata_output() ) {
					$url = get_permalink( get_the_ID() );
					$name = $list->metadata_name();
					$description = $list->metadata_description();

					ob_start();
					WPRM_Recipe_Roundup::output_itemlist_metadata( $url, $name, $description, $internal_post_ids );
					$metadata = ob_get_contents();
					ob_end_clean();
				}
			}

			// Optional align class.
			$align_class = '';
			if ( isset( $atts['align'] ) && $atts['align'] ) {
				$align_class = ' align' . esc_attr( $atts['align'] );
			}

			// Output for list. Note: roundup items are already processed above, so no need to call do_shortcode again.
			$output = '<div id="wprm-list-' . esc_attr( $atts['id'] ) . '" class="wprm-list' . esc_attr( $align_class ) . '">' . $metadata . $output . '</div>';
		}

		return $output;
	}

	/**
	 * Clean up values to use in a shortcode attribute..
	 *
	 * @since    9.0.0
	 * @param    array $value Value to clean up.
	 */
	public static function clean_up_shortcode_attribute( $value ) {
		$value = preg_replace('/"/', '%22', $value);
		$value = preg_replace('/\x5B/', '%5B', $value); // \x5B is the hex code for '['
		$value = preg_replace('/\x5D/', '%5D', $value); // \x5D is the hex code for ']'
		$value = preg_replace('/\r?\n|\r/', '%0A', $value);

		return $value;
	}

	/**
	 * Get count of roundup items that appear before this list in the post content.
	 * Used to calculate correct position numbers for items within the list.
	 *
	 * @since	10.0.0
	 * @param	int $list_id ID of the current list.
	 * @return	int Count of roundup items before this list.
	 */
	private static function get_roundup_items_before_list( $list_id ) {
		static $count_cache = null;
		static $cached_list_id = null;

		// Cache the count per list to avoid recalculating.
		if ( null !== $count_cache && $cached_list_id === $list_id ) {
			return $count_cache;
		}

		$count = 0;

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

		// Count roundup item blocks that appear before this list block.
		if ( ! empty( $post_content ) && function_exists( 'parse_blocks' ) ) {
			$blocks = parse_blocks( $post_content );
			if ( ! empty( $blocks ) ) {
				$found_list = false;
				$count = self::count_roundup_items_before_list( $blocks, $list_id, $found_list );
			}
		}

		$count_cache = $count;
		$cached_list_id = $list_id;
		return $count;
	}

	/**
	 * Recursively count roundup items that appear before a specific list block.
	 *
	 * @since	10.0.0
	 * @param	array $blocks Parsed block list.
	 * @param	int    $list_id ID of the list to find.
	 * @param	bool   $found_list Reference flag indicating if list was found.
	 * @return	int Count of roundup items before the list.
	 */
	private static function count_roundup_items_before_list( $blocks, $list_id, &$found_list ) {
		$count = 0;

		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			// Check if this is the list block we're looking for.
			if ( isset( $block['blockName'] ) && 'wp-recipe-maker/list' === $block['blockName'] ) {
				$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();
				if ( isset( $attrs['id'] ) && intval( $attrs['id'] ) === $list_id ) {
					$found_list = true;
					// Stop counting once we find the target list.
					break;
				}
			}

			// Count roundup item blocks before finding the target list.
			if ( ! $found_list && isset( $block['blockName'] ) && 'wp-recipe-maker/recipe-roundup-item' === $block['blockName'] ) {
				$count++;
			}

			// Also count roundup items from list blocks that appear before this one.
			if ( ! $found_list && isset( $block['blockName'] ) && 'wp-recipe-maker/list' === $block['blockName'] ) {
				$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();
				$other_list_id = isset( $attrs['id'] ) ? intval( $attrs['id'] ) : 0;
				if ( $other_list_id && $other_list_id !== $list_id ) {
					// Count roundup items in this other list.
					// Only do this if WPRM_List_Manager is available to avoid errors.
					if ( class_exists( 'WPRM_List_Manager' ) ) {
						$other_list = WPRM_List_Manager::get_list( $other_list_id );
						if ( $other_list && method_exists( $other_list, 'items' ) ) {
							$other_items = $other_list->items();
							if ( is_array( $other_items ) ) {
								foreach ( $other_items as $item ) {
									if ( is_array( $item ) && isset( $item['type'] ) && 'roundup' === $item['type'] ) {
										$count++;
									}
								}
							}
						}
					}
				}
			}

			// Recursively check inner blocks.
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$count += self::count_roundup_items_before_list( $block['innerBlocks'], $list_id, $found_list );
				if ( $found_list ) {
					break;
				}
			}
		}

		return $count;
	}
}

WPRM_List_Shortcode::init();
