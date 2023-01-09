<?php
/**
 * Handle the recipe snippets shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the recipe snippets shortcode.
 *
 * @since      5.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Snippets {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_shortcode( 'wprm-recipe-snippet', array( __CLASS__, 'recipe_snippet_shortcode' ) );

		add_filter( 'the_content', array( __CLASS__, 'automatically_add_recipe_snippets' ), 20 );
		add_filter( 'get_the_excerpt', array( __CLASS__, 'remove_automatic_snippets' ), 9 );
		add_filter( 'get_the_excerpt', array( __CLASS__, 'readd_automatic_snippets' ), 11 );
	}

	/**
	 * Output for the recipe snippet shortcode.
	 *
	 * @since	4.1.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function recipe_snippet_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => '',
			'template' => '',
		), $atts, 'wprm_recipe_snippet' );

		if (
				( ! is_feed() && ! is_front_page() && is_singular() && is_main_query() )
				|| ( isset( $GLOBALS['wp']->query_vars['rest_route'] ) && '/wp/v2/block-renderer/wp-recipe-maker/recipe-snippet' === $GLOBALS['wp']->query_vars['rest_route'] )
		) {
			$recipe_id = $atts['id'] ? $atts['id'] : WPRM_Template_Shortcodes::get_current_recipe_id();

			if ( $recipe_id ) {
				WPRM_Assets::load();

				if ( 'legacy' === WPRM_Settings::get( 'recipe_template_mode' ) ) {
					$alignment = WPRM_Settings::get( 'recipe_snippets_alignment' );
					return '<div class="wprm-recipe-snippets" style="text-align: ' . esc_attr( $alignment ) . ';">' . do_shortcode( WPRM_Settings::get( 'recipe_snippets_text' ) ) . '</div>';
				} else {
					$template = false;
					$template_slug = trim( $atts['template'] );
	
					if ( $template_slug ) {
						$template = WPRM_Template_Manager::get_template_by_slug( $template_slug );
					}
	
					if ( ! $template ) {
						$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );
						$type = $recipe ? $recipe->type() : 'food';

						$template = WPRM_Template_Manager::get_template_by_type( 'snippet', $type );
					}

					if ( $template ) {
						// Add to used templates.
						WPRM_Template_Manager::add_used_template( $template );

						$output = '<div class="wprm-recipe wprm-recipe-snippet wprm-recipe-template-' . esc_attr( $template['slug'] ) . '">' . do_shortcode( $template['html'] ) . '</div>';
						return apply_filters( 'wprm_recipe_snippet_shortcode_output', $output, $atts, $recipe_id, $template );
					}
				}
			}
		}

		return '';
	}

	/**
	 * Automatically add recipe snippets above the post content.
	 *
	 * @since    1.26.0
	 * @param	 mixed $content Content we want to filter before it gets passed along.
	 */
	public static function automatically_add_recipe_snippets( $content ) {
		if ( ! is_feed() && ! is_front_page() && is_single() && is_main_query() && ! post_password_required() ) {
			$snippet = false;

			if ( 'legacy' === WPRM_Settings::get( 'recipe_template_mode' ) && WPRM_Settings::get( 'recipe_snippets_automatically_add' ) ) {
				$snippet = '<div class="wprm-automatic-recipe-snippets">' . do_shortcode( '[wprm-recipe-snippet]' ) . '</div>';
			} else if ( 'modern' === WPRM_Settings::get( 'recipe_template_mode' ) && WPRM_Settings::get( 'recipe_snippets_automatically_add_modern' ) ) {
				$snippet = do_shortcode( '[wprm-recipe-snippet]' );
			}

			// Add snippet to content.
			if ( $snippet ) {
				// Default to showing at the top of the post content.
				$show_at_top = true;

				if ( 'start' !== WPRM_Settings::get( 'recipe_snippets_automatically_add_placement' ) ) {
					// Check position of recipe.
					$recipe_pos = strpos( $content, 'wprm-recipe-container' );
					$needle = false;

					switch ( WPRM_Settings::get( 'recipe_snippets_automatically_add_placement' ) ) {
						case 'after_first_div':
							$needle = false === $needle ? '</div>' : $needle;
						case 'after_first_paragraph':
							$needle = false === $needle ? '</p>' : $needle;

							$pos = stripos( $content, $needle );
							if ( false !== $pos && $pos < $recipe_pos ) {
								$content = substr_replace( $content, $needle . $snippet, $pos, strlen( $needle ) );
								$show_at_top = false;
							}
							break;
						case 'after_first_image':
							$needle = '<img ';

							$searching_for_good_image = true;
							$content_to_search = $content;
							$content_offset = 0;

							while ( $searching_for_good_image ) {
								$pos_image_start = stripos( $content_to_search, $needle );

								// If we find an image and it's before the recipe card position.
								if ( false !== $pos_image_start && $content_offset + $pos_image_start < $recipe_pos ) {
									$rest_of_content = substr( $content_to_search, $pos_image_start );
									$pos_image_end = strpos( $rest_of_content, '>' );
	
									if ( false !== $pos_image_end ) {
										$image = substr( $rest_of_content, 0, $pos_image_end + 1 );
	
										// Optionally add paragraph end as part of the image, so that snippets appear after.
										if ( '</p>' === substr( $rest_of_content, $pos_image_end + 1, 4 ) ) {
											$image .= '</p>';
										}
	
										if (
											false === stripos( $image, 'dpsp-post-pinterest-image-hidden-inner' )
											&& false === stripos( $image, 'tasty-pins-hidden-image' )
											) {
											// Found an image that's not hidden by some known plugins, stop searching.
											$searching_for_good_image = false;

											// Check if image has caption, if so, make it part of the image so that snippets appear after.
											if ( '<figcaption' === substr( $rest_of_content, $pos_image_end + 1, 11 ) ) {
												$end_of_caption = strpos( substr( $rest_of_content, $pos_image_end + 1 ), '</figcaption>' );
												$caption = substr( $rest_of_content, $pos_image_end + 1, $end_of_caption + 13 );
												$image .= $caption;

												// Check if also inside of a figure.
												if ( '</figure>' === substr( $rest_of_content, $pos_image_end + 1 + $end_of_caption + 13, 9 ) ) {
													$image .= '</figure>';
												}
											}

											$content = substr_replace( $content, $image . $snippet, $content_offset + $pos_image_start, strlen( $image ) );
											$show_at_top = false;
										} else {
											// Keep searching in the rest of the content.
											$content_to_search = substr( $rest_of_content, $pos_image_end + 1 );
											$content_offset += $pos_image_start + strlen( $image );
										}
									} else {
										$searching_for_good_image = false;
									}
								} else {
									// No images left before the recipe card.
									$searching_for_good_image = false;
								}
							}
							break;
					}
				}

				if ( $show_at_top ) {
					$content = $snippet . $content;
				}
			}
		}

		return $content;
	}


	/**
	 * Don't automatically add snippets when getting the excerpt.
	 *
	 * @since    5.6.0
	 */
	public static function remove_automatic_snippets( $excerpt ) {
		remove_filter( 'the_content', array( __CLASS__, 'automatically_add_recipe_snippets' ), 20 );
		return $excerpt;
	}
	public static function readd_automatic_snippets( $excerpt ) {
		add_filter( 'the_content', array( __CLASS__, 'automatically_add_recipe_snippets' ), 20 );
		return $excerpt;
	}
}

WPRM_Shortcode_Snippets::init();
