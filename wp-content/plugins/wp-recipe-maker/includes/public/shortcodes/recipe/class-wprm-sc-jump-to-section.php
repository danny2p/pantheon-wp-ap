<?php
/**
 * Handle the jump to section shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the jump to section shortcode.
 *
 * @since     10.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Jump_To_Section extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-jump-to-section';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'container_header' => array(
				'type' => 'header',
				'default' => __( 'Container', 'wp-recipe-maker' ),
			),
			'alignment' => array(
				'default' => 'flex-start',
				'type' => 'dropdown',
				'options' => array(
					'flex-start' => 'Left',
					'center' => 'Center',
					'flex-end' => 'Right',
					'space-around' => 'Space Around',
					'space-between' => 'Space Between',
				),
			),
			'wrap_items' => array(
				'default' => 'wrap',
				'type' => 'dropdown',
				'options' => array(
					'wrap' => 'Wrap to new line',
					'nowrap' => 'No wrapping',
					'scroll' => 'No wrapping, with horizontal scroll',
				),
			),
			'gap' => array(
				'default' => '10px',
				'type' => 'size',
			),
			'background' => array(
				'default' => '#ffffff',
				'type' => 'color',
			),
			'border' => array(
				'default' => '#333333',
				'type' => 'color',
			),
			'border_width' => array(
				'default' => '0px',
				'type' => 'size',
			),
			'border_radius' => array(
				'default' => '10px',
				'type' => 'size',
			),
			'horizontal_padding' => array(
				'default' => '15px',
				'type' => 'size',
			),
			'vertical_padding' => array(
				'default' => '5px',
				'type' => 'size',
			),
			'items_header' => array(
				'type' => 'header',
				'default' => __( 'Items', 'wp-recipe-maker' ),
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
			'icon_position' => array(
				'default' => 'left',
				'type' => 'dropdown',
				'options' => array(
					'left' => 'Left',
					'above' => 'Above',
					'right' => 'Right',
					'below' => 'Below',
				),
			),
			'icon_size' => array(
				'default' => '16px',
				'type' => 'size',
			),
			'smooth_scroll' => array(
				'default' => '0',
				'type' => 'toggle',
			),
			'smooth_scroll_speed' => array(
				'default' => '500',
				'type' => 'number',
				'dependency' => array(
					'id' => 'smooth_scroll',
					'value' => '1',
				),
			),
			'sections_header' => array(
				'type' => 'header',
				'default' => __( 'Items to Show', 'wp-recipe-maker' ),
			),
			'section_order' => array(
				'name' => 'Order',
				'default' => 'ingredients, equipment, instructions, nutrition, notes, video',
				'type' => 'text',
				'help' => 'Comma seperated list of fields to show. For example: ingredients, equipment, instructions, nutrition, notes, video',
			),
			'ingredients_text' => array(
				'default' => 'Ingredients',
				'type' => 'text',
			),
			'ingredients_icon' => array(
				'default' => 'ingredients',
				'type' => 'icon',
			),
			'equipment_text' => array(
				'default' => 'Equipment',
				'type' => 'text',
			),
			'equipment_icon' => array(
				'default' => 'utensils',
				'type' => 'icon',
			),
			'instructions_text' => array(
				'default' => 'Instructions',
				'type' => 'text',
			),
			'instructions_icon' => array(
				'default' => 'stirring',
				'type' => 'icon',
			),
			'nutrition_text' => array(
				'default' => 'Nutrition',
				'type' => 'text',
			),
			'nutrition_icon' => array(
				'default' => 'heart',
				'type' => 'icon',
			),
			'video_text' => array(
				'default' => 'Video',
				'type' => 'text',
			),
			'video_icon' => array(
				'default' => 'media',
				'type' => 'icon',
			),
			'notes_text' => array(
				'default' => 'Notes',
				'type' => 'text',
			),	
			'notes_icon' => array(
				'default' => 'pencil-2',
				'type' => 'icon',
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
		if ( ! $recipe ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		// Check if there are sections to show.
		$sections = array();
		foreach ( explode( ',', $atts['section_order'] ) as $section ) {
			$section = trim( $section );

			switch ( $section ) {
				case 'ingredients':
				case 'instructions':
				case 'equipment':
				case 'notes':
				case 'video':
					$has_section = ! ! $recipe->$section();
					break;
				case 'nutrition':
					$has_section = '' !== do_shortcode( '[wprm-nutrition-label id="' . $recipe->id() . '" style="simple" nutrition_values="serving"]' );
					break;
				default:
					$has_section = false;
					break;
			}

			if ( $has_section ) {
				$sections[] = $section;
			}
		}

		// Make sure at least one section will be shown.
		if ( ! $sections ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		// Output.
		$classes = array(
			'wprm-recipe-jump-to-section-container',
		);

		if ( 'wrap' !== $atts['wrap_items'] ) {
			$classes[] = 'wprm-recipe-jump-to-section-container-' . $atts['wrap_items'];

			if ( 'scroll' === $atts['wrap_items'] ) {
				$classes[] = 'scrolled-left';
			}
		}

		if ( 'left' !== $atts['icon_position'] ) {
			$classes[] = 'wprm-recipe-jump-to-section-icon-' . $atts['icon_position'];
		}

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }
		$css_variables = parent::get_inline_css_variables( 'jump-to-section', $atts, array( 'gap', 'alignment', 'background', 'text_color', 'border_width', 'border', 'border_radius', 'vertical_padding', 'horizontal_padding' ) );
		$style = WPRM_Shortcode_Helper::get_inline_style( $css_variables );

		$output = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $style . '">';

		foreach ( $sections as $section ) {
			// Get text for section.
			$text = trim( $atts[ $section . '_text' ] );
			
			// Get optional icon.
			$icon = '';
			if ( $atts[ $section . '_icon' ] ) {
				$icon = WPRM_Icon::get( $atts[ $section . '_icon' ], $atts['icon_color'] );
	
				if ( $icon ) {
					$icon_style = '';
					if ( '16px' !== $atts['icon_size'] ) {
						$icon_style = ' style="';
						$icon_style .= 'font-size: ' . esc_attr( $atts['icon_size'] ) . ';';
						$icon_style .= '"';
					}

					$icon = '<span class="wprm-recipe-icon wprm-recipe-jump-to-section-icon"' . $icon_style . '>' . $icon . '</span> ';
				}
			}

			// Classes.
			$section_classes = array(
				'wprm-recipe-jump-to-section',
				'wprm-block-text-' . esc_attr( $atts['text_style'] ),
			);

			// Optional aria-label if no text is set.
			$aria_label = '';
			if ( ! $text ) {
				$translated_label = '';

				switch ( $section ) {
					case 'ingredients':
						$translated_label = __( 'Jump to Ingredients', 'wp-recipe-maker' );
						break;
					case 'instructions':
						$translated_label = __( 'Jump to Instructions', 'wp-recipe-maker' );
						break;
					case 'equipment':
						$translated_label = __( 'Jump to Equipment', 'wp-recipe-maker' );
						break;
					case 'notes':
						$translated_label = __( 'Jump to Notes', 'wp-recipe-maker' );
						break;
					case 'video':
						$translated_label = __( 'Jump to Video', 'wp-recipe-maker' );
						break;
					case 'nutrition':
						$translated_label = __( 'Jump to Nutrition Facts', 'wp-recipe-maker' );
						break;
				}

				$aria_label = ' aria-label="' . $translated_label . '"';
			}

			// Optional smooth scroll.
			$smooth_scroll = (bool) $atts['smooth_scroll'];
			$smooth_scroll_speed = '';
			if ( $smooth_scroll ) {
				$section_classes[] = 'wprm-jump-smooth-scroll';
				$smooth_scroll_speed = ' data-smooth-scroll="' . intval( $atts['smooth_scroll_speed'] ) . '"';
			}

			// ID to jump to.
			$section_url = '#recipe-' . $recipe->id() . '-' . $section;

			if ( 'video' === $section ) {
				$section_url = '#wprm-recipe-video-container-' . $recipe->id();
			}

			$output .= '<a href="' . esc_url( $section_url ) . '" class="' . esc_attr( implode( ' ', $section_classes ) ) . '"' . $smooth_scroll_speed . $aria_label . '>' . $icon . WPRM_Shortcode_Helper::sanitize_html( $text ) . '</a>';
		}

		$output .= '</div>';

		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Jump_To_Section::init();