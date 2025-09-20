<?php
/**
 * Label container component for shortcodes.
 *
 * @link       http://bootstrapped.ventures
 * @since      10.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 */

/**
 * Reusable label container component for shortcodes.
 *
 * @since      10.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Reusable_Label_Container {

	/**
	 * Get attributes for the label container.
	 *
	 * @since	6.0.0
	 */
	public static function get_atts() {
		return array(
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'container_header' => array(
				'type' => 'header',
				'default' => __( 'Optional Label', 'wp-recipe-maker' ),
			),
            'label_container' => array(
				'default' => '0',
				'type' => 'toggle',
			),
            'style' => array(
				'default' => 'separate',
				'type' => 'dropdown',
				'options' => array(
					'inline' => 'Inline',
					'separate' => 'On its own line',
					'separated' => 'On separate lines',
					'columns' => 'Columns',
                ),
                'dependency' => array(
					'id' => 'label_container',
					'value' => '1',
				),
			),
			'icon' => array(
				'default' => '',
                'type' => 'icon',
                'dependency' => array(
					'id' => 'label_container',
					'value' => '1',
				),
			),
			'icon_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
                    array(
                        'id' => 'label_container',
                        'value' => '1',
                    ),
                    array(
                        'id' => 'icon',
                        'value' => '',
                        'type' => 'inverse',
                    ),
				),
			),
			'label' => array(
				'default' => '',
                'type' => 'text',
                'dependency' => array(
					'id' => 'label_container',
					'value' => '1',
				),
			),
			'label_separator' => array(
				'default' => ' ',
                'type' => 'text',
                'dependency' => array(
                    array(
                        'id' => 'label_container',
                        'value' => '1',
                    ),
                    array(
                        'id' => 'label',
                        'value' => '',
                        'type' => 'inverse',
                    ),
				),
			),
			'label_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
                    array(
                        'id' => 'label_container',
                        'value' => '1',
                    ),
                    array(
                        'id' => 'label',
                        'value' => '',
                        'type' => 'inverse',
                    ),
				),
			),
			'accessibility_label' => array(
				'default' => '',
                'type' => 'text',
                'dependency' => array(
					'id' => 'label_container',
					'value' => '1',
				),
			),
			// Needs to pass trough but not actually shown.
			'table_borders' => array(
				'default' => 'top-bottom',
			),
			'table_borders_inside' => array(
				'default' => '1',
			),
			'table_border_width' => array(
				'default' => '1px',
			),
			'table_border_style' => array(
				'default' => 'dotted',
			),
			'table_border_color' => array(
				'default' => '#666666',
			),
		);
	}

	/**
	 * Get label container.
	 *
	 * @since	6.0.0
	 * @param	mixed $atts Attributes for the shortcode.
	 * @param	string $fields Field to get the container for.
	 * @param	string $content Content to put inside the container.
	 */
	public static function get_container( $atts, $fields, $content ) {
		if ( ! (bool) $atts['label_container'] ) {
			return $content;
		}

		// Clean up $fields.
		if ( ! is_array( $fields ) ) {
			$fields = array( $fields );
		}

		foreach ( $fields as $index => $field ) {
			$fields[ $index ] = str_replace( ' ', '', $field );
		}

		// Get optional icon.
		$icon = '';
		if ( $atts['icon'] ) {
			$icon = WPRM_Icon::get( $atts['icon'], $atts['icon_color'] );

			if ( $icon ) {
				$icon_classes = array(
					'wprm-recipe-icon',
				);
				foreach ( $fields as $field ) {
					$icon_classes[] = 'wprm-recipe-' . $field . '-icon';
				}

				$icon = '<span class="' . esc_attr( implode( ' ', $icon_classes ) ) . '">' . $icon . '</span> ';
			}
		}

		// Check for accessibility label.
		$accessibility_label = '';
		if ( $atts['accessibility_label'] ) {
			$accessibility_label = '<span class="sr-only screen-reader-text wprm-screen-reader-text">' . WPRM_Shortcode_Helper::sanitize_html( $atts['accessibility_label'] ) . '</span>';
		}

		// Get optional label.
		$label = '';
		if ( $atts['label'] ) {
			$label_classes = array(
				'wprm-recipe-details-label',
				'wprm-block-text-' . $atts['label_style'],
			);
			foreach ( $fields as $field ) {
				$label_classes[] = 'wprm-recipe-' . $field . '-label';
			}

			$aria_hidden = '';
			if ( $accessibility_label ) {
				$aria_hidden = ' aria-hidden="true"';
			}

			$translated_label = WPRM_i18n::maybe_translate( $atts['label'] );
			$label = '<span class="' . esc_attr( implode( ' ', $label_classes ) ) . '"' . $aria_hidden . '>' . WPRM_Shortcode_Helper::sanitize_html( $translated_label ) . WPRM_Shortcode_Helper::sanitize_html( $atts['label_separator'] ) . '</span>';
		}

		// Inline style.
		$style = '';
		if ( 'table' === $atts['style'] ) {
			if ( 'none' === $atts['table_borders'] ) {
				$atts['table_border_width'] = 0;
			}

			$style .= 'border-width: ' . $atts['table_border_width'] . ';';
			$style .= 'border-style: ' . $atts['table_border_style'] . ';';
			$style .= 'border-color: ' . $atts['table_border_color'] . ';';
		}

        // Get container code.
        $classes = array(
			'wprm-recipe-block-container',
			'wprm-recipe-block-container-' . $atts['style'],
			'wprm-block-text-' . $atts['text_style'],
		);
		foreach ( $fields as $field ) {
			$classes[] = 'wprm-recipe-' . $field . '-container';
		}

		$container = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">';
		$container .= $icon;
		$container .= $accessibility_label;
		$container .= $label;
		$container .= $content;
		$container .= '</div>';
        

		return $container;
	}
}
