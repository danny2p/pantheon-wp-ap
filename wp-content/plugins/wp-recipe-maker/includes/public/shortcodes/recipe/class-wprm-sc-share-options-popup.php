<?php
/**
 * Handle the share options popup shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the share options popup shortcode.
 *
 * @since      10.1.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Share_Options_Popup extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-share-options-popup';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'appearance_header' => array(
				'type' => 'header',
				'default' => __( 'Appearance', 'wp-recipe-maker' ),
			),
			'style' => array(
				'default' => 'text',
				'type' => 'dropdown',
				'options' => array(
					'text' => 'Text',
					'button' => 'Button',
					'inline-button' => 'Inline Button',
					'wide-button' => 'Full Width Button',
				),
			),
			'icon' => array(
				'default' => '',
				'type' => 'icon',
			),
			'text' => array(
				'default' => __( 'Share Recipe', 'wp-recipe-maker' ),
				'type' => 'text',
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
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
			'text_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'text',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'horizontal_padding' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'vertical_padding' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'button_color' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'border_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'border_radius' => array(
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'options_appearance_header' => array(
				'type' => 'header',
				'default' => __( 'Popup Options Appearance', 'wp-recipe-maker' ),
			),
			'popup_background' => array(
				'default' => '#333333',
				'type' => 'color',
			),
			'popup_icon_color' => array(
				'name' => 'Icon Color',
				'default' => '#ffffff',
				'type' => 'color',
			),
			'popup_icon_hover_color' => array(
				'name' => 'Icon Hover Color',
				'default' => '#ffffff',
				'type' => 'color',
			),
			'popup_text_color' => array(
				'name' => 'Text Color',
				'default' => '#ffffff',
				'type' => 'color',
			),
			'popup_text_hover_color' => array(
				'name' => 'Text Hover Color',
				'default' => '#ffffff',
				'type' => 'color',
			),
			'underline' => array(
				'default' => '0',
				'type' => 'toggle',
			),
			'underline_on_hover' => array(
				'default' => '1',
				'type' => 'toggle',
			),
			'popup_align' => array(
				'text' => 'Align',
				'default' => 'flex-start',
				'type' => 'dropdown',
				'options' => array(
					'flex-start' => 'Left',
					'center' => 'Center',
					'flex-end' => 'Right',
					'space-between' => 'Space Between',
				),
			),
			'share_header' => array(
				'type' => 'header',
				'default' => __( 'Share Options', 'wp-recipe-maker' ),
			),
			'share_options' => array(
				'default' => 'pinterest, facebook, twitter',
				'type' => 'text',
				'help' => __( 'Comma separated list of share options to show.', 'wp-recipe-maker' ) . ' ' . __( 'Available options:', 'wp-recipe-maker' ) . ' pinterest, facebook, twitter, bluesky, messenger, whatsapp, text, email',
			),
			'pinterest_action' => array(
				'default' => 'one',
				'type' => 'dropdown',
				'options' => array(
					'one' => 'Pin one image',
					'any' => 'Pin any image from page (only works when loading pinit.js)',
				),
				'dependency' => array(
					'id' => 'share_options',
					'value' => 'pinterest',
					'type' => 'includes',
				),
			),
			'text_message_intro' => array(
				'default' => __( 'Check out this recipe!', 'wp-recipe-maker' ),
				'type' => 'text',
				'dependency' => array(
					'id' => 'share_options',
					'value' => 'text',
					'type' => 'includes',
				),
			),
			'text_message_ingredients' => array(
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(
					'id' => 'share_options',
					'value' => 'text',
					'type' => 'includes',
				),
			),
			'email_message_subject' => array(
				'default' => __( 'Check out this recipe!', 'wp-recipe-maker' ),
				'type' => 'text',
				'dependency' => array(
					'id' => 'share_options',
					'value' => 'email',
					'type' => 'includes',
				),
			),
			'email_message_intro' => array(
				'default' => __( 'I found this recipe you might like:', 'wp-recipe-maker' ),
				'type' => 'text',
				'dependency' => array(
					'id' => 'share_options',
					'value' => 'email',
					'type' => 'includes',
				),
			),
			'email_message_ingredients' => array(
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(
					'id' => 'share_options',
					'value' => 'email',
					'type' => 'includes',
				),
			),
			'icon_label_header' => array(
				'type' => 'header',
				'default' => __( 'Icons & Text', 'wp-recipe-maker' ),
			),
		);

		self::$attributes = $atts;

		add_filter( 'wprm_template_parse_shortcode', array( __CLASS__, 'parse_shortcode' ), 10, 3 );

		parent::init();
	}

	/**
	 * Add dynamic shortcode attributes.
	 *
	 * @since	6.0.0
	 * @param	array $shortcodes 	All shortcodes to parse.
	 * @param	array $shortcode 	Shortcode getting parsed.
	 * @param	array $atts 		Shortcode attributes.
	 */
	public static function parse_shortcode( $shortcodes, $shortcode, $attributes ) {
		if ( 'wprm-recipe-share-options-popup' === $shortcode ) {
			$fields_with_labels = array(
				'pinterest' => __( 'Pinterest', 'wp-recipe-maker' ),
				'facebook' => __( 'Facebook', 'wp-recipe-maker' ),
				'twitter' => __( 'Share on X', 'wp-recipe-maker' ),
				'bluesky' => __( 'Bluesky', 'wp-recipe-maker' ),
				'messenger' => __( 'Messenger', 'wp-recipe-maker' ),
				'whatsapp' => __( 'WhatsApp', 'wp-recipe-maker' ),
				'text' => __( 'Text', 'wp-recipe-maker' ),
				'email' => __( 'Email', 'wp-recipe-maker' ),
			);

			// Add label and icon attributes to shortcode.
			foreach ( $fields_with_labels as $key => $label ) {
				$dependency = array(
					'id' => 'share_options',
					'value' => $key,
					'type' => 'includes',
				);

				// Add Label and Icon attributes.
				$shortcodes[ $shortcode ]['icon_' . $key] = array(
					'default' => 'twitter' === $key ? 'x' : $key,
					'type' => 'icon',
					'dependency' => $dependency,
				);
				$shortcodes[ $shortcode ]['text_' . $key] = array(
					'default' => $label,
					'type' => 'text',
					'dependency' => $dependency,
				);
			}
		}

		return $shortcodes;
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	10.1.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		$output = '';

		// Show teaser for Premium only shortcode in Template editor.
		if ( $atts['is_template_editor_preview'] ) {
			$output = '<div class="wprm-template-editor-premium-only">The Share Options Popup feature is only available in <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/">WP Recipe Maker Premium</a>.</div>';
		}

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Share_Options_Popup::init();