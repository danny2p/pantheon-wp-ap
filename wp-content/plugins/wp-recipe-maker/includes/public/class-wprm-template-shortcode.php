<?php
/**
 * Parent class for the template shortcodes.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Parent class for the template shortcodes.
 *
 * @since      4.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Template_Shortcode {
	public static $attributes = array();
	public static $shortcode = '';

	public static function init() {
		$shortcode = static::$shortcode;

		if ( $shortcode ) {
			// Register shortcode in WordPress.
			add_shortcode( $shortcode, array( get_called_class(), 'shortcode' ) );

			// Add to list of all shortcodes if not deprecated.
			WPRM_Template_Shortcodes::$shortcodes[ $shortcode ] = static::$attributes;
		}
	}
	
	public static function init_deprecated() {
		$shortcode = static::$shortcode;

		if ( $shortcode ) {
			// Only register in WordPress.
			add_shortcode( $shortcode, array( get_called_class(), 'shortcode' ) );
		}
	}

	public static function clean_paragraphs( $text ) {

		// Remove blank lines.
		$text = str_ireplace( '<p></p>', '', $text );
		$text = str_ireplace( '<p><br></p>', '', $text );
		$text = str_ireplace( '<p><br/></p>', '', $text );

		// Replace last occurence of </p> by </span>. Use span and not div to prevent layouts from breaking.
		$pos = strripos( $text, '</p>' );
		if( false !== $pos ) {
			$text = substr_replace( $text, '</span>', $pos, 4 );
		}

		// Replace <p> by <span> while keeping any attributes (text-align!).
		$text = preg_replace_callback(
			'/<p(\s[^>]*>|>)/mi',
			function( $match ) {
				$atts = $match[1];

				// Use block style.
				if ( false !== stripos( $atts, ' style="' ) ) {
					$atts = str_ireplace( ' style="', ' style="display: block;', $atts );
				} else {
					$atts = str_ireplace( '>', ' style="display: block;">', $atts );
				}

				return '<span' . $atts;
			},
			$text
		);

		// Replace remaining </p> with spacer.
		$text = str_ireplace( '</p>', '</span>[wprm-spacer]', $text );

		return trim( do_shortcode( $text ) );
	}

	public static function get_hook() {
		// Load assets when a recipe shortcode is used.
		WPRM_Assets::load();
		
		return str_replace( '-', '_', static::$shortcode ) . '_shortcode';
	}

	protected static function get_attributes( $atts ) {
		// Attributes available for this shortcode.
		$defaults = WPRM_Template_Shortcodes::get_defaults( static::$shortcode );

		// Attributes available for all shortcodes.
		$defaults['class'] = '';

		// Allow filtering of attributes.
		$atts = shortcode_atts( $defaults, $atts, str_replace( '-', '_', static::$shortcode ) );

		// Technical attributes that cannot be overwritten.
		$atts['is_template_editor_preview'] = isset( $GLOBALS['wp']->query_vars['rest_route'] ) && '/wp-recipe-maker/v1/template/preview' === $GLOBALS['wp']->query_vars['rest_route'];

		return $atts;
	}
}
