<?php
/**
 * Handle the image shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      7.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 */

/**
 * Handle the image shortcode.
 *
 * @since      7.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Regular_Image extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-image';

	public static function init() {
		self::$attributes = array(
			'image_id' => array(
				'default' => '0',
				'type' => 'image',
			),
			'style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => array(
					'normal' => 'Normal',
					'rounded' => 'Rounded',
					'circle' => 'Circle',
				),
			),
			'rounded_radius' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'style',
					'value' => 'rounded',
				),
			),
			'size' => array(
				'default' => 'medium',
				'type' => 'image_size'
			),
			'border_width' => array(
				'default' => '0px',
				'type' => 'size',
			),
			'border_style' => array(
				'default' => 'solid',
				'type' => 'dropdown',
				'options' => 'border_styles',
			),
			'border_color' => array(
				'default' => '#666666',
				'type' => 'color',
			),
			'align' => array(
				'default' => 'left',
				'type' => 'dropdown',
				'options' => array(
					'left' => 'Left',
					'center' => 'Center',
					'right' => 'Right',
				),
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	7.3.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$image_id = intval( $atts['image_id'] );
		if ( ! $image_id ) {
			return '';
		}

		$size = $atts['size'];

		// Check if size should be handled as array.
		preg_match( '/^(\d+)x(\d+)$/i', $size, $match );
		if ( ! empty( $match ) ) {
			$size = array( intval( $match[1] ), intval( $match[2] ) );
		}

		// Get image.
		$img = wp_get_attachment_image( $image_id, $size );

		if ( ! $img ) {
			return '';
		}

		// Output.
		$classes = array(
			'wprm-image',
			'wprm-block-image-' . esc_attr( $atts['style'] ),
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		// Align.
		if ( 'left' !== $atts['align'] ) {
			$classes[] = 'wprm-align-' . esc_attr( $atts['align'] );
		}

		// Image Style.
		$style = '';
		$style .= 'border-width: ' . esc_attr( $atts['border_width'] ) . ';';
		$style .= 'border-style: ' . esc_attr( $atts['border_style'] ) . ';';
		$style .= 'border-color: ' . esc_attr( $atts['border_color'] ) . ';';

		if ( 'rounded' === $atts['style'] ) {
			$style .= 'border-radius: ' . esc_attr( $atts['rounded_radius'] ) . ';';
		}

		if ( $style ) {
			if ( false !== stripos( $img, ' style="' ) ) {
				$img = str_ireplace( ' style="', ' style="' . esc_attr( $style ), $img );
			} else {
				$img = str_ireplace( '<img ', '<img style="' . esc_attr( $style ) . '" ', $img );
			}
		}

		// Prevent lazy image loading on print page.
		if ( 'print' === WPRM_Context::get() ) {
			$img = str_ireplace( ' class="', ' class="skip-lazy disable-lazyload ', $img );
		}

		$output = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $img . '</div>';
		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Regular_Image::init();