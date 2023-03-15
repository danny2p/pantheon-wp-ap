<?php
/**
 * Handle the recipe URL shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      8.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe URL shortcode.
 *
 * @since      8.5.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Url extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-url';

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
				'options' => array(
					'span' => 'span',
					'div' => 'div',
				),
			),
			'show_protocol' => array(
				'default' => '1',
				'type' => 'toggle',
			),
			'show_path' => array(
				'default' => '1',
				'type' => 'toggle',
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
		$url = $recipe->permalink();
		if ( ! $recipe || ! $url ) {
			return '';
		}

		// Output.
		$classes = array(
			'wprm-recipe-url',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$tag = sanitize_key( $atts['tag'] );

		// Text to show.
		$text = $url;
		$url_parts = parse_url( $url );

		if ( $url_parts ) {
			$url_parts_text = '';

			if ( ( bool ) $atts['show_protocol'] ) {
				if ( isset( $url_parts['scheme'] ) && $url_parts['scheme'] ) {
					$url_parts_text .= $url_parts['scheme'] . '://';
				}
			}

			if ( isset( $url_parts['host'] ) && $url_parts['host'] ) {
				$url_parts_text .= $url_parts['host'];
			}

			if ( ( bool ) $atts['show_path'] ) {
				if ( isset( $url_parts['path'] ) && $url_parts['path'] ) {
					$url_parts_text .= $url_parts['path'];
				}
			}

			if ( $url_parts_text ) {
				$text = $url_parts_text;
			}
		}

		if ( $atts['link'] ) {
			$target = $recipe->parent_url_new_tab() ? ' target="_blank"' : '';
			$nofollow = $recipe->parent_url_nofollow() ? ' rel="nofollow"' : '';

			$text = '<a href="' . esc_url( $url ) . '"' . $target . $nofollow . '>' . WPRM_Shortcode_Helper::sanitize_html( $text ) . '</a>';
		}

		$output = '<' . $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $text . '</' . $tag . '>';
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Url::init();