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
			'toggle_style' => array(
				'default' => 'buttons',
				'type' => 'dropdown',
				'options' => array(
					'buttons' => __( 'Buttons', 'wp-recipe-maker' ),
					'switch' => __( 'Switch', 'wp-recipe-maker' ),
				),
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'button_background' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'buttons',
				),
			),
			'button_accent' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'buttons',
				),
			),
			'button_radius' => array(
				'default' => '3px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'buttons',
				),
			),
			'button_background' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'buttons',
				),
			),
			'switch_style' => array(
				'default' => 'rounded',
				'type' => 'dropdown',
				'options' => array(
					'square' => 'Square Switch',
					'rounded' => 'Rounded Switch',
				),
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'switch',
				),
			),
			'switch_height' => array(
				'default' => '28px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'switch',
				),
			),
			'switch_off' => array(
				'default' => '#cccccc',
				'type' => 'color',
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'switch',
				),
			),
			'switch_off_knob' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'switch',
			
				),
			),
			'switch_off_text' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'switch',
				),
			),
			'switch_on' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'switch',
				),
			),
			'switch_on_knob' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'switch',
				),
			),
			'switch_on_text' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'toggle_style',
					'value' => 'switch',
				),
			),
			'off_icon' => array(
				'default' => 'camera-no',
				'type' => 'icon',
			),
			'off_text' => array(
				'default' => '',
				'type' => 'text',
			),
			'on_icon' => array(
				'default' => 'camera-2',
				'type' => 'icon',
			),
			'on_text' => array(
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
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
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
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		$classes = array(
			'wprm-recipe-media-toggle-container',
			'wprm-toggle-container',
			'wprm-toggle-' . $atts['toggle_style'] . '-container',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		
		// Get output for different styles.
		$style = '';
		$toggle_output = '';

		if ( 'buttons' === $atts['toggle_style'] ) {
			$buttons = self::get_buttons( $recipe, $atts );
			$style = $buttons['style'];
			$toggle_output = $buttons['output'];
		} elseif ( 'switch' === $atts['toggle_style'] ) {
			$switch = self::get_switch( $recipe, $atts );
			$style = $switch['style'];
			$toggle_output = $switch['output'];
		}

		// Output.
		$output = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">' . $toggle_output . '</div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}

	/**
	 * Get the buttons for the toggle.
	 *
	 * @since	10.0.0
	 * @param	object $recipe Recipe to get the toggle for.
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function get_buttons( $recipe, $atts ) {
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

		return array(
			'style' => $style,
			'output' => $buttons_output,
		);
	}

	/**
	 * Get the switch style for the toggle.
	 *
	 * @since	10.0.0
	 * @param	object $recipe Recipe to get the toggle for.
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function get_switch( $recipe, $atts ) {
		$style = '';
		$output = '';

		$uid = wp_rand();

		// Arguments for toggle.
		$toggle_switch_args = array(
			'uid' => $uid,
			'class' => 'wprm-media-toggle-checkbox',
			'checked' => 'on' === WPRM_Settings::get( 'instruction_media_toggle_default' ),
			'type' => 'inside',
			'aria_label' => __( 'Toggle instruction images', 'wp-recipe-maker' ),
		);

		$output .= WPRM_Shortcode_Helper::get_toggle_switch( $atts, $toggle_switch_args );

		return array(
			'style' => $style,
			'output' => $output,
		);
	}
}

WPRM_SC_Media_Toggle::init();