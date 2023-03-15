<?php
/**
 * Handle the recipe tag shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe tag shortcode.
 *
 * @since      3.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Tag extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-tag';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'key' => array(
				'default' => '',
				'type' => 'dropdown',
				'options' => 'recipe_tags',
			),
			'separator' => array(
				'default' => ', ',
				'type' => 'text',
			),
			'display_style' => array(
				'default' => 'text',
				'type' => 'dropdown',
				'options' => array(
					'text' => 'Text',
					'images' => 'Images',
					'text_images' => 'Text with Images',
				),
			),
			'image_tooltip' => array(
				'default' => 'none',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'No Tooltip',
					'term' => 'Show term name',
					'title' => 'Show image title attribute',
					'caption' => 'Show image caption attribute',
					'description' => 'Show image description attribute',
				),
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'image_size' => array(
				'default' => '30x30',
				'type' => 'image_size',
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'image_position' => array(
				'default' => 'left',
				'type' => 'dropdown',
				'options' => array(
					'left' => 'Left',
					'top' => 'Top',
					'right' => 'Right',
					'bottom' => 'Bottom',
				),
				'dependency' => array(
					'id' => 'display_style',
					'value' => 'text_images',
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
	 * @since	3.3.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$key = $atts['key'];

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->tags( $key ) ) {
			return '';
		}

		$terms = $recipe->tags( $key );

		// Output.
		$classes = array(
			'wprm-recipe-' . $atts['key'],
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$output = '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		foreach ( $terms as $index => $term ) {
			if ( 0 !== $index ) {
				$output .= $atts['separator'];
			}
			$name = is_object( $term ) ? $term->name : $term;

			if ( is_object( $term ) && 'suitablefordiet' === $key ) {
				$name = get_term_meta( $term->term_id, 'wprm_term_label', true );
			}

			$term_output = $name;
			$term_output = apply_filters( 'wprm_recipe_tag_shortcode_term', $term_output, $term, $atts, $recipe );
			$term_output = apply_filters( 'wprm_recipe_tag_shortcode_link', $term_output, $term );

			$output .= $term_output;
		}

		$output .= '</span>';

		$output = WPRM_Shortcode_Helper::get_label_container( $atts, array( 'tag', $atts['key'] ), $output );
		
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Tag::init();