<?php
/**
 * Section header component for shortcodes.
 *
 * @link       http://bootstrapped.ventures
 * @since      10.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 */

/**
 * Reusable section header component for shortcodes.
 *
 * @since      10.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Reusable_Section_Header {

	/**
	 * Get attributes for a section.
	 *
	 * @since	6.0.0
	 */
	public static function get_atts() {
		return array(
			'header' => array(
				'default' => '',
				'type' => 'text',
			),
			'header_tag' => array(
				'name' => 'Tag',
				'default' => 'h3',
				'type' => 'dropdown',
				'options' => 'header_tags',
				'dependency' => array(
					'id' => 'header',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'header_style' => array(
				'name' => 'Style',
				'default' => 'bold',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					'id' => 'header',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'header_align' => array(
				'name' => 'Alignment',
				'default' => 'left',
				'type' => 'dropdown',
				'options' => array(
					'left' => 'Left',
					'center' => 'Center',
					'right' => 'Right',
				),
				'dependency' => array(
                    array(
                        'id' => 'header',
                        'value' => '',
                        'type' => 'inverse',
					),
				),
			),
			'header_background_color' => array(
				'name' => 'Background Color',
				'default' => '',
				'type' => 'color',
				'dependency' => array(
                    array(
                        'id' => 'header',
                        'value' => '',
                        'type' => 'inverse',
					),
				),
			),
			'header_vertical_padding' => array(
				'name' => 'Vertical Padding',
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
                    array(
                        'id' => 'header',
                        'value' => '',
                        'type' => 'inverse',
					),
				),
			),
			'header_horizontal_padding' => array(
				'name' => 'Horizontal Padding',
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
                    array(
                        'id' => 'header',
                        'value' => '',
                        'type' => 'inverse',
					),
				),
			),
			'header_bottom_margin' => array(
				'name' => 'Bottom Margin',
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
                    array(
                        'id' => 'header',
                        'value' => '',
                        'type' => 'inverse',
					),
				),
			),
			'header_decoration' => array(
				'name' => 'Decoration',
				'default' => 'none',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'None',
					'line' => 'Line',
					'icon' => 'Icon',
					'icon-line' => 'Icon & Line',
					'spacer' => 'Spacer',
				),
				'dependency' => array(
					'id' => 'header',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'header_line_width' => array(
				'name' => 'Line Width',
				'default' => '1px',
				'type' => 'size',
				'dependency' => array(
                    array(
                        'id' => 'header',
                        'value' => '',
                        'type' => 'inverse',
					),
					array(
                        'id' => 'header_decoration',
                        'value' => array( 'line', 'icon-line' ),
					),
				),
			),
			'header_line_color' => array(
				'name' => 'Line Color',
				'default' => '#9B9B9B',
				'type' => 'color',
				'dependency' => array(
                    array(
                        'id' => 'header',
                        'value' => '',
                        'type' => 'inverse',
					),
					array(
                        'id' => 'header_decoration',
                        'value' => array( 'line', 'icon-line' ),
					),
				),
			),
			'header_icon' => array(
				'name' => 'Icon',
				'default' => '',
				'type' => 'icon',
				'dependency' => array(
					array(
						'id' => 'header',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'header_decoration',
						'value' => array( 'icon', 'icon-line' ),
					),
				),
			),
			'header_icon_color' => array(
				'name' => 'Icon Color',
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'header',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'header_decoration',
						'value' => array( 'icon', 'icon-line' ),
					),
					array(
						'id' => 'header_icon',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'header_collapsible' => array(
				'name' => 'Collapsible',
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(
					'id' => 'header',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'header_icon_collapsed' => array(
				'name' => 'Icon Collapsed',
				'default' => 'arrow-small-up',
				'type' => 'icon',
				'dependency' => array(
					array(
						'id' => 'header',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'header_collapsible',
						'value' => '1',
					),
				),
			),
			'header_icon_expanded' => array(
				'name' => 'Icon Expanded',
				'default' => 'arrow-small-down',
				'type' => 'icon',
				'dependency' => array(
					array(
						'id' => 'header',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'header_collapsible',
						'value' => '1',
					),
				),
			),
			'header_collapsible_icon_color' => array(
				'name' => 'Icon Color',
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'header',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'header_collapsible',
						'value' => '1',
					),
				),
			),
		);
	}

	/**
	 * Get section header to output.
	 *
	 * @since	6.0.0
	 * @param	mixed $atts Attributes for the shortcode.
	 * @param	string $field Field to get the header for.
	 * @param	string $args Optional arguments.
	 */
	public static function get_header( $atts, $field, $args = array() ) {
		$header = '';

		if ( $atts['header'] ) {
			$classes = array(
				'wprm-recipe-header',
				'wprm-recipe-' . $field . '-header',
				'wprm-block-text-' . $atts['header_style'],
				'wprm-align-' . $atts['header_align'],
				'wprm-header-decoration-' . $atts['header_decoration'],
			);

			// Custom header line style.
			$header_line_style = '';

			if ( '#000000' !== $atts['header_line_color'] ) {
				$header_line_style .= 'border-color: ' . $atts['header_line_color'] . ';';
			}
			if ( '1px' !== $atts['header_line_width'] ) {
				$header_line_style .= 'border-width: ' . $atts['header_line_width'] . ';';
			}

			$header_line_style = WPRM_Shortcode_Helper::get_inline_style( $header_line_style );

			// Add decoration before/after header.
			$before_header = '';
			$after_header = '';
			if ( 'line' === $atts['header_decoration'] ) {
				if ( 'left' === $atts['header_align'] || 'center' === $atts['header_align'] ) {
					$after_header = '<div class="wprm-decoration-line"' . $header_line_style . '></div>';
				}
				if ( 'right' === $atts['header_align'] || 'center' === $atts['header_align'] ) {
					$before_header = '<div class="wprm-decoration-line"' . $header_line_style . '></div>';
				}
			} elseif ( 'icon' === $atts['header_decoration'] ) {
				$icon = '';
				if ( $atts['header_icon'] ) {
					$icon = WPRM_Icon::get( $atts['header_icon'], $atts['header_icon_color'] );

					if ( $icon ) {
						$icon = '<span class="wprm-recipe-icon" aria-hidden="true">' . trim( $icon ) . '</span>';
					}
				}
				$before_header = $icon;
			} elseif ( 'icon-line' === $atts['header_decoration'] ) {
				$icon = '';
				if ( $atts['header_icon'] ) {
					$icon = WPRM_Icon::get( $atts['header_icon'], $atts['header_icon_color'] );

					if ( $icon ) {
						$icon = '<span class="wprm-recipe-icon" aria-hidden="true">' . trim( $icon ) . '</span>';
					}
				}
				$before_header = $icon;

				if ( 'left' === $atts['header_align'] || 'center' === $atts['header_align'] ) {
					$after_header = '<div class="wprm-decoration-line"' . $header_line_style . '></div>';
				}
				if ( 'right' === $atts['header_align'] || 'center' === $atts['header_align'] ) {
					$before_header = '<div class="wprm-decoration-line"' . $header_line_style . '></div>' . $before_header;
				}
			} elseif ( 'spacer' === $atts['header_decoration'] ) {
				if ( 'left' === $atts['header_align'] || 'center' === $atts['header_align'] ) {
					$after_header = '<div class="wprm-decoration-spacer"></div>';
				}
				if ( 'right' === $atts['header_align'] || 'center' === $atts['header_align'] ) {
					$before_header = '<div class="wprm-decoration-spacer"></div>';
				}
			}

			// Special for ingredients.
			if ( 'ingredients' === $field ) {
				if ( 'header' === $atts['unit_conversion'] && isset( $args['unit_conversion_atts'] ) ) {
					$after_header .= '&nbsp;' . WPRM_SC_Unit_Conversion::shortcode( $args['unit_conversion_atts'] );
					$classes[] = 'wprm-header-has-actions';
				}
				if ( 'header' === $atts['adjustable_servings'] && isset( $args['adjustable_servings_atts'] ) ) {
					$after_header .= '&nbsp;' . WPRM_SC_Adjustable_Servings::shortcode( $args['adjustable_servings_atts'] );
					$classes[] = 'wprm-header-has-actions';
				}
			}

			// Special for instructions.
			if ( 'instructions' === $field ) {
				if ( 'header' === $atts['prevent_sleep'] && isset( $args['prevent_sleep_atts'] ) ) {
					$after_header .= '&nbsp;' . WPRM_SC_Prevent_Sleep::shortcode( $args['prevent_sleep_atts'] );
					$classes[] = 'wprm-header-has-actions';
				}
				if ( 'header' === $atts['media_toggle'] && isset( $args['media_toggle_atts'] ) ) {
					$after_header .= '&nbsp;' . WPRM_SC_Media_Toggle::shortcode( $args['media_toggle_atts'] );
					$classes[] = 'wprm-header-has-actions';
				}
			}

			// Header collapsible.
			$collapsible_output = '';
			if ( (bool) $atts['header_collapsible'] ) {
				$collapsible_output = WPRM_Shortcode_Reusable_Collapsible_Button::get_html(
					$atts['header_icon_collapsed'],
					$atts['header_icon_expanded'],
					$atts['header_collapsible_icon_color'],
					'header',
				);

				if ( $collapsible_output ) {
					$classes[] = 'wprm-expandable-container-separated';
					$classes[] = 'wprm-expandable-expanded';

					$after_header .= $collapsible_output;
				}
			}

			// Header text with optional placeholders.
			$header_text = WPRM_i18n::maybe_translate( $atts['header'] );
			if ( isset( $atts['id'] ) ) {
				$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
				if ( $recipe ) {
					$header_text = $recipe->replace_placeholders( $header_text );
				}
			}

			// Custom inline styling.
			$style = '';

			if ( '0px' !== $atts['header_vertical_padding'] || '0px' !== $atts['header_horizontal_padding'] ) {
				$style .= 'padding: ' . $atts['header_vertical_padding'] . ' ' . $atts['header_horizontal_padding'] . ';';
			}
			if ( '' !== $atts['header_background_color'] ) {
				$style .= 'background-color: ' . $atts['header_background_color'] . ';';
			}

			$tag = WPRM_Shortcode_Helper::sanitize_html_element( $atts['header_tag'] );
			$header .= '<' . $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">' . $before_header . WPRM_Shortcode_Helper::sanitize_html( $header_text ) . $after_header . '</' . $tag . '>';

			if ( '0px' !== $atts['header_bottom_margin'] ) {
				$header .= do_shortcode( '[wprm-spacer size="' . $atts['header_bottom_margin'] . '"]' );
			}
		}

		return $header;
	}
}
