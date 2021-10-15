<?php
/**
 * Handle the recipe adjustable servings shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      6.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe adjustable servings shortcode.
 *
 * @since      6.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Adjustable_Servings extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-adjustable-servings';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'serving_options' => array(
				'default' => '1x;2x;3x',
				'type' => 'text',
			),
			'serving_options_any_value' => array(
				'default' => '',
				'type' => 'text',
			),
			'button_background' => array(
				'default' => '#ffffff',
				'type' => 'color',
			),
			'button_accent' => array(
				'default' => '#333333',
				'type' => 'color',
			),
			'button_radius' => array(
				'default' => '3px',
				'type' => 'size',
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	6.0.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		$output = '';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Adjustable_Servings::init();