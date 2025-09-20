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
			'container_header' => array(
				'type' => 'header',
				'default' => __( 'Container', 'wp-recipe-maker' ),
			),
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
			'border_radius' => array(
				'default' => '0px',
				'type' => 'size',
			),
			'background_color' => array(
				'default' => '',
				'type' => 'color',
			),
			'icon_header' => array(
				'type' => 'header',
				'default' => __( 'Icon', 'wp-recipe-maker' ),
			),
			'icon' => array(
				'default' => 'instagram',
				'type' => 'icon',
			),
			'icon_position' => array(
				'default' => 'left',
				'type' => 'dropdown',
				'options' => array(
					'left' => 'Left',
					'right' => 'Right',
					'top' => 'Top',
					'bottom' => 'Bottom',
				),
				'dependency' => array(
					'id' => 'icon',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'icon_gap' => array(
				'default' => '20px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'icon',
					'value' => '',
					'type' => 'inverse',
				),
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
			'header_header' => array(
				'type' => 'header',
				'default' => __( 'Header', 'wp-recipe-maker' ),
			),
			'header_color' => array(
				'default' => '#333333',
				'type' => 'color',
			),
			'header' => array(
				'default' => __( 'Tried this recipe?', 'wp-recipe-maker' ),
				'type' => 'text',
			),
			'header_tag' => array(
				'default' => 'span',
				'type' => 'dropdown',
				'options' => 'header_tags',
				'dependency' => array(
					'id' => 'header',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'text_header' => array(
				'type' => 'header',
				'default' => __( 'Text', 'wp-recipe-maker' ),
			),
			'text_color' => array(
				'default' => '#333333',
				'type' => 'color',
			),
			'link_color' => array(
				'default' => '#3498db',
				'type' => 'color',
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
					'rating' => 'Open Rating Modal',
				),
			),
			'social_text' => array(
				// translators: %handle% and %tag% should stay as is.
				'default' => __( 'Mention %handle% or tag %tag%!', 'wp-recipe-maker' ),
				// translators: %handle% and %tag% should stay as is.
				'help' => __( 'Use the %handle% and %tag% placeholders where you want them to show up.', 'wp-recipe-maker' ),
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'action',
						'value' => 'custom',
						'type' => 'inverse',
					),
					array(
						'id' => 'action',
						'value' => 'rating',
						'type' => 'inverse',
					),
				),
			),
			'social_handle' => array(
				'default' => 'WPRecipeMaker',
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'action',
						'value' => 'custom',
						'type' => 'inverse',
					),
					array(
						'id' => 'action',
						'value' => 'rating',
						'type' => 'inverse',
					),
				),
			),
			'social_tag' => array(
				'default' => 'wprecipemaker',
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'action',
						'value' => 'custom',
						'type' => 'inverse',
					),
					array(
						'id' => 'action',
						'value' => 'rating',
						'type' => 'inverse',
					),
				),
			),
			'custom_text' => array(
				'default' => __( 'Check out %link%!', 'wp-recipe-maker' ),
				'help' => __( 'Use the %link% placeholder where the link should show up.', 'wp-recipe-maker' ),
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
			'rating_text' => array(
				'default' => __( 'Please consider %link%!', 'wp-recipe-maker' ),
				'help' => __( 'Use the %link% placeholder where the link should show up.', 'wp-recipe-maker' ),
				'type' => 'text',
				'dependency' => array(
					'id' => 'action',
					'value' => 'rating',
				),
			),
			'rating_link_text' => array(
				'default' => 'Leaving a Review',
				'type' => 'text',
				'dependency' => array(
					'id' => 'action',
					'value' => 'rating',
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