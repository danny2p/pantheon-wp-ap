<?php
/**
 * Handle the recipe video shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe video shortcode.
 *
 * @since      3.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Video extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-video';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
		);

		$atts = array_merge( WPRM_Shortcode_Helper::get_section_atts(), $atts );
		unset( $atts['text_style'] );
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
		if ( ! $recipe || ! $recipe->video() ) {
			return '';
		}

		// Output.
		$classes = array(
			'wprm-recipe-video-container',
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$output = '<div id="wprm-recipe-video-container-' . esc_attr( $recipe->id() ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '">';
		$output .= WPRM_Shortcode_Helper::get_section_header( $atts, 'video' );

		$output .= '<div class="wprm-recipe-video">' . do_shortcode( $recipe->video() ) . '</div>';
		$output .= '</div>';

		// Check if force Mediavine video is enabled.
		if ( WPRM_Settings::get( 'metadata_force_mediavine_video_output' ) ) {
			$output = str_ireplace( ' data-disable-jsonld="true"', '', $output );
		}

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Video::init();