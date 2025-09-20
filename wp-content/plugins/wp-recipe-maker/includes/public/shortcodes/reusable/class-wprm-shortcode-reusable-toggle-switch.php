<?php
/**
 * Toggle switch component for shortcodes.
 *
 * @link       http://bootstrapped.ventures
 * @since      10.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 */

/**
 * Reusable toggle switch component for shortcodes.
 *
 * @since      10.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Reusable_Toggle_Switch {

	/**
	 * Get attributes for a toggle switch.
	 *
	 * @since	7.0.0
	 */
	public static function get_atts() {
		return array(
			'appearance_header' => array(
				'type' => 'header',
				'default' => __( 'Appearance', 'wp-recipe-maker' ),
			),
			'switch_type' => array(
				'default' => 'outside',
				'type' => 'dropdown',
				'options' => array(
					'outside' => 'Label Outside Toggle',
					'inside' => 'Label Inside Toggle',
				),
			),
			'switch_style' => array(
				'default' => 'rounded',
				'type' => 'dropdown',
				'options' => array(
					'square' => 'Square Toggle',
					'rounded' => 'Rounded Toggle',
				),
			),
			// Outside toggle type, backwards compatibility.
			'switch_width' => array(
				'default' => '40',
				'type' => 'number',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'outside',
				),
			),
			'switch_inactive' => array(
				'default' => '#cccccc',
				'type' => 'color',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'outside',
				),
			),
			'switch_inactive_knob' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'outside',
				),
			),
			'switch_active' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'outside',
				),
			),
			'switch_active_knob' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'outside',
				),
			),
			// Inside toggle type.
			'switch_height' => array(
				'default' => '28px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
			'switch_off' => array(
				'default' => '#cccccc',
				'type' => 'color',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
			'switch_off_knob' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
			'switch_off_text' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
			'switch_on' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
			'switch_on_knob' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
			'switch_on_text' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
			'icon_text_header' => array(
				'type' => 'header',
				'default' => __( 'Icons & Text', 'wp-recipe-maker' ),
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
			'off_icon' => array(
				'default' => '',
				'type' => 'icon',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
			'off_text' => array(
				'default' => 'Prevent Sleep Mode',
				'type' => 'text',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
			'on_icon' => array(
				'default' => '',
				'type' => 'icon',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
			'on_text' => array(
				'default' => 'Prevent Sleep Mode',
				'type' => 'text',
				'dependency' => array(
					'id' => 'switch_type',
					'value' => 'inside',
				),
			),
		);
	}

	/**
	 * Get toggle switch to output.
	 *
	 * @since	7.0.0
	 * @param	mixed $atts 	Attributes for the shortcode.
	 * @param	string $args	Arguments for the output.
	 */
	public static function get_switch( $atts, $args ) {
		$output = '';

		if ( isset( $args['class'] ) ) {
			// UID for this toggle.
			$uid = isset( $args['uid'] ) ? $args['uid'] : wp_rand();

			// Type of switch.
			if ( isset( $atts['switch_type'] ) ) {
				$type = $atts['switch_type'];
			} else {
				$type = isset( $args['type'] ) ? $args['type'] : 'outside';
			}

			// Custom styling.
			$slider_style = '';

			if ( 'outside' === $type ) {
				$width = intval( $atts['switch_width'] );
				$width = $width < 20 ? 40 : $width;

				// Calculate other sizes.
				$height = ceil( $width / 2 );

				// Custom style for the slider.
				if ( 28 !== $height ) {
					$slider_style .= '--switch-height: ' . $height . 'px;';
				}
			} elseif ( 'inside' === $type ) {
				// Set height for switch.
				$height = intval( $atts['switch_height'] );
				if ( 28 !== $height ) {
					$slider_style .= '--switch-height: ' . $height . 'px;';
				}

				if ( '#333333' !== $atts['switch_off_text'] ) {
					$slider_style .= '--switch-off-text: ' . $atts['switch_off_text'] . ';';
				}
				if ( '#ffffff' !== $atts['switch_on_text'] ) {
					$slider_style .= '--switch-on-text: ' . $atts['switch_on_text'] . ';';
				}
			}

			// Backwards compatibility. When type is outside, priorities old values.
			if ( 'outside' === $type ) {
				$off_color = isset( $atts['switch_inactive'] ) ? $atts['switch_inactive'] : $atts['switch_off'];
				$on_color = isset( $atts['switch_active'] ) ? $atts['switch_active'] : $atts['switch_on'];
				$off_knob_color = isset( $atts['switch_inactive_knob'] ) ? $atts['switch_inactive_knob'] : $atts['switch_off_knob'];
				$on_knob_color = isset( $atts['switch_active_knob'] ) ? $atts['switch_active_knob'] : $atts['switch_on_knob'];	
			} else {
				$off_color = isset( $atts['switch_off'] ) ? $atts['switch_off'] : $atts['switch_inactive'];
				$on_color = isset( $atts['switch_on'] ) ? $atts['switch_on'] : $atts['switch_active'];
				$off_knob_color = isset( $atts['switch_off_knob'] ) ? $atts['switch_off_knob'] : $atts['switch_inactive_knob'];
				$on_knob_color = isset( $atts['switch_on_knob'] ) ? $atts['switch_on_knob'] : $atts['switch_active_knob'];
			}

			// Custom colors.
			if ( '#cccccc' !== $off_color ) 		{ $slider_style .= '--switch-off-color: ' . $off_color . ';'; }
			if ( '#ffffff' !== $off_knob_color ) 	{ $slider_style .= '--switch-off-knob: ' . $off_knob_color . ';'; }
			if ( '#333333' !== $on_color ) 		{ $slider_style .= '--switch-on-color: ' . $on_color . ';'; }
			if ( '#ffffff' !== $on_knob_color ) 	{ $slider_style .= '--switch-on-knob: ' . $on_knob_color . ';'; }

			// Classes.
			$classes = array(
				'wprm-toggle-switch',
				'wprm-toggle-switch-' . $atts['switch_style'],
				'wprm-toggle-switch-' . $type,
			);

			// Data attributes.
			$data = '';
			if ( isset( $args['data'] ) ) {
				foreach ( $args['data'] as $key => $value ) {
					$data .= ' data-' . $key . '="' . esc_attr( $value ) . '"';
				}
			}

			// Aria label.
			$aria_label = '';
			if ( isset( $args['aria_label'] ) ) {
				$aria_label = ' aria-label="' . esc_attr( $args['aria_label'] ) . '"';
			}

			// Checked?
			$checked = isset( $args['checked'] ) ? $args['checked'] : false;
			$checked_html = $checked ? ' checked="checked"' : '';

			$output .= '<label id="wprm-toggle-switch-' . esc_attr( $uid ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $aria_label . '>';
			$output .= '<input type="checkbox" id="' . esc_attr( $args['class'] . '-' . $uid ) . '" class="' . esc_attr( $args['class'] ) . '"' . $checked_html . $data . ' />';
			$output .= '<span class="wprm-toggle-switch-slider" style="' . esc_attr( $slider_style ) . '">';

			if ( 'inside' === $type ) {
				// Get optional icons.
				$off_icon = '';
				if ( isset( $atts['off_icon'] ) && $atts['off_icon'] ) {
					$icon = WPRM_Icon::get( $atts['off_icon'], $atts['switch_off_text'] );

					if ( $icon ) {
						$off_icon = '<span class="wprm-recipe-icon wprm-media-toggle-off-icon">' . $icon . '</span> ';
					}
				}
				$on_icon = '';
				if ( isset( $atts['on_icon'] ) && $atts['on_icon'] ) {
					$icon = WPRM_Icon::get( $atts['on_icon'], $atts['switch_on_text'] );

					if ( $icon ) {
						$on_icon = '<span class="wprm-recipe-icon wprm-media-toggle-on-icon">' . $icon . '</span> ';
					}
				}
				
				$off_text = isset( $atts['off_text'] ) ? $atts['off_text'] : '';
				$on_text = isset( $atts['on_text'] ) ? $atts['on_text'] : '';

				$output .= '<span class="wprm-toggle-switch-text">';
				$output .= '<span class="wprm-toggle-switch-off">' . $off_icon . WPRM_Shortcode_Helper::sanitize_html( $off_text ) . '</span>';
				$output .= '<span class="wprm-toggle-switch-on">' . $on_icon . WPRM_Shortcode_Helper::sanitize_html( $on_text ) . '</span>';
				$output .= '</span>';
			}

			$output .= '</span>';
			
			if ( 'outside' === $type && isset( $args['label'] ) ) {
				$label_classes = array(
					'wprm-toggle-switch-label',
				);

				if ( isset( $args['label_classes'] ) ) {
					$label_classes = array_merge( $label_classes, $args['label_classes'] );
				}

				$output .= '<span class="' . esc_attr( implode( ' ', $label_classes ) ) . '">';
				$output .= $args['label'];
				$output .= '</span>';
			}

			$output .= '</label>';
		}

		return $output;
	}
}
