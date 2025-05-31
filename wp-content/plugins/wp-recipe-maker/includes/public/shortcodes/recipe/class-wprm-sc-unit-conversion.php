<?php
/**
 * Handle the recipe unit conversion shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe unit conversion shortcode.
 *
 * @since      3.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Unit_Conversion extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-unit-conversion';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'style' => array (
				'default' => 'links',
				'type' => 'dropdown',
				'options' => array(
					'links' => 'Links',
					'dropdown' => 'Dropdown',
					'buttons' => 'Buttons',
					'switch' => 'Switch',
				),
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					'id' => 'style',
					'value' => 'dropdown',
					'type' => 'inverse',
				),
			),
			'separator' => array(
				'default' => ' - ',
				'type' => 'text',
				'dependency' => array(
					'id' => 'style',
					'value' => 'links',
				),
			),
			'button_background' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'buttons',
				),
			),
			'button_accent' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'buttons',
				),
			),
			'button_radius' => array(
				'default' => '3px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'style',
					'value' => 'buttons',
				),
			),
			'switch_style' => array(
				'default' => 'rounded',
				'type' => 'dropdown',
				'options' => array(
					'square' => 'Square Switch',
					'rounded' => 'Rounded Switch',
				),
				'dependency' => array(
					'id' => 'style',
					'value' => 'switch',
				),
			),
			'switch_height' => array(
				'default' => '28px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'style',
					'value' => 'switch',
				),
			),
			'switch_off' => array(
				'default' => '#cccccc',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'switch',
				),
			),
			'switch_off_knob' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'switch',
			
				),
			),
			'switch_off_text' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'switch',
				),
			),
			'switch_on' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'switch',
				),
			),
			'switch_on_knob' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'switch',
				),
			),
			'switch_on_text' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'switch',
				),
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	3.2.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		$output = '';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Unit_Conversion::init();