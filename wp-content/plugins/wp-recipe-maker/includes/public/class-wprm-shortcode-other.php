<?php
/**
 * Handle the other shortcodes.
 *
 * @link       https://bootstrapped.ventures
 * @since      5.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the other shortcodes.
 *
 * @since      5.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Other {

	/**
	 * Register actions and filters.
	 *
	 * @since	5.6.0
	 */
	public static function init() {
		add_shortcode( 'adjustable', array( __CLASS__, 'adjustable_shortcode' ) );
		add_shortcode( 'timer', array( __CLASS__, 'timer_shortcode' ) );
		add_shortcode( 'wprm-temperature', array( __CLASS__, 'temperature_shortcode' ) );
		add_shortcode( 'wprm-glossary', array( __CLASS__, 'glossary_shortcode' ) );
		add_shortcode( 'wprm-ingredient', array( __CLASS__, 'ingredient_shortcode' ) );
		add_shortcode( 'wprm-condition', array( __CLASS__, 'condition_shortcode' ) );

		add_filter( 'wprm_localize_admin', array( __CLASS__, 'temperature_icons' ) );
		add_filter( 'the_content', array( __CLASS__, 'recipe_counter_total' ), 99 );
	}

	/**
	 * Output for the adjustable shortcode.
	 *
	 * @since	1.5.0
	 * @param	array $atts 		Shortcode attributes.
	 * @param	mixed $content Content in between the shortcodes.
	 */
	public static function adjustable_shortcode( $atts, $content ) {
		return '<span class="wprm-dynamic-quantity">' . $content . '</span>';
	}

	/**
	 * Output for the timer shortcode.
	 *
	 * @since	1.5.0
	 * @param	array $atts 	Shortcode attributes.
	 * @param	mixed $content Content in between the shortcodes.
	 */
	public static function timer_shortcode( $atts, $content ) {
		$has_label = isset( $atts['label'] );
		$atts = shortcode_atts( array(
			'seconds' => '0',
			'minutes' => '0',
			'hours' => '0',
			'label' => '',
		), $atts, 'wprm_timer' );

		$seconds = intval( $atts['seconds'] );
		$minutes = intval( $atts['minutes'] );
		$hours = intval( $atts['hours'] );

		$seconds = $seconds + (60 * $minutes) + (60 * 60 * $hours);

		if ( $seconds > 0 ) {
			$data_label = $has_label ? ' data-label="' . esc_attr( $atts['label'] ) . '"' : '';
			return '<span class="wprm-timer" data-seconds="' . esc_attr( $seconds ) . '"' . $data_label . '>' . $content . '</span>';
		} else {
			return $content;
		}
	}

	/**
	 * Output for the temperature shortcode.
	 *
	 * @since	8.4.0
	 * @param	array $atts		Shortcode attributes.
	 */
	public static function temperature_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'icon' => '',
			'value' => '',
			'unit' => WPRM_Settings::get( 'default_temperature_unit' ),
			'help' => '',
		), $atts, 'wprm_temperature' );

		// Value needs to be set.
		if ( '' === $atts['value'] ) {
			return '';
		}

		$icon = sanitize_key( $atts['icon'] );
		$value = sanitize_text_field( $atts['value'] );
		$unit = strtoupper( sanitize_key( $atts['unit'] ) );
		$help = sanitize_text_field( $atts['help'] );

		// Classes.
		$classes = array(
			'wprm-temperature-container',
		);

		if ( $atts['help'] ) {
			$classes[] = 'wprm-tooltip';
		}

		// Construct data.
		$data = '';
		$data .= ' data-value="' . esc_attr( $value ) .  '"';
		$data .= ' data-unit="' . esc_attr( $unit ) .  '"';
		$data .= WPRM_Tooltip::get_tooltip_data( $help );

		// Construct output.
		$output = '';
		$output .= '<span class="' . implode( ' ', $classes ) . '"' . $data . '>';

		// Icon output
		if ( $icon && file_exists( WPRM_DIR . 'assets/icons/temperature/' . $icon . '.svg' ) ) {
			$output .= '<span class="wprm-temperature-icon">';
			$output .= '<img src="' . WPRM_URL . 'assets/icons/temperature/' . $icon . '.svg" alt="' . esc_attr( $help ) . '">';
			$output .= '</span>';
		}

		// Value output
		$output .= '<span class="wprm-temperature-value">';
		$output .= esc_html( $value );
		$output .= '</span>';

		// Unit output
		if ( in_array( $unit, array( 'C', 'F' ) ) ) {
			$output .= '<span class="wprm-temperature-unit">';
			switch ( $unit ) {
				case 'C':
					$output .= ' °C';
					break;
				case 'F':
					$output .= ' °F';
					break;
			}
			$output .= '</span>';
		}

		$output .= '</span>';

		return apply_filters( 'wprm_temperature_shortcode', $output, $atts );
	}

	/**
	 * Output for the glossary shortcode.
	 *
	 * @since	8.9.0
	 * @param	array $atts		Shortcode attributes.
	 */
	public static function glossary_shortcode( $atts, $content ) {
		$atts = shortcode_atts( array(
			'id' => '',
		), $atts, 'wprm_glossary' );

		$id = intval( $atts['id'] );
		$original_text = $content ? $content : '';
		$term = $id ? get_term( $id, 'wprm_glossary_term' ) : false;

		$output = '';

		if ( $term ) {
			$name = $original_text ? $original_text : $term->name;

			if ( $name ) {
				$classes = array(
					'wprm-glossary-term',
					'wprm-glossary-term-' . $term->term_id,
				);

				$tooltip = $term->description;
				$data_tooltip = '';

				if ( $tooltip ) { 
					$classes[] = 'wprm-tooltip';
					$data_tooltip = WPRM_Tooltip::get_tooltip_data( $tooltip );
				}

				$output = '<span class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $data_tooltip . '>' . $name . '</span>';
			}
		}

		return $output;
	}

	/**
	 * Output for the ingredient shortcode.
	 *
	 * @since	8.4.0
	 * @param	array $atts		Shortcode attributes.
	 */
	public static function ingredient_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => '',
			'uid' => '',
			'text' => '',
			'style' => 'bold',
			'color' => '',
			'notes_separator' => '',
			'unit_conversion' => '',
			'unit_conversion_both_style' => '',
			'unit_conversion_show_identical' => '',
		), $atts, 'wprm_ingredient' );

		// Default to text as output.
		$output = WPRM_Shortcode_Helper::sanitize_html( $atts['text'] );

		// Get recipe (defaults to current).
		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );

		if ( $recipe && '' !== $atts['uid'] ) {
			$uid_str = (string) $atts['uid'];
			$is_split = false;
			$parent_uid = null;
			$split_id = null;
			$split_percentage = null;
			
			// Check if this is a split (format: "uid:splitId")
			if ( strpos( $uid_str, ':' ) !== false ) {
				$is_split = true;
				$parts = explode( ':', $uid_str, 2 );
				if ( count( $parts ) === 2 && is_numeric( $parts[0] ) && is_numeric( $parts[1] ) ) {
					$parent_uid = intval( $parts[0] );
					$split_id = intval( $parts[1] );
				} else {
					// Invalid split format, fall back to text
					return $output;
				}
			} else if ( is_numeric( $atts['uid'] ) ) {
				$parent_uid = intval( $atts['uid'] );
			} else {
				// Invalid UID format, fall back to text
				return $output;
			}

			$ingredients_flat = $recipe->ingredients_flat();
			$index = array_search( $parent_uid, array_column( $ingredients_flat, 'uid' ) );

			if ( false !== $index && isset( $ingredients_flat[ $index ] ) ) {
				$found_ingredient = $ingredients_flat[ $index ];

				if ( 'ingredient' === $found_ingredient['type'] ) {
					// If this is a split, find the split and calculate amount
					if ( $is_split ) {
						$found_split = null;
						if ( isset( $found_ingredient['splits'] ) && is_array( $found_ingredient['splits'] ) ) {
							foreach ( $found_ingredient['splits'] as $split ) {
								if ( isset( $split['id'] ) && intval( $split['id'] ) === $split_id && isset( $split['percentage'] ) ) {
									$found_split = $split;
									$split_percentage = floatval( $split['percentage'] );
									break;
								}
							}
						}
						
						if ( ! $found_split ) {
							// Split not found, fall back to text
							return $output;
						}
						
						// Calculate split amount from parent amount and percentage
						$parent_amount = isset( $found_ingredient['amount'] ) ? $found_ingredient['amount'] : '';
						$parent_amount_parsed = WPRM_Recipe_Parser::parse_quantity( $parent_amount );
						
						$split_amount = '';
						if ( $parent_amount_parsed > 0 ) {
							$split_amount_parsed = ( $parent_amount_parsed * $split_percentage ) / 100;
							$decimals = intval( WPRM_Settings::get( 'adjustable_servings_round_to_decimals' ) );
							if ( $decimals < 0 ) {
								$decimals = 0;
							}
							
							// Format the calculated amount.
							$split_amount = WPRM_Recipe_Parser::format_quantity( $split_amount_parsed, $decimals, WPRM_Settings::get( 'fractions_enabled' ), true );
						}
						
						// Use parent's unit
						$unit = isset( $found_ingredient['unit'] ) ? $found_ingredient['unit'] : '';
					} else {
						// Regular ingredient
						$split_amount = isset( $found_ingredient['amount'] ) ? $found_ingredient['amount'] : '';
						$unit = isset( $found_ingredient['unit'] ) ? $found_ingredient['unit'] : '';
					}
					
					$parts = array();

					if ( $split_amount ) { $parts[] = $split_amount; };
					if ( $unit ) { $parts[] = $unit; };

					// Optionally add second unit system.
					$show_both_units = 'both' === $atts['unit_conversion'];
					if ( $show_both_units ) {
						$ingredient_for_output = $found_ingredient;

						if ( $is_split && null !== $split_percentage ) {
							$ingredient_for_output['amount'] = $split_amount;
							$ingredient_for_output['unit'] = $unit;

							if ( isset( $ingredient_for_output['converted'] ) && is_array( $ingredient_for_output['converted'] ) ) {
								foreach ( $ingredient_for_output['converted'] as $system => $converted ) {
									if ( isset( $converted['amount'] ) && '' !== $converted['amount'] ) {
										$converted_amount = WPRM_Recipe_Parser::parse_quantity( $converted['amount'] );

										if ( $converted_amount > 0 ) {
											$converted_split = ( $converted_amount * $split_percentage ) / 100;
											// Check system-specific fraction setting, fall back to general setting.
											$allow_fractions = WPRM_Settings::get( 'unit_conversion_system_' . $system . '_fractions' );
											$ingredient_for_output['converted'][ $system ]['amount'] = WPRM_Recipe_Parser::format_quantity( $converted_split, $decimals, $allow_fractions, true );
										} else {
											$ingredient_for_output['converted'][ $system ]['amount'] = '';
										}
									}
								}
							}
						}

						$amount_unit = apply_filters( 'wprm_recipe_ingredients_shortcode_amount_unit', implode( ' ', $parts ), $atts, $ingredient_for_output );
					}

					// Ingredient name and maybe notes.
					$name_with_notes = '';
					if ( $found_ingredient['name'] ) { $name_with_notes = $found_ingredient['name']; };

					if ( '' !== $atts['notes_separator'] ) {
						if ( $found_ingredient['notes'] ) {
							switch ( $atts['notes_separator'] ) {
								case 'comma':
									$name_with_notes .= ', ' . $found_ingredient['notes'];
									break;
								case 'dash':
									$name_with_notes .= ' - ' . $found_ingredient['notes'];
									break;
								case 'parentheses':
									$name_with_notes .= ' (' . $found_ingredient['notes'] . ')';
									break;
								default:
									$name_with_notes .= ' ' . $found_ingredient['notes'];
							}
						}
					}
					$parts[] = $name_with_notes;

					$text_to_show = implode( ' ', $parts );

					if ( $text_to_show ) {
						// Use the full UID (including split ID) for the class
						// Replace colon with dash in class name to avoid CSS selector issues
						$uid_for_class = $is_split ? str_replace( ':', '-', $uid_str ) : $parent_uid;
						
						$classes = array(
							'wprm-inline-ingredient',
							'wprm-inline-ingredient-' . $recipe->id() . '-' . $uid_for_class,
							'wprm-block-text-' . $atts['style'],
						);

						// Custom CSS style.
						$css = '';

						if ( $atts['color'] ) {
							$css = 'color: ' . $atts['color'] . ';';
						}
						$style = WPRM_Shortcode_Helper::get_inline_style( $css );

						// Needed to show both units?
						if ( $show_both_units ) {
							$text_to_show = $amount_unit . ' ' . $found_ingredient['name'];
						}

						// Keep notes?
						$data_keep_notes = '';

						if ( '' !== $atts['notes_separator'] ) {
							$data_keep_notes = ' data-notes-separator="' . esc_attr( $atts['notes_separator'] ) . '"';
						}
						
						// Add split data attributes for adjustable servings
						$split_data_attr = '';
						if ( $is_split && $split_percentage !== null ) {
							$split_data_attr = ' data-split-percentage="' . esc_attr( $split_percentage ) . '" data-split-id="' . esc_attr( $split_id ) . '"';
						}

						$output = '<span class="' . esc_attr( implode( ' ', $classes ) ) .'"' . $data_keep_notes . $split_data_attr . $style . '>' . $text_to_show . '</span>';
					}
				}
			}
		}

		return $output;
	}

	/**
	 * List of temperature icons to localize.
	 *
	 * @since	8.4.0
	 * @param	array $wprm_admin Admin variables to localize.
	 */
	public static function temperature_icons( $wprm_admin ) {
		$icons = array();
		$dir = WPRM_DIR . 'assets/icons/temperature';

		if ( $handle = opendir( $dir ) ) {
			while ( false !== ( $file = readdir( $handle ) ) ) {
				preg_match( '/^(.*?).svg/', $file, $match );
				if ( isset( $match[1] ) ) {
					$file = $match[0];
					$name = $match[1];
					
					$icons[ $name ] = array(
						'file' => WPRM_DIR . 'assets/icons/temperature/' . $file,
						'url' => WPRM_URL . 'assets/icons/temperature/' . $file,
					);
				}
			}
		}

		$wprm_admin['temperature'] = array(
			'default_unit' => WPRM_Settings::get( 'default_temperature_unit' ),
			'icons' => $icons,
		);

		return $wprm_admin;
	}

	/**
nn	 * Set the count and total for the recipe counter shortcode.
	 *
	 * @since	8.8.0
	 * @param	string $content The content to filter.
	 */
	public static function recipe_counter_total( $content ) {
		if ( isset( $GLOBALS['wprm_recipe_counter_needs_replacement'] ) && $GLOBALS['wprm_recipe_counter_needs_replacement'] ) {
			// Count the actual roundup items in the rendered content.
			// This ensures we only count items that are actually output, not items
			// processed by other plugins that filter the_content and run do_shortcode
			// without outputting the result.
			$total_count = 0;
			
			// Count roundup items by looking for the class in the rendered HTML.
			// Handle both single and double quotes for the class attribute.
			if ( preg_match_all( '/class=["\'][^"\']*\bwprm-recipe-roundup-item\b[^"\']*["\']/', $content, $matches ) ) {
				$total_count = count( $matches[0] );
			}
			
			// Fallback to 1 if no items found (shouldn't happen, but be safe).
			if ( 0 === $total_count ) {
				$total_count = 1;
			}
			
			// Replace %total% placeholders with the actual total count.
			$content = str_replace( '<span class="wprm-recipe-counter-total">1</span>', $total_count, $content );
			
			// Replace %count% placeholders with individual counts.
			// Find all count placeholders and replace them sequentially.
			$count = 0;
			$content = preg_replace_callback(
				'/<span class="wprm-recipe-counter-count">1<\/span>/',
				function( $matches ) use ( &$count ) {
					$count++;
					return $count;
				},
				$content
			);
		}

		return $content;
	}

	/**
	 * Output for the condition shortcode.
	 *
	 * @since	8.2.0
	 * @param	array $atts		Shortcode attributes.
	 * @param	mixed $content	Content in between the shortcodes.
	 */
	public static function condition_shortcode( $atts, $content ) {
		// If we're in the template editor preview, just return the content.
		if ( isset( $GLOBALS['wp']->query_vars['rest_route'] ) && '/wp-recipe-maker/v1/template/preview' === $GLOBALS['wp']->query_vars['rest_route'] ) {
			return do_shortcode( $content );
		}

		$atts = shortcode_atts( array(
			'id' => '0',
			'field' => '',
			'key' => '',
			'device' => '',
			'min_width' => '',
			'max_width' => '',
			'user' => '',
			'rating' => '',
			'taxonomy' => '',
			'term_ids' => '',
			'term_slugs' => '',
			'inverse' => '0',
		), $atts, 'wprm_condition' );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );

		$classes = array(
			'wprm-condition',
		);
		$matches_conditions = array();

		// Field conditions.
		if ( $atts['field'] ) {
			$field_condition = strtolower( $atts['field'] );
			$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );

			if ( $recipe ) {
				$classes[] = 'wprm-condition-field';
				$classes[] = 'wprm-condition-field-' . $field_condition;

				switch ( $field_condition ) {
					case 'image':
						$matches_conditions[] = 0 < intval( $recipe->image_id() ) || 'url' === $recipe->image_id() && $recipe->image_url();
						break;
					case 'video':
						$matches_conditions[] = '' !== $recipe->video();
						break;
					case 'nutrition':
						$matches_conditions[] = '' !== do_shortcode( '[wprm-nutrition-label id="' . $recipe->id() . '" style="simple" nutrition_values="serving"]' );
						break;
					case 'unit-conversion':
						$matches_conditions[] = '' !== do_shortcode( '[wprm-recipe-unit-conversion id="' . $recipe->id() . '"]' );
						break;
					case 'custom':
						$custom_field_key = sanitize_key( $atts['key'] );

						if ( $custom_field_key ) {
							$custom_field = $recipe->custom_field( $custom_field_key );

							$matches_conditions[] = '' !== $custom_field && false !== $custom_field;
						} else {
							$matches_conditions[] = false;
						}
						break;
					default:
						if ( method_exists( $recipe, $field_condition ) ) {
							$matches_conditions[] = ! ! $recipe->$field_condition();
						} else {
							$matches_conditions[] = false;
						}
						break;
				}
			}
		}

		// Taxonomy conditions.
		if ( $atts['taxonomy'] && ( $atts['term_ids'] || $atts['term_slugs'] ) ) {
			$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );

			if ( $recipe ) {
				// Get recipe taxonomy terms.
				$taxonomy = sanitize_key( $atts['taxonomy'] );
				$taxonomy = 'wprm_' === substr( $taxonomy, 0, 5 ) ? substr( $taxonomy, 5 ) : $taxonomy;
				$terms = $recipe->tags( $taxonomy );

				$classes[] = 'wprm-condition-taxonomy';
				$classes[] = '-taxonomy-' . $taxonomy;

				// Get terms to match on.
				if ( $atts['term_ids'] ) {
					$terms_to_match = wp_list_pluck( $terms, 'term_id' );

					$values = explode( ';', str_replace( ',', ';', trim( $atts['term_ids'] ) ) );
					$values = array_map( 'intval', $values );
				} else {
					$terms_to_match = wp_list_pluck( $terms, 'slug' );

					$values = explode( ';', str_replace( ',', ';', trim( $atts['term_slugs'] ) ) );
				}

				// Check for match.
				$matches = array_intersect( $values, $terms_to_match );

				// Matches if there is at least one.
				$matches_conditions[] = 0 < count( $matches );
			}
		}

		// Device conditions.
		if ( $atts['device'] ) {
			if ( ! class_exists( 'Mobile_Detect' ) ) {
				require_once( WPRM_DIR . 'vendor/Mobile-Detect/Mobile_Detect.php' );
			}

			if ( class_exists( 'Mobile_Detect' ) ) {
				$detect = new Mobile_Detect;

				// Check current device.
				$device = 'desktop';
				if ( $detect && $detect->isMobile() ) { $device = 'mobile'; }
				if ( $detect && $detect->isTablet() ) { $device = 'tablet'; }

				$device_condition = strtolower( str_replace( ',', ';', $atts['device'] ) );
				$device_conditions = explode( ';', $device_condition );
				$matches_conditions[] = in_array( $device, $device_conditions );

				$classes[] = 'wprm-condition-device';
				foreach ( $device_conditions as $device_condition ) {
					$classes[] = 'wprm-condition-device-' . $device_condition;
				}
			}
		}

		// Width conditions.
		if ( $atts['min_width'] || $atts['max_width'] ) {
			$classes[] = 'wprm-condition-width';
			$matches_conditions[] = true;

			if ( $atts['min_width'] ) {
				$value = intval( $atts['min_width'] );
				$classes[] = 'wprm-condition-min-width-' . $value;
			}
			if ( $atts['max_width'] ) {
				$value = intval( $atts['max_width'] );
				$classes[] = 'wprm-condition-max-width-' . $value;
			}
		}

		// User conditions.
		if ( $atts['user'] ) {
			$user_condition = strtolower( str_replace( '-', '_', $atts['user'] ) );

			$classes[] = 'wprm-condition-user';
			$classes[] = 'wprm-condition-user-' . str_replace( '_', '-', $user_condition );

			switch( $user_condition ) {
				case 'logged_in':
					$matches_conditions[] = is_user_logged_in();
					break;
				case 'guest':
				case 'logged_out':
					$matches_conditions[] = ! is_user_logged_in();
					break;
			}
		}

		// Rating conditions.
		if ( $atts['rating'] ) {
			$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );

			if ( $recipe ) {
				// Find boundaries.
				$rating_condition = explode( '-', $atts['rating'] );
				$lower_bound = floatval( $rating_condition[0] );
				$upper_bound = isset( $rating_condition[1] ) ? floatval( $rating_condition[1] ) : $lower_bound;

				// Get recipe rating.
				$rating = $recipe->rating();
				$rating = isset( $rating['average'] ) ? floatval( $rating['average'] ) : 0.0;

				$matches_conditions[] = $lower_bound <= $rating && $rating <= $upper_bound;
			}
		}

		// Combine conditions.
		if ( 0 < count( $matches_conditions ) ) {
			$match = true;
			foreach( $matches_conditions as $matches_condition ) {
				$match = $match && $matches_condition;
			}
		} else {
			$match = false;
		}
		
		// Optional inverse match.
		if ( (bool) $atts['inverse'] ) {
			$classes[] = 'wprm-condition-inverse';
			$match = ! $match;
		}

		// Return content if it matches the condition, empty otherwise.
		if ( $match ) {
			return '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">' . do_shortcode( $content ) . '</span>';
		} else {
			return '';
		}
	}
}

WPRM_Shortcode_Other::init();
