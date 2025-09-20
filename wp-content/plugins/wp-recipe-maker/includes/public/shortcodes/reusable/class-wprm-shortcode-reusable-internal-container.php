<?php
/**
 * Internal container component for shortcodes.
 *
 * @link       http://bootstrapped.ventures
 * @since      10.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 */

/**
 * Reusable internal container component for shortcodes.
 *
 * @since      10.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Reusable_Internal_Container {

	/**
	 * Get attributes for an internal container.
	 *
	 * @since	10.0.0
	 */
	public static function get_atts() {
		return array(
			'has_container' => array(
				'default' => '0',
				'type' => 'toggle',
			),
			'container_background' => array(
				'name' => 'Background',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'has_container',
					'value' => '1',
				),
			),
			'container_border' => array(
				'name' => 'Border',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'has_container',
					'value' => '1',
				),
			),
			'container_border_width' => array(
				'name' => 'Border Width',
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'has_container',
					'value' => '1',
				),
			),
			'container_border_radius' => array(
				'name' => 'Border Radius',
				'default' => '20px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'has_container',
					'value' => '1',
				),
			),
			'container_padding' => array(
				'name' => 'Padding',
				'default' => '20px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'has_container',
					'value' => '1',
				),
			),
			'container_collapsible' => array(
				'name' => 'Collapsible',
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(
					'id' => 'has_container',
					'value' => '1',
				),
			),
			'container_icon_collapsed' => array(
				'name' => 'Icon Collapsed',
				'default' => 'arrow-small-up',
				'type' => 'icon',
				'dependency' => array(
					array(
						'id' => 'has_container',
						'value' => '1',
					),
					array(
						'id' => 'container_collapsible',
						'value' => '1',
					),
				),
			),
			'container_icon_expanded' => array(
				'name' => 'Icon Expanded',
				'default' => 'arrow-small-down',
				'type' => 'icon',
				'dependency' => array(
					array(
						'id' => 'has_container',
						'value' => '1',
					),
					array(
						'id' => 'container_collapsible',
						'value' => '1',
					),
				),
			),
			'container_icon_color' => array(
				'name' => 'Icon Color',
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'has_container',
						'value' => '1',
					),
					array(
						'id' => 'container_collapsible',
						'value' => '1',
					),
				),
			),
		);
	}

	/**
	 * Get internal container to output.
	 *
	 * @since	10.0.0
	 * @param	mixed $atts Attributes for the shortcode.
	 * @param	string $field Field to get the container for.
	 */
	public static function get_container( $atts, $field ) {
		$classes = array(
			'wprm-internal-container',
			'wprm-internal-container-' . $field,
		);

		$style = '';

		if ( '#ffffff' !== $atts['container_background'] ) {
			$style .= 'background-color: ' . $atts['container_background'] . ';';
		}
		if ( '20px' !== $atts['container_padding'] ) {
			$style .= 'padding: ' . $atts['container_padding'] . ';';
		}
		if ( '#ffffff' !== $atts['container_border'] ) {
			$style .= 'border-color: ' . $atts['container_border'] . ';';
		}
		if ( '0px' !== $atts['container_border_width'] ) {
			$style .= 'border-width: ' . $atts['container_border_width'] . ';';
		}
		if ( '20px' !== $atts['container_border_radius'] ) {
			$style .= 'border-radius: ' . $atts['container_border_radius'] . ';';
		}

		// Check if collapsible.
		$collapsible_output = '';
		if ( (bool) $atts['container_collapsible'] ) {
			$collapsible_output = WPRM_Shortcode_Reusable_Collapsible_Button::get_html(
				$atts['container_icon_collapsed'],
				$atts['container_icon_expanded'],
				$atts['container_icon_color'],
				'internal-container',
			);

			if ( $collapsible_output ) {
				$classes[] = 'wprm-expandable-container';
				$classes[] = 'wprm-expandable-expanded';
			}
		}

		// Construct output.
		$container = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">';
		$container .= $collapsible_output;

		return $container;
	}
}
