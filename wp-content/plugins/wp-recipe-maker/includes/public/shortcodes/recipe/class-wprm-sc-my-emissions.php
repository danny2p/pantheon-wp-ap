<?php
/**
 * Handle the My Emissions label shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      7.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the My Emissions label shortcode.
 *
 * @since      7.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_My_Emissions extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-my-emissions-label';

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
		$output = '';

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->id() ) {
			return '';
		}

		// Only if integration is active.
		if ( ! WPRM_Compatibility_My_Emissions::is_active() ) {
			if ( $atts['is_template_editor_preview'] ) {
				return '<div class="wprm-template-editor-premium-only">Enable My Emissions in the settings first.</div>';
			} else {
				return '';
			}
		}

		// Actual output.
		$output = '';

		// Check if we should output for this recipe.
		$show_label_for_this_recipe = WPRM_Settings::get( 'my_emissions_show_all' );

		if ( ! $show_label_for_this_recipe ) {
			$show_label_for_this_recipe = $recipe->my_emissions();
		}

		// Output label.
		if ( $atts['is_template_editor_preview'] || $show_label_for_this_recipe ) {
			$classes = array(
				'wprm-recipe-my-emissions-container',
				'wprm-block-text-' . $atts['text_style'],
			);

			// Add custom class if set.
			if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

			$output = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';
			$output .= WPRM_Shortcode_Helper::get_section_header( $atts, 'my-emissions' );
			$output .= '<div class="wprm-recipe-my-emissions">';

			if ( $atts['is_template_editor_preview'] ) {
				$output .= '<div>The My Emissions label will display here.</div>';
			} else {
				// Make sure JS gets loaded.
				WPRM_Compatibility_My_Emissions::load();

				$output .= '<div class="myemissionslabel" data-recipe="' . esc_attr( $recipe->id() ) . '"></div>';
			}

			$output .= '</div>';
			$output .= '</div>';
		}

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_My_Emissions::init();