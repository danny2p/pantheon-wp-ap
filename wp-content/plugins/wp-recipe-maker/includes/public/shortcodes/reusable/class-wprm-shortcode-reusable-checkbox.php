<?php
/**
 * Checkbox component for shortcodes.
 *
 * @link       http://bootstrapped.ventures
 * @since      10.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 */

/**
 * Reusable checkbox component for shortcodes.
 *
 * @since      10.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Reusable_Checkbox {

	/**
	 * Get attributes for list checkboxes.
	 *
	 * @since	10.0.0
	 */
	public static function get_atts() {
		return array(
			'checkbox_header' => array(
				'type' => 'header',
				'default' => __( 'Checkboxes', 'wp-recipe-maker' ),
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'checkbox',
				),
			),
			'checkbox_size' => array(
				'name' => 'Size',
				'default' => '18px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'checkbox',
				),
			),
			'checkbox_left_position' => array(
				'name' => 'Left Position',
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'checkbox',
				),
			),
			'checkbox_top_position' => array(
				'name' => 'Top Position',
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'checkbox',
				),
			),
			'checkbox_background' => array(
				'name' => 'Background',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'checkbox',
				),
			),
			'checkbox_border_width' => array(
				'name' => 'Border Width',
				'default' => '1px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'checkbox',
				),
			),
			'checkbox_border_style' => array(
				'name' => 'Border Style',
				'default' => 'solid',
				'type' => 'dropdown',
				'options' => 'border_styles',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'checkbox',
				),
			),
			'checkbox_border_color' => array(
				'name' => 'Border Color',
				'default' => 'inherit',
				'type' => 'color',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'checkbox',
				),
			),
			'checkbox_border_radius' => array(
				'name' => 'Border Radius',
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'checkbox',
				),
			),
			'checkbox_check_width' => array(
				'name' => 'Check Width',
				'default' => '2px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'checkbox',
				),
			),
			'checkbox_check_color' => array(
				'name' => 'Check Color',
				'default' => 'inherit',
				'type' => 'color',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'checkbox',
				),
			),
		);
	}
}
