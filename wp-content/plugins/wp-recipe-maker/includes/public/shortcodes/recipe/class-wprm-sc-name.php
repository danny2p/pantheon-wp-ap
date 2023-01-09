<?php
/**
 * Handle the recipe name shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe name shortcode.
 *
 * @since      3.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Name extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-name';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'text_style' => array(
				'default' => 'bold',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'tag' => array(
				'default' => 'span',
				'type' => 'dropdown',
				'options' => 'header_tags',
			),
			'link' => array(
				'default' => '0',
				'type' => 'toggle',
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
			'wprm-recipe-name',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$tag = sanitize_key( $atts['tag'] );
		$name = $recipe->name();

		if ( $atts['link'] && $recipe->permalink() ) {
			$target = $recipe->parent_url_new_tab() ? ' target="_blank"' : '';
			$nofollow = $recipe->parent_url_nofollow() ? ' rel="nofollow"' : '';

			$name = '<a href="' . esc_url( $recipe->permalink() ) . '"' . $target . $nofollow . '>' . $name . '</a>';
		}

		$output = '<' . $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $name . '</' . $tag . '>';
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Name::init();