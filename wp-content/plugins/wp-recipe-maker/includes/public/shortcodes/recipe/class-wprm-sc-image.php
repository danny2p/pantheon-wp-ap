<?php
/**
 * Handle the recipe image shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe image shortcode.
 *
 * @since      3.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Image extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-image';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'default' => array(
				'default' => '',
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
				'default' => '',
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

		// Use explicit size if set.
		$size = $atts['size'];

		// If no explicit size set, use value set in settings or default one.
		if ( ! $size ) {
			$settings_size = 'legacy' === WPRM_Settings::get( 'recipe_template_mode' ) ? WPRM_Settings::get( 'template_recipe_image' ) : false;
			$size = $settings_size ? $settings_size : $atts['default'];
		}

		// Check if size should be handled as array.
		preg_match( '/^(\d+)x(\d+)$/i', $size, $match );
		if ( ! empty( $match ) ) {
			$size = array( intval( $match[1] ), intval( $match[2] ) );
		}

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->image_id() ) {
			return '';
		}
		
		// Output.
		$classes = array(
			'wprm-recipe-image',
			'wprm-block-image-' . $atts['style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		
		$img = $recipe->image( $size );

		// Image Style.
		$style = '';
		$style .= 'border-width: ' . $atts['border_width'] . ';';
		$style .= 'border-style: ' . $atts['border_style'] . ';';
		$style .= 'border-color: ' . $atts['border_color'] . ';';

		if ( 'rounded' === $atts['style'] ) {
			$style .= 'border-radius: ' . $atts['rounded_radius'] . ';';
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

		// Link image.
		if ( $atts['link'] && $recipe->permalink() ) {
			$url = $recipe->permalink();

			$target = $recipe->parent_url_new_tab() ? ' target="_blank"' : '';
			$nofollow = $recipe->parent_url_nofollow() ? ' rel="nofollow"' : '';

			if ( false !== stripos( $img, ' href="' ) ) {
				$img = preg_replace( '/\shref=\"[^\"]*"/', ' href="' . esc_url( $url ) . '"' . $target . $nofollow, $img );
			} else {
				$img = '<a href="' . esc_url( $url ) . '"' . $target . $nofollow . '>' . $img . '</a>';
			}
		}

		$output = '<div class="' . esc_attr( implode( ' ', $classes ) ). '">' . $img . '</div>';
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Image::init();