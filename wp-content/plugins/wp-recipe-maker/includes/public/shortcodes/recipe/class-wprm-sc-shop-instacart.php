<?php
/**
 * Handle the Shop with Instacart shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      8.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the Shop with Instacart shortcode.
 *
 * @since      8.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Shop_Instacart extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-shop-instacart';

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
			return '';
		}

		// Placeholder in template editor.
		if ( $atts['is_template_editor_preview'] ) {
			if ( WPRM_Settings::get( 'integration_instacart_agree' ) ) {
				return '<div class="wprm-template-editor-premium-only">' . __( 'Placeholder for the Instacart button', 'wp-recipe-maker' ) . '</div>';
			} else {
				return '<div class="wprm-template-editor-premium-only">' . __( 'Make sure to agree with the terms on the WP Recipe Maker > Settings > Integrations page', 'wp-recipe-maker' ) . '</div>';
			}
		}

		// Make sure Instacart integration gets loaded.
		add_filter( 'wprm_load_instacart', '__return_true' );

		// Optional affiliate ID output.
		$affiliate_attributes = '';
		$affiliate_id = WPRM_Settings::get( 'integration_instacart_affiliate_id' );

		if ( $affiliate_id ) {
			$affiliate_attributes = ' data-affiliate_id="' . esc_attr( $affiliate_id ) . '" data-affiliate_platform="recipe_widget"';
		}


		// Actual output.
		$output = '<div id="shop-with-instacart-v1"' . $affiliate_attributes . '></div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Shop_Instacart::init();