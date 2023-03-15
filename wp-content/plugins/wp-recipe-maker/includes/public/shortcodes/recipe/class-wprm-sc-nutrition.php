<?php
/**
 * Handle the recipe nutrition shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe nutrition shortcode.
 *
 * @since      3.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Nutrition extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-nutrition';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'field' => array(
				'default' => '',
				'type' => 'dropdown',
				'options' => 'nutrition_fields',
			),
			'display_value' => array(
				'default' => 'serving',
				'type' => 'dropdown',
				'options' => array(
					'serving' => __( 'Per serving', 'wp-recipe-maker' ),
					'100g' => __( 'Per 100g', 'wp-recipe-maker' ),
				),
			),
			'unit' => array(
				'default' => '0',
				'type' => 'toggle',
			),
			'unit_separator' => array(
				'default' => '',
				'type' => 'text',
				'dependency' => array(
					'id' => 'unit',
					'value' => '1',
				),
			),
			'daily' => array(
				'default' => '0',
				'type' => 'toggle',
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
		if ( ! $recipe ) {
			return '';
		}

		$show_unit = (bool) $atts['unit'];
		$nutrient = array(
			'value' => '',
			'unit' => '',
		);

		// We can only output calories in free version.
		if ( 'calories' === $atts['field'] ) {
			if ( false !== $recipe->calories() ) {
				$nutrient['value'] = $recipe->calories();
				$nutrient['unit'] = __( 'kcal', 'wp-recipe-maker' );
			}
		}

		$nutrient = apply_filters( 'wprm_nutrition_shortcode_nutrient', $nutrient, $atts, $recipe );

		if ( $nutrient['value'] === false || ( ! $nutrient['value'] && ! WPRM_Settings::get( 'nutrition_label_zero_values' ) ) ) {
			return '';
		}

		// Output.
		$classes = array(
			'wprm-recipe-details',
			'wprm-recipe-nutrition',
			'wprm-recipe-' . $atts['field'],
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$output = '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $nutrient['value'] .  '</span>';

		if ( $show_unit && $nutrient['unit'] ) {
			$classes = array(
				'wprm-recipe-details-unit',
				'wprm-recipe-nutrition-unit',
				'wprm-recipe-' . $atts['field'] . '-unit',
				'wprm-block-text-' . $atts['text_style'],
			);

			$output = '<span class="wprm-recipe-nutrition-with-unit">' . $output . WPRM_Shortcode_Helper::sanitize_html( $atts['unit_separator'] ) . '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $nutrient['unit'] . '</span></span>';
		}

		$output = WPRM_Shortcode_Helper::get_label_container( $atts, array( 'nutrition', $atts['field'] ), $output );

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Nutrition::init();