<?php
/**
 * Handle the text shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 */

/**
 * Handle the text shortcode.
 *
 * @since      4.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Text extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-text';

	public static function init() {
		$atts = array(
			'text' => array(
				'default' => '',
				'type' => 'text',
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'tag' => array(
				'default' => 'p',
				'type' => 'dropdown',
				'options' => array(
					'p' => 'p',
					'span' => 'span',
					'div' => 'div',
					'h1' => 'h1',
					'h2' => 'h2',
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5',
					'h6' => 'h6',
				),
			),
			'align' => array(
				'default' => 'left',
				'type' => 'dropdown',
				'options' => array(
					'left' => 'Left',
					'center' => 'Center',
					'right' => 'Right',
				),
				'dependency' => array(
                    array(
                        'id' => 'tag',
                        'value' => 'span',
                        'type' => 'inverse',
					),
				),
			),
		);

		$atts = array_merge( WPRM_Shortcode_Helper::get_section_atts(), $atts );
		self::$attributes = $atts;

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

		$text = $atts['text'];
		if ( ! $text ) {
			return '';
		}

		// Output.
		$classes = array(
			'wprm-text',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$output = '';
		$tag = sanitize_key( $atts['tag'] );

		// Alignment.
		if ( 'span' !== $tag && 'left' !== $atts['align'] ) {
			$classes[] = 'wprm-align-' . esc_attr( $atts['align'] );
		}

		// If inside of a recipe card, replace placeholders.
		$recipe = WPRM_Template_Shortcodes::get_recipe( 0 );

		if ( $recipe ) {
			$text = $recipe->replace_placeholders( $text );
		}

		// Optional header.
		$header = WPRM_Shortcode_Helper::get_section_header( $atts, 'text' );

		if ( $header ) {
			$output .= '<div class="wprm-text-container">';
			$output .= $header;
		}

		$output .= '<' . $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '">' . WPRM_Shortcode_Helper::sanitize_html( $text ) . '</' . $tag . '>';

		if ( $header ) {
			$output .= '</div>';
		}

		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Text::init();