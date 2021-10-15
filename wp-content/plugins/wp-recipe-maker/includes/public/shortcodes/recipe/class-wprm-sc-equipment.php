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
			'display_style' => array(
				'default' => 'list',
				'type' => 'dropdown',
				'options' => array(
					'list' => 'List',
					'images' => 'Images',
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
			'list_style_continue_numbers' => array(
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'list_style_background' => array(
				'default' => '#444444',
				'type' => 'color',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'list_style_size' => array(
				'default' => '18px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'list_style_text' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'list_style_text_size' => array(
				'default' => '12px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'image_size' => array(
				'default' => '100x100',
				'type' => 'image_size',
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'images',
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
			),
		);

		$atts = array_merge( WPRM_Shortcode_Helper::get_section_atts(), $atts );
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
			return '';
		}

		// Output.
		$classes = array(
			'wprm-recipe-equipment-container',
			'wprm-block-text-' . $atts['text_style'],
		);

		$output = '<div class="' . implode( ' ', $classes ) . '" data-recipe="' . esc_attr( $recipe->id() ) . '">';
		$output .= WPRM_Shortcode_Helper::get_section_header( $atts, 'equipment' );

		if ( 'list' === $atts['display_style'] ) {
			$output .= '<ul class="wprm-recipe-equipment wprm-recipe-equipment-list">';
			foreach ( $recipe->equipment() as $equipment ) {
				$list_style_type = 'checkbox' === $atts['list_style'] || 'advanced' === $atts['list_style'] ? 'none' : $atts['list_style'];
				$style = 'list-style-type: ' . $list_style_type . ';';
				$output .= '<li class="wprm-recipe-equipment-item" style="' . $style . '">';

				// Equipment link.
				$name = apply_filters( 'wprm_recipe_equipment_shortcode_link', $equipment['name'], $equipment );

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

		$output .= '</div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Equipment::init();