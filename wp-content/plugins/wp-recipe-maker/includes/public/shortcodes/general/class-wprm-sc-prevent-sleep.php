<?php
/**
 * Handle the Prevent Sleep shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      7.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 */

/**
 * Handle the Prevent Sleep shortcode.
 *
 * @since      7.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Prevent_Sleep extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-prevent-sleep';

	public static function init() {
		$atts = array(
			'label' => array(
				'default' => 'Cook Mode',
				'type' => 'text',
			),
			'label_style' => array(
				'default' => 'bold',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'description' => array(
				'default' => 'Prevent your screen from going dark',
				'type' => 'text',
			),
			'description_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
		);

		$atts = array_merge( WPRM_Shortcode_Helper::get_toggle_switch_atts(), $atts );
		self::$attributes = $atts;
		
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	4.0.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		// Show teaser for Premium only shortcode in Template editor.
		$output = '';
		if ( $atts['is_template_editor_preview'] ) {
			$output = '<div class="wprm-template-editor-premium-only">The Prevent Sleep shortcode is only available in <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/">WP Recipe Maker Premium</a>.</div>';
		}

		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Prevent_Sleep::init();