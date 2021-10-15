<?php
/**
 * Handle the recipe pin shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe pin shortcode.
 *
 * @since      4.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Pin extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-pin';

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
				'default' => __( 'Pin Recipe', 'wp-recipe-maker' ),
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
	 * @since	4.0.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->pin_image_url() ) {
			return '';
		}

		// Build pin URL.
		$url = $recipe->permalink();
		$url = $url ? $url : get_permalink();
		$url = $url ? $url : get_home_url();

		$pin_url = 'https://www.pinterest.com/pin/create/bookmarklet/';
		$pin_url .= '?url=' . urlencode( $url );
		$pin_url .= '&media=' . urlencode( $recipe->pin_image_url() );
		$pin_url .= '&description=' . urlencode( $recipe->pin_image_description() );
		$pin_url .= '&is_video=false';

		$pin_script = "onclick=\"window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=500,height=500'); return false;\"";

		// Get optional icon.
		$icon = '';
		if ( $atts['icon'] ) {
			$icon = WPRM_Icon::get( $atts['icon'], $atts['icon_color'] );

			if ( $icon ) {
				$icon = '<span class="wprm-recipe-icon wprm-recipe-pin-icon">' . $icon . '</span> ';
			}
		}

		// Output.
		$classes = array(
			'wprm-recipe-pin',
			'wprm-recipe-link',
			'wprm-block-text-' . $atts['text_style'],
		);

		$style = 'color: ' . $atts['text_color'] . ';';
		if ( 'text' !== $atts['style'] ) {
			$classes[] = 'wprm-recipe-pin-' . $atts['style'];
			$classes[] = 'wprm-recipe-link-' . $atts['style'];
			$classes[] = 'wprm-color-accent';

			$style .= 'background-color: ' . $atts['button_color'] . ';';
			$style .= 'border-color: ' . $atts['border_color'] . ';';
			$style .= 'border-radius: ' . $atts['border_radius'] . ';';
			$style .= 'padding: ' . $atts['vertical_padding'] . ' ' . $atts['horizontal_padding'] . ';';
		}

		$output = '<a href="' . $pin_url . '" ' . $pin_script .' data-recipe="' . esc_attr( $recipe->id() ) . '" style="' . $style . '" class="' . implode( ' ', $classes ) . '" target="_blank" rel="nofollow noopener">' . $icon . __( $atts['text'], 'wp-recipe-maker' ) . '</a>';
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Pin::init();