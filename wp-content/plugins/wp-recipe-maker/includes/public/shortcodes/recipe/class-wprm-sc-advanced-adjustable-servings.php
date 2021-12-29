<?php
/**
 * Handle the recipe advanced adjustable servings shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      8.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe advanced adjustable servings shortcode.
 *
 * @since      8.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Advanced_Adjustable_Servings extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-advanced-adjustable-servings';

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
			'before_text' => array(
				'default' => __( 'Makes:', 'wp-recipe-maker' ),
				'type' => 'text',
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	8.0.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		$output = '';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Advanced_Adjustable_Servings::init();