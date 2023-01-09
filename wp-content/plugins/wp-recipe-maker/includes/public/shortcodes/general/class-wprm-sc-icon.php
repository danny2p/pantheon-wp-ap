<?php
/**
 * Handle the icon shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 */

/**
 * Handle the icon shortcode.
 *
 * @since      6.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Icon extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-icon';

	public static function init() {
		self::$attributes = array(
			'icon' => array(
				'default' => '',
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
			'icon_size' => array(
				'default' => '16px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'icon',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'style' => array(
				'default' => 'separate',
				'type' => 'dropdown',
				'options' => array(
					'inline' => 'Inline',
					'separate' => 'On its own line',
				),
				'dependency' => array(
					'id' => 'icon',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'align' => array(
				'default' => 'center',
				'type' => 'dropdown',
				'options' => array(
					'left' => 'Left',
					'center' => 'Center',
					'right' => 'Right',
				),
				'dependency' => array(
                    array(
                        'id' => 'icon',
                        'value' => '',
                        'type' => 'inverse',
					),
					array(
                        'id' => 'style',
                        'value' => 'separate',
                    ),
				),
			),
			'decoration' => array(
				'default' => 'line',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'None',
					'line' => 'Line',
				),
				'dependency' => array(
                    array(
                        'id' => 'icon',
                        'value' => '',
                        'type' => 'inverse',
					),
					array(
                        'id' => 'style',
                        'value' => 'separate',
                    ),
				),
			),
			'line_color' => array(
				'default' => '#9B9B9B',
				'type' => 'color',
				'dependency' => array(
                    array(
                        'id' => 'icon',
                        'value' => '',
                        'type' => 'inverse',
					),
					array(
                        'id' => 'style',
                        'value' => 'separate',
					),
					array(
                        'id' => 'decoration',
                        'value' => 'line',
                    ),
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

		$icon = '';
		if ( $atts['icon'] ) {
			$icon = WPRM_Icon::get( $atts['icon'], $atts['icon_color'] );

			if ( $icon ) {
				$icon = '<span class="wprm-recipe-icon" aria-hidden="true">' . $icon . '</span> ';
			}
		}

		if ( ! $icon ) {
			return '';
		}

		// Output.
		$classes = array(
			'wprm-icon-shortcode',
			'wprm-icon-shortcode-' . esc_attr( $atts['style'] ),
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }
		
		$before_icon = '';
		$after_icon = '';

		$style = '';
		if ( '16px' !== $atts['icon_size'] ) {
			$style .= 'font-size: ' . esc_attr( $atts['icon_size'] ) . ';';
			$style .= 'height: ' . esc_attr( $atts['icon_size'] ) . ';';
		}

		if ( 'separate' === $atts['style'] ) {
			$classes[] = 'wprm-align-' . esc_attr( $atts['align'] );
			$classes[] = 'wprm-icon-decoration-' . esc_attr( $atts['decoration'] );

			if ( 'line' === $atts['decoration'] ) {
				if ( 'left' === $atts['align'] || 'center' === $atts['align'] ) {
					$after_icon = '<div class="wprm-decoration-line" style="border-color: ' . esc_attr( $atts['line_color'] ) . '"></div>';
				}
				if ( 'right' === $atts['align'] || 'center' === $atts['align'] ) {
					$before_icon = '<div class="wprm-decoration-line" style="border-color: ' . esc_attr( $atts['line_color'] ) . '"></div>';
				}
			}
		}

		$output = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) .'">' . $before_icon . $icon . $after_icon . '</div>';
		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Icon::init();