<?php
/**
 * Handle the recipe media toggle shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      6.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe media toggle shortcode.
 *
 * @since      6.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Media_Toggle extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-media-toggle';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'button_background' => array(
				'default' => '#ffffff',
				'type' => 'color',
			),
			'button_accent' => array(
				'default' => '#333333',
				'type' => 'color',
			),
			'button_radius' => array(
				'default' => '3px',
				'type' => 'size',
			),
			'on_icon' => array(
				'default' => 'camera-2',
				'type' => 'icon',
			),
			'on_text' => array(
				'default' => '',
				'type' => 'text',
			),
			'off_icon' => array(
				'default' => 'camera-no',
				'type' => 'icon',
			),
			'off_text' => array(
				'default' => '',
				'type' => 'text',
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	6.0.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->instructions() ) {
			return '';
		}

		$has_instructions_media = false;
		$instructions_flat = $recipe->instructions_flat();

		foreach( $instructions_flat as $instruction ) {
			if ( isset( $instruction['image'] ) && $instruction['image'] || ( isset( $instruction['video'] ) && isset( $instruction['video']['type'] ) && in_array( $instruction['video']['type'], array( 'upload', 'embed' ) ) ) ) {
				$has_instructions_media = true;
				break;
			}
		}

		if ( ! $has_instructions_media && 'demo' !== $recipe->id() ) {
			return '';
		}

		$classes = array(
			'wprm-recipe-media-toggle-container',
			'wprm-toggle-container',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		// Custom style.
		$style = '';
		$style .= 'background-color: ' . $atts['button_background'] . ';';
		$style .= 'border-color: ' . $atts['button_accent'] . ';';
		$style .= 'color: ' . $atts['button_accent'] . ';';
		$style .= 'border-radius: ' . $atts['button_radius'] . ';';

		// Buttons.
		$buttons = array(
			'on' => __( 'Show instruction media', 'wp-recipe-maker' ),
			'off' => __( 'Hide instruction media', 'wp-recipe-maker' ),
		);
		$buttons_output = '';

		foreach ( $buttons as $button => $label ) {
			// Button style.
			$button_style = '';
			$button_style .= 'background-color: ' . $atts['button_accent'] . ';';
			$button_style .= 'color: ' . $atts['button_background'] . ';';

			if ( 'on' !== $button ) {
				$border = is_rtl() ? 'border-right' : 'border-left';
				$button_style .= $border . ': 1px solid ' . $atts['button_accent'] . ';';
			}

			// Get button text.
			$button_text = '';
			if ( $atts[ $button . '_text'] ) {
				$button_text .= '<span class="wprm-toggle-text">' . WPRM_Shortcode_Helper::sanitize_html( $atts[ $button . '_text'] ) . '</span>';
			}

			// Get optional icon.
			$icon = '';
			if ( $atts[ $button . '_icon'] ) {
				$icon_active = WPRM_Icon::get( $atts[ $button . '_icon' ], $atts['button_background'] );
				$icon_inactive = WPRM_Icon::get( $atts[ $button . '_icon' ], $atts['button_accent'] );

				if ( $icon_active && $icon_inactive ) {
					$icons = '<span class="wprm-recipe-icon wprm-toggle-icon wprm-toggle-icon-active">' . $icon_active . '</span>';
					$icons .= '<span class="wprm-recipe-icon wprm-toggle-icon wprm-toggle-icon-inactive">' . $icon_inactive . '</span>';
					$button_text = $icons . $button_text;
				}
			}

			$active = 'on' === $button ? ' wprm-toggle-active' : ''; 
			$buttons_output .= '<button class="wprm-recipe-media-toggle wprm-toggle' . $active . '" data-state="' . esc_attr( $button ) . '" data-recipe="' . esc_attr( $recipe->id() ) . '" style="' . esc_attr( $button_style ) .'" aria-label="' . $label . '">' . $button_text . '</button>';
		}

		// Output.
		$output = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">' . $buttons_output . '</div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Media_Toggle::init();