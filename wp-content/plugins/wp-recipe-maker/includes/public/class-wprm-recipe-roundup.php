<?php
/**
 * Handle the recipe roundup feature.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the recipe roundup feature.
 *
 * @since      4.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Recipe_Roundup {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_shortcode( 'wprm-recipe-roundup-item', array( __CLASS__, 'shortcode' ) );

		add_action( 'init', array( __CLASS__, 'meta_fields_in_rest' ) );
		add_action( 'wp_head', array( __CLASS__, 'metadata_in_head' ), 2 );
	}

	/**
	 * Output itemlist metadata in the HTML head.
	 *
	 * @since    4.3.0
	 */
	public static function metadata_in_head() {
		if ( is_singular() && ( ! WPRM_Metadata::has_outputted_metadata() || false === WPRM_Settings::get( 'recipe_roundup_no_metadata_when_recipe' ) ) ) {
			$post = get_post();
			$recipe_ids = self::get_items_from_content( $post->post_content );

			// Need at least 2 items before outputting a list.
			if ( 1 < count( $recipe_ids ) ) {
				$metadata = array(
					'@context' => 'http://schema.org',
					'@type' => 'ItemList',
					'url' => get_permalink( $post ),
					'itemListElement' => array(),
				);

				$name = get_post_meta( get_the_ID(), 'wprm-recipe-roundup-name', true );
				if ( $name ) {
					$metadata['name'] = wp_strip_all_tags( $name );
				}

				$description = get_post_meta( get_the_ID(), 'wprm-recipe-roundup-description', true );
				if ( $description ) {
					$metadata['description'] = wp_strip_all_tags( $description );
				}

				$item_list_counter = 0;
				foreach ( $recipe_ids as $recipe_id ) {
					$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

					if ( $recipe ) {
						$url = $recipe->permalink();

						if ( $url ) {
							$item_list_counter++;
							$metadata['itemListElement'][] = array(
								'@type'    => 'ListItem',
								'position' => $item_list_counter,
								'url'      => $url,
							);
						}
					}
				}

				$metadata['numberOfItems'] = $item_list_counter;

				if ( 1 < $item_list_counter ) {
					echo '<script type="application/ld+json">' . wp_json_encode( $metadata ) . '</script>';
				}
			}
		}
	}

	/**
	 * Get recipe roundup items from the content.
	 *
	 * @since    4.3.0
	 * @param    mixed $content Content to get the recipe roundup items from.
	 */
	public static function get_items_from_content( $content ) {
		$recipe_ids = array();

		$recipe_shortcodes = array();
		$pattern = get_shortcode_regex( array( 'wprm-recipe-roundup-item' ) );

		if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) && array_key_exists( 2, $matches ) ) {
			foreach ( $matches[2] as $key => $value ) {
				if ( 'wprm-recipe-roundup-item' === $value ) {
					$recipe_shortcodes[ $matches[0][ $key ] ] = shortcode_parse_atts( stripslashes( $matches[3][ $key ] ) );
				}
			}
		}

		foreach ( $recipe_shortcodes as $shortcode => $shortcode_options ) {
			$recipe_id = isset( $shortcode_options['id'] ) ? intval( $shortcode_options['id'] ) : 0;

			if ( $recipe_id ) {
				$recipe_ids[] = $recipe_id;
			}
		}

		return $recipe_ids;
	}

	/**
	 * Register the meta fields in the REST API.
	 *
	 * @since    4.3.0
	 */
	public static function meta_fields_in_rest() {
		register_meta( 'post', 'wprm-recipe-roundup-name', array( 'show_in_rest' => true, 'single' => true ) );
		register_meta( 'post', 'wprm-recipe-roundup-description', array( 'show_in_rest' => true, 'single' => true ) );
	}

	/**
	 * Output for the recipe roundup item shortcode.
	 *
	 * @since    4.3.0
	 * @param    array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => false,
				'align' => '',
				'link' => '',
				'image' => '',
				'image_url' => '',
				'summary' => '',
				'name' => '',
				'template' => '',
				'nofollow' => false,
				'newtab' => true,
			),
			$atts,
			'wprm_recipe_roundup_item'
		);

		$recipe = false;
		$recipe_template = trim( $atts['template'] );
		$recipe_id = intval( $atts['id'] );

		if ( $recipe_id ) {
			$type = 'internal';
			$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );
		} else {
			$type = 'external';
			$recipe_data = array(
				'type' => 'food',
				'parent_id' => true,
				'parent_url' => urldecode( $atts['link'] ),
				'permalink' => urldecode( $atts['link'] ),
				'name' => urldecode( $atts['name'] ),
				'summary' => urldecode( str_replace( '%0A', '<br/>', $atts['summary'] ) ),
				'parent_url_new_tab' => $atts['newtab'] ? true : false,
				'parent_url_nofollow' => $atts['nofollow'] ? true : false,
			);

			$image_id = intval( $atts['image'] );
			if ( -1 === $image_id ) {
				$recipe_data['image_id'] = 'url';
				$recipe_data['image_url'] = $atts['image_url'];
			} else {
				$recipe_data['image_id'] = $image_id;
			}

			$recipe = new WPRM_Recipe_Shell( $recipe_data );
		}

		if ( $recipe ) {
			$template = false;
			$template_slug = trim( $atts['template'] );

			if ( $template_slug ) {
				$template = WPRM_Template_Manager::get_template_by_slug( $template_slug );
			}

			if ( ! $template ) {
				$template = WPRM_Template_Manager::get_template_by_type( 'roundup', $recipe->type() );
			}

			if ( $template ) {
				// Add to used templates.
				WPRM_Template_Manager::add_used_template( $template );

				$align_class = '';
				if ( isset( $atts['align'] ) && $atts['align'] ) {
					$align_class = ' align' . $atts['align'];
				}

				$output = '<div class="wprm-recipe wprm-recipe-roundup-item wprm-recipe-roundup-item-' . $recipe->id() . ' wprm-recipe-template-' . $template['slug'] . $align_class . '" data-servings="' . esc_attr( $recipe->servings() ). '">';

				if ( 'internal' === $type ) {
					WPRM_Template_Shortcodes::set_current_recipe_id( $recipe->id() );
					$output .= do_shortcode( $template['html'] );
					WPRM_Template_Shortcodes::set_current_recipe_id( false );
				} else {
					WPRM_Template_Shortcodes::set_current_recipe_shell( $recipe );
					$output .= do_shortcode( $template['html'] );
					WPRM_Template_Shortcodes::set_current_recipe_shell( false );
				}

				$output .= '</div>';

				return $output;
			}
		}

		return '';
	}
}

WPRM_Recipe_Roundup::init();
