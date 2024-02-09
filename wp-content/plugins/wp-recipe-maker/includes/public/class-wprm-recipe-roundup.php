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
	 * Roundup overrides.
	 *
	 * @since	8.0.0
	 * @access	private
	 * @var		array $roundup_overrides Overrides to use for recipe values in the roundup.
	 */
	private static $roundup_overrides = array();

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
				$name = get_post_meta( get_the_ID(), 'wprm-recipe-roundup-name', true );
				$description = get_post_meta( get_the_ID(), 'wprm-recipe-roundup-description', true );

				self::output_itemlist_metadata( get_permalink( $post ), $name, $description, $recipe_ids );
			}
		}

		// Archive pages.
		if ( is_archive() && WPRM_Settings::get( 'itemlist_metadata_archive_pages' ) ) {
			self::list_metadata_for_archive_pages();	
		}
	}

	/**
	 * Output list metadata for archive pages.
	 *
	 * @since	8.0.0
	 */
	public static function list_metadata_for_archive_pages() {
		global $wp_query;

		$recipe_ids = array();
		foreach ( $wp_query->posts as $post ) {
			if ( WPRM_POST_TYPE === $post->post_type ) {
				$recipe_ids[] = $post->ID;
			} else if ( 'all' === WPRM_Settings::get( 'itemlist_metadata_archive_pages_post_types' ) ) {
				$recipe_ids_in_post = WPRM_Recipe_Manager::get_recipe_ids_from_post( $post->ID );

				if ( $recipe_ids_in_post ) {
					if ( ! WPRM_Settings::get( 'metadata_only_show_for_first_recipe' ) ) {
						// Output metadata for all recipes.
						$recipe_ids = array_merge( $recipe_ids, $recipe_ids_in_post );
					} else {
						// Only add metadata for first food recipe on page.
						foreach ( $recipe_ids_in_post as $recipe_id_in_post ) {
							$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id_in_post );
	
							if ( $recipe && 'other' !== $recipe->type() ) {
								$recipe_ids[] = $recipe_id_in_post;
								break;
							}
						}
					}
				}
			}
		}

		if ( 1 < count( $recipe_ids ) ) {
			// TODO term name.
			$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			self::output_itemlist_metadata( $url, '', '', $recipe_ids );
		}
	}

	/**
	 * Output ItemList metadata for a set of recipe ids.
	 *
	 * @since	8.0.0
	 * @param	mixed $url			URL of the roundup page.
	 * @param	mixed $name			Name for the ItemList.
	 * @param	mixed $description	Description for the ItemList.
	 * @param	mixed $post_ids 	IDs of the posts to get the ItemList metadata for.
	 */
	public static function output_itemlist_metadata( $url, $name, $description, $post_ids ) {
		$metadata = array(
			'@context' => 'http://schema.org',
			'@type' => 'ItemList',
			'url' => $url,
			'itemListElement' => array(),
		);

		if ( $name ) {
			$metadata['name'] = wp_strip_all_tags( $name );
		}

		if ( $description ) {
			$metadata['description'] = wp_strip_all_tags( $description );
		}

		$item_list_counter = 0;
		foreach ( $post_ids as $post_id ) {
			$url = false;
			
			if ( WPRM_POST_TYPE === get_post_type( $post_id ) ) {
				$recipe = WPRM_Recipe_Manager::get_recipe( $post_id );

				if ( $recipe ) {
					$url = $recipe->permalink();
				}
			} else {
				$url = get_permalink( $post_id );
			}

			if ( $url ) {
				$item_list_counter++;
				$metadata['itemListElement'][] = array(
					'@type'    => 'ListItem',
					'position' => $item_list_counter,
					'url'      => $url,
				);
			}
		}

		$metadata['numberOfItems'] = $item_list_counter;

		if ( 1 < $item_list_counter ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $metadata ) . '</script>';
		}
	}

	/**
	 * Get recipe roundup items from the content.
	 *
	 * @since    4.3.0
	 * @param    mixed $content Content to get the recipe roundup items from.
	 */
	public static function get_items_from_content( $content ) {
		$post_ids = array();

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
			$post_id = isset( $shortcode_options['id'] ) ? intval( $shortcode_options['id'] ) : 0;

			if ( $post_id ) {
				$post_ids[] = $post_id;
			}
		}

		return $post_ids;
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
				'credit' => '',
				'summary' => '',
				'name' => '',
				'button' => '',
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
		self::$roundup_overrides = array();

		if ( $recipe_id ) {
			$type = 'internal';
			$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

			if ( $atts['image'] && 0 < intval( $atts['image'] ) ) { self::$roundup_overrides['image_id'] = intval( $atts['image'] ); }
			if ( $atts['name'] ) 	{ self::$roundup_overrides['name'] = rawurldecode( $atts['name'] ); }
			if ( $atts['summary'] ) { self::$roundup_overrides['summary'] = rawurldecode( str_replace( '%0A', '<br/>', $atts['summary'] ) ); }

			// If no recipe was found, maybe it was of a "post" type instead.
			if ( ! $recipe ) {
				$post = get_post( $recipe_id );

				if ( $post && WPRM_POST_TYPE !== $post->post_type ) {
					$type = 'post';

					$image = $atts['image'] && 0 < intval( $atts['image'] ) ? intval( $atts['image'] ) : get_post_thumbnail_id( $post );
					$name = rawurldecode( $atts['name'] );
					$summary = rawurldecode( str_replace( '%0A', '<br/>', $atts['summary'] ) );

					$recipe_data = array(
						'type' => 'food',
						'parent_id' => true,
						'parent_url' => get_permalink( $post ),
						'permalink' => get_permalink( $post ),
						'post_status' => $post->post_status,
						'name' => $name ? $name : $post->post_title,
						'summary' => $summary ? $summary : '',
						'image_id' => $image,
						'parent_url_new_tab' => false,
						'parent_url_nofollow' => false,
					);

					$recipe = new WPRM_Recipe_Shell( $recipe_data );
				}
			}

			// Only display published recipes/posts.
			if ( WPRM_Settings::get( 'recipe_roundup_published_only' ) ) {
				if ( $recipe && 'publish' !== $recipe->post_status() ) {
					// If not in Gutenberg preview, return empty shortcode.
					if ( ! WPRM_Context::is_gutenberg_preview() ) {
						return '';
					}
				}
			}
		} else {
			$type = 'external';
			$recipe_data = array(
				'type' => 'food',
				'parent_id' => true,
				'parent_url' => rawurldecode( $atts['link'] ),
				'permalink' => rawurldecode( $atts['link'] ),
				'name' => rawurldecode( $atts['name'] ),
				'summary' => rawurldecode( str_replace( '%0A', '<br/>', $atts['summary'] ) ),
				'parent_url_new_tab' => $atts['newtab'] ? true : false,
				'parent_url_nofollow' => $atts['nofollow'] ? true : false,
				'credit' => rawurldecode( $atts['credit'] ),
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

		// Both internal and external.
		if ( $atts['button'] ) { self::$roundup_overrides['roundup_link_button_text'] = rawurldecode( $atts['button'] ); }

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
					$align_class = ' align' . esc_attr( $atts['align'] );
				}

				$output = '<div class="wprm-recipe wprm-recipe-roundup-item wprm-recipe-roundup-item-' . esc_attr( $recipe->id() ) . ' wprm-recipe-template-' . esc_attr( $template['slug'] ) . esc_attr( $align_class ) . '" data-servings="' . esc_attr( $recipe->servings() ). '">';

				// Add filters for overrides and immediately remove after doing shortcode.
				add_filter( 'wprm_recipe_roundup_link_text', array( __CLASS__, 'roundup_link_text_override' ) );
				if ( 'internal' === $type ) {
					add_filter( 'wprm_recipe_field', array( __CLASS__, 'recipe_field_overrides' ), 10, 2 );
					WPRM_Template_Shortcodes::set_current_recipe_id( $recipe->id() );
					$output .= do_shortcode( $template['html'] );
					WPRM_Template_Shortcodes::set_current_recipe_id( false );
					remove_filter( 'wprm_recipe_field', array( __CLASS__, 'recipe_field_overrides' ), 10, 2 );
				} else {
					WPRM_Template_Shortcodes::set_current_recipe_shell( $recipe );
					$output .= do_shortcode( $template['html'] );
					WPRM_Template_Shortcodes::set_current_recipe_shell( false );
				}
				remove_filter( 'wprm_recipe_roundup_link_text', array( __CLASS__, 'roundup_link_text_override' ) );

				$output .= '</div>';

				return $output;
			}
		}

		return '';
	}

	/**
	 * Maybe apply overrides to recipe fields.
	 *
	 * @since    8.0.0
	 * @param    mixed $output	Current recipe field output.
	 * @param    mixed $field	Current recipe field getting output.
	 */
	public static function recipe_field_overrides( $output, $field ) {
		foreach ( self::$roundup_overrides as $key => $value ) {
			if ( $value && $field === $key ) {
				return $value;
			}
		}

		return $output;
	}

	/**
	 * Maybe apply override to the roundup link text..
	 *
	 * @since    8.0.0
	 * @param    mixed $output	Current roundup link text.
	 */
	public static function roundup_link_text_override( $output ) {
		if ( isset( self::$roundup_overrides['roundup_link_button_text'] ) && self::$roundup_overrides['roundup_link_button_text'] ) {
			return self::$roundup_overrides['roundup_link_button_text'];
		}

		return $output;
	}
}

WPRM_Recipe_Roundup::init();
