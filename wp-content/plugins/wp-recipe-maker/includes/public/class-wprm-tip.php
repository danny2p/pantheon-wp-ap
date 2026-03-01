<?php
/**
 * Shared tip helper.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.3.2
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Shared tip helper.
 *
 * @since      10.3.2
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tip {
	const DEFAULT_ICON = 'lightbulb';
	const DEFAULT_ACCENT = '#2b6cb0';
	const DEFAULT_TEXT_COLOR = '#000000';
	const DEFAULT_STYLE = 'left-border-straight';
	const NO_ICON = '__none__';

	/**
	 * Cached valid icon IDs.
	 *
	 * @since    10.3.2
	 * @var      mixed
	 */
	private static $icon_ids = false;

	/**
	 * Get valid tip styles.
	 *
	 * @since    10.3.2
	 */
	public static function get_styles() {
		return array(
			'left-border-straight',
			'left-border-rounded',
			'filled',
			'outline',
			'banner',
		);
	}

	/**
	 * Normalize supported tip style values.
	 *
	 * @since    10.3.2
	 * @param    mixed $style Tip style.
	 */
	public static function normalize_style( $style ) {
		$style = sanitize_key( trim( (string) $style ) );

		// Legacy value used before split styles were introduced.
		if ( 'left-border' === $style ) {
			$style = 'left-border-straight';
		}

		return in_array( $style, self::get_styles(), true ) ? $style : false;
	}

	/**
	 * Check if style is valid.
	 *
	 * @since    10.3.2
	 * @param    mixed $style Tip style.
	 */
	public static function is_valid_style( $style ) {
		return false !== self::normalize_style( $style );
	}

	/**
	 * Check if a tip icon value explicitly indicates no icon.
	 *
	 * @since    10.3.2
	 * @param    mixed $icon Tip icon value.
	 */
	public static function is_no_icon( $icon ) {
		return self::NO_ICON === strtolower( trim( (string) $icon ) );
	}

	/**
	 * Resolve tip style with fallback.
	 *
	 * @since    10.3.2
	 * @param    mixed $style        Raw style.
	 * @param    mixed $default_style Raw default style.
	 */
	public static function resolve_style( $style, $default_style = self::DEFAULT_STYLE ) {
		$resolved_style = self::normalize_style( $style );
		if ( ! $resolved_style ) {
			$resolved_style = self::normalize_style( $default_style );
		}
		if ( ! $resolved_style ) {
			$resolved_style = self::DEFAULT_STYLE;
		}

		return $resolved_style;
	}

	/**
	 * Validate and sanitize tip accent color value.
	 * Allows hex colors and CSS variables (var(--variable-name)).
	 *
	 * @since    10.3.2
	 * @param    mixed $accent Accent color value.
	 */
	public static function sanitize_accent( $accent ) {
		if ( ! $accent || ! is_string( $accent ) ) {
			return false;
		}

		$accent = trim( $accent );
		if ( ! $accent ) {
			return false;
		}

		$hex_color = sanitize_hex_color( $accent );
		if ( $hex_color ) {
			return $hex_color;
		}

		// Allow CSS custom property usage, e.g. var(--glacier-accent-color).
		if ( preg_match( '/^var\(\s*--[a-z0-9_-]+\s*\)$/i', $accent ) ) {
			return $accent;
		}

		return false;
	}

	/**
	 * Validate and sanitize tip text color value.
	 * Allows hex colors and CSS variables (var(--variable-name)).
	 *
	 * @since    10.3.2
	 * @param    mixed $text_color Text color value.
	 */
	public static function sanitize_text_color( $text_color ) {
		if ( ! $text_color || ! is_string( $text_color ) ) {
			return false;
		}

		$text_color = trim( $text_color );
		if ( ! $text_color ) {
			return false;
		}

		$hex_color = sanitize_hex_color( $text_color );
		if ( $hex_color ) {
			return $hex_color;
		}

		// Allow CSS custom property usage, e.g. var(--glacier-text-color).
		if ( preg_match( '/^var\(\s*--[a-z0-9_-]+\s*\)$/i', $text_color ) ) {
			return $text_color;
		}

		return false;
	}

	/**
	 * Resolve tip accent with fallback.
	 *
	 * @since    10.3.2
	 * @param    mixed $accent         Raw accent.
	 * @param    mixed $default_accent Raw default accent.
	 */
	public static function resolve_accent( $accent, $default_accent = self::DEFAULT_ACCENT ) {
		$resolved_accent = self::sanitize_accent( $accent );
		if ( ! $resolved_accent ) {
			$resolved_accent = self::sanitize_accent( $default_accent );
		}
		if ( ! $resolved_accent ) {
			$resolved_accent = self::DEFAULT_ACCENT;
		}

		return $resolved_accent;
	}

	/**
	 * Resolve tip text color with fallback.
	 *
	 * @since    10.3.2
	 * @param    mixed $text_color         Raw text color.
	 * @param    mixed $default_text_color Raw default text color.
	 */
	public static function resolve_text_color( $text_color, $default_text_color = self::DEFAULT_TEXT_COLOR ) {
		$resolved_text_color = self::sanitize_text_color( $text_color );
		if ( ! $resolved_text_color ) {
			$resolved_text_color = self::sanitize_text_color( $default_text_color );
		}
		if ( ! $resolved_text_color ) {
			$resolved_text_color = self::DEFAULT_TEXT_COLOR;
		}

		return $resolved_text_color;
	}

	/**
	 * Validate and sanitize tip icon ID or URL.
	 *
	 * @since    10.3.2
	 * @param    mixed $icon Icon ID or URL.
	 */
	public static function sanitize_icon( $icon ) {
		if ( ! $icon || ! is_string( $icon ) ) {
			return false;
		}

		$icon = trim( $icon );
		if ( ! $icon || self::is_no_icon( $icon ) ) {
			return false;
		}

		// Allow custom URLs.
		if ( filter_var( $icon, FILTER_VALIDATE_URL ) ) {
			return $icon;
		}

		$icon_id = sanitize_key( $icon );

		if ( false === self::$icon_ids ) {
			self::$icon_ids = array_keys( WPRM_Icon::get_all() );
		}

		if ( in_array( $icon_id, self::$icon_ids, true ) ) {
			return $icon_id;
		}

		return false;
	}

	/**
	 * Resolve tip icon with fallback and explicit no-icon support.
	 *
	 * @since    10.3.2
	 * @param    mixed $icon                 Raw icon value.
	 * @param    mixed $default_icon         Raw default icon value.
	 * @param    mixed $default_icon_defined Whether default icon was explicitly defined.
	 */
	public static function resolve_icon( $icon, $default_icon = self::DEFAULT_ICON, $default_icon_defined = true ) {
		$icon = trim( (string) $icon );
		$default_icon = trim( (string) $default_icon );
		$default_icon_defined = (bool) $default_icon_defined;

		// Explicit per-tip icon disable always wins.
		if ( self::is_no_icon( $icon ) ) {
			return false;
		}

		$resolved_icon = self::sanitize_icon( $icon );

		if ( ! $resolved_icon && $default_icon ) {
			$resolved_icon = self::sanitize_icon( $default_icon );
		}

		// Explicitly empty default means "no icon".
		if ( ! $resolved_icon && $default_icon_defined && ( '' === $default_icon || self::is_no_icon( $default_icon ) ) ) {
			return false;
		}

		// Missing or invalid non-empty defaults fall back to lightbulb.
		if ( ! $resolved_icon ) {
			$resolved_icon = self::sanitize_icon( self::DEFAULT_ICON );
		}

		return $resolved_icon;
	}

	/**
	 * Render tip markup.
	 *
	 * @since    10.3.2
	 * @param    mixed $text    Tip text content.
	 * @param    mixed $options Rendering options.
	 */
	public static function render( $text, $options = array() ) {
		$options = wp_parse_args(
			$options,
			array(
				'id' => '',
				'wrapper' => 'div',
				'classes' => array(),
				'css' => '',
				'style' => '',
				'style_default' => self::DEFAULT_STYLE,
				'style_class_prefixes' => array( 'wprm-recipe-tip-style-' ),
				'accent' => '',
				'accent_default' => self::DEFAULT_ACCENT,
				'text_color' => '',
				'text_color_default' => self::DEFAULT_TEXT_COLOR,
				'icon' => '',
				'icon_default' => self::DEFAULT_ICON,
				'icon_default_defined' => true,
				'icon_classes' => array( 'wprm-recipe-icon', 'wprm-recipe-tip-icon' ),
				'text_classes' => array( 'wprm-recipe-tip-text' ),
			)
		);

		$style = self::resolve_style( $options['style'], $options['style_default'] );
		$accent = self::resolve_accent( $options['accent'], $options['accent_default'] );
		$text_color = self::resolve_text_color( $options['text_color'], $options['text_color_default'] );
		$icon = self::resolve_icon( $options['icon'], $options['icon_default'], $options['icon_default_defined'] );

		$classes = array();
		$classes_raw = $options['classes'];
		if ( ! is_array( $classes_raw ) ) {
			$classes_raw = array( $classes_raw );
		}
		foreach ( $classes_raw as $class_raw ) {
			$class_raw = trim( (string) $class_raw );
			if ( ! $class_raw ) {
				continue;
			}

			$class_names = preg_split( '/\s+/', $class_raw );
			foreach ( $class_names as $class_name ) {
				if ( $class_name ) {
					$classes[] = sanitize_html_class( $class_name );
				}
			}
		}
		$classes[] = 'wprm-recipe-tip';

		$style_class_prefixes = is_array( $options['style_class_prefixes'] ) ? $options['style_class_prefixes'] : array();
		foreach ( $style_class_prefixes as $prefix ) {
			if ( $prefix ) {
				$classes[] = $prefix . $style;
			}
		}

		$classes = array_filter( array_unique( $classes ) );

		$css = '--wprm-tip-accent: ' . $accent . ';';
		$css .= '--wprm-tip-text-color: ' . $text_color . ';';
		if ( $options['css'] ) {
			$css .= (string) $options['css'];
		}
		$style_attr = WPRM_Shortcode_Helper::get_inline_style( $css );

		$id = '';
		if ( $options['id'] ) {
			$id = ' id="' . esc_attr( $options['id'] ) . '"';
		}

		$wrapper = WPRM_Shortcode_Helper::sanitize_html_element( $options['wrapper'] );
		$output = '<' . $wrapper . $id . ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $style_attr . '>';

		if ( $icon ) {
			$icon_classes = is_array( $options['icon_classes'] ) ? $options['icon_classes'] : array( 'wprm-recipe-icon', 'wprm-recipe-tip-icon' );
			$icon_classes = array_map( 'sanitize_html_class', array_filter( array_unique( $icon_classes ) ) );
			$output .= '<span class="' . esc_attr( implode( ' ', $icon_classes ) ) . '" aria-hidden="true">' . WPRM_Icon::get( $icon, $accent ) . '</span>';
		}

		if ( $text ) {
			$text_classes = is_array( $options['text_classes'] ) ? $options['text_classes'] : array( 'wprm-recipe-tip-text' );
			$text_classes = array_map( 'sanitize_html_class', array_filter( array_unique( $text_classes ) ) );
			$output .= '<div class="' . esc_attr( implode( ' ', $text_classes ) ) . '">' . $text . '</div>';
		}

		$output .= '</' . $wrapper . '>';

		return $output;
	}
}
