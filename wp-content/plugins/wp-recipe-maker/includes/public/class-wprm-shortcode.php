<?php
/**
 * Handle the recipe shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the recipe shortcode.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode {
	/**
	 * Type of shortcode we're outputting.
	 *
	 * @since    5.10.0
	 * @access   private
	 * @var      array $shortcode_type Shortcode type we're currently outputting.
	 */
	private static $shortcode_type = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_shortcode( 'wprm-recipe', array( __CLASS__, 'recipe_shortcode' ) );

		add_filter( 'content_edit_pre', array( __CLASS__, 'replace_imported_shortcodes' ) );
		add_filter( 'the_content', array( __CLASS__, 'replace_tasty_shortcode' ), 1 );
		add_filter( 'the_content', array( __CLASS__, 'replace_ziplist_shortcode' ), 1 );

		add_filter( 'render_block', array( __CLASS__, 'replace_blocks' ), 10, 2 );

		add_filter( 'get_the_excerpt', array( __CLASS__, 'before_generating_excerpt' ), 9 );
		add_filter( 'get_the_excerpt', array( __CLASS__, 'after_generating_excerpt' ), 11 );

		add_action( 'init', array( __CLASS__, 'fallback_shortcodes' ), 99 );
	}

	/**
	 * Set current shortcode type to "excerpt".
	 *
	 * @since    5.10.0
	 * @param	 mixed $excerpt Excerpt that is being output.
	 */
	public static function before_generating_excerpt( $excerpt ) {
		self::$shortcode_type[] = 'excerpt';
		return $excerpt;
	}
	/**
	 * Remove "excerpt" shortcode type again.
	 *
	 * @since    5.10.0
	 * @param	 mixed $excerpt Excerpt that is being output.
	 */
	public static function after_generating_excerpt( $excerpt ) {
		array_pop( self::$shortcode_type );
		return $excerpt;
	}

	/**
	 * Fallback shortcodes for recipe plugins that we imported from.
	 *
	 * @since    1.3.0
	 */
	public static function fallback_shortcodes() {
		if ( ! shortcode_exists( 'seo_recipe' ) ) {
			add_shortcode( 'seo_recipe', array( __CLASS__, 'recipe_shortcode_fallback' ) );
		}

		if ( ! shortcode_exists( 'tasty-recipe' ) ) {
			add_shortcode( 'tasty-recipe', array( __CLASS__, 'recipe_shortcode_fallback' ) );
		}

		if ( ! shortcode_exists( 'ultimate-recipe' ) ) {
			add_shortcode( 'ultimate-recipe', array( __CLASS__, 'recipe_shortcode_fallback' ) );
		}

		if ( ! shortcode_exists( 'cooked-recipe' ) ) {
			add_shortcode( 'cooked-recipe', array( __CLASS__, 'recipe_shortcode_fallback' ) );
		}

		// Recipes by Simmer.
		if ( ! shortcode_exists( 'recipe' ) ) {
			add_shortcode( 'recipe', array( __CLASS__, 'recipe_shortcode_fallback' ) );
		}

		if ( ! shortcode_exists( 'nutrition-label' ) ) {
			add_shortcode( 'nutrition-label', array( __CLASS__, 'remove_shortcode' ) );
			add_shortcode( 'ultimate-nutrition-label', array( __CLASS__, 'remove_shortcode' ) );
		}

		if ( ! shortcode_exists( 'recipe-timer' ) ) {
			add_shortcode( 'recipe-timer', array( __CLASS__, 'timer_shortcode' ) );
		}
	}

	/**
	 * Replace imported shortcode with ours.
	 *
	 * @since	2.1.0
	 * @param	mixed $content Content we want to filter before it gets passed along.
	 */
	public static function replace_imported_shortcodes( $content ) {
		$content = self::replace_wpultimaterecipe_shortcode( $content );
		$content = self::replace_tasty_shortcode( $content );
		$content = self::replace_ziplist_shortcode( $content );
		$content = self::replace_bigoven_shortcode( $content );

		return $content;
	}

	/**
	 * Replace WP Ultimate Recipe shortcode with ours.
	 *
	 * @since    1.3.0
	 * @param		 mixed $content Content we want to filter before it gets passed along.
	 */
	public static function replace_wpultimaterecipe_shortcode( $content ) {
		preg_match_all( "/\[ultimate-recipe\s.*?id='?\"?(\d+).*?]/im", $content, $matches );
		foreach ( $matches[0] as $key => $match ) {
			if ( WPRM_POST_TYPE === get_post_type( $matches[1][ $key ] ) ) {
				$content = str_replace( $match, '[wprm-recipe id="' . $matches[1][ $key ] . '"]', $content );
			}
		}

		return $content;
	}

	/**
	 * Replace Tasty Recipes shortcode with ours.
	 *
	 * @since    1.23.0
	 * @param	 mixed $content Content we want to filter before it gets passed along.
	 */
	public static function replace_tasty_shortcode( $content ) {
		preg_match_all( "/\[tasty-recipe\s.*?id='?\"?(\d+).*?]/im", $content, $matches );
		foreach ( $matches[0] as $key => $match ) {
			if ( WPRM_POST_TYPE === get_post_type( $matches[1][ $key ] ) ) {
				$content = str_replace( $match, '[wprm-recipe id="' . $matches[1][ $key ] . '"]', $content );
			}
		}

		if ( function_exists( 'parse_blocks' ) ) {
			preg_match_all( '/<!--(.*?)-->/im', $content, $matches);

			foreach ( $matches[0] as $key => $match ) {
				$blocks = parse_blocks( $match );

				if ( $blocks && 1 === count( $blocks ) && $blocks[0] && 'wp-tasty/tasty-recipe' === $blocks[0]['blockName'] ) {
					$id = $blocks[0]['attrs']['id'];

					if ( $id && WPRM_POST_TYPE === get_post_type( $id ) ) {
						$content = str_replace( $match, '[wprm-recipe id="' . $id . '"]', $content );
					}
				}
			}			
		}

		return $content;
	}

	/**
	 * Replace ZipList shortcode with ours.
	 *
	 * @since    1.23.0
	 * @param	 mixed $content Content we want to filter before it gets passed along.
	 */
	public static function replace_ziplist_shortcode( $content ) {
		preg_match_all( "/\[zrdn-recipe\s.*?id='?\"?(\d+).*?]/im", $content, $matches );
		foreach ( $matches[0] as $key => $match ) {
			$zl_id = intval( $matches[1][ $key ] );

			if ( $zl_id ) {
				global $wpdb;
				$zl_recipe = $wpdb->get_row( 'SELECT post_id FROM ' . $wpdb->prefix . 'amd_zlrecipe_recipes WHERE recipe_id=' . $zl_id );
				$post_id = $zl_recipe ? $zl_recipe->post_id : false;

				if ( $post_id && WPRM_POST_TYPE === get_post_type( $post_id ) ) {
					$content = str_replace( $match, '[wprm-recipe id="' . $post_id . '"]', $content );
				}
			}
		}

		if ( function_exists( 'parse_blocks' ) ) {
			preg_match_all( '/<!--(.*?)-->/im', $content, $matches);

			foreach ( $matches[0] as $key => $match ) {
				$blocks = parse_blocks( $match );

				if ( $blocks && 1 === count( $blocks ) && $blocks[0] && 'zip-recipes/recipe-block' === $blocks[0]['blockName'] ) {
					$zl_id = intval( $blocks[0]['attrs']['id'] );

					if ( $zl_id ) {
						global $wpdb;
						$zl_recipe = $wpdb->get_row( 'SELECT post_id FROM ' . $wpdb->prefix . 'amd_zlrecipe_recipes WHERE recipe_id=' . $zl_id );
						$post_id = $zl_recipe ? $zl_recipe->post_id : false;
		
						if ( $post_id && WPRM_POST_TYPE === get_post_type( $post_id ) ) {
							$content = str_replace( $match, '[wprm-recipe id="' . $post_id . '"]', $content );
						}
					}
				}
			}			
		}

		return $content;
	}

	/**
	 * Replace BigOven shortcode with ours.
	 *
	 * @since    1.7.0
	 * @param	 mixed $content Content we want to filter before it gets passed along.
	 */
	public static function replace_bigoven_shortcode( $content ) {
		preg_match_all( "/\[seo_recipe\s.*?id='?\"?(\d+).*?]/im", $content, $matches );
		foreach ( $matches[0] as $key => $match ) {
			if ( WPRM_POST_TYPE === get_post_type( $matches[1][ $key ] ) ) {
				$content = str_replace( $match, '[wprm-recipe id="' . $matches[1][ $key ] . '"]', $content );
			}
		}

		return $content;
	}


	/**
	 * Replace blocks by other recipe plugins with ours, if they have been imported.
	 *
	 * @since    8.0.0
	 * @param	 mixed $content Content we want to filter before it gets passed along.
	 * @param	 mixed $block 	Block we're currently filtering.
	 */
	public static function replace_blocks( $content, $block ) {
		// Tasty Recipes.
		if ( 'wp-tasty/tasty-recipe' === $block['blockName'] ) {
			$id = isset( $block['attrs']['id'] ) ? intval( $block['attrs']['id'] ) : false;

			if ( $id && WPRM_POST_TYPE === get_post_type( $id ) ) {
				$content = do_shortcode( '[wprm-recipe id="' . $id . '"]' );
			}
		}
		
		// Zip Recipes.
		if ( 'zip-recipes/recipe-block' === $block['blockName'] ) {
			$zl_id = isset( $block['attrs']['id'] ) ? intval( $block['attrs']['id'] ) : false;

			if ( $zl_id ) {
				global $wpdb;
				$zl_recipe = $wpdb->get_row( 'SELECT post_id FROM ' . $wpdb->prefix . 'amd_zlrecipe_recipes WHERE recipe_id=' . $zl_id );
				$post_id = $zl_recipe ? $zl_recipe->post_id : false;

				if ( $post_id && WPRM_POST_TYPE === get_post_type( $post_id ) ) {
					$content = do_shortcode( '[wprm-recipe id="' . $post_id . '"]' );
				}
			}
		}
		
		return $content;
	}

	/**
	 * To be used for shortcodes we want to (temporarily) remove from the content.
	 *
	 * @since    1.3.0
	 */
	public static function remove_shortcode() {
		return '';
	}

	/**
	 * Output for a fallback shortcode from another plugin.
	 *
	 * @since    4.2.1
	 * @param    array $atts Options passed along with the shortcode.
	 */
	public static function recipe_shortcode_fallback( $atts ) {
		$atts = shortcode_atts( array(
			'id' => false,
			'template' => '',
		), $atts );

		// Prevent outputting random recipe.
		if ( false === $atts['id'] ) {
			return '';
		} else {
			if ( WPRM_POST_TYPE !== get_post_type( $atts['id'] ) ) {
				// Find recipe in content.
				$recipe_ids = WPRM_Recipe_Manager::get_recipe_ids_from_post( $atts['id'] );

				if ( $recipe_ids && isset( $recipe_ids[0] ) ) {
					$atts['id'] = $recipe_ids[0];
				} else {
					// WP Ultimate Recipe shortcode migrated?
					$migrated_id = get_post_meta( $atts['id'], '_wpurp_wprm_migrated', true );
					if ( $migrated_id ) {
						$atts['id'] = intval( $migrated_id );
					}
				}
			}

			return self::recipe_shortcode( $atts );
		}
	}

	/**
	 * Output for the recipe shortcode.
	 *
	 * @since    1.0.0
	 * @param		 array $atts Options passed along with the shortcode.
	 */
	public static function recipe_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => 'random',
			'align' => '',
			'template' => '',
		), $atts, 'wprm_recipe' );

		$recipe_template = trim( $atts['template'] );

		// Get recipe.
		if ( 'random' === $atts['id'] ) {
			$posts = get_posts( array(
				'post_type' => WPRM_POST_TYPE,
				'posts_per_page' => 1,
				'orderby' => 'rand',
			) );

			$recipe_id = isset( $posts[0] ) ? $posts[0]->ID : 0;
		} elseif ( 'latest' === $atts['id'] ) {
			$posts = get_posts(array(
				'post_type' => WPRM_POST_TYPE,
				'posts_per_page' => 1,
			));

			$recipe_id = isset( $posts[0] ) ? $posts[0]->ID : 0;
		} elseif ( 'demo' === $atts['id'] ) {
			$recipe_id = 'demo';
		} else {
			$recipe_id = intval( $atts['id'] );
		}

		$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

		if ( $recipe ) {			
			WPRM_Assets::load();

			// Check type of recipe we need to output.
			if ( 0 < count( self::$shortcode_type ) ) {
				$type = end( self::$shortcode_type );
			} else {
				$type = 'single';

				if ( is_feed() ) {
					$type = 'feed';
				} elseif ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
					$type = 'amp';
					$recipe_template = ''; // Force default AMP template.
				} elseif ( isset( $GLOBALS['wp']->query_vars['rest_route'] ) && '/wp/v2/posts' === substr( $GLOBALS['wp']->query_vars['rest_route'], 0, 12 ) ) {
					$type = 'single'; // Use single template when accessing post through REST API.
				} elseif ( is_admin() ) {
					$type = 'single';
				} elseif ( is_front_page() || ! is_singular() || ! is_main_query() ) {
					$type = 'archive';
				}
			}

			// Early return recipe summary if this is for an excerpt.
			if ( 'excerpt' === $type ) {
				return $recipe->summary();
			}

			// Output full snippet or recipe template.
			$align_class = '';
			if ( isset( $atts['align'] ) && $atts['align'] ) {
				$align_class = ' align' . esc_attr( $atts['align'] );
			}

			if ( $recipe_template && 'snippet-' === substr( $recipe_template, 0, 8 ) ) {
				$output = '<div id="wprm-recipe-snippet-container-' . esc_attr( $recipe->id() ) . '" class="wprm-recipe-snippet-container' . esc_attr( $align_class ) . '" data-recipe-id="' . esc_attr( $recipe->id() ) . '">';
			} else {
				$output = '<div id="wprm-recipe-container-' . esc_attr( $recipe->id() ) . '" class="wprm-recipe-container' . esc_attr( $align_class ) . '" data-recipe-id="' . esc_attr( $recipe->id() ) . '" data-servings="' . esc_attr( $recipe->servings() ) . '">';
			}

			if ( 'amp' === $type || 'single' === $type ) {
				if ( 'recipe' === WPRM_Settings::get( 'metadata_location' ) && ! WPRM_Metadata::use_yoast_seo_integration() && WPRM_Metadata::should_output_metadata_for( $recipe->id() ) ) {
					$metadata_output = WPRM_Metadata::get_metadata_output( $recipe );

					if ( $metadata_output ) {
						$output .= $metadata_output;
						WPRM_Metadata::outputted_metadata_for( $recipe->id() );
					}
				}
			}

			$output .= WPRM_Template_Manager::get_template( $recipe, $type, $recipe_template );
			$output .= '</div>';
			
			return apply_filters( 'wprm_recipe_shortcode_output', $output, $recipe, $type, $recipe_template );
		} else {
			return '';
		}
	}
}

WPRM_Shortcode::init();
