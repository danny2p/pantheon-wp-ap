<?php
/**
 * Handle the recipe jump video shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe jump video shortcode.
 *
 * @since      3.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Jump_Video extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-jump-video';

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
				'default' => __( 'Jump to Video', 'wp-recipe-maker' ),
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
			'smooth_scroll' => array(
				'default' => '0',
				'type' => 'toggle',
			),
			'smooth_scroll_speed' => array(
				'default' => '500',
				'type' => 'number',
				'dependency' => array(
					'id' => 'smooth_scroll',
					'value' => '1',
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

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->video() ) {
			return '';
		}

		// Get optional icon.
		$icon = '';
		if ( $atts['icon'] ) {
			$icon = WPRM_Icon::get( $atts['icon'], $atts['icon_color'] );

			if ( $icon ) {
				$icon = '<span class="wprm-recipe-icon wprm-recipe-jump-video-icon">' . $icon . '</span> ';
			}
		}

		// Output.
		$classes = array(
			'wprm-recipe-jump-video',
			'wprm-recipe-link',
			'wprm-jump-to-video-shortcode', // Backwards compatibility.
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$smooth_scroll = (bool) $atts['smooth_scroll'];
		$smooth_scroll_speed = '';
		if ( $smooth_scroll ) {
			$classes[] = 'wprm-jump-smooth-scroll';
			$smooth_scroll_speed = ' data-smooth-scroll="' . intval( $atts['smooth_scroll_speed'] ) . '"';
		}

		$style = 'color: ' . $atts['text_color'] . ';';
		if ( 'text' !== $atts['style'] ) {
			$classes[] = 'wprm-recipe-jump-video-' . $atts['style'];
			$classes[] = 'wprm-recipe-link-' . $atts['style'];
			$classes[] = 'wprm-color-accent';
			
			$style .= 'background-color: ' . $atts['button_color'] . ';';
			$style .= 'border-color: ' . $atts['border_color'] . ';';
			$style .= 'border-radius: ' . $atts['border_radius'] . ';';
			$style .= 'padding: ' . $atts['vertical_padding'] . ' ' . $atts['horizontal_padding'] . ';';
		}

		// Backwards compatibility.
		if ( 'legacy' === WPRM_Settings::get( 'recipe_template_mode' ) ) {
			$style = '';
		}

		// Text and optional aria-label.
		$text = __( $atts['text'], 'wp-recipe-maker' );

		$aria_label = '';
		if ( ! $text ) {
			$aria_label = ' aria-label="' . __( 'Jump to Video', 'wp-recipe-maker' ) . '"';
		}

		// "wprm-jump-to-video-shortcode" class for backwards compatibility.
		$output = '<a href="#wprm-recipe-video-container-' . esc_attr( $recipe->id() ) . '" data-recipe="' . esc_attr( $recipe->id() ) . '" style="' . esc_attr( $style ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $smooth_scroll_speed . $aria_label . '>' . $icon . WPRM_Shortcode_Helper::sanitize_html( $text ) . '</a>';
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Jump_Video::init();