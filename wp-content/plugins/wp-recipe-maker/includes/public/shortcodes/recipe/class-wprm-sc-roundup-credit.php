<?php
/**
 * Handle the recipe roundup credit shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe roundup credit shortcode.
 *
 * @since      9.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Roundup_Credit extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-roundup-credit';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'icon' => array(
				'default' => '',
				'type' => 'icon',
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
			'text_size' => array(
				'default' => '0.8em',
				'type' => 'size',
			),
			'label' => array(
				'default' => __( 'Photo credit', 'wp-recipe-maker' ) . ':',
				'type' => 'text',
			),
			'label_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					'id' => 'label',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'credit_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	9.0.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe ) {
			return '';
		}

		$credit = $recipe->credit();

		// Placeholder in template editor.
		if ( ! $credit && $atts['is_template_editor_preview'] ) {
			$credit = 'example.com';
		}

		// Make sure there actually is a credit set.
		if ( ! $credit ) {
			return '';
		}

		// Make sure there actually is an image.
		if ( intval( $recipe->image_id() ) <= 0 && ! $recipe->image_url() ) {
			return '';
		}

		// Get optional icon.
		$icon = '';
		if ( $atts['icon'] ) {
			$icon = WPRM_Icon::get( $atts['icon'], $atts['icon_color'] );

			if ( $icon ) {
				$icon = '<span class="wprm-recipe-icon wprm-recipe-roundup-credit-icon">' . $icon . '</span> ';
			}
		}

		// Get optional label.
		$label = '';
		if ( $atts['label'] ) {
			$label = '<span class="wprm-block-text-' . esc_attr( $atts['label_style'] ) . ' wprm-recipe-roundup-credit-label">' . $atts['label'] . '</span> ';
		}

		// Get credit text.
		$credit = '<span class="wprm-block-text-' . esc_attr( $atts['credit_style'] ) . ' wprm-recipe-roundup-credit-credit">' . $credit . '</span>';

		// Output.
		$classes = array(
			'wprm-recipe-roundup-credit',
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		// Custom style.
		$style = '';
		$style .= 'color: ' . $atts['text_color'] . ';';
		$style .= 'font-size: ' . $atts['text_size'] . ';';

		$output = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">' . $icon . $label . $credit . '</div>';
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Roundup_Credit::init();
