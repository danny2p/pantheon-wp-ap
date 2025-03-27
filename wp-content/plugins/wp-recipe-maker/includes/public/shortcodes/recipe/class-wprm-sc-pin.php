<?php
/**
 * Handle the recipe pin shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe pin shortcode.
 *
 * @since      4.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Pin extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-pin';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'action' => array(
				'default' => 'one',
				'type' => 'dropdown',
				'options' => array(
					'one' => 'Pin one image',
					'any' => 'Pin any image from page (only works when loading pinit.js)',
				),
			),
			'style' => array(
				'default' => 'text',
				'type' => 'dropdown',
				'options' => array(
					'text' => 'Text',
					'button' => 'Button',
					'inline-button' => 'Inline Button',
					'wide-button' => 'Full Width Button',
				),
			),
			'icon' => array(
				'default' => '',
				'type' => 'icon',
			),
			'text' => array(
				'default' => __( 'Pin Recipe', 'wp-recipe-maker' ),
				'type' => 'text',
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'icon_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'icon',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'text_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'text',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'horizontal_padding' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'vertical_padding' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'button_color' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'border_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'border_radius' => array(
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	4.0.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ( ! $recipe->pin_image_url() && ! $recipe->pin_image_repin_id() && 'any' !== $atts['action'] ) ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}
		
		// Values in recipe.
		$media = $recipe->pin_image_url();
		$repin_id = $recipe->pin_image_repin_id();
		$description = $recipe->pin_image_description();

		// Check if we want to override media from other plugins.
		if ( WPRM_Settings::get( 'pinterest_use_image_from_other_plugins' ) ) {
			$parent_post_id = $recipe->parent_post_id();

			if ( $parent_post_id ) {
				// Hubbub Pro.
				$hubbub = get_post_meta( $parent_post_id, 'dpsp_share_options', true );
				if ( is_array( $hubbub ) ) {
					if ( isset( $hubbub['custom_image_pinterest'] ) && is_array( $hubbub['custom_image_pinterest'] ) && $hubbub['custom_image_pinterest']['src'] ) {
						$media = $hubbub['custom_image_pinterest']['src'];

						if ( isset( $hubbub['custom_description_pinterest'] ) && $hubbub['custom_description_pinterest'] ) {
							$description = $hubbub['custom_description_pinterest'];
						}
					}
				}

				
			}
		}

		// Make sure we have something to pin, otherwise return.
		if ( ! $media && ! $repin_id && 'any' !== $atts['action'] ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		// Build pin URL.
		$url = $recipe->permalink();
		$url = $url ? $url : get_permalink();
		$url = $url ? $url : get_home_url();

		$pin_url = '#';
		if ( $media ) {
			$pin_url = 'https://www.pinterest.com/pin/create/bookmarklet/';
			$pin_url .= '?url=' . urlencode( $url );
			$pin_url .= '&media=' . urlencode( $media );
			$pin_url .= '&description=' . urlencode( $description );
			$pin_url .= '&is_video=false';
		}

		// Get optional icon.
		$icon = '';
		if ( $atts['icon'] ) {
			$icon = WPRM_Icon::get( $atts['icon'], $atts['icon_color'] );

			if ( $icon ) {
				$icon = '<span class="wprm-recipe-icon wprm-recipe-pin-icon">' . $icon . '</span> ';
			}
		}

		// Output.
		$classes = array(
			'wprm-recipe-pin',
			'wprm-recipe-link',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$style = 'color: ' . $atts['text_color'] . ';';
		if ( 'text' !== $atts['style'] ) {
			$classes[] = 'wprm-recipe-pin-' . $atts['style'];
			$classes[] = 'wprm-recipe-link-' . $atts['style'];
			$classes[] = 'wprm-color-accent';

			$style .= 'background-color: ' . $atts['button_color'] . ';';
			$style .= 'border-color: ' . $atts['border_color'] . ';';
			$style .= 'border-radius: ' . $atts['border_radius'] . ';';
			$style .= 'padding: ' . $atts['vertical_padding'] . ' ' . $atts['horizontal_padding'] . ';';
		}

		// Text and optional aria-label.
		$text = WPRM_i18n::maybe_translate( $atts['text'] );

		$aria_label = '';
		if ( ! $text ) {
			$aria_label = ' aria-label="' . __( 'Pin Recipe', 'wp-recipe-maker' ) . '"';
		}

		// Construct link attributes.
		$attributes = '';
		$attributes .= ' style="' . esc_attr( $style ) . '"';
		$attributes .= ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
		$attributes .= ' target="_blank"';
		$attributes .= ' rel="nofollow noopener"';
		$attributes .= ' data-recipe="' . esc_attr( $recipe->id() ) . '"';
		$attributes .= ' data-url="' . esc_attr( $url ) . '"';
		$attributes .= ' data-media="' . esc_attr( $media ) . '"';
		$attributes .= ' data-description="' . esc_attr( $description ) . '"';
		$attributes .= ' data-repin="' . esc_attr( $repin_id ) . '"';
		$attributes .= ' role="button"';
		$attributes .= $aria_label;

		if ( 'any' === $atts['action'] ) {
			$attributes .= ' data-pin-action="any"';
		}

		// Make sure pinit.js gets loaded.
		add_filter( 'wprm_load_pinit', '__return_true' );

		$output = '<a href="' . esc_attr( $pin_url ) . '"' . $attributes . '>' . $icon . WPRM_Shortcode_Helper::sanitize_html( $text ) . '</a>';
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Pin::init();