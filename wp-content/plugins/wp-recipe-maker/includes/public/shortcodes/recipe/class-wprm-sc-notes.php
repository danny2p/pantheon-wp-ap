<?php
/**
 * Handle the recipe notes shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe notes shortcode.
 *
 * @since      3.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Notes extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-notes';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
		);

		$atts = array_merge( WPRM_Shortcode_Helper::get_section_atts(), $atts );
		self::$attributes = $atts;

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
		if ( ! $recipe || ! $recipe->notes() ) {
			return '';
		}

		// Output.
		$classes = array(
			'wprm-recipe-notes-container',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$output = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';
		$output .= WPRM_Shortcode_Helper::get_section_header( $atts, 'notes' );

		$notes = parent::clean_paragraphs( $recipe->notes() );

		$output .= '<div class="wprm-recipe-notes">';
		$output .= do_shortcode( $notes );
		$output .= '</div>';
		$output .= '</div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Notes::init();