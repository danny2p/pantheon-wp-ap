<?php
/**
 * Handle the recipe author shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe author shortcode.
 *
 * @since      3.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Author extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-author';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'author_image' => array(
				'default' => '0',
				'type' => 'toggle',
			),
			'image_style' => array(
				'default' => 'circle',
				'type' => 'dropdown',
				'options' => array(
					'normal' => 'Normal',
					'rounded' => 'Rounded',
					'circle' => 'Circle',
				),
				'dependency' => array(
					'id' => 'author_image',
					'value' => '1',
				),
			),
			'rounded_radius' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'author_image',
						'value' => '1',
					),
					array(
						'id' => 'image_style',
						'value' => 'rounded',
					),
				),
			),
			'image_size' => array(
				'default' => '30px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'author_image',
					'value' => '1',
				),
			),
			'image_border_width' => array(
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'author_image',
					'value' => '1',
				),
			),
			'image_border_style' => array(
				'default' => 'solid',
				'type' => 'dropdown',
				'options' => 'border_styles',
				'dependency' => array(
					array(
						'id' => 'author_image',
						'value' => '1',
					),
					array(
						'id' => 'image_border_width',
						'value' => '0px',
						'type' => 'inverse',
					),
				),
			),
			'image_border_color' => array(
				'default' => '#666666',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'author_image',
						'value' => '1',
					),
					array(
						'id' => 'image_border_width',
						'value' => '0px',
						'type' => 'inverse',
					),
				),
			),
		);

		$atts = array_merge( $atts, WPRM_Shortcode_Helper::get_label_container_atts() );
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
		if ( ! $recipe || ! $recipe->author() ) {
			return '';
		}

		$output = '';

		// Author name.
		$classes = array(
			'wprm-recipe-details',
			'wprm-recipe-author',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$output .= '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $recipe->author() . '</span>';

		// Optional author image.
		$img = '';
		if ( (bool) $atts['author_image'] ) {
			$author_id = false;

			switch ( $recipe->author_display() ) {
				case 'post_author':
					$author_id = $recipe->post_author();
					break;
				case 'same':
					$same_author_image = WPRM_Settings::get( 'recipe_author_same_image_user_id' );
					if ( $same_author_image ) {
						$author_id = intval( $same_author_image );
					}
					break;
			}

			if ( $author_id ) {
				$avatar = get_avatar( $author_id, $atts['image_size'] );

				if ( $avatar ) {
					$img = $avatar;
				}
			}
		}

		if ( $img ) {
			// Image Style.
			$style = '';
			$style .= 'border-width: ' . $atts['image_border_width'] . ';';
			$style .= 'border-style: ' . $atts['image_border_style'] . ';';
			$style .= 'border-color: ' . $atts['image_border_color'] . ';';

			if ( 'rounded' === $atts['image_style'] ) {
				$style .= 'border-radius: ' . $atts['rounded_radius'] . ';';
			} elseif ( 'circle' === $atts['image_style'] ) {
				$style .= 'border-radius: 50%;';
			}

			if ( $style ) {
				if ( false !== stripos( $img, ' style="' ) ) {
					$img = str_ireplace( ' style="', ' style="' . esc_attr( $style ), $img );
				} else {
					$img = str_ireplace( '<img ', '<img style="' . esc_attr( $style ) . '" ', $img );
				}
			}

			$output = '<span class="wprm-recipe-author-with-image"><span class="wprm-recipe-author-image">' . $img . '</span>' . $output . '</span>';
		}

		// Surround with optional container.
		$output = WPRM_Shortcode_Helper::get_label_container( $atts, 'author', $output );

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Author::init();