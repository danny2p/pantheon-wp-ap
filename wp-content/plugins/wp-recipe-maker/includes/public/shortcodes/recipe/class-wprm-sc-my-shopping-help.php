<?php
/**
 * Handle the My Shopping Help shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the My Shopping Help shortcode.
 *
 * @since      10.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_My_Shopping_Help extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-my-shopping-help';

	/**
	 * Initialize shortcode attributes.
	 */
	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'style' => array(
				'default' => 'button',
				'type' => 'dropdown',
				'options' => array(
					'text' => 'Text',
					'button' => 'Button',
					'inline-button' => 'Inline Button',
					'wide-button' => 'Full Width Button',
				),
			),
			'custom_icon' => array(
				'default' => '0',
				'type' => 'toggle',
			),
			'icon' => array(
				'default' => 'calendar-plus',
				'type' => 'icon',
				'dependency' => array(
					'id' => 'custom_icon',
					'value' => '1',
				),
			),
			'text' => array(
				'default' => __( 'Add to my MSH planner', 'wp-recipe-maker' ),
				'type' => 'text',
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'icon_color' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'icon',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'custom_icon',
						'value' => '1',
					),
				),
			),
			'text_color' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'text',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'horizontal_padding' => array(
				'default' => '10px',
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
				'default' => '#4BBC23',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'border_color' => array(
				'default' => '#4BBC23',
				'type' => 'color',
				'dependency' => array(
					'id' => 'style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'border_radius' => array(
				'default' => '100px',
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
	 * @since	10.3.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );
		$output = '';

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->id() ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		// Check if My Shopping Help plugin is activated.
		$activated = class_exists( 'Nakko\Msh\MshPlugin' );

		// Placeholder inside template editor when not configured yet.
		if ( $atts['is_template_editor_preview'] && ! $activated ) {
			return '<div class="wprm-template-editor-premium-only">' . __( 'Make sure the "My Shopping Help" plugin is activated on your site.', 'wp-recipe-maker' ) . '</div>';
		}

		if ( ! $activated ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		// Optional icon.
		$icon = '';
		$use_custom_icon = wp_validate_boolean( $atts['custom_icon'] );

		if ( $use_custom_icon ) {
			if ( $atts['icon'] ) {
				$icon = WPRM_Icon::get( $atts['icon'], $atts['icon_color'] );

				if ( $icon ) {
					$icon = '<span class="wprm-recipe-icon wprm-recipe-my-shopping-help-icon">' . $icon . '</span> ';
				}
			}
		} else {
			$svg_path = WPRM_DIR . 'assets/icons/integrations/my-shopping-help.svg';
			$svg_content = '';
			if ( file_exists( $svg_path ) ) {
				$svg_content = file_get_contents( $svg_path );
			}
			if ( $svg_content ) {
				$icon = '<span class="wprm-recipe-icon wprm-recipe-my-shopping-help-icon wprm-recipe-my-shopping-help-default-icon" aria-hidden="true">' . $svg_content . '</span> ';
			}
		}

		// Output classes & styles.
		$classes = array(
			'wprm-recipe-my-shopping-help',
			'wprm-recipe-link',
			'wprm-block-text-' . $atts['text_style'],
		);

		if ( $atts['class'] ) {
			$classes[] = esc_attr( $atts['class'] );
		}

		$style = 'color: ' . $atts['text_color'] . ';';

		if ( 'text' !== $atts['style'] ) {
			$classes[] = 'wprm-recipe-link-' . $atts['style'];
			$classes[] = 'wprm-color-accent';
			$style .= 'background-color: ' . $atts['button_color'] . ';';
			$style .= 'border-color: ' . $atts['border_color'] . ';';
			$style .= 'border-radius: ' . $atts['border_radius'] . ';';
			$style .= 'padding: ' . $atts['vertical_padding'] . ' ' . $atts['horizontal_padding'] . ';';
		}

		// Hide by default, will be shown by JavaScript if window.msh is available.
		// Don't hide in template editor preview.
		if ( ! $atts['is_template_editor_preview'] ) {
			$style .= ' visibility: hidden;';
		}

		$text = WPRM_i18n::maybe_translate( $atts['text'] );
		$aria_label = '';
		if ( ! $text ) {
			$aria_label = ' aria-label="' . __( 'Add recipe to My Shopping Help', 'wp-recipe-maker' ) . '"';
		}

		$recipe_data = array(
			'id' => strval( $recipe->id() ),
			'type' => 'recipe',
			'url' => $recipe->permalink(),
			'name' => $recipe->name(),
			'image' => $recipe->image_url( 'full' ),
		);

		$recipe_data = array_filter( $recipe_data );
		$data_attributes = '';

		if ( ! empty( $recipe_data ) ) {
			$data_attributes .= ' data-recipe="' . esc_attr( wp_json_encode( $recipe_data ) ) . '"';

			foreach ( $recipe_data as $key => $value ) {
				$data_attributes .= ' data-recipe-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}

		$output .= '<a href="#" rel="nofollow noopener" style="' . esc_attr( $style ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $aria_label . $data_attributes . '>' . $icon . WPRM_Shortcode_Helper::sanitize_html( $text ) . '</a>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_My_Shopping_Help::init();
