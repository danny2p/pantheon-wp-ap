<?php
/**
 * Handle the Emeals shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the Emeals shortcode.
 *
 * @since      9.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Emeals extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-emeals';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
		);

		self::$attributes = $atts;

		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	8.3.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );
		$output = '';

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->id() ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		// Placeholder in template editor.
		if ( $atts['is_template_editor_preview'] ) {
			return '<div class="wprm-template-editor-premium-only">' . __( 'Placeholder for the Emeals button', 'wp-recipe-maker' ) . '</div>';
		}

		// Make sure eMeals integration gets loaded.
		add_filter( 'wprm_load_emeals', '__return_true' );

		$style = 'border-radius: 100px; background-color: #0071CE; border: 0; box-sizing: border-box; color: #ffffff; font-family: Poppins, sans-serif; font-size: 15px; font-weight: normal; line-height: 1.25rem; padding: 16px 12px; text-align: center; text-decoration-thickness: auto; text-transform: none; cursor: pointer; user-select: none; touch-action: manipulation; width: 300px;';
		$text = __( 'Get ingredients with', 'wp-recipe-maker' );
		$img = '<img src="https://emeals-content.s3.amazonaws.com/web-shoppable/walmart-logo.png" style="width: auto; height: 20px; margin-right: 5px; vertical-align: bottom;"/>';

		// Actual output.
		$output = '<button id="em-shop-button" data-vendor="walmart" style="' . esc_attr( $style ) . '">' . $text . ' ' . $img  . '</button>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Emeals::init();

