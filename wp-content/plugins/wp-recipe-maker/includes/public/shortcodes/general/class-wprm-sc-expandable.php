<?php
/**
 * Handle the expandable shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 */

/**
 * Handle the expandable shortcode.
 *
 * @since      9.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Expandable extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-expandable';

	public static function init() {
		self::$attributes = array(
			'style' => array(
				'default' => 'button',
				'type' => 'dropdown',
				'options' => array(
					'button' => 'Button',
					'disappearing' => 'Disappearing Button',
				),
			),
			'initial_state' => array(
				'default' => 'collapsed',
				'type' => 'dropdown',
				'options' => array(
					'collapsed' => 'Collapsed',
					'expanded' => 'Expanded',
				),
				'dependency' => array(
					'id' => 'style',
					'value' => 'disappearing',
					'type' => 'inverse',
				),
			),
			'text_collapsed' => array(
				'default' => 'Show',
				'type' => 'text',
			),
			'text_expanded' => array(
				'default' => 'Hide',
				'type' => 'text',
				'dependency' => array(
					'id' => 'style',
					'value' => 'disappearing',
					'type' => 'inverse',
				),
			),
			'button_style_header' => array(
				'type' => 'header',
				'default' => __( 'Button Style', 'wp-recipe-maker' ),
			),
			'button_style' => array(
				'default' => 'text',
				'type' => 'dropdown',
				'options' => array(
					'text' => 'Text',
					'button' => 'Button',
					'inline-button' => 'Inline Button',
					'wide-button' => 'Full Width Button',
				),
			),
			'text_style' => array(
				'default' => 'bold',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'text_color' => array(
				'default' => '#333333',
				'type' => 'color',
			),
			'icon_collapsed' => array(
				'default' => 'triangle-right',
				'type' => 'icon',
			),
			'icon_expanded' => array(
				'default' => 'triangle-down',
				'type' => 'icon',
				'dependency' => array(
					'id' => 'style',
					'value' => 'disappearing',
					'type' => 'inverse',
				),
			),
			'icon_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'icon_collapsed',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'icon_expanded',
						'value' => '',
						'type' => 'inverse',
					),
				),
				'dependency_compare' => 'OR',
			),
			'icon_position' => array(
				'default' => 'before',
				'type' => 'dropdown',
				'options' => array(
					'before' => 'Before Text',
					'after' => 'After Text',
				),
				'dependency' => array(
					array(
						'id' => 'icon_collapsed',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'icon_expanded',
						'value' => '',
						'type' => 'inverse',
					),
				),
				'dependency_compare' => 'OR',
			),
			'horizontal_padding' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'button_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'vertical_padding' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'button_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'button_color' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'button_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'border_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'button_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'border_radius' => array(
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'button_style',
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
	 * @since	9.3.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts, $content ) {
		$atts = parent::get_attributes( $atts );

		// Get optional icons.
		$icon_collapsed = '';
		if ( $atts['icon_collapsed'] ) {
			$icon_collapsed = WPRM_Icon::get( $atts['icon_collapsed'], $atts['icon_color'] );

			if ( $icon_collapsed ) {
				$icon_collapsed = '<span class="wprm-recipe-icon wprm-adjustable-icon">' . $icon_collapsed . '</span> ';
			}
		}
		$icon_expanded = '';
		if ( $atts['icon_expanded'] ) {
			$icon_expanded = WPRM_Icon::get( $atts['icon_expanded'], $atts['icon_color'] );

			if ( $icon_expanded ) {
				$icon_expanded = '<span class="wprm-recipe-icon wprm-adjustable-icon">' . $icon_expanded . '</span> ';
			}
		}

		// Text to show.
		$text_collapsed = WPRM_Shortcode_Helper::sanitize_html( $atts['text_collapsed'] );
		$text_expanded = WPRM_Shortcode_Helper::sanitize_html( $atts['text_expanded'] );

		// Add icon to text.
		if ( 'before' === $atts['icon_position'] ) {
			$text_collapsed = $icon_collapsed . $text_collapsed;
			$text_expanded = $icon_expanded . $text_expanded;
		} else {
			$text_collapsed .= ' ' .$icon_collapsed;
			$text_expanded .= ' ' . $icon_expanded;
		}

		// If inside of a recipe card, replace placeholders.
		$recipe = WPRM_Template_Shortcodes::get_recipe( 0 );

		if ( $recipe ) {
			$text_collapsed = $recipe->replace_placeholders( $text_collapsed );
			$text_expanded = $recipe->replace_placeholders( $text_expanded );
		}

		// Initial state.
		$initial_state = 'expanded' === $atts['initial_state'] && 'disappearing' !== $atts['style'] ? 'expanded' : 'collapsed';

		// Output.
		$classes = array(
			'wprm-expandable-container',
			'wprm-expandable-' . esc_attr( $atts['style'] ),
			'wprm-expandable-' . $initial_state,
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		// Buttons.
		$button_classes = array(
			'wprm-expandable-button',
			'wprm-recipe-link',
			'wprm-block-text-' . esc_attr( $atts['text_style'] ),
		);

		$button_style = 'color: ' . $atts['text_color'] . ';';
		$button_tag = 'a';
		if ( 'text' !== $atts['button_style'] ) {
			$button_tag = 'button';
			$button_classes[] = 'wprm-recipe-link-' . esc_attr( $atts['button_style'] );
			$button_classes[] = 'wprm-color-accent';

			$button_style .= 'background-color: ' . $atts['button_color'] . ';';
			$button_style .= 'border-color: ' . $atts['border_color'] . ';';
			$button_style .= 'border-radius: ' . $atts['border_radius'] . ';';
			$button_style .= 'padding: ' . $atts['vertical_padding'] . ' ' . $atts['horizontal_padding'] . ';';
		}

		$output = '';
		$output .= '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';
		$output .= '<div class="wprm-expandable-button-container">';
		$output .= '<' . $button_tag . ' role="button" aria-expanded="false" class="wprm-expandable-button-show ' . esc_attr( implode( ' ', $button_classes ) ). '" style="' . esc_attr( $button_style ) . '">' . $text_collapsed . '</' . $button_tag . '>';

		if ( 'disappearing' !== $atts['style'] ) {
			$output .= '<' . $button_tag . ' role="button" aria-expanded="true" class="wprm-expandable-button-hide ' . esc_attr( implode( ' ', $button_classes ) ). '" style="' . esc_attr( $button_style ) . '">' . $text_expanded . '</' . $button_tag . '>';
		}

		$output .= '</div>';
		$output .= '<div class="wprm-expandable-content">' . do_shortcode( $content ) . '</div>';
		$output .= '</div>';

		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Expandable::init();