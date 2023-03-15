<?php
/**
 * Handle the recipe printing.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the recipe printing.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Print {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'print_page' ) );

		add_filter( 'wprm_print_output', array( __CLASS__, 'output_first' ), 1, 2 );
		add_filter( 'wprm_print_output', array( __CLASS__, 'output_last' ), 999, 2 );
	}

	/**
	 * Get the print slug.
	 *
	 * @since	6.1.0
	 */
	public static function slug() {
		$slug = WPRM_Settings::get( 'print_slug' );

		if ( ! $slug ) {
			$slug = WPRM_Settings::get_default( 'print_slug' );
		}

		return $slug;
	}

	/**
	 * Check if someone is trying to reach the print page.
	 *
	 * @since    1.3.0
	 */
	public static function print_page() {
		preg_match( '/[\/\?]' . self::slug() . '[\/=](.+?)(\/)?$/', $_SERVER['REQUEST_URI'], $print_url ); // Input var okay.
		$print_query = isset( $print_url[1] ) ? $print_url[1] : '';

		// Get individual print args.
		$print_query = str_ireplace( '?', '/', $print_query );
		$print_query = str_ireplace( '&', '/', $print_query );
		$print_args = explode( '/', $print_query );

		// Backwards compatibility with old /wprm_print/123 URL
		if ( '' . intval( $print_args[0] ) === $print_args[0] ) {
			$print_args = array_merge(
				array( 'recipe' ),
				$print_args
			);
		}

		if ( $print_args && 1 <= count( $print_args ) && $print_args[0] ) {
			WPRM_Context::set( 'print' );

			// Get assets to output while making sure nothing gets output yet.
			ob_start();
			$output = apply_filters( 'wprm_print_output', array(
				'type' => false,
				'assets' => array(),
				''
			), $print_args );
			$prevent_from_getting_output = ob_get_contents();
			ob_end_clean();

			if ( $output && $output['type'] ) {
				// Prevent WP Rocket lazy image loading on print page.
				add_filter( 'do_rocket_lazyload', '__return_false' );

				// Prevent Avada lazy image loading on print page.
				if ( class_exists( 'Fusion_Images' ) && property_exists( 'Fusion_Images', 'lazy_load' ) ) {
					Fusion_Images::$lazy_load = false;
				}

				// Load print template file.
				header( 'HTTP/1.1 200 OK' );
				require( WPRM_DIR . 'templates/public/print.php' );
				flush();
				exit;
			} else {
				// Redirect to homepage.
				wp_redirect( home_url() );
				exit();
			}
		}
	}

	/**
	 * Get output for the print page with high priority.
	 *
	 * @since    6.1.0
	 * @param	array $output 	Current output for the print page.
	 * @param	array $args	 	Arguments for the print page.
	 */
	public static function output_first( $output, $args ) {
		// Default assets to load.
		$output['assets'][] = array(
			'type' => 'css',
			'url' => WPRM_URL . 'dist/public-' . WPRM_Settings::get( 'recipe_template_mode' ) . '.css',
		);
		$output['assets'][] = array(
			'type' => 'css',
			'url' => WPRM_URL . 'dist/print.css',
		);
		$output['assets'][] = array(
			'type' => 'js',
			'url' => WPRM_URL . 'dist/print.js',
		);
		$output['assets'][] = array(
			'type' => 'custom',
			'html' => '<style>' . WPRM_Assets::get_custom_css( 'print' ) . '</style>',
		);
		$output['assets'][] = array(
			'type' => 'custom',
			'html' => self::print_accent_color_styling(),
		);
		$output['assets'][] = array(
			'type' => 'custom',
			'html' => '<script>var wprm_print_settings = ' . wp_json_encode( array(
				'print_remove_links' => WPRM_Settings::get( 'print_remove_links' ),
			) ) . ';</script>',
		);

		if ( WPRM_Settings::get( 'print_recipe_page_break' ) ) {
			$output['assets'][] = array(
				'type' => 'custom',
				'html' => '<style>@media print { .wprm-print-recipe + .wprm-print-recipe { page-break-before: always; } }</style>',
			);
		}

		// Printing a single recipe.
		if ( 'recipe' === $args[0] ) {
			$recipe_id = intval( $args[1] );

			if ( WPRM_POST_TYPE === get_post_type( $recipe_id ) ) {
				$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

				if ( ! self::has_permission( $recipe ) ) {
					return $output;
				}

				// Get template to output.
				$template = false;
				if ( isset( $args[2] ) ) {
					$template_slug = sanitize_key( $args[2] );
					$template = WPRM_Template_Manager::get_template_by_slug( $template_slug );
				}

				// Use default print template if no specific template set.
				if ( ! $template ) {
					$template = WPRM_Template_Manager::get_template_by_type( 'print', $recipe->type() );
				}

				// Add styling for this recipe's print template.
				$output['assets'][] = array(
					'type' => 'custom',
					'html' => '<style type="text/css">' . WPRM_Template_Manager::get_template_css( $template ) . '</style>',
				);

				// Add options to header.
				$output['header'] = self::print_header_images( $recipe );
				$output['type'] = 'recipe';
				$output['recipe'] = $recipe;
				$output['title'] = $recipe->name() . ' - ' . get_bloginfo( 'name' );
				$output['url'] = $recipe->permalink();
				$output['html'] = '<div id="wprm-print-recipe-0" data-recipe-id="' . $recipe_id . '" class="wprm-print-recipe wprm-print-recipe-' . $recipe_id . '"  data-servings="' . esc_attr( $recipe->servings() ) . '" data-recipe-unit-system="' . esc_attr( $recipe->unit_system() ) . '">' . WPRM_Template_Manager::get_template( $recipe, 'print', $template['slug'] ) . '</div>';
			}
		}

		// Printing a list of recipes.
		if ( 'recipes' === $args[0] ) {
			$recipes = array();
			$recipe_ids = self::decode_ids( $args[1] );

			if ( $recipe_ids ) {
				$all_recipes = array();
				for ( $i = 0; $i < count( $recipe_ids ); $i += 2 ) {
					// Servings passed along as large number to support decimals.
					$servings_passed = floatval( $recipe_ids[ $i + 1 ] );
					$servings = $servings_passed / pow( 10, 6 );

					// Prevent most backwards compatibility issues.
					if ( $servings_passed < 100 ) {
						$servings = $servings_passed;
					}

					$all_recipes[] = array(
						'id' => intval( $recipe_ids[ $i ] ),
						'servings' => $servings,
					);
				}
	
				// Maybe remove duplicates.
				if ( WPRM_Settings::get( 'recipe_collections_print_recipes_multiple_times' ) ) {
					$unique_recipes = $all_recipes;
				} else {
					$serialized = array_map( 'serialize', $all_recipes );
					$unique = array_unique( $serialized );
					$unique_recipes = array_intersect_key( $all_recipes, $unique );
				}

				$uid = 1;
				$output['html'] = '';
				foreach ( $unique_recipes as $unique_recipe ) {
					if ( WPRM_POST_TYPE === get_post_type( $unique_recipe['id'] ) ) {
						$recipe = WPRM_Recipe_Manager::get_recipe( $unique_recipe['id'] );
	
						if ( ! self::has_permission( $recipe ) ) {
							continue;
						}
	
						$recipes[] = array(
							'id' => $uid,
							'recipe_id' => $unique_recipe['id'],
							'original_servings' => $recipe->servings(),
							'servings' => 0 < $unique_recipe['servings'] ? $unique_recipe['servings'] : $recipe->servings(),
						);
	
						// Add styling for this recipe's print template.
						$output['assets'][] = array(
							'type' => 'custom',
							'html' => WPRM_Template_Manager::get_template_styles( $recipe, 'print' ),
						);
						$recipe_template = WPRM_Template_Manager::get_template( $recipe, 'print' );

						// Update ID for adjustable servings.
						$recipe_template = str_replace( 'wprm-recipe-servings-' . $unique_recipe['id'], 'wprm-recipe-servings-' . $uid, $recipe_template );
						$recipe_template = str_replace( 'wprm-recipe-adjustable-servings-' . $unique_recipe['id'] . '-container', 'wprm-recipe-adjustable-servings-' . $uid . '-container', $recipe_template );
						$recipe_template = str_replace( 'wprm-recipe-advanced-servings-' . $unique_recipe['id'] . '-container', 'wprm-recipe-advanced-servings-' . $uid . '-container', $recipe_template );

						$output['html'] .= '<div id="wprm-print-recipe-' . $uid . '" data-recipe-id="' . $uid . '" class="wprm-print-recipe wprm-print-recipe-' . $uid . '" data-servings="' . esc_attr( $recipe->servings() ) . '" data-recipe-unit-system="' . esc_attr( $recipe->unit_system() ) . '">' . $recipe_template . '</div>';
						$uid++;
					}
				}
			}
			
			// Add options to header.
			$output['header'] = self::print_header_images();
			$output['type'] = 'recipes';
			$output['recipes'] = $recipes;
		}

		return $output;
	}

	/**
	 * Get custom styling for the print accent color.
	 *
	 * @since    6.1.0
	 */
	public static function print_accent_color_styling() {
		$output = '';
		$color = WPRM_Settings::get( 'print_accent_color' );
		$color_default = WPRM_Settings::get_default( 'print_accent_color' );

		if ( $color !== $color_default ) {
			$output .= '<style>';
			$output .= ' #wprm-print-button-print { border-color: ' . $color . ' !important; background-color: ' . $color . ' !important; }';
			$output .= ' .wprm-print-toggle:checked + label:before { border-color: ' . $color . ' !important; background: ' . $color . ' !important; }';
			$output .= ' .wprm-print-option-container a.wprm-print-option { border-color: ' . $color . ' !important; }';
			$output .= ' .wprm-print-option-container a.wprm-print-option.option-active { background-color: ' . $color . ' !important; }';
			$output .= '</style>';
		}

		return $output;
	}

	/**
	 * Get print header image toggles.
	 *
	 * @since    6.1.0
	 * @param	mixed $recipe Recipe getting printed.
	 */
	public static function print_header_images( $recipe = false ) {
		$header = '';

		// Recipe image toggle.
		if ( false === $recipe || $recipe->image() ) {
			$checked = WPRM_Settings::get( 'print_show_recipe_image' ) ? 'checked="checked"' : '';

			$header .= '<div class="wprm-print-toggle-container">';
			$header .= '<input type="checkbox" id="wprm-print-toggle-recipe-image" class="wprm-print-toggle" value="1" ' . $checked . '/><label for="wprm-print-toggle-recipe-image">' . __( 'Recipe Image', 'wp-recipe-maker' ) . '</label>';
			$header .= '</div>';
		}

		// Instruction images toggle.
		$has_instructions_media = false;
		$instructions_flat = $recipe ? $recipe->instructions_flat() : array();

		foreach( $instructions_flat as $instruction ) {
			if ( isset( $instruction['image'] ) && $instruction['image'] || ( isset( $instruction['video'] ) && isset( $instruction['video']['type'] ) && in_array( $instruction['video']['type'], array( 'upload', 'embed' ) ) ) ) {
				$has_instructions_media = true;
				break;
			}
		}

		if ( false === $recipe || $has_instructions_media ) {
			$checked = WPRM_Settings::get( 'print_show_instruction_images' ) ? ' checked="checked"' : '';

			$header .= '<div class="wprm-print-toggle-container">';
			$header .= '<input type="checkbox" id="wprm-print-toggle-recipe-instruction-media" class="wprm-print-toggle" value="1" ' . $checked . '/><label for="wprm-print-toggle-recipe-instruction-media">' . __( 'Instruction Images', 'wp-recipe-maker' ) . '</label>';
			$header .= '</div>';
		}

		// Recipe notes toggle.
		if ( false === $recipe || $recipe->notes() ) {
			$checked = WPRM_Settings::get( 'print_show_notes' ) ? ' checked="checked"' : '';

			$header .= '<div class="wprm-print-toggle-container">';
			$header .= '<input type="checkbox" id="wprm-print-toggle-recipe-notes" class="wprm-print-toggle" value="1" ' . $checked . '/><label for="wprm-print-toggle-recipe-notes">' . __( 'Notes', 'wp-recipe-maker' ) . '</label>';
			$header .= '</div>';
		}

		return $header;
	}

	/**
	 * Get output for the print page with low priority.
	 *
	 * @since    6.1.0
	 * @param	array $output 	Current output for the print page.
	 * @param	array $args	 	Arguments for the print page.
	 */
	public static function output_last( $output, $args ) {
		if ( $output ) {
			// Add optional custom print CSS setting.
			if ( isset( $output['assets'] ) ) {
				$custom_print_css = WPRM_Settings::get( 'print_css' );

				if ( $custom_print_css ) {
					$output['assets'][] = array(
						'type' => 'custom',
						'html' => '<style>' . $custom_print_css . '</style>',
					);	
				}
			}

			// Add optional print credit.
			if ( 'recipe' === $args[0] && isset( $output['html'] ) ) {
				if ( WPRM_Settings::get( 'print_credit_use_html' ) ) {
					$credit = WPRM_Settings::get( 'print_credit_html' );
				} else {
					$credit = WPRM_Settings::get( 'print_credit' );
				}

				if ( $credit ) {
					$recipe = WPRM_Recipe_Manager::get_recipe( intval( $args[1] ) );
					$output['html'] .= '<div id="wprm-print-footer">' . $recipe->replace_placeholders( $credit ) . '</div>';

					// Add class to indicate there's a print credit.
					if ( ! isset( $output['classes'] ) ) {
						$output['classes'] = array();
					}
					$output['classes'][] = 'wprm-print-has-footer';
				}
			}

			// Add option link back.

			if ( isset( $output['url'] ) && $output['url'] ) {
				$output['assets'][] = array(
					'type' => 'custom',
					'html' => '<script>var wprm_print_url = ' . wp_json_encode( $output['url'] ) . ';</script>',
				);
			}

			// Print size options.
			if ( WPRM_Settings::get( 'print_size_options' ) ) {
				$output['header'] .= self::get_size_header();
			}
		}

		return $output;
	}

	/**
	 * Get header for size options.
	 *
	 * @since	8.7.0
	 */
	private static function get_size_header() {
		$output = '';

		$output .= '<div id="wprm-print-size-container" class="wprm-print-option-container">';

		$options = array(
			'small' => __( 'Smaller', 'wp-recipe-maker' ),
			'normal' => __( 'Normal', 'wp-recipe-maker' ),
			'large' => __( 'Larger', 'wp-recipe-maker' ),
		);

		foreach ( $options as $value => $label ) {
			$active = 'normal' === $value ? ' option-active' : '';
			$output .= '<a href="#" role="button" class="wprm-print-size wprm-print-option' . $active . '" data-size="' . esc_attr( $value ) . '" aria-label="' . __( 'Make print size', 'wp-recipe-maker' ) . ' ' . esc_attr( $label ) . '">' . $label . '</a>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Bulk print recipes.
	 *
	 * @since	6.1.0
	 * @param	array $recipe_ids IDs of the recipes to print.
	 */
	public static function bulk_print_url( $recipe_ids ) {
		$ids_to_encode = array();
		foreach( $recipe_ids as $id ) {
			$ids_to_encode[] = $id;
			$ids_to_encode[] = 0; // Use original servings.
		}
		$encoded = self::encode_ids( $ids_to_encode );

		// Combine with function in class-wprm-recipe:
		$home_url = WPRM_Compatibility::get_home_url();
		$query_params = false;

		if ( false !== strpos( $home_url, '?' ) ) {
			$home_url_parts = explode( '?', $home_url, 2 );

			$home_url = trailingslashit( $home_url_parts[0] );
			$query_params = $home_url_parts[1];
		}

		if ( get_option( 'permalink_structure' ) ) {
			$print_url = $home_url . WPRM_Print::slug() . '/recipes/' . $encoded;

			if ( $query_params ) {
				$print_url .= '?' . $query_params;
			}
		} else {
			$print_url = $home_url . '?' . WPRM_Print::slug() . '=recipes&' . $encoded;

			if ( $query_params ) {
				$print_url .= '&' . $query_params;
			}
		}

		return $print_url;
	}

	/**
	 * Encode recipe ids for printing.
	 *
	 * @since	6.1.0
	 * @param	array $recipe_ids	IDs of the recipes to encode for printing.
	 * @param	mixed $servings 	Optional servings for these recipes.
	 */
	public static function encode_ids( $recipe_ids ) {
		require_once( WPRM_DIR . 'vendor/hashids/lib/Hashids/HashGenerator.php' );
		require_once( WPRM_DIR . 'vendor/hashids/lib/Hashids/Hashids.php' );
		$hashids = new Hashids\Hashids('wp-recipe-maker');
		return $hashids->encode( $recipe_ids );
	}

	/**
	 * Encode recipe ids from printed URL.
	 *
	 * @since	6.1.0
	 * @param	mixed $encoded 	Encoded recipe ids.
	 */
	public static function decode_ids( $encoded ) {
		require_once( WPRM_DIR . 'vendor/hashids/lib/Hashids/HashGenerator.php' );
		require_once( WPRM_DIR . 'vendor/hashids/lib/Hashids/Hashids.php' );
		$hashids = new Hashids\Hashids('wp-recipe-maker');
		return $hashids->decode( $encoded );
	}

	/**
	 * Check if current user has the permissions to print a particular recipe.
	 * 
	 * @since	6.3.0
	 * @param	mixed $recipe Recipe to print.
	 */
	public static function has_permission( $recipe ) {
		// Admin always has permission.
		if ( current_user_can( 'administrator' ) ) {
			return true;
		}

		// Simple check: is recipe published?
		if ( WPRM_Settings::get( 'print_published_recipes_only' ) && 'publish' !== $recipe->post_status() ) {
			return false;
		}

		// Advanced check: is recipe readable in parent post?
		if ( WPRM_Settings::get( 'print_recipes_in_parent_content_only' ) ) {
			$parent_post = $recipe->parent_post();

			if ( $parent_post ) {
				// Make sure the_content filter gets applied correctly (membership plugins).
				$GLOBALS['post'] = $parent_post;
				setup_postdata( $parent_post );

				$post_content = apply_filters( 'the_content', $parent_post->post_content );

				if ( false !== strpos( $post_content, 'id="wprm-recipe-container-' . $recipe->id() . '"') ) {
					return true;
				}
			}
			
			return false;
		}

		return true;
	}
}

WPRM_Print::init();
