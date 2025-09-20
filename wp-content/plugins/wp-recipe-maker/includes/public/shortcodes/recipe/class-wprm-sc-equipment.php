<?php
/**
 * Handle the recipe equipment shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe equipment shortcode.
 *
 * @since      3.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Equipment extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-equipment';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'section_header' => array(
				'type' => 'header',
				'default' => __( 'Header', 'wp-recipe-maker' ),
			),
			'appearance_header' => array(
				'type' => 'header',
				'default' => __( 'Appearance', 'wp-recipe-maker' ),
			),
			'display_style' => array(
				'default' => 'list',
				'type' => 'dropdown',
				'options' => array(
					'list' => 'List',
					'images' => 'Images',
					'grid' => 'Grid',
				),
			),
			'bottom_border' => array(
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'list',
				),
			),
			'bottom_border_gap' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'display_style',
						'value' => 'list',
					),
					array(
						'id' => 'bottom_border',
						'value' => '1',
					),
				),
			),
			'bottom_border_width' => array(
				'default' => '1px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'display_style',
						'value' => 'list',
					),
					array(
						'id' => 'bottom_border',
						'value' => '1',
					),
				),
			),
			'bottom_border_style' => array(
				'default' => 'solid',
				'type' => 'dropdown',
				'options' => 'border_styles',
				'dependency' => array(
					array(
						'id' => 'display_style',
						'value' => 'list',
					),
					array(
						'id' => 'bottom_border',
						'value' => '1',
					),
				),
			),
			'bottom_border_color' => array(
				'default' => '#eeeeee',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'display_style',
						'value' => 'list',
					),
					array(
						'id' => 'bottom_border',
						'value' => '1',
					),
				),
			),
			'grid_columns' => array(
				'default' => '3',
				'type' => 'dropdown',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'grid',
				),
			),
			'list_header' => array(
				'type' => 'header',
				'default' => __( 'List', 'wp-recipe-maker' ),
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'list',
				),
			),
			'force_item_position' => array(
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'list',
				),
			),
			'list_item_position' => array(
				'default' => '32px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'display_style',
						'value' => 'list',
					),
					array(
						'id' => 'force_item_position',
						'value' => '1',
					),
				),
			),
			'list_style' => array(
				'default' => 'disc',
				'type' => 'dropdown',
				'options' => 'list_style_types',
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'list',
				),
			),
			'image_header' => array(
				'type' => 'header',
				'default' => __( 'Images', 'wp-recipe-maker' ),
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'list',
					'type' => 'inverse',
				),
			),
			'image_size' => array(
				'default' => '100x100',
				'type' => 'image_size',
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'list',
					'type' => 'inverse',
				),
			),
			'image_border_radius' => array(
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'list',
					'type' => 'inverse',
				),
			),
			'image_alignment' => array(
				'default' => 'left',
				'type' => 'dropdown',
				'options' => array(
					'left' => 'Left',
					'center' => 'Centered',
					'right' => 'Right',
					'spaced' => 'Spaced Evenly',
				),
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'images',
				),
			),
			'text_header' => array(
				'type' => 'header',
				'default' => __( 'Text', 'wp-recipe-maker' ),
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'equipment_notes_separator' => array(
				'name' => 'Notes Separator',
				'default' => 'none',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'None',
					'comma' => 'Comma',
					'dash' => 'Dash',
					'parentheses' => 'Parentheses',
				),
			),
			'notes_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => array(
					'normal' => 'Normal',
					'faded' => 'Faded',
					'smaller' => 'Smaller',
					'smaller-faded' => 'Smaller & Faded',
				),
			),
			'container_header' => array(
				'type' => 'header',
				'default' => __( 'Equipment Container', 'wp-recipe-maker' ),
			),
		);

		$atts = WPRM_Shortcode_Helper::insert_atts_after_key( $atts, 'section_header', WPRM_Shortcode_Helper::get_section_atts() );
		$atts = WPRM_Shortcode_Helper::insert_atts_after_key( $atts, 'container_header', WPRM_Shortcode_Helper::get_internal_container_atts() );

		// Extra dependencies for list style atts.
		$checkbox_atts = WPRM_Shortcode_Helper::get_checkbox_atts();
		$advanced_list_atts = WPRM_Shortcode_Helper::get_advanced_list_atts();

		foreach ( $checkbox_atts as $key => $value ) {
			$checkbox_atts[ $key ]['dependency'] = array(
				$value['dependency'],
				array(
				'id' => 'display_style',
				'value' => 'list',
			));
		}
		foreach ( $advanced_list_atts as $key => $value ) {
			$advanced_list_atts[ $key ]['dependency'] = array(
				$value['dependency'],
				array(
				'id' => 'display_style',
				'value' => 'list',
			));
		}
		$atts = WPRM_Shortcode_Helper::insert_atts_after_key( $atts, 'list_style', $checkbox_atts );
		$atts = WPRM_Shortcode_Helper::insert_atts_after_key( $atts, 'list_style', $advanced_list_atts );

		self::$attributes = $atts;

		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	3.3.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->equipment() ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		// Output.
		$classes = array(
			'wprm-recipe-equipment-container',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		// Custom style.
		$css_variables = 'checkbox' === $atts['list_style'] ? parent::get_inline_css_variables( 'list', $atts, array( 'checkbox_size', 'checkbox_left_position', 'checkbox_top_position', 'checkbox_background', 'checkbox_border_width', 'checkbox_border_style', 'checkbox_border_color', 'checkbox_border_radius', 'checkbox_check_width', 'checkbox_check_color' ) ) : '';
		$style = WPRM_Shortcode_Helper::get_inline_style( $css_variables );

		$output = '<div id="recipe-' . esc_attr( $recipe->id() ) . '-equipment" class="' . esc_attr( implode( ' ', $classes ) ) . '" data-recipe="' . esc_attr( $recipe->id() ) . '"' . $style . '>';
		$output .= WPRM_Shortcode_Helper::get_section_header( $atts, 'equipment' );

		if ( (bool) $atts['has_container'] ) {
			$output .= WPRM_Shortcode_Helper::get_internal_container( $atts, 'equipment' );
		}

		if ( 'list' === $atts['display_style'] ) {
			$output .= '<ul class="wprm-recipe-equipment wprm-recipe-equipment-list">';
			foreach ( $recipe->equipment() as $index => $equipment ) {
				$list_style_type = 'checkbox' === $atts['list_style'] || 'advanced' === $atts['list_style'] ? 'none' : $atts['list_style'];
				$style = 'list-style-type: ' . $list_style_type . ';';

				// Add bottom border.
				if ( (bool) $atts['bottom_border'] ) {
					// Only if this is not the last item.
					if ( $index !== count( $recipe->equipment() ) - 1 ) {
						$style .= 'border-bottom: ' . esc_attr( $atts['bottom_border_width'] ) . ' ' . esc_attr( $atts['bottom_border_style'] ) . ' ' . esc_attr( $atts['bottom_border_color'] ) . ';';
						$style .= 'padding-bottom: ' . esc_attr( $atts['bottom_border_gap'] ) . ';';
						$style .= 'margin-bottom: ' . esc_attr( $atts['bottom_border_gap'] ) . ';';
					}
				}

				if ( (bool) $atts['force_item_position'] ) {
					$style .= 'margin-left: ' . esc_attr( $atts['list_item_position'] ) . ';';	
				}

				$output .= '<li class="wprm-recipe-equipment-item" style="' . esc_attr( $style ) . '">';

				// Equipment link.
				$name = apply_filters( 'wprm_recipe_equipment_shortcode_link', $equipment['name'], $equipment );

				// Maybe add amount or notes.
				if ( isset( $equipment['amount'] ) && $equipment['amount'] ) {
					$name = $equipment['amount'] . ' ' . $name;
				}
				if ( isset( $equipment['notes'] ) && $equipment['notes'] ) {
					$notes = $equipment['notes'];

					switch ( $atts['equipment_notes_separator'] ) {
						case 'comma':
							$separator = ',&#32;';
							break;
						case 'dash':
							$separator = '&#32;-&#32;';
							break;
						case 'parentheses':
							$notes = '(' . $notes . ')';
							// Fall through to default separator.
						default:
							$separator = '&#32;';
					}

					$name = $name . $separator . '<span class="wprm-recipe-equipment-notes wprm-recipe-equipment-notes-' . esc_attr( $atts['notes_style'] ) . '">' . $notes . '</span>';
				}

				$equipment_output = '<div class="wprm-recipe-equipment-name">' . $name . '</div>';

				// Optional checkbox.
				if ( 'checkbox' === $atts['list_style'] ) {
					$equipment_output = apply_filters( 'wprm_recipe_equipment_shortcode_checkbox', $equipment_output );
				}

				$output .= $equipment_output;

				$output .= '</li>';
			}
			$output .= '</ul>';
		} else {
			$output = apply_filters( 'wprm_recipe_equipment_shortcode_display', $output, $atts, $recipe );
		}

		if ( (bool) $atts['has_container'] ) {
			$output .= '</div>';
		}

		$output .= '</div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Equipment::init();