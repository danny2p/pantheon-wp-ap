<?php
/**
 * Handle the jump to comments shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the jump to comments shortcode.
 *
 * @since      4.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Jump_To_Comments extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-jump-to-comments';

	public static function init() {
		self::$attributes = array(
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
			'link' => array(
				'default' => '#commentform',
				'type' => 'text',
			),
			'text' => array(
				'default' => __( 'Rate this Recipe', 'wp-recipe-maker' ),
				'help' => __( 'Use the %comments% placeholder to show the number of comments.', 'wp-recipe-maker' ),
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
	 * @since	4.0.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		if ( ! comments_open() && ! $atts['is_template_editor_preview'] ) {
			return '';
		}

		// Get optional icon.
		$icon = '';
		if ( $atts['icon'] ) {
			$icon = WPRM_Icon::get( $atts['icon'], $atts['icon_color'] );

			if ( $icon ) {
				$icon = '<span class="wprm-recipe-icon wprm-recipe-jump-to-comments-icon">' . $icon . '</span> ';
			}
		}

		// Output.
		$classes = array(
			'wprm-recipe-jump-to-comments',
			'wprm-recipe-link',
			'wprm-block-text-' . esc_attr( $atts['text_style'] ),
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
			$classes[] = 'wprm-recipe-jump-to-comments-' . $atts['style'];
			$classes[] = 'wprm-recipe-link-' . $atts['style'];
			$classes[] = 'wprm-color-accent';

			$style .= 'background-color: ' . $atts['button_color'] . ';';
			$style .= 'border-color: ' . $atts['border_color'] . ';';
			$style .= 'border-radius: ' . $atts['border_radius'] . ';';
			$style .= 'padding: ' . $atts['vertical_padding'] . ' ' . $atts['horizontal_padding'] . ';';
		}

		// Optionally display number of comments.
		$text = __( $atts['text'], 'wp-recipe-maker' );
		if ( false !== strpos( $text, '%comments%' ) ) {
			$nbr_comments = $atts['is_template_editor_preview'] ? 2 : get_comments_number();
			$text = str_ireplace( '%comments%', $nbr_comments, $text );
		}

		// If inside of a recipe card, replace placeholders.
		$recipe = WPRM_Template_Shortcodes::get_recipe( 0 );

		if ( $recipe ) {
			$text = $recipe->replace_placeholders( $text );
		}

		// Optional aria-label.
		$aria_label = '';
		if ( ! $text ) {
			$aria_label = ' aria-label="' . __( 'Rate this Recipe', 'wp-recipe-maker' ) . '"';
		}

		$output = '<a href="' . esc_url( $atts['link'] ) . '" style="' . esc_attr( $style ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $smooth_scroll_speed . $aria_label . '>' . $icon . WPRM_Shortcode_Helper::sanitize_html( $text ) . '</a>';
		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Jump_To_Comments::init();