<?php
/**
 * Handle the recipe counter shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.9.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe counter shortcode.
 *
 * @since      6.9.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Counter extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-counter';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'text' => array(
				'help' => 'Potential placeholders: %count%, %recipe_name%',
				'default' => '%count%. %recipe_name%',
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
	 * @since	6.9.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		$text = $atts['text'];
		if ( ! $recipe || ! $text ) {
			return '';
		}

		// Output.
		$classes = array(
			'wprm-recipe-counter',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		// Global count.
		$count = isset( $GLOBALS['wprm_recipe_counter'] ) ? $GLOBALS['wprm_recipe_counter'] + 1 : 1;
		$GLOBALS['wprm_recipe_counter'] = $count;
		
		$text = str_ireplace( '%count%', $count, $text );
		$text = $recipe->replace_placeholders( $text );

		if ( $atts['link'] && $recipe->permalink() ) {
			$target = $recipe->parent_url_new_tab() ? ' target="_blank"' : '';
			$nofollow = $recipe->parent_url_nofollow() ? ' rel="nofollow"' : '';

			$text = '<a href="' . esc_url( $recipe->permalink() ) . '"' . $target . $nofollow . '>' . WPRM_Shortcode_Helper::sanitize_html( $text ) . '</a>';
		}

		$tag = sanitize_key( $atts['tag'] );
		$output = '<' . $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $text . '</' . $tag . '>';
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Counter::init();