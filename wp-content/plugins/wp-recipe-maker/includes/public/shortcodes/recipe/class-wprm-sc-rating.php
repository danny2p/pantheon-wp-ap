<?php
/**
 * Handle the recipe rating shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe rating shortcode.
 *
 * @since      3.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Rating extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-rating';

	private static $uid = 0;

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'display' => array(
				'default' => 'stars',
				'type' => 'dropdown',
				'options' => array(
					'stars' => 'Stars',
					'stars-details' => 'Stars with Details',
					'details' => 'Details',
					'average' => 'Average',
					'count' => 'Total # Ratings',
				),
			),
			'style' => array(
				'default' => 'separate',
				'type' => 'dropdown',
				'options' => array(
					'inline' => 'Inline',
					'separate' => 'On its own line',
				),
				'dependency' => array(
					'id' => 'display',
					'value' => 'stars-details',
				),
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					'id' => 'display',
					'value' => 'stars',
					'type' => 'inverse',
				),
			),
			'voteable' => array(
				'default' => '1',
				'type' => 'toggle',
				'dependency' => array(
					array(
						'id' => 'display',
						'value' => 'details',
						'type' => 'inverse'
					),
					array(
						'id' => 'display',
						'value' => 'average',
						'type' => 'inverse'
					),
					array(
						'id' => 'display',
						'value' => 'count',
						'type' => 'inverse'
					),
				),
			),
			'icon' => array(
				'default' => 'star-empty',
				'type' => 'icon',
			),
			'icon_color' => array(
				'default' => '#343434',
				'type' => 'color',
				'dependency' => array(
					'id' => 'icon',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'icon_size' => array(
				'default' => '1em',
				'type' => 'size',
				'dependency' => array(
					'id' => 'icon',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'icon_padding' => array(
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'icon',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'average_decimals' => array(
				'default' => '2',
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'display',
						'value' => 'stars',
						'type' => 'inverse'
					),
					array(
						'id' => 'display',
						'value' => 'count',
						'type' => 'inverse'
					),
				),
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	3.2.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->rating() ) {
			return '';
		}
		
		$rating = $recipe->rating();

		if ( 'stars' === $atts['display'] || 'stars-details' === $atts['display'] ) {
			$output = self::get_stars( $rating, $atts, $recipe );

			if ( ! $output ) {
				return '';
			}
		} else {
			$output = '<div class="wprm-recipe-rating">';
		}

		// Get formatted average.
		$decimals = intval( $atts['average_decimals'] );
		$formatted_average = WPRM_Recipe_Parser::format_quantity( $rating['average'], $decimals );

		if ( 'details' === $atts['display'] || 'stars-details' === $atts['display'] ) {
			$classes = array(
				'wprm-recipe-rating-details',
				'wprm-block-text-' . $atts['text_style'],
			);

			// Add custom class if set.
			if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

			$output .= '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . WPRM_Rating::get_formatted_rating( $rating, $decimals ) . '</div>';
		} elseif ( 'average' === $atts['display'] ) {
			$classes = array(
				'wprm-recipe-rating-average',
				'wprm-block-text-' . $atts['text_style'],
			);

			// Add custom class if set.
			if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

			$output .= '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $formatted_average . '</div>';
		} elseif ( 'count' === $atts['display'] ) {
			$classes = array(
				'wprm-recipe-rating-count',
				'wprm-block-text-' . $atts['text_style'],
			);

			// Add custom class if set.
			if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

			$output .= '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $rating['count'] . '</div>';
		}

		$output .= '</div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}

	/**
	 * Get the stars output for a rating.
	 *
	 * @since    3.2.0
	 * @param    array 	 $rating   	Rating to display.
	 * @param    mixed	 $atts		Options passed along with the shortcode.
	 * @param    mixed 	 $recipe   	Recipe to display the rating for.
	 */
	private static function get_stars( $rating, $atts, $recipe ) {
		$output = '';
		$rating_value = $rating['average'];

		// UID for these stars.
		$id = 'wprm-recipe-rating-' . self::$uid;
		self::$uid++;

		// Backwards compatibility.
		$voteable = (bool) $atts['voteable'];
		$icon = esc_attr( $atts['icon'] );
		$color = esc_attr( $atts['icon_color'] );

		// Only output when there is an actual rating or users can rate.
		if ( $rating_value ) {
			// Output style for star color.
			$output .= self::get_stars_style( $id, $atts );

			// Get classes.
			$classes = array(
				'wprm-recipe-rating',
			);

			if ( 'stars-details' === $atts['display'] ) {
				$classes[] = 'wprm-recipe-rating-' . $atts['style'];
			}

			// Output stars.
			$output .= '<div id="' . esc_attr( $id ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '">';
			for ( $i = 1; $i <= 5; $i++ ) {
				// Get star class.
				if ( $i <= $rating_value ) {
					$class = 'wprm-rating-star-full';
				} else {
					$difference = $rating_value - $i + 1;
					if ( 0 < $difference && $difference <= 0.33 ) {
						$class = 'wprm-rating-star-33';
					} elseif ( 0 < $difference && $difference <= 0.5 ) {
						$class = 'wprm-rating-star-50';
					} elseif( 0 < $difference && $difference <= 0.66 ) {
						$class = 'wprm-rating-star-66';
					} elseif( 0 < $difference && $difference <= 1 ) {
						$class = 'wprm-rating-star-full';
					} else {
						$class = 'wprm-rating-star-empty';
					}
				}

				// Style.
				$style = self::get_star_style( $i, $atts );

				$output .= '<span class="wprm-rating-star wprm-rating-star-' . $i . ' ' . esc_attr( $class ) . '" data-rating="' . esc_attr( $i ) . '" data-color="' . esc_attr( $color ) . '"' . $style . '>';
				$output .= apply_filters( 'wprm_recipe_rating_star_icon', WPRM_Icon::get( $icon, $color) );
				$output .= '</span>';
			}	
		}

		return apply_filters( 'wprm_recipe_rating_shortcode_stars', $output, $recipe, $rating, $voteable, $icon, $color, $atts );
	}

	/**
	 * Get style output for the stars container.
	 *
	 * @since    8.7.0
	 * @param    mixed 	 $id  	ID for the stars container.
	 * @param    mixed	 $atts	Options passed along with the shortcode.
	 */
	public static function get_stars_style( $id, $atts ) {
		$output = '';
		$color = esc_attr( $atts['icon_color'] );

		// Output style for star color.
		$output .= '<style>';
		$output .= '#' . $id . ' .wprm-rating-star.wprm-rating-star-full svg * { fill: ' . $color . '; }';
		$output .= '#' . $id . ' .wprm-rating-star.wprm-rating-star-33 svg * { fill: url(#' . $id . '-33); }';
		$output .= '#' . $id . ' .wprm-rating-star.wprm-rating-star-50 svg * { fill: url(#' . $id . '-50); }';
		$output .= '#' . $id . ' .wprm-rating-star.wprm-rating-star-66 svg * { fill: url(#' . $id . '-66); }';
		$output .= 'linearGradient#' . $id . '-33 stop { stop-color: ' . $color . '; }';
		$output .= 'linearGradient#' . $id . '-50 stop { stop-color: ' . $color . '; }';
		$output .= 'linearGradient#' . $id . '-66 stop { stop-color: ' . $color . '; }';
		$output .= '</style>';

		// Definitions for quarter and half stars.
		$output .= '<svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" style="display:block;width:0px;height:0px">';
		if ( is_rtl() ) {
			$output .= '<defs><linearGradient id="' . $id .'-33"><stop offset="0%" stop-opacity="0" /><stop offset="66%" stop-opacity="0" /><stop offset="66%" stop-opacity="1" /><stop offset="100%" stop-opacity="1" /></linearGradient></defs>';
			$output .= '<defs><linearGradient id="' . $id .'-50"><stop offset="0%" stop-opacity="0" /><stop offset="50%" stop-opacity="0" /><stop offset="50%" stop-opacity="1" /><stop offset="100%" stop-opacity="1" /></linearGradient></defs>';
			$output .= '<defs><linearGradient id="' . $id .'-66"><stop offset="0%" stop-opacity="0" /><stop offset="33%" stop-opacity="0" /><stop offset="33%" stop-opacity="1" /><stop offset="100%" stop-opacity="1" /></linearGradient></defs>';
		} else {
			$output .= '<defs><linearGradient id="' . $id .'-33"><stop offset="0%" stop-opacity="1" /><stop offset="33%" stop-opacity="1" /><stop offset="33%" stop-opacity="0" /><stop offset="100%" stop-opacity="0" /></linearGradient></defs>';
			$output .= '<defs><linearGradient id="' . $id .'-50"><stop offset="0%" stop-opacity="1" /><stop offset="50%" stop-opacity="1" /><stop offset="50%" stop-opacity="0" /><stop offset="100%" stop-opacity="0" /></linearGradient></defs>';
			$output .= '<defs><linearGradient id="' . $id .'-66"><stop offset="0%" stop-opacity="1" /><stop offset="66%" stop-opacity="1" /><stop offset="66%" stop-opacity="0" /><stop offset="100%" stop-opacity="0" /></linearGradient></defs>';
		}
		$output .= '</svg>';

		return $output;
	}

	/**
	 * Get the star icon style attribute.
	 *
	 * @since    8.7.0
	 * @param    int 	 $i   	Star number we're displaying.
	 * @param    mixed	 $atts	Options passed along with the shortcode.
	 */
	public static function get_star_style( $i, $atts ) {
		$style = '';

		$style .= ' style="';
		$style .= 'font-size: ' . esc_attr( $atts['icon_size' ] ) . ';';

		if ( '0px' !== $atts['icon_padding'] ) {
			$style .= 'padding: ' . esc_attr( $atts['icon_padding'] ) . ';';
			switch ( $i ) {
				case 1:
					$style .= 'padding-left: 0;';
					break;
				case 5:
					$style .= 'padding-right: 0;';
					break;
			}
		}

		$style .= '"';

		return $style;
	}
}

WPRM_SC_Rating::init();