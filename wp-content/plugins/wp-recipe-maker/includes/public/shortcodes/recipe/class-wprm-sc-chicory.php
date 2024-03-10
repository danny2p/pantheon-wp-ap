<?php
/**
 * Handle the Chicory shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the Chicory shortcode.
 *
 * @since      9.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Chicory extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-chicory';

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
			if ( WPRM_Settings::get( 'integration_chicory_activate' ) ) {
				return '<div class="wprm-template-editor-premium-only">' . __( 'Placeholder for the Chicory button', 'wp-recipe-maker' ) . '</div>';
			} else {
				return '<div class="wprm-template-editor-premium-only">' . __( 'Make sure to activate the integration on the WP Recipe Maker > Settings > Integrations page', 'wp-recipe-maker' ) . '</div>';
			}
		}

		// Make sure Chicory integration gets loaded.
		add_filter( 'wprm_load_chicory', '__return_true' );

		// Actual output.
		$output = '<div class="chicory-order-ingredients"></div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Chicory::init();