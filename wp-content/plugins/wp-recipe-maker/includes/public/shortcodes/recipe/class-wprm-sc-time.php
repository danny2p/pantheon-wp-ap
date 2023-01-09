<?php
/**
 * Handle the recipe time shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe time shortcode.
 *
 * @since      3.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Time extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-time';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'type' => array(
				'default' => '',
				'type' => 'dropdown',
				'options' => 'recipe_times',
			),
			'shorthand' => array(
				'default' => '0',
				'type' => 'toggle',
			),
		);

		$atts = array_merge( $atts, WPRM_Shortcode_Helper::get_label_container_atts() );
		self::$attributes = $atts;

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
		if ( ! $recipe ) {
			return '';
		}

		$output = '';
		switch ( $atts['type'] ) {
			case 'prep':
				$output = self::get_time( 'prep_time', $recipe->prep_time(), $recipe->prep_time_zero(), $atts );
				break;
			case 'cook':
				$output = self::get_time( 'cook_time', $recipe->cook_time(), $recipe->cook_time_zero(), $atts );
				break;
			case 'total':
				$output = self::get_time( 'total_time', $recipe->total_time(), false, $atts );
				break;
			case 'custom':
				$atts['label'] = $recipe->custom_time_label();
				$output = self::get_time( 'custom_time', $recipe->custom_time(), $recipe->custom_time_zero(), $atts );
				break;
		}

		// Only continue if there actually is a time set.
		if ( ! $output ) {
			return '';
		}

		$output = WPRM_Shortcode_Helper::get_label_container( $atts, array( 'time', $atts['type']. '-time' ), $output );

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}

	/**
	 * Get output for formatted time.
	 *
	 * @since    3.2.0
	 * @param	 mixed  	$type 		Type of time we're displaying.
	 * @param	 int    	$time 		Total minutes of time to display.
	 * @param	 boolean    $show_zero 	Wether or not to show when value is zero.
	 * @param    mixed 		$atts 		Shortcode attributes.
	 */
	private static function get_time( $type, $time, $show_zero, $atts ) {
		$shorthand = (bool) $atts['shorthand'];

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

		if ( $output ) {
			// Output.
			$classes = array(
				'wprm-recipe-time',
				'wprm-block-text-' . $atts['text_style'],
			);

			// Add custom class if set.
			if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

			$output = '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $output . '</span>';
		}

		return $output;
	}
}

WPRM_SC_Time::init();