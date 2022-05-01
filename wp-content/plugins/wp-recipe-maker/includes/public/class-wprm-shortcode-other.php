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
		add_shortcode( 'wprm-condition', array( __CLASS__, 'condition_shortcode' ) );
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
			return $content;
		} else {
			return '';
		}
	}
}

WPRM_Shortcode_Other::init();
