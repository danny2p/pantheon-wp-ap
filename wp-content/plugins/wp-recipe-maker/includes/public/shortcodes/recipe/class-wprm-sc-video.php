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

		$output = '<div id="wprm-recipe-video-container-' . $recipe->id() . '" class="wprm-recipe-video-container">';
		$output .= WPRM_Shortcode_Helper::get_section_header( $atts, 'video' );

		$output .= '<div class="wprm-recipe-video">' . do_shortcode( $recipe->video() ) . '</div>';
		$output .= '</div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Video::init();