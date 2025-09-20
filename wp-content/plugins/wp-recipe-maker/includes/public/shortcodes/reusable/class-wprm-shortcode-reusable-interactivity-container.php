<?php
/**
 * Internal interactivity component for shortcodes.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 */

/**
 * Reusable interactivity container component for shortcodes.
 *
 * @since      10.1.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Reusable_Interactivity_Container {

	/**
	 * Get attributes for an internal container.
	 *
	 * @since	10.1.0
	 */
	public static function get_atts() {
		return array(
			'interactivity_container' => array(
				'default' => '0',
				'type' => 'toggle',
			),			
			'interactivity_alignment' => array(
				'name' => 'Alignment',
				'default' => 'flex-start',
				'type' => 'dropdown',
				'options' => array(
					'flex-start' => 'Left',
					'flex-end' => 'Right',
					'center' => 'Center',
					'space-between' => 'Space Between',
					'space-around' => 'Space Around',
				),
				'dependency' => array(
					'id' => 'interactivity_container',
					'value' => '1',
				),
			),
			'interactivity_gap' => array(
				'name' => 'Gap',
				'default' => '10px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'interactivity_container',
					'value' => '1',
				),
			),
			'interactivity_order' => array(
				'name' => 'Order',
				'default' => 'regular',
				'type' => 'dropdown',
				'options' => array(
					'regular' => __( 'Regular', 'wp-recipe-maker' ),
					'reverse' => __( 'Reverse', 'wp-recipe-maker' ),
				),
				'dependency' => array(
					'id' => 'interactivity_container',
					'value' => '1',
				),
			),
			'interactivity_top_margin' => array(
				'name' => 'Top Margin',
				'default' => '10px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'interactivity_container',
					'value' => '1',
				),
			),
			'interactivity_horizontal_margin' => array(
				'name' => 'Horizontal Margin',
				'default' => '20px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'interactivity_container',
					'value' => '1',
				),
			),
			'interactivity_bottom_margin' => array(
				'name' => 'Bottom Margin',
				'default' => '0',
				'type' => 'size',
				'dependency' => array(
					'id' => 'interactivity_container',
					'value' => '1',
				),
			),
		);
	}

	/**
	 * Get interactivity container to output.
	 *
	 * @since	10.1.0
	 * @param	mixed $atts Attributes for the shortcode.
	 * @param	string $field Field to get the container for.
	 */
	public static function get_container( $atts, $field ) {
		$classes = array(
			'wprm-interactivity-container',
			'wprm-interactivity-container-' . $field,
		);

		$style = '';

		if ( 'flex-start' !== $atts['interactivity_alignment'] ) {
			$style .= 'justify-content: ' . $atts['interactivity_alignment'] . ';';
		}
		if ( '10px' !== $atts['interactivity_gap'] ) {
			$style .= 'gap: ' . $atts['interactivity_gap'] . ';';
		}
		if ( 'reverse' === $atts['interactivity_order'] ) {
			$style .= 'flex-direction: row-reverse;';
		}
		if ( '10px' !== $atts['interactivity_top_margin'] || '20px' !== $atts['interactivity_horizontal_margin'] || '0' !== $atts['interactivity_bottom_margin'] ) {
			$style .= 'margin: ' . $atts['interactivity_top_margin'] . ' ' . $atts['interactivity_horizontal_margin'] . ' ' . $atts['interactivity_bottom_margin'] . ';';
		}

		// Construct output.
		$container = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">';

		return $container;
	}
}
