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
			'style' => array(
				'default' => 'dark',
				'type' => 'dropdown',
				'options' => array(
					'dark' => 'Dark',
					'light' => 'Light',
					'white' => 'White',
				),
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

		// Check if agreed to Instacart Button terms.
		if ( ! WPRM_Settings::get( 'integration_instacart_agree' ) ) {
			if ( $atts['is_template_editor_preview'] ) {
				return '<div class="wprm-template-editor-premium-only">' . __( 'Make sure to agree to the Instacart terms on the WP Recipe Maker > Settings > Integrations page first', 'wp-recipe-maker' ) . '</div>';
			} else {
				return '';
			}
		}

		// Output.
		$classes = array(
			'wprm-recipe-shop-instacart',
			'wprm-recipe-shop-instacart-' . $atts['style'],
		);

		// Hide by default if not in Template Editor.
		$style = '';
		if ( ! $atts['is_template_editor_preview'] ) {
			$style .= 'visibility: hidden;';
		}

		// Button.
		$button = '<img src="' . WPRM_URL . 'assets/icons/integrations/instacart.svg" alt="" data-pin-nopin="true" /><span>Get Recipe Ingredients</span>';

		$output = '<div role="button" data-recipe="' . esc_attr( $recipe->id() ) . '" style="' . esc_attr( $style ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $button . '</div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Shop_Instacart::init();