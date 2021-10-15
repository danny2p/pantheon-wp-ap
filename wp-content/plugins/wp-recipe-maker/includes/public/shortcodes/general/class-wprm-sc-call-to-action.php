<?php
/**
 * Handle the Call to Action shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 */

/**
 * Handle the Call to Action shortcode.
 *
 * @since      4.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Call_to_Action extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-call-to-action';

	public static function init() {
		self::$attributes = array(
			'style' => array(
				'default' => 'simple',
				'type' => 'dropdown',
				'options' => array(
					'simple' => 'Simple'
				),
			),
			'padding' => array(
				'default' => '10px',
				'type' => 'size',
			),
			'margin' => array(
				'default' => '0px',
				'type' => 'size',
			),
			'background_color' => array(
				'default' => '',
				'type' => 'color',
			),
			'icon' => array(
				'default' => 'instagram',
				'type' => 'icon',
			),
			'icon_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'icon',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'header_color' => array(
				'default' => '#333333',
				'type' => 'color',
			),
			'text_color' => array(
				'default' => '#333333',
				'type' => 'color',
			),
			'link_color' => array(
				'default' => '#3498db',
				'type' => 'color',
			),
			'header' => array(
				'default' => __( 'Tried this recipe?', 'wp-recipe-maker' ),
				'type' => 'text',
			),
			'action' => array(
				'default' => 'instagram',
				'type' => 'dropdown',
				'options' => array(
					'instagram' => 'Instagram',
					'twitter' => 'Twitter',
					'facebook' => 'Facebook',
					'pinterest' => 'Pinterest',
					'custom' => 'Custom Link',
				),
			),
			'social_text' => array(
				'default' => __( 'Mention %handle% or tag %tag%!', 'wp-recipe-maker' ),
				'type' => 'text',
				'dependency' => array(
					'id' => 'action',
					'value' => 'custom',
					'type' => 'inverse',
				),
			),
			'social_handle' => array(
				'default' => 'WPRecipeMaker',
				'type' => 'text',
				'dependency' => array(
					'id' => 'action',
					'value' => 'custom',
					'type' => 'inverse',
				),
			),
			'social_tag' => array(
				'default' => 'wprecipemaker',
				'type' => 'text',
				'dependency' => array(
					'id' => 'action',
					'value' => 'custom',
					'type' => 'inverse',
				),
			),
			'custom_text' => array(
				'default' => __( 'Check out %link%!', 'wp-recipe-maker' ),
				'type' => 'text',
				'dependency' => array(
					'id' => 'action',
					'value' => 'custom',
				),
			),
			'custom_link_url' => array(
				'default' => 'http://bootstrapped.ventures/wp-recipe-maker/',
				'type' => 'text',
				'dependency' => array(
					'id' => 'action',
					'value' => 'custom',
				),
			),
			'custom_link_text' => array(
				'default' => 'WP Recipe Maker',
				'type' => 'text',
				'dependency' => array(
					'id' => 'action',
					'value' => 'custom',
				),
			),
			'custom_link_target' => array(
				'default' => '_blank',
				'type' => 'dropdown',
				'options' => array(
					'_self' => 'Open in same tab',
					'_blank' => 'Open in new tab',
				),
				'dependency' => array(
					'id' => 'action',
					'value' => 'custom',
				),
			),
			'custom_link_nofollow' => array(
				'default' => 'dofollow',
				'type' => 'dropdown',
				'options' => array(
					'dofollow' => 'Do not add nofollow attribute',
					'nofollow' => 'Add nofollow attribute',
				),
				'dependency' => array(
					'id' => 'action',
					'value' => 'custom',
				),
			),
		);
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
			$output = '<div class="wprm-template-editor-premium-only">The Call to Action is only available in <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/">WP Recipe Maker Premium</a>.</div>';
		}

		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Call_to_Action::init();