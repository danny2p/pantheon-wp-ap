<?php
/**
 * Handle the Smart with Food shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      8.10.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the Smart with Food shortcode.
 *
 * @since      8.10.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Smart_With_Food extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-smart-with-food';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'text' => array(
				'default' => __( 'Direct in je mandje bij', 'wp-recipe-maker' ),
				'type' => 'text',
			),
			'text_style' => array(
				'default' => 'italic',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					'id' => 'text',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'text_color' => array(
				'default' => '#367dc1',
				'type' => 'color',
				'dependency' => array(
					'id' => 'text',
					'value' => '',
					'type' => 'inverse',
				),
			),
		);

		self::$attributes = $atts;

		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	8.10.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );
		$output = '';

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->id() ) {
			return '';
		}

		$token = WPRM_Settings::get( 'integration_smartwithfood_token' );

		// Placeholder in template editor.
		if ( $atts['is_template_editor_preview'] ) {
			if ( ! $token ) {
				return '<div class="wprm-template-editor-premium-only">' . __( 'Make sure to set your token on the WP Recipe Maker > Settings > Integrations page', 'wp-recipe-maker' ) . '</div>';
			}
		}

		if ( ! $token ) {
			return '';
		}

		// Make sure Smart with Food integration gets loaded.
		add_filter( 'wprm_load_smartwithfood', '__return_true' );

		// Output.
		$classes = array(
			'wprm-recipe-smart-with-food',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$style = 'color: ' . $atts['text_color'] . ';';

		// Hide by default.
		if ( ! $atts['is_template_editor_preview'] ) {
			$style .= 'display: none;';
		}

		// Text and optional aria-label.
		$text = __( $atts['text'], 'wp-recipe-maker' );

		$aria_label = '';
		if ( ! $text ) {
			$aria_label = ' aria-label="' . __( 'Direct in je mandje bij Collect & Go', 'wp-recipe-maker' ) . '"';
		}

		$name = $recipe->name();
		$image = $recipe->image_url( 'full' );

		$output = '<div style="' . esc_attr( $style ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '" data-recipe="' . esc_attr( $recipe->id() ) . '" data-recipe-name="' . esc_attr( $name ) . '" data-recipe-image="' . esc_attr( $image ) . '">' . WPRM_Shortcode_Helper::sanitize_html( $text ) . '<a href="#" class="wprm-recipe-smart-with-food-button"' . $aria_label . '><img src="https://fgdjrynm.filerobot.com/icons/49ecf8a9664802bfa8649382155f978e407ce0a1ec70a7aadfbedbda464065ba.svg?vh=2d14be"></a></div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Smart_With_Food::init();