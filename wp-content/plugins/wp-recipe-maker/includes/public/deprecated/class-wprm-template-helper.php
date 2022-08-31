<?php
/**
 * Providing helper functions to use in the recipe template.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/deprecated
 */

/**
 * Providing helper functions to use in the recipe template.
 *
 * @since      1.5.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/deprecated
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Template_Helper {

	/**
	 * Display a label that can be changed in the settings.
	 *
	 * @since    1.10.0
	 * @param	 mixed $uid 	UID of the label.
	 * @param	 mixed $default Default text for the label.
	 */
	public static function label( $uid, $default = '' ) {
		$uid = sanitize_key( $uid );

		$label = WPRM_Settings::get( 'label_' . $uid );

		return $label ? $label : $default;
	}

	/**
	 * Get the default labels.
	 *
	 * @since    1.10.0
	 */
	public static function get_default_labels() {
		$defaults = array(
			'print_button' => __( 'Print', 'wp-recipe-maker' ),
			'course_tags' => __( 'Course', 'wp-recipe-maker' ),
			'cuisine_tags' => __( 'Cuisine', 'wp-recipe-maker' ),
			'keyword_tags' => __( 'Keyword', 'wp-recipe-maker' ),
			'prep_time' => __( 'Prep Time', 'wp-recipe-maker' ),
			'cook_time' => __( 'Cook Time', 'wp-recipe-maker' ),
			'total_time' => __( 'Total Time', 'wp-recipe-maker' ),
			'servings' => __( 'Servings', 'wp-recipe-maker' ),
			'calories' => __( 'Calories', 'wp-recipe-maker' ),
			'author' => __( 'Author', 'wp-recipe-maker' ),
			'ingredients' => __( 'Ingredients', 'wp-recipe-maker' ),
			'instructions' => __( 'Instructions', 'wp-recipe-maker' ),
			'video' => __( 'Recipe Video', 'wp-recipe-maker' ),
			'notes' => __( 'Recipe Notes', 'wp-recipe-maker' ),
			'comment_rating' => __( 'Recipe Rating', 'wp-recipe-maker' ),
		);

		return apply_filters( 'wprm_label_defaults', $defaults );
	}

	/**
	 * Display the ingredient name with or without link.
	 *
	 * @since    1.5.0
	 * @param		 array   $ingredient Ingredient to display.
	 * @param		 boolean $show_link  Wether to display the ingredient link if present.
	 */
	public static function ingredient_name( $ingredient, $show_link = false, $recipe = false ) {
		$name = $ingredient['name'];
		$show_link = WPRM_Addons::is_active( 'premium' ) ? $show_link : false;

		$link = array();
		if ( $show_link ) {
			if ( $recipe && 'global' === $recipe->ingredient_links_type() ) {
				$link = WPRMP_Ingredient_Links::get_ingredient_link( $ingredient['id'] );
			} elseif ( isset( $ingredient['link'] ) ) {
				$link = $ingredient['link'];
			} else {
				$link = WPRMP_Ingredient_Links::get_ingredient_link( $ingredient['id'] );
			}

			// Easy Affiliate Links integration.
			if ( class_exists( 'EAFL_Link_Manager' ) ) {
				if ( isset( $link['eafl'] ) && $link['eafl'] ) {
					return do_shortcode( '[eafl id="' . $link['eafl'] .'"]' . $name . '[/eafl]' );
				}
			}
		}

		if ( isset( $link['url'] ) && $link['url'] ) {
			$target = WPRM_Settings::get( 'ingredient_links_open_in_new_tab' ) ? ' target="_blank"' : '';

			// Nofollow.
			switch ( $link['nofollow'] ) {
				case 'follow':
					$nofollow = '';
					break;
				case 'nofollow':
					$nofollow = ' rel="nofollow"';
					break;
				case 'sponsored':
					$nofollow = ' rel="nofollow sponsored"';
				default:
					$default = WPRM_Settings::get( 'ingredient_links_nofollow' );

					if ( 'nofollow' === $default ) {
						$nofollow = ' rel="nofollow"';
					} elseif ( 'sponsored' === $default ) {
						$nofollow = ' rel="nofollow sponsored"';
					}
			}

			return '<a href="' . $link['url'] . '"' . $target . $nofollow . '>' . $name . '</a>';
		} else {
			return $name;
		}
	}

	/**
	 * Display formatted time.
	 *
	 * @since    1.6.0
	 * @param	 mixed   $type Type of time we're displaying.
	 * @param	 int     $time Total minutes of time to display.
	 * @param	 boolean $show_zero Wether or not to show when value is zero.
	 * @param    boolean $shorthand Wether to use shorthand for the unit text.
	 */
	public static function time( $type, $time, $show_zero, $shorthand ) {
		$time = intval( $time );
		$days = floor( $time / (24 * 60) );
		$hours = floor( ( $time - $days * 24 * 60 ) / 60 );
		$minutes = ( $time - $days * 24 * 60 ) % 60;

		$output = '';

		if ( $days > 0 ) {
			$output .= '<span class="wprm-recipe-details wprm-recipe-details-days wprm-recipe-' . $type . ' wprm-recipe-' . $type . '-days">';
			$output .= $days;
			$output .= '</span> <span class="wprm-recipe-details-unit wprm-recipe-details-unit-days wprm-recipe-' . $type . '-unit wprm-recipe-' . $type . 'unit-days">';

			if ( $shorthand ) {
				$output .= $days != 1 ? __( 'd', 'wp-recipe-maker' ) : __( 'd', 'wp-recipe-maker' );
			} else {
				$output .= $days != 1 ? __( 'days', 'wp-recipe-maker' ) : __( 'day', 'wp-recipe-maker' );
			}

			$output .= '</span>';
		}

		if ( $hours > 0 ) {
			if ( $days > 0 ) {
				$output .= ' ';
			}
			$output .= '<span class="wprm-recipe-details wprm-recipe-details-hours wprm-recipe-' . $type . ' wprm-recipe-' . $type . '-hours">';
			$output .= $hours;
			$output .= '</span> <span class="wprm-recipe-details-unit wprm-recipe-details-unit-hours wprm-recipe-' . $type . '-unit wprm-recipe-' . $type . 'unit-hours">';

			if ( $shorthand ) {
				$output .= $hours != 1 ? __( 'hrs', 'wp-recipe-maker' ) : __( 'hr', 'wp-recipe-maker' );
			} else {
				$output .= $hours != 1 ? __( 'hours', 'wp-recipe-maker' ) : __( 'hour', 'wp-recipe-maker' );
			}

			$output .= '</span>';
		}

		if ( $minutes > 0 || ( 0 === $time && $show_zero ) ) {
			if ( $days > 0 || $hours > 0 ) {
				$output .= ' ';
			}
			$output .= '<span class="wprm-recipe-details wprm-recipe-details-minutes wprm-recipe-' . $type . ' wprm-recipe-' . $type . '-minutes">';
			$output .= $minutes;
			$output .= '</span> <span class="wprm-recipe-details-unit wprm-recipe-details-minutes wprm-recipe-' . $type . '-unit wprm-recipe-' . $type . 'unit-minutes">';

			if ( $shorthand ) {
				$output .= $minutes != 1 ? __( 'mins', 'wp-recipe-maker' ) : __( 'min', 'wp-recipe-maker' );
			} else {
				$output .= $minutes != 1 ? __( 'minutes', 'wp-recipe-maker' ) : __( 'minute', 'wp-recipe-maker' );
			}

			$output .= '</span>';
		}

		return $output;
	}

	/**
	 * Display the recipe rating as stars.
	 *
	 * @since    1.6.0
	 * @param    array 	 $rating       Rating to display.
	 * @param    boolean $show_details Wether to display the rating details.
	 */
	public static function rating_stars( $rating, $show_details = false ) {
		$user_ratings = WPRM_Addons::is_active( 'premium' ) && WPRM_Settings::get( 'features_user_ratings' );
		$rating_value = ceil( $rating['average'] );

		// Only output when there is an actual rating or users can rate.
		if ( ! $user_ratings && ! $rating_value ) {
			return '';
		}

		$functions = '';
		if ( $user_ratings && WPRMP_User_Rating::is_user_allowed_to_vote() ) {
			$class = ' wprm-user-rating wprm-user-rating-allowed';
			$data = ' data-average="' . $rating['average'] . '" data-count="' . $rating['count'] . '" data-total="' . $rating['total'] . '" data-user="' . $rating['user'] . '"';

			$functions .= ' onmouseenter="window.WPRecipeMaker.userRating.enter(this)"';
			$functions .= ' onfocus="window.WPRecipeMaker.userRating.enter(this)"';
			$functions .= ' onmouseleave="window.WPRecipeMaker.userRating.leave(this)"';
			$functions .= ' onblur="window.WPRecipeMaker.userRating.leave(this)"';
			$functions .= ' onclick="window.WPRecipeMaker.userRating.click(this, event)"';
			$functions .= ' onkeypress="window.WPRecipeMaker.userRating.click(this, event)"';
		} elseif ( $user_ratings ) {
			$class = ' wprm-user-rating';
			$data = '';
		} else {
			$class = '';
			$data = '';
		}

		$output = '<div class="wprm-recipe-rating' . $class . '"' . $data . '>';
		for ( $i = 1; $i <= 5; $i++ ) {
			$class = $i <= $rating_value ? 'wprm-rating-star-full' : 'wprm-rating-star-empty';
			$output .= '<span class="wprm-rating-star wprm-rating-star-' . $i . ' ' . $class . $functions . '" data-rating="' . $i . '">';

			ob_start();
			include( WPRM_DIR . 'assets/icons/star-empty.svg' );
			$star_icon = ob_get_contents();
			ob_end_clean();

			$output .= apply_filters( 'wprm_recipe_rating_star_icon', $star_icon );
			$output .= '</span>';
		}

		if ( $show_details ) {
			$output .= '<div class="wprm-recipe-rating-details"><span class="wprm-recipe-rating-average">' . $rating['average'] . '</span> ' . __( 'from', 'wp-recipe-maker' ) . ' <span class="wprm-recipe-rating-count">' . $rating['count'] . '</span> ' . _n( 'vote', 'votes', $rating['count'], 'wp-recipe-maker' ) . '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Display the nutrition label.
	 *
	 * @since    1.10.0
	 * @param    int $recipe_id Recipe ID of the label we want to display.
	 */
	public static function nutrition_label( $recipe_id = 0 ) {
		$label = '';
		if ( 'disabled' !== WPRM_Settings::get( 'show_nutrition_label' ) ) {
			$label = '[wprm-nutrition-label id="' . $recipe_id . '" align="' . WPRM_Settings::get( 'show_nutrition_label' ) . '"]';
		}
		return $label;
	}

	/**
	 * Metadata to add for tags.
	 *
	 * @since    1.10.0
	 * @param    mixed $key Tag we're adding the metadata for.
	 */
	public static function tags_meta( $key ) {
		return ''; // No inline metadata anymore.
	}

	/**
	 * Replace placeholders in text with recipe values.
	 *
	 * @since    1.16.0
	 * @param    mixed $recipe Recipe to replace the placeholders for.
	 * @param    mixed $text   Text to replace the placeholders in.
	 */
	public static function recipe_placeholders( $recipe, $text ) {
		return $recipe->replace_placeholders( $text );
	}

	/**
	 * Output the recipe image.
	 *
	 * @since    1.16.0
	 * @param    mixed $recipe Recipe to output the image for.
	 * @param    mixed $size   Default size to output.
	 */
	public static function recipe_image( $recipe, $size ) {
		$settings_size = 'legacy' === WPRM_Settings::get( 'recipe_template_mode' ) ? WPRM_Settings::get( 'template_recipe_image' ) : false;

		if ( $settings_size ) {
			preg_match( '/^(\d+)x(\d+)$/i', $settings_size, $match );
			if ( ! empty( $match ) ) {
				$size = array( intval( $match[1] ), intval( $match[2] ) );
			} else {
				$size = $settings_size;
			}
		}

		return $recipe->image( $size );
	}

	/**
	 * Output an instruction image.
	 *
	 * @since    1.16.0
	 * @param    mixed $instruction Instruction to output the image for.
	 * @param    mixed $size        Default size to output.
	 */
	public static function instruction_image( $instruction, $size ) {
		$settings_size = WPRM_Settings::get( 'template_instruction_image' );

		if ( $settings_size ) {
			preg_match( '/^(\d+)x(\d+)$/i', $settings_size, $match );
			if ( ! empty( $match ) ) {
				$size = array( intval( $match[1] ), intval( $match[2] ) );
			} else {
				$size = $settings_size;
			}
		}

		$img = wp_get_attachment_image( $instruction['image'], $size );

		// Disable instruction image pinning.
		if ( WPRM_Settings::get( 'pinterest_nopin_instruction_image' ) ) {
			$img = str_ireplace( '<img ', '<img data-pin-nopin="true" ', $img );
		}

		// Clickable images.
		if ( WPRM_Settings::get( 'instruction_image_clickable' ) ) {
			$settings_size = WPRM_Settings::get( 'clickable_image_size' );

			preg_match( '/^(\d+)x(\d+)$/i', $settings_size, $match );
			if ( ! empty( $match ) ) {
				$size = array( intval( $match[1] ), intval( $match[2] ) );
			} else {
				$size = $settings_size;
			}

			$clickable_image = wp_get_attachment_image_src( $instruction['image'], $size );
			$clickable_image_url = $clickable_image && isset( $clickable_image[0] ) ? $clickable_image[0] : '';
			if ( $clickable_image_url ) {
				$img = '<a href="' . esc_url( $clickable_image_url ) . '">' . $img . '</a>';
			}
		}

		return $img;
	}

	/**
	 * Output the Unit Conversion switcher.
	 *
	 * @param    mixed $recipe Recipe to output the unit conversion switch for.
	 * @since    1.20.0
	 */
	public static function unit_conversion( $recipe ) {
		$output = '';

		if ( WPRM_Addons::is_active( 'unit-conversion' ) && WPRM_Settings::get( 'unit_conversion_enabled' ) ) {
			$ingredients = $recipe->ingredients_without_groups();
			$unit_systems = array(
				1 => true, // Default unit system.
			);

			// Check if there are values for any other unit system.
			foreach ( $ingredients as $ingredient ) {
				if ( isset( $ingredient['converted'] ) ) {
					foreach ( $ingredient['converted'] as $system => $values ) {
						if ( $values['amount'] || $values['unit'] ) {
							$unit_systems[ $system ] = true;
						}
					}
				}
			}

			if ( count( $unit_systems ) > 1 ) {
				$recipe_unit_system = intval( $recipe->unit_system() );

				$unit_systems_output = array();
				foreach ( $unit_systems as $unit_system => $value ) {
					$active = 1 === $unit_system ? ' wprmpuc-active' : '';
					$unit_system_label = 2 === $recipe_unit_system ? WPRM_Settings::get( 'unit_conversion_system_' . ( 3 - $unit_system ) ) : WPRM_Settings::get( 'unit_conversion_system_' . $unit_system );
					$unit_systems_output[] = '<a href="#" class="wprm-unit-conversion' . esc_attr( $active ) . '" data-system="' . esc_attr( $unit_system ) . '" data-recipe="' . esc_attr( $recipe->id() ) . '">' . $unit_system_label . '</a>';
				}

				$output = '<div class="wprm-unit-conversion-container" data-recipe-unit-system="' . esc_attr( $recipe_unit_system ) . '">' . implode( ' - ', $unit_systems_output ) . '</div>';

				WPRM_Assets::add_js_data( 'wprmpuc_recipe_' . $recipe->id(), array(
					'ingredients' => $ingredients,
				));
			}
		}
		return $output;
	}
}
