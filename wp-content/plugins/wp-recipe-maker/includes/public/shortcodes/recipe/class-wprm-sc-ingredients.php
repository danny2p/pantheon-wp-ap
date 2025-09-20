<?php
/**
 * Handle the recipe ingredients shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe ingredients shortcode.
 *
 * @since      3.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Ingredients extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-ingredients';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'section_header' => array(
				'type' => 'header',
				'default' => __( 'Header', 'wp-recipe-maker' ),
			),
			'group_header' => array(
				'type' => 'header',
				'default' => __( 'Ingredient Groups', 'wp-recipe-maker' ),
			),
			'group_tag' => array(
				'default' => 'h4',
				'type' => 'dropdown',
				'options' => 'header_tags',
			),
			'group_style' => array(
				'default' => 'bold',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'group_custom_color' => array(
				'name' => 'Use Custom Color',
				'default' => '0',
				'type' => 'toggle',
			),
			'group_color' => array(
				'name' => 'Color',
				'default' => '#444444',
				'type' => 'color',
				'dependency' => array(
					'id' => 'group_custom_color',
					'value' => '1',
				),
			),
			'group_bottom_margin' => array(
				'name' => 'Bottom Margin',
				'default' => '0px',
				'type' => 'size',
			),
			'container_header' => array(
				'type' => 'header',
				'default' => __( 'Ingredient Container', 'wp-recipe-maker' ),
			),
			'list_style_header' => array(
				'type' => 'header',
				'default' => __( 'List Style', 'wp-recipe-maker' ),
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'force_item_position' => array(
				'default' => '0',
				'type' => 'toggle',
			),
			'list_item_position' => array(
				'default' => '32px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'force_item_position',
					'value' => '1',
				),
			),
			'bottom_border' => array(
				'default' => '0',
				'type' => 'toggle',
			),
			'bottom_border_gap' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'bottom_border',
					'value' => '1',
				),
			),
			'bottom_border_width' => array(
				'default' => '1px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'bottom_border',
					'value' => '1',
				),
			),
			'bottom_border_style' => array(
				'default' => 'solid',
				'type' => 'dropdown',
				'options' => 'border_styles',
				'dependency' => array(
					'id' => 'bottom_border',
					'value' => '1',
				),
			),
			'bottom_border_color' => array(
				'default' => '#eeeeee',
				'type' => 'color',
				'dependency' => array(
					'id' => 'bottom_border',
					'value' => '1',
				),
			),
			'list_style' => array(
				'default' => 'disc',
				'type' => 'dropdown',
				'options' => 'list_style_types',
			),
			'ingredient_fields_header' => array(
				'type' => 'header',
				'default' => __( 'Ingredient Fields', 'wp-recipe-maker' ),
			),
			'ingredients_order' => array(
				'default' => 'regular',
				'type' => 'dropdown',
				'options' => array(
					'regular' => 'Regular',
					'names-first' => 'Ingredient Names First',
				),
			),
			'ingredients_style' => array(
				'default' => 'regular',
				'type' => 'dropdown',
				'options' => array(
					'regular' => 'Regular',
					'grouped' => 'Grouped',
					'table-2' => 'Table with 2 columns',
					'table-2-align' => 'Table with 2 columns, aligned right',
					'table-3' => 'Table with 3 columns',
					'table-3-align' => 'Table with 3 columns, aligned right',
				),
			),
			'group_width' => array(
				'default' => '250px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'ingredients_style',
					'value' => 'grouped',
				),
			),
			'group_column_gap' => array(
				'default' => '10px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'ingredients_style',
					'value' => 'grouped',
				),
			),
			'table_amount_width' => array(
				'default' => '100px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'ingredients_style',
						'value' => 'regular',
						'type' => 'inverse',
					),
					array(
						'id' => 'ingredients_style',
						'value' => 'grouped',
						'type' => 'inverse'
					),
				),
			),
			'table_name_width' => array(
				'default' => '200px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'ingredients_style',
						'value' => 'regular',
						'type' => 'inverse',
					),
					array(
						'id' => 'ingredients_style',
						'value' => 'table-2',
						'type' => 'inverse'
					),
					array(
						'id' => 'ingredients_style',
						'value' => 'table-2-align',
						'type' => 'inverse',
					),
					array(
						'id' => 'ingredients_style',
						'value' => 'grouped',
						'type' => 'inverse'
					),
				),
			),
			'ingredient_notes_separator' => array(
				'default' => 'none',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'None',
					'comma' => 'Comma',
					'dash' => 'Dash',
					'parentheses' => 'Parentheses',
				),
			),
			'notes_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => array(
					'normal' => 'Normal',
					'faded' => 'Faded',
					'smaller' => 'Smaller',
					'smaller-faded' => 'Smaller & Faded',
				),
			),
			'ingredient_images_header' => array(
				'type' => 'header',
				'default' => __( 'Ingredient Images', 'wp-recipe-maker' ),
			),
			'image_position' => array(
				'default' => 'before',
				'type' => 'dropdown',
				'options' => array(
					'' => "Don't show",
					'before' => 'Before the ingredient',
					'after' => 'After the ingredient',
				),
			),
			'image_size' => array(
				'default' => '50x50',
				'type' => 'image_size',
				'dependency' => array(
					'id' => 'image_position',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'no_image_width' => array(
				'default' => '50',
				'type' => 'size',
				'dependency' => array(
					'id' => 'image_position',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'no_image_height' => array(
				'default' => '50',
				'type' => 'size',
				'dependency' => array(
					'id' => 'image_position',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'image_margin' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'image_position',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'image_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => array(
					'normal' => 'Normal',
					'rounded' => 'Rounded',
					'circle' => 'Circle',
				),
				'dependency' => array(
					'id' => 'image_position',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'image_rounded_radius' => array(
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'image_position',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'image_style',
						'value' => 'rounded',
					),
				),
			),
			'image_border_width' => array(
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'image_position',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'image_border_style' => array(
				'default' => 'solid',
				'type' => 'dropdown',
				'options' => 'border_styles',
				'dependency' => array(
					array(
						'id' => 'image_position',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'image_border_width',
						'value' => '0px',
						'type' => 'inverse',
					),
				),
			),
			'image_border_color' => array(
				'default' => '#666666',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'image_position',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'image_border_width',
						'value' => '0px',
						'type' => 'inverse',
					),
				),
			),
			'unit_conversion_header' => array(
				'type' => 'header',
				'default' => __( 'Unit Conversion', 'wp-recipe-maker' ),
			),
			'unit_conversion' => array(
				'default' => 'after',
				'type' => 'dropdown',
				'options' => array(
					'' => "Don't show",
					'header' => 'Show selector in the header',
					'before' => 'Show selector before the ingredients',
					'after' => 'Show selector after the ingredients',
					'both' => 'Show both systems at once',
				),
			),
			'unit_conversion_style' => array(
				'name' => 'Style',
				'default' => 'links',
				'type' => 'dropdown',
				'options' => array(
					'links' => 'Links',
					'dropdown' => 'Dropdown',
					'buttons' => 'Buttons',
					'switch' => 'Switch',
				),
				'dependency' => array(
					array(
						'id' => 'unit_conversion',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion',
						'value' => 'both',
						'type' => 'inverse'
					),
				),
			),
			'unit_conversion_text_style' => array(
				'name' => 'Text Style',
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					array(
						'id' => 'unit_conversion',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion',
						'value' => 'both',
						'type' => 'inverse'
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'dropdown',
						'type' => 'inverse',
					),
				),
			),
			'unit_conversion_separator' => array(
				'name' => 'Separator',
				'default' => ' - ',
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'unit_conversion',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion',
						'value' => 'both',
						'type' => 'inverse'
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'links',
					),
				),
			),
			'unit_conversion_button_background' => array(
				'name' => 'Button Background',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'unit_conversion',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion',
						'value' => 'both',
						'type' => 'inverse'
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'buttons',
					),
				),
			),
			'unit_conversion_button_accent' => array(
				'name' => 'Button Accent',
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'unit_conversion',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion',
						'value' => 'both',
						'type' => 'inverse'
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'buttons',
					),
				),
			),
			'unit_conversion_button_radius' => array(
				'name' => 'Button Radius',
				'default' => '3px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'unit_conversion',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion',
						'value' => 'both',
						'type' => 'inverse'
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'buttons',
					),
				),
			),
			'conversion_switch_style' => array(
				'name' => 'Switch Style',
				'default' => 'rounded',
				'type' => 'dropdown',
				'options' => array(
					'square' => 'Square Switch',
					'rounded' => 'Rounded Switch',
				),
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'switch',
					),
				),
			),
			'conversion_switch_height' => array(
				'name' => 'Switch Height',
				'default' => '28px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'switch',
					),
				),
			),
			'conversion_switch_off' => array(
				'name' => 'Switch Off',
				'default' => '#cccccc',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'switch',
					),
				),
			),
			'conversion_switch_off_knob' => array(
				'name' => 'Switch Off Knob',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'switch',
					),
				),
			),
			'conversion_switch_off_text' => array(
				'name' => 'Switch Off Text',
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'switch',
					),
				),
			),
			'conversion_switch_on' => array(
				'name' => 'Switch On',
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'switch',
					),
				),
			),
			'conversion_switch_on_knob' => array(
				'name' => 'Switch On Knob',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'switch',
					),
				),
			),
			'conversion_switch_on_text' => array(
				'name' => 'Switch On Text',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'unit_conversion_style',
						'value' => 'switch',
					),
				),
			),
			'unit_conversion_both_style' => array(
				'name' => 'Both Style',
				'default' => 'parentheses',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'None',
					'parentheses' => 'Parentheses',
					'slash' => 'Slash',
				),
				'dependency' => array(
					'id' => 'unit_conversion',
					'value' => 'both',
				),
			),
			'unit_conversion_show_identical' => array(
				'name' => 'Show Identical',
				'default' => '1',
				'type' => 'toggle',
				'dependency' => array(
					'id' => 'unit_conversion',
					'value' => 'both',
				),
			),
			'adjustable_servings_header' => array(
				'type' => 'header',
				'default' => __( 'Adjustable Servings', 'wp-recipe-maker' ),
			),
			'adjustable_servings' => array(
				'default' => '',
				'type' => 'dropdown',
				'options' => array(
					'' => "Don't show",
					'header' => 'Show adjustable servings in the header',
					'before' => 'Show adjustable servings before the ingredients',
				),
			),
			'servings_style' => array (
				'default' => 'buttons',
				'type' => 'dropdown',
				'options' => array(
					'buttons' => 'Buttons',
					'pills' => 'Pills',
				),
				'dependency' => array(
					'id' => 'adjustable_servings',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'servings_text_style' => array(
				'name' => 'Text Style',
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					'id' => 'adjustable_servings',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'servings_options' => array(
				'default' => '1x;2x;3x',
				'type' => 'text',
				'dependency' => array(
					'id' => 'adjustable_servings',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'serving_options_any_value' => array(
				'name' => 'Any Value',
				'default' => '',
				'type' => 'text',
				'dependency' => array(
					'id' => 'adjustable_servings',
					'value' => '',
					'type' => 'inverse',
				),
				'help' => 'Optional text for button to click on to set any serving size the visitor wants. Leave blank to disable.',
			),
			'servings_button_background' => array(
				'name' => 'Button Background',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'buttons',
					),
				),
			),
			'servings_button_accent' => array(
				'name' => 'Button Accent',
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'buttons',
					),
				),
			),
			'servings_button_radius' => array(
				'name' => 'Button Radius',
				'default' => '3px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'buttons',
					),
				),
			),
			'pills_height' => array(
				'default' => '28px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'pills',
					),
				),
			),
			'pills_gap' => array(
				'default' => '10px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'pills',
					),
				),
			),
			'pills_radius' => array(
				'default' => '999px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'pills',
					),
				),
			),
			'pills_background' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'pills',
					),
				),
			),
			'pills_border' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'pills',
					),
				),
			),
			'pills_text' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'pills',
					),
				),
			),
			'pills_active_background' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'pills',
					),
				),
			),
			'pills_active_border' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'pills',
					),
				),
			),
			'pills_active_text' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'adjustable_servings',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'servings_style',
						'value' => 'pills',
					),
				),
			),
			'advanced_servings_header' => array(
				'type' => 'header',
				'default' => __( 'Advanced Adjustable Servings', 'wp-recipe-maker' ),
			),
			'advanced_servings' => array(
				'default' => 'after',
				'type' => 'dropdown',
				'options' => array(
					'' => "Don't show",
					'before' => 'Show selector before the ingredients',
					'after' => 'Show selector after the ingredients',
				),
			),
			'advanced_servings_before' => array(
				'name' => 'Before Text',
				'default' => __( 'Makes:', 'wp-recipe-maker' ),
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'advanced_servings',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'advanced_servings_text_style' => array(
				'name' => 'Text Style',
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					'id' => 'advanced_servings',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'before_container_header' => array(
				'type' => 'header',
				'default' => __( 'Before Ingredients', 'wp-recipe-maker' ),
				'dependency' => array(
					array(
						'id' => 'unit_conversion',
						'value' => 'before',
					),
					array(
						'id' => 'adjustable_servings',
						'value' => 'before',
					),
					array(
						'id' => 'advanced_servings',
						'value' => 'before',
					),
					array(
						'id' => 'interactivity_container',
						'value' => '1',
					),
				),
				'dependency_compare' => 'OR',
			),
		);

		$atts = WPRM_Shortcode_Helper::insert_atts_after_key( $atts, 'section_header', WPRM_Shortcode_Helper::get_section_atts() );
		$atts = WPRM_Shortcode_Helper::insert_atts_after_key( $atts, 'container_header', WPRM_Shortcode_Helper::get_internal_container_atts() );
		$atts = WPRM_Shortcode_Helper::insert_atts_after_key( $atts, 'list_style', WPRM_Shortcode_Helper::get_checkbox_atts() );
		$atts = WPRM_Shortcode_Helper::insert_atts_after_key( $atts, 'list_style', WPRM_Shortcode_Helper::get_advanced_list_atts() );

		// Interactivity container.
		$interactivity_atts = WPRM_Shortcode_Helper::get_interactivity_container_atts();
		$interactivity_atts['interactivity_container']['dependency'] = array(
			array(
				'id' => 'unit_conversion',
				'value' => 'before',
			),
			array(
				'id' => 'adjustable_servings',
				'value' => 'before',
			),
			array(
				'id' => 'advanced_servings',
				'value' => 'before',
			),
			array(
				'id' => 'interactivity_container',
				'value' => '1',
			),
		);
		$interactivity_atts['interactivity_container']['dependency_compare'] = 'OR';
		$atts = WPRM_Shortcode_Helper::insert_atts_after_key( $atts, 'before_container_header', $interactivity_atts );

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

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe || ! $recipe->ingredients() ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		// Output.
		$classes = array(
			'wprm-recipe-ingredients-container',
			'wprm-recipe-' . $recipe->id() .'-ingredients-container',
			'wprm-block-text-' . $atts['text_style'],
			'wprm-ingredient-style-' . $atts['ingredients_style'],
		);

		if ( '' !== $atts['image_position'] ) {
			$classes[] = 'wprm-recipe-images-' . esc_attr( $atts['image_position'] );
		}

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		// Args for optional unit conversion and adjustable servings.
		$unit_conversion_atts = array(
			'id' => $atts['id'],
			'style' => $atts['unit_conversion_style'],
			'text_style' => $atts['unit_conversion_text_style'],
			'separator' => $atts['unit_conversion_separator'],
			'button_background' => $atts['unit_conversion_button_background'],
			'button_accent' => $atts['unit_conversion_button_accent'],
			'button_radius' => $atts['unit_conversion_button_radius'],
			'switch_style' => $atts['conversion_switch_style'],
			'switch_height' => $atts['conversion_switch_height'],
			'switch_off' => $atts['conversion_switch_off'],
			'switch_off_knob' => $atts['conversion_switch_off_knob'],
			'switch_off_text' => $atts['conversion_switch_off_text'],
			'switch_on' => $atts['conversion_switch_on'],
			'switch_on_knob' => $atts['conversion_switch_on_knob'],
			'switch_on_text' => $atts['conversion_switch_on_text'],
		);
		$adjustable_servings_atts = array(
			'id' => $atts['id'],
			'style' => $atts['servings_style'],
			'text_style' => $atts['servings_text_style'],
			'serving_options' => $atts['servings_options'],
			'serving_options_any_value' => $atts['serving_options_any_value'],
			'button_background' => $atts['servings_button_background'],
			'button_accent' => $atts['servings_button_accent'],
			'button_radius' => $atts['servings_button_radius'],
			'pills_height' => $atts['pills_height'],
			'pills_gap' => $atts['pills_gap'],
			'pills_radius' => $atts['pills_radius'],
			'pills_background' => $atts['pills_background'],
			'pills_border' => $atts['pills_border'],
			'pills_text' => $atts['pills_text'],
			'pills_active_background' => $atts['pills_active_background'],
			'pills_active_border' => $atts['pills_active_border'],
			'pills_active_text' => $atts['pills_active_text'],
		);
		$advanced_servings_atts = array(
			'id' => $atts['id'],
			'before_text' => $atts['advanced_servings_before'],
			'text_style' => $atts['advanced_servings_text_style'],
		);

		// Custom style.
		$css_variables = 'checkbox' === $atts['list_style'] ? parent::get_inline_css_variables( 'list', $atts, array( 'checkbox_size', 'checkbox_left_position', 'checkbox_top_position', 'checkbox_background', 'checkbox_border_width', 'checkbox_border_style', 'checkbox_border_color', 'checkbox_border_radius', 'checkbox_check_width', 'checkbox_check_color' ) ) : '';
		$style = WPRM_Shortcode_Helper::get_inline_style( $css_variables );

		$output = '<div id="recipe-' . esc_attr( $recipe->id() ) . '-ingredients" class="' . esc_attr( implode( ' ', $classes ) ) . '" data-recipe="' . esc_attr( $recipe->id() ) . '" data-servings="' . esc_attr( $recipe->servings() ) . '"' . $style .'>';
		$output .= WPRM_Shortcode_Helper::get_section_header( $atts, 'ingredients', array(
			'unit_conversion_atts' => $unit_conversion_atts,
			'adjustable_servings_atts' => $adjustable_servings_atts,
		) );

		$output_before = '';

		if ( 'before' === $atts['adjustable_servings'] ) {
			$output_before .= WPRM_SC_Adjustable_Servings::shortcode( $adjustable_servings_atts );
		}
		if ( 'before' === $atts['advanced_servings'] ) {
			$output_before .= WPRM_SC_Advanced_Adjustable_Servings::shortcode( $advanced_servings_atts );
		}
		if ( 'before' === $atts['unit_conversion'] ) {
			$output_before .= WPRM_SC_Unit_Conversion::shortcode( $unit_conversion_atts );
		}

		// Output functionality before the ingredients, optionally with container.
		if ( $output_before && (bool) $atts['interactivity_container'] ) {
			$output .= WPRM_Shortcode_Helper::get_interactivity_container( $atts, 'ingredients-before' );
		}

		$output .= $output_before;

		if ( $output_before && (bool) $atts['interactivity_container'] ) {
			$output .= '</div>';
		}

		// Maybe output container for the ingredients.
		if ( (bool) $atts['has_container'] ) {
			$output .= WPRM_Shortcode_Helper::get_internal_container( $atts, 'ingredients' );
		}

		$ingredients = $recipe->ingredients();
		foreach ( $ingredients as $ingredient_group ) {
			$output .= '<div class="wprm-recipe-ingredient-group">';

			if ( $ingredient_group['name'] ) {
				$classes = array(
					'wprm-recipe-group-name',
					'wprm-recipe-ingredient-group-name',
					'wprm-block-text-' . $atts['group_style'],
				);

				$style = '';
				if ( (bool) $atts['group_custom_color'] ) {
					$style = ' style="color: ' . esc_attr( $atts['group_color'] ) . ';"';
				}

				$tag = WPRM_Shortcode_Helper::sanitize_html_element( $atts['group_tag'] );
				$output .= '<' . $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $style . '>' . $ingredient_group['name'] . '</' . $tag . '>';

				if ( '0px' !== $atts['group_bottom_margin'] ) {
					$output .= do_shortcode( '[wprm-spacer size="' . $atts['group_bottom_margin'] . '"]' );
				}
			}

			if ( isset( $ingredient_group['ingredients'] ) && $ingredient_group['ingredients'] ) {
				$ul_style = '';

				if ( 'grouped' === $atts['ingredients_style'] ) {
					if ( '0px' !== $atts['group_column_gap'] ) {
						$ul_style = ' style="column-gap: ' . esc_attr( $atts['group_column_gap'] ) . ';"';
					}
				}

				$output .= '<ul class="wprm-recipe-ingredients"' . $ul_style . '>';

				foreach ( $ingredient_group['ingredients'] as $index => $ingredient ) {
					$list_style_type = 'checkbox' === $atts['list_style'] || 'advanced' === $atts['list_style'] ? 'none' : $atts['list_style'];
					$style = 'list-style-type: ' . $list_style_type . ';';

					$uid = '';
					if ( isset( $ingredient['uid'] ) ) {
						$uid = ' data-uid="' . esc_attr( $ingredient['uid'] ) . '"';
					}

					// Add group width if set to grouped style.
					if ( 'grouped' === $atts['ingredients_style'] ) {
						$style .= 'flex-basis: ' . $atts['group_width'] . ';';
					}

					// Add bottom border.
					if ( (bool) $atts['bottom_border'] ) {
						// Only if this is not the last item.
						if ( $index !== count( $ingredient_group['ingredients'] ) - 1 ) {
							$style .= 'border-bottom: ' . esc_attr( $atts['bottom_border_width'] ) . ' ' . esc_attr( $atts['bottom_border_style'] ) . ' ' . esc_attr( $atts['bottom_border_color'] ) . ';';
							$style .= 'padding-bottom: ' . esc_attr( $atts['bottom_border_gap'] ) . ';';
							$style .= 'margin-bottom: ' . esc_attr( $atts['bottom_border_gap'] ) . ';';
						}
					}

					if ( (bool) $atts['force_item_position'] ) {
						$style .= 'margin-left: ' . esc_attr( $atts['list_item_position'] ) . ';';	
					}

					$output .= '<li class="wprm-recipe-ingredient" style="' . esc_attr( $style ) . '"' . $uid . '>';

					// Maybe replace fractions in amount.
					if ( WPRM_Settings::get( 'automatic_amount_fraction_symbols' ) ) {
						$ingredient['amount'] = WPRM_Recipe_Parser::replace_any_fractions_with_symbol( $ingredient['amount'] );
					}

					// Ingredient images.
					$image = apply_filters( 'wprm_recipe_ingredients_shortcode_image', '', $atts, $ingredient );
					
					// Amount & Unit.
					$amount_unit = '';

					if ( $ingredient['amount'] || ( isset( $ingredient['converted'] ) && isset( $ingredient['converted'][2] ) && $ingredient['converted'][2]['amount'] ) ) {
						$amount_unit .= '<span class="wprm-recipe-ingredient-amount">' . $ingredient['amount'] . '</span>&#32;';
					}
					if ( $ingredient['unit'] || ( isset( $ingredient['converted'] ) && isset( $ingredient['converted'][2] ) && $ingredient['converted'][2]['unit'] ) ) {
						$amount_unit .= '<span class="wprm-recipe-ingredient-unit">' . $ingredient['unit'] . '</span>&#32;';
					}

					// Allow filtering for second unit system.
					$amount_unit = apply_filters( 'wprm_recipe_ingredients_shortcode_amount_unit', $amount_unit, $atts, $ingredient );

					// Surround with container if not regular or grouped style.
					if ( ! in_array( $atts['ingredients_style'], array( 'regular', 'grouped' ) ) ) {
						$amount_unit = '<span class="wprm-recipe-ingredient-amount-unit" style="flex-basis: ' . esc_attr( $atts['table_amount_width'] ) .';">' . trim( $amount_unit ) . '</span> ';
					}

					// Ingredient name.
					$name_output = '';
					$name = '';
					$separator = '';

					if ( $ingredient['name'] ) {
						$separator = '';
						if ( $ingredient['notes'] ) {
							switch ( $atts['ingredient_notes_separator'] ) {
								case 'comma':
									$separator = ',&#32;';
									break;
								case 'dash':
									$separator = '&#32;-&#32;';
									break;
								default:
									$separator = '&#32;';
							}	
						}

						// Ingredient link.
						$name = apply_filters( 'wprm_recipe_ingredients_shortcode_link', $ingredient['name'], $ingredient, $recipe );
					}

					if ( $name || ! in_array( $atts['ingredients_style'], array( 'regular', 'grouped' ) ) ) {
						if ( 'table-3' === substr( $atts['ingredients_style'], 0, 7 ) ) {
							$name_output = '<span class="wprm-recipe-ingredient-name" style="flex-basis: ' . esc_attr( $atts['table_name_width'] ) .';">' . $name . '</span>'  . $separator;
						} else {
							$name_output = '<span class="wprm-recipe-ingredient-name">' . $name . '</span>'  . $separator;
						}
					}

					// Ingredient Notes.
					$notes_output = '';

					if ( $ingredient['notes'] ) {
						if ( 'parentheses' === $atts['ingredient_notes_separator'] ) {
							$notes_output .= '<span class="wprm-recipe-ingredient-notes wprm-recipe-ingredient-notes-' . esc_attr( $atts['notes_style'] ) . '">(' . $ingredient['notes'] . ')</span>';
						} else {
							$notes_output .= '<span class="wprm-recipe-ingredient-notes wprm-recipe-ingredient-notes-' . esc_attr( $atts['notes_style'] ) . '">' . $ingredient['notes'] . '</span>';
						}
					} elseif( ! in_array( $atts['ingredients_style'], array( 'regular', 'grouped' ) ) ) {
						$notes_output .= '<span class="wprm-recipe-ingredient-notes"></span>';
					}

					// Table layout.
					$names_notes = $name_output . $notes_output;
					if ( 'table-2' === substr( $atts['ingredients_style'], 0, 7 ) ) {
						$names_notes = '<span class="wprm-recipe-ingredient-name-notes">' . $names_notes . '</span>';
					}

					// Output in order.
					$ingredient_output = '';

					if ( 'names-first' === $atts['ingredients_order'] ) {
						$ingredient_output .= $names_notes;
						$ingredient_output .= '&#32;';
						$ingredient_output .= $amount_unit;
					} else {
						$ingredient_output .= $amount_unit;
						$ingredient_output .= $names_notes;
					}

					// Have image separate when using the grouped style.
					if ( 'grouped' === $atts['ingredients_style'] ) {
						$ingredient_output = '<span class="wprm-recipe-ingredient-details">' . $ingredient_output . '</span>';
					}

					// Output optional ingredient image.
					if ( 'before' === $atts['image_position'] ) {
						$ingredient_output = $image . $ingredient_output;
					} elseif ( 'after' === $atts['image_position'] ) {
						$ingredient_output .= $image;
					}

					// Output checkbox.
					if ( 'checkbox' === $atts['list_style'] ) {
						$ingredient_output = apply_filters( 'wprm_recipe_ingredients_shortcode_checkbox', $ingredient_output );
					}

					// Add container when using the grouped style.
					if ( 'grouped' === $atts['ingredients_style'] ) {
						$ingredient_output = '<span class="wprm-recipe-ingredient-details-container">' . $ingredient_output . '</span>';
					}

					$output .= $ingredient_output;
					$output .= '</li>';
				}

				$output .= '</ul>';
			}

			$output .= '</div>';
		}

	 	if ( 'after' === $atts['advanced_servings'] ) {
			$output .= WPRM_SC_Advanced_Adjustable_Servings::shortcode( $advanced_servings_atts );
		}
	 	if ( 'after' === $atts['unit_conversion'] ) {
			$output .= WPRM_SC_Unit_Conversion::shortcode( $unit_conversion_atts );
		}

		$output .= '</div>';

		if ( (bool) $atts['has_container'] ) {
			$output .= '</div>';
		}

		// Make sure shortcodes work.
		$output = do_shortcode( $output );

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Ingredients::init();