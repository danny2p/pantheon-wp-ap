<?php
/**
 * Handle the other shortcodes.
 *
 * @link       http://bootstrapped.ventures
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
		add_shortcode( 'wprm-ingredient', array( __CLASS__, 'ingredient_shortcode' ) );
		add_shortcode( 'wprm-condition', array( __CLASS__, 'condition_shortcode' ) );

		add_filter( 'wprm_localize_admin', array( __CLASS__, 'temperature_icons' ) );
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
		$atts = shortcode_atts( array(
			'seconds' => '0',
			'minutes' => '0',
			'hours' => '0',
		), $atts, 'wprm_timer' );

		$seconds = intval( $atts['seconds'] );
		$minutes = intval( $atts['minutes'] );
		$hours = intval( $atts['hours'] );

		$seconds = $seconds + (60 * $minutes) + (60 * 60 * $hours);

		if ( $seconds > 0 ) {
			return '<span class="wprm-timer" data-seconds="' . esc_attr( $seconds ) . '">' . $content . '</span>';
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
		$value = $atts['value'];
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
		$data .= ' data-tooltip="' . esc_attr( $help ) .  '"';

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
		), $atts, 'wprm_ingredient' );

		// Default to text as output.
		$output = WPRM_Shortcode_Helper::sanitize_html( $atts['text'] );

		// Get recipe (defaults to current).
		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );

		if ( $recipe && is_numeric( $atts['uid'] ) ) {
			$uid = intval( $atts['uid'] );

			$ingredients_flat = $recipe->ingredients_flat();
			$index = array_search( $uid, array_column( $ingredients_flat, 'uid' ) );

			if ( false !== $index && isset( $ingredients_flat[ $index ] ) ) {
				$found_ingredient = $ingredients_flat[ $index ];

				if ( 'ingredient' === $found_ingredient['type'] ) {
					$parts = array();

					if ( $found_ingredient['amount'] ) { $parts[] = $found_ingredient['amount']; };
					if ( $found_ingredient['unit'] ) { $parts[] = $found_ingredient['unit']; };
					if ( $found_ingredient['name'] ) { $parts[] = $found_ingredient['name']; };

					$text_to_show = implode( ' ', $parts );

					if ( $text_to_show ) {
						$classes = array(
							'wprm-inline-ingredient',
							'wprm-inline-ingredient-' . $recipe->id() . '-' . $uid,
							'wprm-block-text-' . $atts['style'],
						);

						// Custom CSS style.
						$style = '';

						if ( $atts['color'] ) {
							$style = ' style="color: ' . esc_attr( $atts['color'] ) . ';"';
						}
					
						$output = '<span class="' . esc_attr( implode( ' ', $classes ) ) .'"' . $style . '>' . $text_to_show . '</span>';
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
	 * Output for the condition shortcode.
	 *
	 * @since	8.2.0
	 * @param	array $atts		Shortcode attributes.
	 * @param	mixed $content	Content in between the shortcodes.
	 */
	public static function condition_shortcode( $atts, $content ) {
		$atts = shortcode_atts( array(
			'id' => '0',
			'field' => '',
			'device' => '',
			'user' => '',
			'inverse' => '0',
		), $atts, 'wprm_condition' );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );

		$matches_conditions = array();

		// Field conditions.
		if ( $atts['field'] ) {
			$field_condition = strtolower( $atts['field'] );
			$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );

			if ( $recipe ) {
				switch ( $field_condition ) {
					case 'image':
						$matches_conditions[] = 0 < $recipe->image_id();
						break;
					case 'video':
						$matches_conditions[] = '' !== $recipe->video();
						break;
					case 'nutrition':
						$matches_conditions[] = '' !== do_shortcode( '[wprm-nutrition-label id="' . $recipe->id() . '"]' );
						break;
					case 'unit-conversion':
						$matches_conditions[] = '' !== do_shortcode( '[wprm-recipe-unit-conversion id="' . $recipe->id() . '"]' );
						break;
				}
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
				$matches_conditions[] = in_array( $device, explode( ';', $device_condition ) );
			}
		}

		// User conditions.
		if ( $atts['user'] ) {
			$user_condition = strtolower( str_replace( '-', '_', $atts['user'] ) );

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
			$match = ! $match;
		}

		// Return content if it matches the condition, empty otherwise.
		if ( $match ) {
			return do_shortcode( $content );
		} else {
			return '';
		}
	}
}

WPRM_Shortcode_Other::init();
