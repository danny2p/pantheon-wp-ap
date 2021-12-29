<?php
/**
 * Handle the recipe custom field shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe custom field shortcode.
 *
 * @since      5.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Custom_Field extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-custom-field';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'key' => array(
				'default' => '',
				'type' => 'dropdown',
				'options' => 'custom_fields',
			),
			'image_size' => array(
				'default' => 'thumbnail',
				'type' => 'image_size',
				'dependency' => array(
					array(
						'id' => 'key',
						'value' => 'actual_values_set_in_parse_shortcode_below',
					),
				),
				'dependency_compare' => 'OR',
			),
			'link_text' => array(
				'default' => '',
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'key',
						'value' => 'actual_values_set_in_parse_shortcode_below',
					),
				),
				'dependency_compare' => 'OR',
			),
			'link_target' => array(
				'default' => '_blank',
				'type' => 'dropdown',
				'options' => array(
					'_self' => 'Open in same tab',
					'_blank' => 'Open in new tab',
				),
				'dependency' => array(
					array(
						'id' => 'key',
						'value' => 'actual_values_set_in_parse_shortcode_below',
					),
				),
				'dependency_compare' => 'OR',
			),
			'link_nofollow' => array(
				'default' => 'nofollow',
				'type' => 'dropdown',
				'options' => array(
					'dofollow' => 'Do not add nofollow attribute',
					'nofollow' => 'Add nofollow attribute',
				),
				'dependency' => array(
					array(
						'id' => 'key',
						'value' => 'actual_values_set_in_parse_shortcode_below',
					),
				),
				'dependency_compare' => 'OR',
			),
			'link_style' => array(
				'default' => 'text',
				'type' => 'dropdown',
				'options' => array(
					'text' => 'Text',
					'button' => 'Button',
					'inline-button' => 'Inline Button',
					'wide-button' => 'Full Width Button',
				),
				'dependency' => array(
					'id' => 'link_text',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'link_icon' => array(
				'default' => '',
				'type' => 'icon',
				'dependency' => array(
					'id' => 'link_text',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'link_text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					'id' => 'link_text',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'link_icon_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'link_icon',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'link_text_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'link_text',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'link_horizontal_padding' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'link_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'link_vertical_padding' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'link_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'link_button_color' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'link_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'link_border_color' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					'id' => 'link_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'link_border_radius' => array(
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'link_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
		);

		$atts = array_merge( WPRM_Shortcode_Helper::get_section_atts(), $atts );
		$atts = array_merge( $atts, WPRM_Shortcode_Helper::get_label_container_atts() );
		self::$attributes = $atts;

		add_filter( 'wprm_template_parse_shortcode', array( __CLASS__, 'parse_shortcode' ), 10, 3 );

		parent::init();
	}

	/**
	 * Add dynamic shortcode attributes.
	 *
	 * @since	6.0.0
	 * @param	array $shortcodes 	All shortcodes to parse.
	 * @param	array $shortcode 	Shortcode getting parsed.
	 * @param	array $atts 		Shortcode attributes.
	 */
	public static function parse_shortcode( $shortcodes, $shortcode, $attributes ) {
		if ( 'wprm-recipe-custom-field' === $shortcode ) {
			$custom_fields = class_exists( 'WPRM_Addons' ) && WPRM_Addons::is_active( 'custom-fields' ) ? WPRMPCF_Manager::get_custom_fields() : array();
			
			foreach ( $custom_fields as $key => $custom_field ) {
				$this_key_dependency = array(
					'id' => 'key',
					'value' => $key,
				);

				switch ( $custom_field['type'] ) {
					case 'image':
						$shortcodes[ $shortcode ]['image_size']['dependency'][] = $this_key_dependency;
						break;
					case 'link':
						$shortcodes[ $shortcode ]['link_text']['dependency'][] = $this_key_dependency;
						$shortcodes[ $shortcode ]['link_nofollow']['dependency'][] = $this_key_dependency;
						$shortcodes[ $shortcode ]['link_target']['dependency'][] = $this_key_dependency;
						break;
				}
			}
		}

		return $shortcodes;
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	5.2.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$output = '';
		// Show teaser for Premium only shortcode in Template editor.
		if ( $atts['is_template_editor_preview'] ) {
			$output = '<div class="wprm-template-editor-premium-only">Custom Fields are only available in the <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/">WP Recipe Maker Pro Bundle</a>.</div>';
		}

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Custom_Field::init();