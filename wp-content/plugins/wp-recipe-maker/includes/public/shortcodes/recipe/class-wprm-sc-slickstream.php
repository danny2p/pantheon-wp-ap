<?php
/**
 * Handle the Slickstream shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      7.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the Slicksteam shortcode.
 *
 * @since      7.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Slickstream extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-slickstream-favorites';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
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
				'default' => __( 'Save', 'wp-recipe-maker' ),
				'type' => 'text',
			),
			'icon_added' => array(
				'default' => '',
				'type' => 'icon',
			),
			'text_added' => array(
				'default' => __( 'Saved!', 'wp-recipe-maker' ),
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
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	3.2.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );
		$output = '';

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->id() ) {
			return '';
		}

		// Get optional icon.
		$icon = '';
		if ( $atts['icon'] ) {
			$icon = WPRM_Icon::get( $atts['icon'], $atts['icon_color'] );

			if ( $icon ) {
				$icon = '<span class="wprm-recipe-icon wprm-recipe-slickstream-icon wprm-recipe-slickstream-not-saved-icon">' . $icon . '</span> ';
			}
		}
		$icon_added = '';
		if ( $atts['icon_added'] ) {
			$icon_added = WPRM_Icon::get( $atts['icon_added'], $atts['icon_color'] );

			if ( $icon_added ) {
				$icon_added = '<span class="wprm-recipe-icon wprm-recipe-slickstream-icon wprm-recipe-slickstream-saved-icon">' . $icon_added . '</span> ';
			}
		}

		// Output.
		$classes = array(
			'wprm-recipe-slickstream',
			'wprm-recipe-link',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$style = 'color: ' . $atts['text_color'] . ';';
		if ( 'text' !== $atts['style'] ) {
			$classes[] = 'wprm-recipe-link-' . $atts['style'];
			$classes[] = 'wprm-color-accent';

			$style .= 'background-color: ' . $atts['button_color'] . ';';
			$style .= 'border-color: ' . $atts['border_color'] . ';';
			$style .= 'border-radius: ' . $atts['border_radius'] . ';';
			$style .= 'padding: ' . $atts['vertical_padding'] . ' ' . $atts['horizontal_padding'] . ';';
		}

		$output = '';

		// Unless in Template Editor, hide both by default, but take up space.
		if ( ! $atts['is_template_editor_preview'] ) {
			$style .= 'visibility: hidden;';
		}

		// Text and optional aria-label.
		$text = __( $atts['text'], 'wp-recipe-maker' );
		$text_added = __( $atts['text_added'], 'wp-recipe-maker' );

		$aria_label = '';
		if ( ! $text ) {
			$aria_label = ' aria-label="' . __( 'Save to Slickstream', 'wp-recipe-maker' ) . '"';
		}
		$aria_label_added = '';
		if ( ! $text_added ) {
			$aria_label_added = ' aria-label="' . __( 'Unsave from Slickstream', 'wp-recipe-maker' ) . '"';
		}

		$output .= '<a href="#" rel="nofollow noreferrer" style="' . esc_attr( $style ) . '" class="wprm-recipe-slickstream-not-saved ' . esc_attr( implode( ' ', $classes ) ) . '" data-recipe-id="' . esc_attr( $recipe->id() ) . '"' . $aria_label . '>' . $icon . WPRM_Shortcode_Helper::sanitize_html( $text ) . '</a>';
		$style .= 'display: none;';
		$output .= '<a href="#" rel="nofollow noreferrer" style="' . esc_attr( $style ) . '" class="wprm-recipe-slickstream-saved ' . esc_attr( implode( ' ', $classes ) ) . '" data-recipe-id="' . esc_attr( $recipe->id() ) . '"' . $aria_label_added . '>' . $icon_added . WPRM_Shortcode_Helper::sanitize_html( $text_added ) . '</a>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Slickstream::init();