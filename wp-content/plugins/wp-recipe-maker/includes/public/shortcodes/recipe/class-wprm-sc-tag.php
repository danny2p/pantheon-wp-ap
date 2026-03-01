<?php
/**
 * Handle the recipe tag shortcode.
 *
 * @link       https://bootstrapped.ventures
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
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
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
			$term_output = self::feature_explorer_icon_term_output( $term_output, $term, $atts, $recipe );
			$term_output = apply_filters( 'wprm_recipe_tag_shortcode_term', $term_output, $term, $atts, $recipe );
			$term_output = apply_filters( 'wprm_recipe_tag_shortcode_link', $term_output, $term );

			$output .= $term_output;
		}

		$output .= '</span>';

		$output = WPRM_Shortcode_Helper::get_label_container( $atts, array( 'tag', $atts['key'] ), $output );
		
		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}

	/**
	 * Feature Explorer fallback for taxonomy icons using inline SVG map in demo recipe JSON.
	 * This only applies to the Feature Explorer demo recipe.
	 *
	 * @since	10.5.0
	 * @param	string $output Current term output.
	 * @param	mixed  $term   Term value.
	 * @param	array  $atts   Shortcode attributes.
	 * @param	object $recipe Recipe object.
	 */
	private static function feature_explorer_icon_term_output( $output, $term, $atts, $recipe ) {
		if ( ! isset( $atts['display_style'] ) || ! in_array( $atts['display_style'], array( 'images', 'text_images' ), true ) ) {
			return $output;
		}

		$taxonomy_key = isset( $atts['key'] ) ? sanitize_key( $atts['key'] ) : '';
		if ( ! $taxonomy_key ) {
			return $output;
		}

		$icons_by_taxonomy = self::get_feature_explorer_icons_for_tag( $recipe, $atts );

		if ( ! $taxonomy_key || ! is_array( $icons_by_taxonomy ) || ! isset( $icons_by_taxonomy[ $taxonomy_key ] ) || ! is_array( $icons_by_taxonomy[ $taxonomy_key ] ) ) {
			return $output;
		}

		$term_name = is_object( $term ) && isset( $term->name ) ? sanitize_text_field( $term->name ) : sanitize_text_field( $term );
		if ( ! $term_name ) {
			return $output;
		}

		$term_slug = is_object( $term ) && isset( $term->slug ) ? sanitize_key( $term->slug ) : sanitize_title( $term_name );
		$icon = isset( $icons_by_taxonomy[ $taxonomy_key ][ $term_slug ] ) && is_string( $icons_by_taxonomy[ $taxonomy_key ][ $term_slug ] ) ? $icons_by_taxonomy[ $taxonomy_key ][ $term_slug ] : '';

		if ( ! $icon ) {
			return $output;
		}
		$icon = self::get_feature_explorer_sized_inline_icon( $icon, $atts );

		$classes = array(
			'wprm-recipe-tag-term',
			'wprm-recipe-tag-' . $atts['display_style'],
			'wprm-recipe-tag-image-align-' . esc_attr( $atts['image_position'] ),
			'wprm-recipe-tag-term-has-image',
		);
		$data = '';

		if ( isset( $atts['image_tooltip'] ) && 'term' === $atts['image_tooltip'] && $term_name ) {
			$classes[] = 'wprm-tooltip';
			$data = WPRM_Tooltip::get_tooltip_data( $term_name );
		}

		$image_wrapper_style = self::get_feature_explorer_icon_wrapper_style( $atts );
		$image_output = '<div class="wprm-recipe-tag-term-image"' . $image_wrapper_style . '>' . $icon . '</div>';
		$text_output = 'text_images' === $atts['display_style'] ? '<div class="wprm-recipe-tag-term-text">' . $output . '</div>' : '';

		return '<div class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $data . '>' . $image_output . $text_output . '</div>';
	}

	/**
	 * Get Feature Explorer taxonomy icons map for the current shortcode render.
	 *
	 * @since	10.5.0
	 * @param	object $recipe Recipe object.
	 * @param	array  $atts   Shortcode attributes.
	 */
	private static function get_feature_explorer_icons_for_tag( $recipe, $atts ) {
		$icons_by_taxonomy = array();

		if ( $recipe && method_exists( $recipe, 'meta' ) ) {
			$icons_by_taxonomy = $recipe->meta( 'feature_explorer_taxonomy_icons', array() );
		}

		$has_icons = is_array( $icons_by_taxonomy ) && ! empty( $icons_by_taxonomy );
		$is_preview = isset( $atts['is_template_editor_preview'] ) && $atts['is_template_editor_preview'];
		$allow_preview_fallback = false;

		if ( isset( $atts['class'] ) && is_string( $atts['class'] ) ) {
			$class_names = preg_split( '/\s+/', trim( $atts['class'] ) );
			$allow_preview_fallback = in_array( 'wprm-feature-explorer-taxonomy-icons', $class_names, true );
		}

		if ( ! $has_icons && $is_preview && $allow_preview_fallback && method_exists( 'WPRM_Recipe_Manager', 'get_feature_explorer_taxonomy_icons' ) ) {
			$icons_by_taxonomy = WPRM_Recipe_Manager::get_feature_explorer_taxonomy_icons();
		}

		return is_array( $icons_by_taxonomy ) ? $icons_by_taxonomy : array();
	}

	/**
	 * Get style attributes for Feature Explorer icon wrapper.
	 *
	 * @since	10.5.0
	 * @param	array $atts Shortcode attributes.
	 */
	private static function get_feature_explorer_icon_wrapper_style( $atts ) {
		$size = self::get_feature_explorer_icon_size( $atts );
		if ( ! $size ) {
			return '';
		}

		$width = intval( $size[0] );
		$height = intval( $size[1] );

		if ( 0 >= $width || 0 >= $height ) {
			return '';
		}

		$style = 'width:' . $width . 'px;height:' . $height . 'px;min-width:' . $width . 'px;min-height:' . $height . 'px;flex:0 0 ' . $width . 'px;';
		return ' style="' . esc_attr( $style ) . '"';
	}

	/**
	 * Add explicit dimensions to Feature Explorer inline SVG icon.
	 *
	 * @since	10.5.0
	 * @param	string $icon Inline SVG icon.
	 * @param	array  $atts Shortcode attributes.
	 */
	private static function get_feature_explorer_sized_inline_icon( $icon, $atts ) {
		if ( ! is_string( $icon ) || false === stripos( $icon, '<svg' ) ) {
			return $icon;
		}

		$size = self::get_feature_explorer_icon_size( $atts );
		if ( ! $size ) {
			return $icon;
		}

		$width = intval( $size[0] );
		$height = intval( $size[1] );

		if ( 0 >= $width || 0 >= $height ) {
			return $icon;
		}

		$style = 'width:' . $width . 'px;height:' . $height . 'px;display:block;max-width:none;';
		$svg_attributes = ' width="' . esc_attr( strval( $width ) ) . '" height="' . esc_attr( strval( $height ) ) . '" style="' . esc_attr( $style ) . '"';

		return preg_replace( '/<svg\b/i', '<svg' . $svg_attributes, $icon, 1 );
	}

	/**
	 * Get numeric icon size from shortcode attributes.
	 *
	 * @since	10.5.0
	 * @param	array $atts Shortcode attributes.
	 */
	private static function get_feature_explorer_icon_size( $atts ) {
		$image_size = isset( $atts['image_size'] ) ? $atts['image_size'] : '';

		preg_match( '/^(\d+)x(\d+)(\!?)$/i', $image_size, $match );
		if ( ! empty( $match ) ) {
			return array(
				intval( $match[1] ),
				intval( $match[2] ),
			);
		}

		return false;
	}
}

WPRM_SC_Tag::init();
