<?php
/**
 * Handle the recipe date shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      7.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe date shortcode.
 *
 * @since      7.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Date extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-date';

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
			'tag' => array(
				'default' => 'span',
				'type' => 'dropdown',
				'options' => 'header_tags',
			),
			'date_format' => array(
				'default' => 'F j, Y',
				'type' => 'text',
				'help' => __( 'Use the PHP date format. Leave empty to use default WordPress date format from the Settings > General page.', 'wp-recipe-maker' ),
			),
		);
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
		if ( ! $recipe ) {
			return '';
		}

		// Output.
		$classes = array(
			'wprm-recipe-date',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$tag = sanitize_key( $atts['tag'] );
		
		// Date format.
		$format = $atts['date_format'];
		if ( ! $format ) {
			$format = get_option( 'date_format' );
		}
		$date = date_i18n( $format, strtotime( $recipe->date() ) );

		$output = '<' . $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $date . '</' . $tag . '>';
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Date::init();