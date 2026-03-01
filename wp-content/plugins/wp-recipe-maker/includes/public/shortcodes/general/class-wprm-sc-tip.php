<?php
/**
 * Handle the tip shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.3.2
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 */

/**
 * Handle the tip shortcode.
 *
 * @since      10.3.2
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Tip extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-tip';
	private static $context_defaults_stack = array();

	public static function init() {
		self::$attributes = array(
			'text' => array(
				'default' => '',
				'type' => 'text',
			),
			'style' => array(
				'default' => WPRM_Tip::DEFAULT_STYLE,
				'type' => 'dropdown',
				'options' => array(
					'left-border-straight' => __( 'Left Border Straight', 'wp-recipe-maker' ),
					'left-border-rounded' => __( 'Left Border Rounded', 'wp-recipe-maker' ),
					'filled' => __( 'Filled', 'wp-recipe-maker' ),
					'outline' => __( 'Outline', 'wp-recipe-maker' ),
					'banner' => __( 'Banner', 'wp-recipe-maker' ),
				),
			),
			'icon' => array(
				'default' => WPRM_Tip::DEFAULT_ICON,
				'type' => 'icon',
			),
			'accent' => array(
				'default' => WPRM_Tip::DEFAULT_ACCENT,
				'type' => 'color',
			),
			'text_color' => array(
				'default' => WPRM_Tip::DEFAULT_TEXT_COLOR,
				'type' => 'color',
			),
		);
		parent::init();
	}

	/**
	 * Push context defaults for tip shortcodes rendered in nested content.
	 *
	 * @since    10.3.2
	 * @param    array $defaults Context defaults.
	 */
	public static function push_context_defaults( $defaults ) {
		$defaults = is_array( $defaults ) ? $defaults : array();
		self::$context_defaults_stack[] = $defaults;
	}

	/**
	 * Pop context defaults for tip shortcodes rendered in nested content.
	 *
	 * @since    10.3.2
	 */
	public static function pop_context_defaults() {
		if ( self::$context_defaults_stack ) {
			array_pop( self::$context_defaults_stack );
		}
	}

	/**
	 * Get currently active context defaults.
	 *
	 * @since    10.3.2
	 */
	private static function get_context_defaults() {
		if ( ! self::$context_defaults_stack ) {
			return false;
		}

		return self::$context_defaults_stack[ count( self::$context_defaults_stack ) - 1 ];
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since    10.3.2
	 * @param    array $atts    Options passed along with the shortcode.
	 * @param    mixed $content Content in between the shortcodes.
	 */
	public static function shortcode( $atts, $content = '' ) {
		$raw_atts = is_array( $atts ) ? $atts : array();
		$atts = parent::get_attributes( $atts );

		$context_defaults = self::get_context_defaults();
		if ( $context_defaults ) {
			$context_keys = array(
				'style',
				'icon',
				'accent',
				'text_color',
			);

			foreach ( $context_keys as $context_key ) {
				if ( array_key_exists( $context_key, $raw_atts ) ) {
					continue;
				}

				if ( isset( $context_defaults[ $context_key ] ) ) {
					$atts[ $context_key ] = $context_defaults[ $context_key ];
				}
			}
		}

		$text = '';
		if ( null !== $content && '' !== $content ) {
			$text = $content;
		} elseif ( $atts['text'] ) {
			$text = $atts['text'];
		}

		$text = parent::clean_paragraphs( $text );

		if ( ! $text ) {
			return apply_filters( parent::get_hook(), '', $atts, $content );
		}

		$classes = array(
			'wprm-recipe-tip-shortcode',
		);

		// Add custom class if set.
		if ( $atts['class'] ) {
			$classes[] = $atts['class'];
		}

		$output = WPRM_Tip::render(
			$text,
			array(
				'wrapper' => 'div',
				'classes' => $classes,
				'style' => $atts['style'],
				'style_default' => WPRM_Tip::DEFAULT_STYLE,
				'style_class_prefixes' => array(
					'wprm-recipe-tip-style-',
					'wprm-recipe-instruction-tip-style-',
				),
				'accent' => $atts['accent'],
				'accent_default' => WPRM_Tip::DEFAULT_ACCENT,
				'text_color' => $atts['text_color'],
				'text_color_default' => WPRM_Tip::DEFAULT_TEXT_COLOR,
				'icon' => $atts['icon'],
				'icon_default' => WPRM_Tip::DEFAULT_ICON,
				'icon_default_defined' => isset( $atts['icon'] ),
				'icon_classes' => array(
					'wprm-recipe-icon',
					'wprm-recipe-tip-icon',
					'wprm-recipe-instruction-tip-icon',
				),
				'text_classes' => array(
					'wprm-recipe-tip-text',
					'wprm-recipe-instruction-tip-text',
				),
			)
		);

		return apply_filters( parent::get_hook(), $output, $atts, $content );
	}
}

WPRM_SC_Tip::init();
