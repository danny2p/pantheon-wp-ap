<?php
/**
 * Handle the recipe email share shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe email share shortcode.
 *
 * @since      6.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Email_Share extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-email-share';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
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
				'default' => __( 'Share by Email', 'wp-recipe-maker' ),
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
			'email_message_subject' => array(
				'default' => __( 'Check out this recipe!', 'wp-recipe-maker' ),
				'type' => 'text',
			),
			'email_message_intro' => array(
				'default' => __( 'I found this recipe you might like:', 'wp-recipe-maker' ),
				'type' => 'text',
			),
			'email_message_ingredients' => array(
				'default' => '0',
				'type' => 'toggle',
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	6.6.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe ) {
			return '';
		}

		// Build email message body.
		$url = $recipe->permalink();
		$url = $url ? $url : get_permalink();
		$url = $url ? $url : get_home_url();

		$body = '';

		if ( $atts['email_message_intro'] ) {
			$body .= $atts['email_message_intro'];
			$body .= "\n\n";
		}

		$body .= $recipe->name();
		$body .= "\n";
		$body .= $url;

		if ( (bool) $atts['email_message_ingredients'] ) {
			$body .= "\n\n";

			$ingredients = $recipe->ingredients_without_groups();
			foreach ( $ingredients as $ingredient ) {
				$ingredient_text = trim( $ingredient['amount'] . ' ' . $ingredient['unit'] . ' ' . $ingredient['name'] );

				if ( $ingredient['notes'] ) {
					$ingredient_text .= ' (' . $ingredient['notes'] . ')';
				}

				$body .= str_replace( '  ', ' ', strip_shortcodes( wp_strip_all_tags( $ingredient_text ) ) );
				$body .= "\n";
			}
		}

		$mailto_url = 'mailto:?subject=' . rawurlencode( $atts['email_message_subject'] ) . '&body=' . rawurlencode( $body );

		// Get optional icon.
		$icon = '';
		if ( $atts['icon'] ) {
			$icon = WPRM_Icon::get( $atts['icon'], $atts['icon_color'] );

			if ( $icon ) {
				$icon = '<span class="wprm-recipe-icon wprm-recipe-email-share-icon">' . $icon . '</span> ';
			}
		}

		// Output.
		$classes = array(
			'wprm-recipe-email-share',
			'wprm-recipe-link',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$style = 'color: ' . $atts['text_color'] . ';';
		if ( 'text' !== $atts['style'] ) {
			$classes[] = 'wprm-recipe-email-share-' . $atts['style'];
			$classes[] = 'wprm-recipe-link-' . $atts['style'];
			$classes[] = 'wprm-color-accent';

			$style .= 'background-color: ' . $atts['button_color'] . ';';
			$style .= 'border-color: ' . $atts['border_color'] . ';';
			$style .= 'border-radius: ' . $atts['border_radius'] . ';';
			$style .= 'padding: ' . $atts['vertical_padding'] . ' ' . $atts['horizontal_padding'] . ';';
		}

		// Text and optional aria-label.
		$text = __( $atts['text'], 'wp-recipe-maker' );

		$aria_label = '';
		if ( ! $text ) {
			$aria_label = ' aria-label="' . __( 'Share by Email', 'wp-recipe-maker' ) . '"';
		}

		$output = '<a href="' . esc_attr( $mailto_url ) . '" data-recipe="' . esc_attr( $recipe->id() ) . '" style="' . esc_attr( $style ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '" target="_blank" rel="nofollow"' . $aria_label . '>' . $icon . WPRM_Shortcode_Helper::sanitize_html( $text ) . '</a>';
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Email_Share::init();