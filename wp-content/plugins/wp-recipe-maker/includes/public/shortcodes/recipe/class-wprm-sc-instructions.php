<?php
/**
 * Handle the recipe instructions shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      3.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe instructions shortcode.
 *
 * @since      3.3.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Instructions extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-instructions';

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
				'default' => __( 'Instruction Groups', 'wp-recipe-maker' ),
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
				'default' => '0',
				'type' => 'toggle',
			),
			'group_color' => array(
				'default' => '#444444',
				'type' => 'color',
				'dependency' => array(
					'id' => 'group_custom_color',
					'value' => '1',
				),
			),
			'group_bottom_margin' => array(
				'default' => '0px',
				'type' => 'size',
			),
			'text_margin' => array(
				'default' => '0px',
				'type' => 'size',
			),
			'container_header' => array(
				'type' => 'header',
				'default' => __( 'Instruction Container', 'wp-recipe-maker' ),
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
			'list_tag' => array(
				'default' => 'ul',
				'type' => 'dropdown',
				'options' => array(
					'ul' => 'ul',
					'ol' => 'ol',
				),
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
			'list_style' => array(
				'default' => 'decimal',
				'type' => 'dropdown',
				'options' => 'list_style_types',
			),
			'inline_ingredients_header' => array(
				'type' => 'header',
				'default' => __( 'Inline Ingredients', 'wp-recipe-maker' ),
			),
			'inline_text_style' => array(
				'name' => 'Text Style',
				'default' => 'bold',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'inline_use_custom_color' => array(
				'name' => 'Use Custom Color',
				'default' => '0',
				'type' => 'toggle',
			),
			'inline_custom_color' => array(
				'name' => 'Custom Color',
				'default' => '#000000',
				'type' => 'color',
				'dependency' => array(
					'id' => 'inline_use_custom_color',
					'value' => '1',
				),
			),
			'inline_show_notes' => array(
				'name' => 'Show Notes',
				'default' => '0',
				'type' => 'toggle',
			),
			'inline_notes_separator' => array(
				'name' => 'Notes Separator',
				'default' => 'none',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'None',
					'comma' => 'Comma',
					'dash' => 'Dash',
					'parentheses' => 'Parentheses',
				),
				'dependency' => array(
					'id' => 'inline_show_notes',
					'value' => '1',
				),
			),
			'associated_ingredients_header' => array(
				'type' => 'header',
				'default' => __( 'Associated Ingredients', 'wp-recipe-maker' ),
			),
			'ingredients_position' => array(
				'default' => 'after',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'Do not display',
					'before' => 'Before Text',
					'after' => 'After Text',
				),
			),
			'ingredients_text_style' => array(
				'name' => 'Text Style',
				'default' => 'faded',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					'id' => 'ingredients_position',
					'value' => 'none',
					'type' => 'inverse',
				),
			),
			'ingredients_text_margin' => array(
				'name' => 'Text Margin',
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'ingredients_position',
					'value' => 'none',
					'type' => 'inverse',
				),
			),
			'ingredients_display' => array(
				'name' => 'Display',
				'default' => 'inline',
				'type' => 'dropdown',
				'options' => array(
					'inline' => 'On one line',
					'separate' => 'On separate lines',
				),
				'dependency' => array(
					'id' => 'ingredients_position',
					'value' => 'none',
					'type' => 'inverse',
				),
			),
			'ingredients_separator' => array(
				'name' => 'Separator',
				'default' => ', ',
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'ingredients_position',
						'value' => 'none',
						'type' => 'inverse',
					),
					array(
						'id' => 'ingredients_display',
						'value' => 'inline',
					),
				),
			),
			'ingredients_show_notes' => array(
				'name' => 'Show Notes',
				'default' => '0',
				'type' => 'toggle',
			),
			'ingredient_notes_separator' => array(
				'name' => 'Notes Separator',
				'default' => 'none',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'None',
					'comma' => 'Comma',
					'dash' => 'Dash',
					'parentheses' => 'Parentheses',
				),
				'dependency' => array(
					array(
						'id' => 'ingredients_position',
						'value' => 'none',
						'type' => 'inverse',
					),
					array(
						'id' => 'ingredients_show_notes',
						'value' => '1',
					),
				),
			),
			'ingredients_unit_conversion_header' => array(
				'type' => 'header',
				'default' => __( 'Unit Conversion', 'wp-recipe-maker' ),
			),
			'ingredients_show_both_units' => array(
				'name' => 'Show Both Units',
				'default' => '0',
				'type' => 'toggle',
			),
			'both_units_style' => array(
				'default' => 'parentheses',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'None',
					'parentheses' => 'Parentheses',
					'slash' => 'Slash',
				),
				'dependency' => array(
					'id' => 'ingredients_show_both_units',
					'value' => '1',
				),
			),
			'both_units_show_if_identical' => array(
				'name' => 'Show if Identical',
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(
					'id' => 'ingredients_show_both_units',
					'value' => '1',
				),
			),
			'instruction_images_header' => array(
				'type' => 'header',
				'default' => __( 'Instruction Images', 'wp-recipe-maker' ),
			),
			'image_size' => array(
				'default' => 'thumbnail',
				'type' => 'image_size',
			),
			'image_border_radius' => array(
				'default' => '0px',
				'type' => 'size',
			),
			'image_alignment' => array(
				'default' => 'left',
				'type' => 'dropdown',
				'options' => array(
					'left' => 'Left',
					'center' => 'Center',
					'right' => 'Right',
				),
			),
			'image_position' => array(
				'default' => 'after',
				'type' => 'dropdown',
				'options' => array(
					'before' => 'Before Text',
					'after' => 'After Text',
				),
			),
			'media_toggle_header' => array(
				'type' => 'header',
				'default' => __( 'Media Toggle', 'wp-recipe-maker' ),
			),
			'media_toggle' => array(
				'default' => '',
				'type' => 'dropdown',
				'options' => array(
					'' => "Don't show",
					'header' => 'Show media toggle in the header',
					'before' => 'Show media toggle before the instructions',
				),
			),
			'toggle_style' => array(
				'default' => 'buttons',
				'type' => 'dropdown',
				'options' => array(
					'buttons' => __( 'Buttons', 'wp-recipe-maker' ),
					'switch' => __( 'Switch', 'wp-recipe-maker' ),
				),
				'dependency' => array(
					'id' => 'media_toggle',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'toggle_text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					'id' => 'media_toggle',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'toggle_button_background' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'toggle_style',
						'value' => 'buttons',
					),
				),
			),
			'toggle_button_accent' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'toggle_style',
						'value' => 'buttons',
					),
				),
			),
			'toggle_button_radius' => array(
				'default' => '3px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'toggle_style',
						'value' => 'buttons',
					),
				),
			),
			'toggle_switch_style' => array(
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
						'id' => 'toggle_style',
						'value' => 'switch',
					),
				),
			),
			'toggle_switch_height' => array(
				'default' => '28px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'toggle_style',
						'value' => 'switch',
					),
				),
			),
			'toggle_switch_off' => array(
				'default' => '#cccccc',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'toggle_style',
						'value' => 'switch',
					),
				),
			),
			'toggle_switch_off_knob' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'toggle_style',
						'value' => 'switch',
					),
				),
			),
			'toggle_switch_off_text' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'toggle_style',
						'value' => 'switch',
					),
				),
			),
			'toggle_switch_on' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'toggle_style',
						'value' => 'switch',
					),
				),
			),
			'toggle_switch_on_knob' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'toggle_style',
						'value' => 'switch',
					),
				),
			),
			'toggle_switch_on_text' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'media_toggle',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'toggle_style',
						'value' => 'switch',
					),
				),
			),
			'toggle_off_icon' => array(
				'default' => 'camera-no',
				'type' => 'icon',
				'dependency' => array(
					'id' => 'media_toggle',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'toggle_off_text' => array(
				'default' => '',
				'type' => 'text',
				'dependency' => array(
					'id' => 'media_toggle',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'toggle_on_icon' => array(
				'default' => 'camera-2',
				'type' => 'icon',
				'dependency' => array(
					'id' => 'media_toggle',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'toggle_on_text' => array(
				'default' => '',
				'type' => 'text',
				'dependency' => array(
					'id' => 'media_toggle',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'prevent_sleep_header' => array(
				'type' => 'header',
				'default' => __( 'Prevent Sleep', 'wp-recipe-maker' ),
			),
			'prevent_sleep' => array(
				'default' => '',
				'type' => 'dropdown',
				'options' => array(
					'' => "Don't show",
					'header' => 'Show prevent sleep toggle in the header',
					'before' => 'Show prevent sleep toggle before the instructions',
					'after' => 'Show prevent sleep toggle after the instructions',
				),
			),
			'prevent_sleep_switch_type' => array(
				'name' => 'Switch Type',
				'default' => 'outside',
				'type' => 'dropdown',
				'options' => array(
					'outside' => 'Label Outside Toggle',
					'inside' => 'Label Inside Toggle',
				),
				'dependency' => array(
					'id' => 'prevent_sleep',
					'value' => '',
					'type' => 'inverse',
				),
			),
			'prevent_sleep_switch_style' => array(
				'name' => 'Switch Style',
				'default' => 'rounded',
				'type' => 'dropdown',
				'options' => array(
					'square' => 'Square Toggle',
					'rounded' => 'Rounded Toggle',
				),
				'dependency' => array(
					'id' => 'prevent_sleep',
					'value' => '',
					'type' => 'inverse',
				),
			),
			// Outside toggle type, backwards compatibility.
			'prevent_sleep_switch_width' => array(
				'name' => 'Switch Width',
				'default' => '40',
				'type' => 'number',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'outside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_switch_inactive' => array(
				'name' => 'Switch Inactive',
				'default' => '#cccccc',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'outside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_switch_inactive_knob' => array(
				'name' => 'Switch Inactive Knob',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'outside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_switch_active' => array(
				'name' => 'Switch Active',
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'outside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_switch_active_knob' => array(
				'name' => 'Switch Active Knob',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'outside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			// Inside toggle type.
			'prevent_sleep_switch_height' => array(
				'name' => 'Switch Height',
				'default' => '28px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'inside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_switch_off' => array(
				'name' => 'Switch Off',
				'default' => '#cccccc',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'inside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_switch_off_knob' => array(
				'name' => 'Switch Off Knob',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'inside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_switch_off_text' => array(
				'name' => 'Switch Off Text',
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'inside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_switch_on' => array(
				'name' => 'Switch On',
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'inside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_switch_on_knob' => array(
				'name' => 'Switch On Knob',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'inside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_switch_on_text' => array(
				'name' => 'Switch On Text',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'inside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_off_icon' => array(
				'name' => 'Off Icon',
				'default' => '',
				'type' => 'icon',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'inside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_off_text' => array(
				'name' => 'Off Text',
				'default' => 'Prevent Sleep Mode',
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'inside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_on_icon' => array(
				'name' => 'On Icon',
				'default' => '',
				'type' => 'icon',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'inside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_on_text' => array(
				'name' => 'On Text',
				'default' => 'Prevent Sleep Mode',
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'inside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_label' => array(
				'name' => 'Label',
				'default' => 'Cook Mode',
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'outside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_label_style' => array(
				'name' => 'Label Style',
				'default' => 'bold',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_switch_type',
						'value' => 'outside',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_description' => array(
				'name' => 'Description',
				'default' => 'Prevent your screen from going dark',
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'prevent_sleep_description_style' => array(
				'name' => 'Description Style',
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					array(
						'id' => 'prevent_sleep_description',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'prevent_sleep',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'cook_mode_header' => array(
				'type' => 'header',
				'default' => __( 'Cook Mode', 'wp-recipe-maker' ),
			),
			'cook_mode' => array(
				'default' => '',
				'type' => 'dropdown',
				'options' => array(
					'' => "Don't show",
					'header' => 'Show cook mode button in the header',
					'before' => 'Show cook mode button before the instructions',
					'after' => 'Show cook mode button after the instructions',
				),
			),
			'cook_mode_style' => array(
				'label' => __( 'Style', 'wp-recipe-maker' ),
				'default' => 'text',
				'type' => 'dropdown',
				'options' => array(
					'text' => 'Text',
					'button' => 'Button',
					'inline-button' => 'Inline Button',
					'wide-button' => 'Full Width Button',
				),
				'dependency' => array(
					array(
						'id' => 'cook_mode',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'cook_mode_icon' => array(
				'label' => __( 'Icon', 'wp-recipe-maker' ),
				'default' => '',
				'type' => 'icon',
				'dependency' => array(
					array(
						'id' => 'cook_mode',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'cook_mode_text' => array(
				'label' => __( 'Text', 'wp-recipe-maker' ),
				'default' => __( 'Start Cooking', 'wp-recipe-maker' ),
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'cook_mode',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'cook_mode_text_style' => array(
				'label' => __( 'Text Style', 'wp-recipe-maker' ),
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					array(
						'id' => 'cook_mode',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'cook_mode_icon_color' => array(
				'label' => __( 'Icon Color', 'wp-recipe-maker' ),
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'cook_mode',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'cook_mode_icon',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'cook_mode_text_color' => array(
				'label' => __( 'Text Color', 'wp-recipe-maker' ),
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'cook_mode',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'cook_mode_text',
						'value' => '',
						'type' => 'inverse',
					),
				),
			),
			'cook_mode_horizontal_padding' => array(
				'label' => __( 'Horizontal Padding', 'wp-recipe-maker' ),
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'cook_mode',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'cook_mode_style',
						'value' => 'text',
						'type' => 'inverse',
					),
				),
			),
			'cook_mode_vertical_padding' => array(
				'label' => __( 'Vertical Padding', 'wp-recipe-maker' ),
				'default' => '5px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'cook_mode',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'cook_mode_style',
						'value' => 'text',
						'type' => 'inverse',
					),
				),
			),
			'cook_mode_button_color' => array(
				'label' => __( 'Button Color', 'wp-recipe-maker' ),
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'cook_mode',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'cook_mode_style',
						'value' => 'text',
						'type' => 'inverse',
					),
				),
			),
			'cook_mode_border_color' => array(
				'label' => __( 'Border Color', 'wp-recipe-maker' ),
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'cook_mode',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'cook_mode_style',
						'value' => 'text',
						'type' => 'inverse',
					),
				),
			),
			'cook_mode_border_radius' => array(
				'label' => __( 'Border Radius', 'wp-recipe-maker' ),
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'cook_mode',
						'value' => '',
						'type' => 'inverse',
					),
					array(
						'id' => 'cook_mode_style',
						'value' => 'text',
						'type' => 'inverse',
					),
				),
			),
			'before_container_header' => array(
				'type' => 'header',
				'default' => __( 'Before Instructions', 'wp-recipe-maker' ),
				'dependency' => array(
					array(
						'id' => 'prevent_sleep',
						'value' => 'before',
					),
					array(
						'id' => 'media_toggle',
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
				'id' => 'prevent_sleep',
				'value' => 'before',
			),
			array(
				'id' => 'media_toggle',
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
		if ( ! $recipe || ! $recipe->instructions() ) {
			return apply_filters( parent::get_hook(), '', $atts, $recipe );
		}

		// Output.
		$classes = array(
			'wprm-recipe-instructions-container',
			'wprm-recipe-' . $recipe->id() .'-instructions-container',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		// Args for optional media toggle and prevent sleep switch.
		$media_toggle_atts = array(
			'id' => $atts['id'],
			'toggle_style' => $atts['toggle_style'],
			'text_style' => $atts['toggle_text_style'],
			'button_background' => $atts['toggle_button_background'],
			'button_accent' => $atts['toggle_button_accent'],
			'button_radius' => $atts['toggle_button_radius'],
			'switch_style' => $atts['toggle_switch_style'],
			'switch_height' => $atts['toggle_switch_height'],
			'switch_off' => $atts['toggle_switch_off'],
			'switch_off_knob' => $atts['toggle_switch_off_knob'],
			'switch_off_text' => $atts['toggle_switch_off_text'],
			'switch_on' => $atts['toggle_switch_on'],
			'switch_on_knob' => $atts['toggle_switch_on_knob'],
			'switch_on_text' => $atts['toggle_switch_on_text'],
			'off_icon' => $atts['toggle_off_icon'],
			'off_text' => $atts['toggle_off_text'],
			'on_icon' => $atts['toggle_on_icon'],
			'on_text' => $atts['toggle_on_text'],
		);

		$prevent_sleep_atts = array(
			'switch_type' => $atts['prevent_sleep_switch_type'],
			'switch_style' => $atts['prevent_sleep_switch_style'],
			'switch_width' => $atts['prevent_sleep_switch_width'],
			'switch_inactive' => $atts['prevent_sleep_switch_inactive'],
			'switch_inactive_knob' => $atts['prevent_sleep_switch_inactive_knob'],
			'switch_active' => $atts['prevent_sleep_switch_active'],
			'switch_active_knob' => $atts['prevent_sleep_switch_active_knob'],
			'switch_height' => $atts['prevent_sleep_switch_height'],
			'switch_off' => $atts['prevent_sleep_switch_off'],
			'switch_off_knob' => $atts['prevent_sleep_switch_off_knob'],
			'switch_off_text' => $atts['prevent_sleep_switch_off_text'],
			'switch_on' => $atts['prevent_sleep_switch_on'],
			'switch_on_knob' => $atts['prevent_sleep_switch_on_knob'],
			'switch_on_text' => $atts['prevent_sleep_switch_on_text'],
			'off_icon' => $atts['prevent_sleep_off_icon'],
			'off_text' => $atts['prevent_sleep_off_text'],
			'on_icon' => $atts['prevent_sleep_on_icon'],
			'on_text' => $atts['prevent_sleep_on_text'],
			'label' => $atts['prevent_sleep_label'],
			'label_style' => $atts['prevent_sleep_label_style'],
			'description' => $atts['prevent_sleep_description'],
			'description_style' => $atts['prevent_sleep_description_style'],
		);

		$cook_mode_atts = array(
			'style' => $atts['cook_mode_style'],
			'icon' => $atts['cook_mode_icon'],
			'text' => $atts['cook_mode_text'],
			'text_style' => $atts['cook_mode_text_style'],
			'icon_color' => $atts['cook_mode_icon_color'],
			'text_color' => $atts['cook_mode_text_color'],
			'horizontal_padding' => $atts['cook_mode_horizontal_padding'],
			'vertical_padding' => $atts['cook_mode_vertical_padding'],
			'button_color' => $atts['cook_mode_button_color'],
			'border_color' => $atts['cook_mode_border_color'],
			'border_radius' => $atts['cook_mode_border_radius'],
		);

		// Custom style.
		$css_variables = 'checkbox' === $atts['list_style'] ? parent::get_inline_css_variables( 'list', $atts, array( 'checkbox_size', 'checkbox_left_position', 'checkbox_top_position', 'checkbox_background', 'checkbox_border_width', 'checkbox_border_style', 'checkbox_border_color', 'checkbox_border_radius', 'checkbox_check_width', 'checkbox_check_color' ) ) : '';
		$style = WPRM_Shortcode_Helper::get_inline_style( $css_variables );

		$output = '<div id="recipe-' . esc_attr( $recipe->id() ) . '-instructions" class="' . esc_attr( implode( ' ', $classes ) ) . '" data-recipe="' . esc_attr( $recipe->id() ) . '"' . $style . '>';
		$output .= WPRM_Shortcode_Helper::get_section_header( $atts, 'instructions', array(
			'media_toggle_atts' => $media_toggle_atts,
			'prevent_sleep_atts' => $prevent_sleep_atts,
			'cook_mode_atts' => $cook_mode_atts,
		) );

		$output_before = '';

		if ( 'before' === $atts['cook_mode'] ) {
			$output_before .= WPRM_SC_Cook_Mode::shortcode( $cook_mode_atts );
		}

		if ( 'before' === $atts['prevent_sleep'] ) {
			$output_before .= WPRM_SC_Prevent_Sleep::shortcode( $prevent_sleep_atts );
		}

		if ( 'before' === $atts['media_toggle'] ) {
			$output_before .= WPRM_SC_Media_Toggle::shortcode( $media_toggle_atts );
		}

		// Output functionality before the instructions, optionally with container.
		if ( $output_before && (bool) $atts['interactivity_container'] ) {
			$output .= WPRM_Shortcode_Helper::get_interactivity_container( $atts, 'instructions-before' );
		}

		$output .= $output_before;

		if ( $output_before && (bool) $atts['interactivity_container'] ) {
			$output .= '</div>';
		}

		if ( (bool) $atts['has_container'] ) {
			$output .= WPRM_Shortcode_Helper::get_internal_container( $atts, 'instructions' );
		}

		$list_tag = 'ol' === $atts['list_tag'] ? 'ol' : 'ul';

		$instructions = $recipe->instructions();
		foreach ( $instructions as $group_index => $instruction_group ) {
			$output .= '<div class="wprm-recipe-instruction-group">';

			if ( $instruction_group['name'] ) {
				$classes = array(
					'wprm-recipe-group-name',
					'wprm-recipe-instruction-group-name',
					'wprm-block-text-' . $atts['group_style'],
				);

				$style = '';
				if ( (bool) $atts['group_custom_color'] ) {
					$style = ' style="color: ' . esc_attr( $atts['group_color'] ) . ';"';
				}

				$tag = WPRM_Shortcode_Helper::sanitize_html_element( $atts['group_tag'] );
				$output .= '<' . $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $style . '>' . $instruction_group['name'] . '</' . $tag . '>';

				if ( '0px' !== $atts['group_bottom_margin'] ) {
					$output .= do_shortcode( '[wprm-spacer size="' . $atts['group_bottom_margin'] . '"]' );
				}
			}

			$output .= '<' . $list_tag . ' class="wprm-recipe-instructions">';

			foreach ( $instruction_group['instructions'] as $index => $instruction ) {
				$list_style_type = 'checkbox' === $atts['list_style'] || 'advanced' === $atts['list_style'] ? 'none' : $atts['list_style'];
				$style = 'list-style-type: ' . $list_style_type . ';';
				
				if ( (bool) $atts['force_item_position'] ) {
					$style .= 'margin-left: ' . esc_attr( $atts['list_item_position'] ) . ';';	
				}

				// Build instruction content
				$instruction_content = '';

				if ( 'before' === $atts['ingredients_position'] ) {
					$instruction_content .= self::instruction_ingredients( $recipe, $instruction, $atts );
				}
				if ( 'before' === $atts['image_position'] ) {
					$instruction_content .= self::instruction_media( $recipe, $instruction, $atts );
				}
				if ( $instruction['text'] ) {
					$text = $instruction['text'];
					$text = self::inline_ingredients( $text, $atts );
					$text = parent::clean_paragraphs( $text );
					$text_style = '';

					if ( '0px' !== $atts['text_margin'] ) {
						$text_style = ' style="margin-bottom: ' . esc_attr( $atts['text_margin'] ) . ';"';
					}

					$instruction_text = '<div class="wprm-recipe-instruction-text"' . $text_style . '>' . $text . '</div>';

					// Output checkbox.
					if ( 'checkbox' === $atts['list_style'] ) {
						$instruction_text = apply_filters( 'wprm_recipe_instructions_shortcode_checkbox', $instruction_text );
					}
					$instruction_content .= $instruction_text;
				}
				if ( 'after' === $atts['ingredients_position'] ) {
					$instruction_content .= self::instruction_ingredients( $recipe, $instruction, $atts );
				}
				if ( 'after' === $atts['image_position'] ) {
					$instruction_content .= self::instruction_media( $recipe, $instruction, $atts );
				}

				// Build the complete <li>...</li> structure
				$li_id = 'wprm-recipe-' . esc_attr( $recipe->id() ) . '-step-' . esc_attr( $group_index ) . '-' . esc_attr( $index );
				$li_attributes = 'id="' . $li_id . '" class="wprm-recipe-instruction" style="' . esc_attr( $style ) . '"';
				$instruction_line = '<li ' . $li_attributes . '>' . $instruction_content . '</li>';
				
				// Apply filter to the complete instruction line
				$instruction_line = apply_filters( 'wprm_recipe_instructions_shortcode_instruction', $instruction_line, $atts, $instruction, $recipe );
				
				$output .= $instruction_line;
			}

			$output .= '</' . $list_tag . '>';
			$output .= '</div>';
		}

		if ( (bool) $atts['has_container'] ) {
			$output .= '</div>';
		}

		if ( 'after' === $atts['prevent_sleep'] ) {
			$output .= WPRM_SC_Prevent_Sleep::shortcode( $prevent_sleep_atts );
		}

		if ( 'after' === $atts['cook_mode'] ) {
			$output .= WPRM_SC_Cook_Mode::shortcode( $cook_mode_atts );
		}

		$output .= '</div>';

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}

	/**
	 * Set attributes for inline ingredients.
	 *
	 * @since	8.7.0
	 * @param	string	$text	Text to check for inline ingredients.
	 * @param	mixed 	$atts	Shortcode attributes.
	 */
	private static function inline_ingredients( $text, $atts ) {
		// Construct attributes to add to inline ingredients shortcode.
		$inline_atts = 'style="' . esc_attr( $atts['inline_text_style'] ) . '"';

		if ( $atts['inline_use_custom_color'] ) {
			$inline_atts .= ' color="' . esc_attr( $atts['inline_custom_color'] ) . '"';
		}

		// Maybe include notes.
		if ( (bool) $atts['inline_show_notes'] ) {
			$inline_atts .= ' notes_separator="' . esc_attr( $atts['inline_notes_separator'] ) . '"';
		}

		// Unit Conversion related.
		$show_both_units = (bool) $atts['ingredients_show_both_units'];
		if ( $show_both_units ) {
			$inline_atts .= ' unit_conversion="both"';
			$inline_atts .= ' unit_conversion_both_style="' . esc_attr( $atts['both_units_style'] ) . '"';
			$inline_atts .= ' unit_conversion_show_identical="' . esc_attr( $atts['both_units_show_if_identical'] ) . '"';
		}

		// Add attributes to potential inline ingredients.
		$text = str_replace( '[wprm-ingredient ', '[wprm-ingredient ' . $inline_atts . ' ', $text );

		return $text;
	}

	/**
	 * Output the associated ingredients.
	 *
	 * @since	7.4.0
	 * @param	mixed $recipe		Recipe to output the instruction for.
	 * @param	mixed $instruction	Instruction to output the ingredients for.
	 * @param	mixed $atts			Shortcode attributes.
	 */
	private static function instruction_ingredients( $recipe, $instruction, $atts ) {
		$output = '';

		if ( isset( $instruction['ingredients'] ) && $instruction['ingredients'] ) {
			$ingredients_to_output = array();
			$ingredients_flat = $recipe->ingredients_flat();

			foreach ( $instruction['ingredients'] as $ingredient ) {
				$ingredient_str = (string) $ingredient;
				$is_split = false;
				$parent_uid = null;
				$split_id = null;
				
				// Check if this is a split (format: "uid:splitId")
				if ( strpos( $ingredient_str, ':' ) !== false ) {
					$is_split = true;
					$parts = explode( ':', $ingredient_str, 2 );
					if ( count( $parts ) === 2 && is_numeric( $parts[0] ) && is_numeric( $parts[1] ) ) {
						$parent_uid = intval( $parts[0] );
						$split_id = intval( $parts[1] );
					} else {
						// Invalid split format, skip
						continue;
					}
				} else {
					// Regular ingredient UID
					$parent_uid = intval( $ingredient );
				}
				
				// Find the parent ingredient
				$index = array_search( $parent_uid, array_column( $ingredients_flat, 'uid' ) );
				
				if ( false !== $index && isset( $ingredients_flat[ $index ] ) ) {
					$found_ingredient = $ingredients_flat[ $index ];

					if ( 'ingredient' === $found_ingredient['type'] ) {
						$parts = array();
						$amount = '';
						$unit = '';
						$output_key = $found_ingredient['uid'];
						$split_percentage = null;
						
						// If it's a split, calculate amount from percentage
						if ( $is_split && isset( $found_ingredient['splits'] ) && is_array( $found_ingredient['splits'] ) ) {
							$found_split = null;
							foreach ( $found_ingredient['splits'] as $split ) {
								if ( isset( $split['id'] ) && intval( $split['id'] ) === $split_id ) {
									$found_split = $split;
									break;
								}
							}
							
							if ( $found_split && isset( $found_split['percentage'] ) ) {
								// Calculate split amount from parent amount and percentage
								$parent_amount = isset( $found_ingredient['amount'] ) ? $found_ingredient['amount'] : '';
								$parent_amount_parsed = WPRM_Recipe_Parser::parse_quantity( $parent_amount );
								
								if ( $parent_amount_parsed > 0 ) {
									$split_percentage = floatval( $found_split['percentage'] );
									$split_amount_parsed = ( $parent_amount_parsed * $split_percentage ) / 100;
									$decimals = intval( WPRM_Settings::get( 'adjustable_servings_round_to_decimals' ) );
									if ( $decimals < 0 ) {
										$decimals = 0;
									}
									
									// Format the calculated amount.
									$amount = WPRM_Recipe_Parser::format_quantity( $split_amount_parsed, $decimals, WPRM_Settings::get( 'fractions_enabled' ), true );
								} else {
									$amount = '';
								}
								
								// Use parent's unit
								$unit = isset( $found_ingredient['unit'] ) ? $found_ingredient['unit'] : '';
								// Use split value as key to avoid conflicts
								$output_key = $ingredient_str;
							} else {
								// Split not found, fall back to parent ingredient
								$amount = isset( $found_ingredient['amount'] ) ? $found_ingredient['amount'] : '';
								$unit = isset( $found_ingredient['unit'] ) ? $found_ingredient['unit'] : '';
							}
						} else {
							// Regular ingredient, use parent's amount and unit
							$amount = isset( $found_ingredient['amount'] ) ? $found_ingredient['amount'] : '';
							$unit = isset( $found_ingredient['unit'] ) ? $found_ingredient['unit'] : '';
						}
						
						if ( $amount ) { $parts[] = $amount; };
						if ( $unit ) { $parts[] = $unit; };

						// Optionally add second unit system.
						$show_both_units = (bool) $atts['ingredients_show_both_units'];
						if ( $show_both_units ) {
							$atts['unit_conversion'] = 'both';
							$atts['unit_conversion_both_style'] = $atts['both_units_style'];
							$atts['unit_conversion_show_identical'] = $atts['both_units_show_if_identical'];
							$ingredient_for_output = $found_ingredient;

							if ( $is_split && null !== $split_percentage ) {
								$ingredient_for_output['amount'] = $amount;
								$ingredient_for_output['unit'] = $unit;

								if ( isset( $ingredient_for_output['converted'] ) && is_array( $ingredient_for_output['converted'] ) ) {
									foreach ( $ingredient_for_output['converted'] as $system => $converted ) {
										if ( isset( $converted['amount'] ) && '' !== $converted['amount'] ) {
											$converted_amount = WPRM_Recipe_Parser::parse_quantity( $converted['amount'] );

											if ( $converted_amount > 0 ) {
												$converted_split = ( $converted_amount * $split_percentage ) / 100;
												// Check system-specific fraction setting, fall back to general setting.
												$allow_fractions = WPRM_Settings::get( 'unit_conversion_system_' . $system . '_fractions' );
												$ingredient_for_output['converted'][ $system ]['amount'] = WPRM_Recipe_Parser::format_quantity( $converted_split, $decimals, $allow_fractions, true );
											} else {
												$ingredient_for_output['converted'][ $system ]['amount'] = '';
											}
										}
									}
								}
							}

							$amount_unit = apply_filters( 'wprm_recipe_ingredients_shortcode_amount_unit', implode( ' ', $parts ), $atts, $ingredient_for_output );
						}

						// Ingredient name and maybe notes.
						$name_with_notes = '';
						if ( $found_ingredient['name'] ) { $name_with_notes = $found_ingredient['name']; };

						if ( (bool) $atts['ingredients_show_notes'] ) {
							if ( $found_ingredient['notes'] ) {
								switch ( $atts['ingredient_notes_separator'] ) {
									case 'comma':
										$name_with_notes .= ', ' . $found_ingredient['notes'];
										break;
									case 'dash':
										$name_with_notes .= ' - ' . $found_ingredient['notes'];
										break;
									case 'parentheses':
										$name_with_notes .= ' (' . $found_ingredient['notes'] . ')';
										break;
									default:
										$name_with_notes .= ' ' . $found_ingredient['notes'];
								}
							}
						}
						$parts[] = $name_with_notes;

						$text_to_show = implode( ' ', $parts );

						if ( $text_to_show ) {
							if ( $show_both_units ) {
								$text_to_show = $amount_unit . ' ' . $found_ingredient['name'];
							}
							$ingredients_to_output[ $output_key ] = $text_to_show;
						}
					}
				}
			}

			if ( $ingredients_to_output ) {
				$classes = array(
					'wprm-recipe-instruction-ingredients',
					'wprm-recipe-instruction-ingredients-' . esc_attr( $atts['ingredients_display'] ),
					'wprm-block-text-' . esc_attr( $atts['ingredients_text_style'] ),
				);

				$style = '';
				if ( 'after' === $atts['ingredients_position'] && '0px' !== $atts['text_margin'] ) {
					$style = ' style="margin-top: -' . esc_attr( $atts['text_margin'] ) . '; margin-bottom: ' . esc_attr( $atts['text_margin'] ) . ';"';
				}

				$i = 0;
				$output .= '<div class="'. esc_attr( implode( ' ', $classes ) ) . '"' . $style . '>';
				$tag = 'inline' === $atts['ingredients_display'] ? 'span' : 'div';

				foreach ( $ingredients_to_output as $key => $text ) {
					// For CSS class, use the UID part (for splits, extract the UID from "uid:splitId")
					$uid_for_class = $key;
					$is_split = false;
					$split_data_attr = '';
					
					if ( is_string( $key ) && strpos( $key, ':' ) !== false ) {
						$parts = explode( ':', $key, 2 );
						$uid_for_class = isset( $parts[0] ) ? $parts[0] : $key;
						$is_split = true;
						
						// Find the split percentage to store in data attribute for adjustable servings
						$parent_uid = intval( $parts[0] );
						$split_id = intval( $parts[1] );
						$index = array_search( $parent_uid, array_column( $ingredients_flat, 'uid' ) );
						
						if ( false !== $index && isset( $ingredients_flat[ $index ] ) ) {
							$found_ingredient = $ingredients_flat[ $index ];
							if ( isset( $found_ingredient['splits'] ) && is_array( $found_ingredient['splits'] ) ) {
								foreach ( $found_ingredient['splits'] as $split ) {
									if ( isset( $split['id'] ) && intval( $split['id'] ) === $split_id && isset( $split['percentage'] ) ) {
										// Store split percentage for adjustable servings calculation
										$split_percentage = floatval( $split['percentage'] );
										$split_data_attr = ' data-split-percentage="' . esc_attr( $split_percentage ) . '" data-split-id="' . esc_attr( $split_id ) . '"';
										break;
									}
								}
							}
						}
					}
					
					$classes = array(
						'wprm-recipe-instruction-ingredient',
						'wprm-recipe-instruction-ingredient-' . esc_attr( $recipe->id() ) . '-' . esc_attr( $uid_for_class ),
					);

					$style = '';
					if ( '0px' !== $atts['ingredients_text_margin'] ) {
						$style = ' style="margin-bottom: ' . esc_attr( $atts['ingredients_text_margin'] ) . ';"';
					}

					// Optional separator, if not last item.
					$separator = '';
					if ( $i + 1 !== count( $ingredients_to_output ) ) {
						if ( 'inline' === $atts['ingredients_display'] ) {
							$separator = $atts['ingredients_separator'];
						}
					}

					// Keep notes?
					$data_keep_notes = '';

					if ( (bool) $atts['ingredients_show_notes'] ) {
						$data_keep_notes = ' data-notes-separator="' . esc_attr( $atts['ingredient_notes_separator'] ) . '"';
					}

					// Output.
					$output .= '<' . $tag . ' class="'. esc_attr( implode( ' ', $classes ) ) . '" data-separator="' . esc_attr( $separator ) . '"' . $data_keep_notes . $split_data_attr . $style . '>';
					$output .= wp_strip_all_tags( $text );
					$output .= $separator;

					$output .= '</' . $tag . '>';

					$i++;
				}

				$output .= '</div>';
			}
		}

		return $output;
	}

	/**
	 * Output the instruction media.
	 *
	 * @since	5.11.0
	 * @param	mixed $recipe		Recipe to output the instruction for.
	 * @param	mixed $instruction	Instruction to output the media for.
	 * @param	mixed $atts			Shortcode attributes.
	 */
	private static function instruction_media( $recipe, $instruction, $atts ) {
		$output = '';

		if ( isset( $instruction['image'] ) && $instruction['image'] ) {
			$style = '';
			if ( 'left' !== $atts['image_alignment'] ) {
				$style = 'text-align: ' . $atts['image_alignment'] . ';';
			}

			if ( $style ) {
				$style = ' style="' . esc_attr( $style ) . '"';
			}

			$output = '<div class="wprm-recipe-instruction-media wprm-recipe-instruction-image"' . $style . '>' . self::instruction_image( $recipe, $instruction, $atts['image_size'], $atts['image_border_radius'] ) . '</div> ';
		} else if ( isset( $instruction['video'] ) && isset( $instruction['video']['type'] ) && in_array( $instruction['video']['type'], array( 'upload', 'embed' ) ) ) {
			$output = '<div class="wprm-recipe-instruction-media wprm-recipe-instruction-video">' . self::instruction_video( $recipe, $instruction ) . '</div> ';
		}

		return $output;
	}

	/**
	 * Output an instruction image.
	 *
	 * @since	3.3.0
	 * @param	mixed $recipe			  Recipe to output the instruction for.
	 * @param	mixed $instruction		  Instruction to output the image for.
	 * @param	mixed $default_image_size Default image size to use.
	 */
	private static function instruction_image( $recipe, $instruction, $default_image_size, $border_radius = false ) {
		$settings_size = 'legacy' === WPRM_Settings::get( 'recipe_template_mode' ) ? WPRM_Settings::get( 'template_instruction_image' ) : false;
		$size = $settings_size ? $settings_size : $default_image_size;
		$force_size = false;

		preg_match( '/^(\d+)x(\d+)(\!?)$/i', $size, $match );
		if ( ! empty( $match ) ) {
			$size = array( intval( $match[1] ), intval( $match[2] ) );
			$force_size = isset( $match[3] ) && '!' === $match[3];
		}

		$thumbnail_size = WPRM_Shortcode_Helper::get_thumbnail_image_size( $instruction['image'], $size, $force_size );
		$img = wp_get_attachment_image( $instruction['image'], $thumbnail_size );

		// Maybe add border radius.
		$style = '';
		if ( false !== $border_radius && '0px' !== $border_radius ) {
			$style = 'border-radius: ' . $border_radius . ';';
		}

		// Prevent instruction image from getting stretched in Gutenberg preview.
		if ( WPRM_Context::is_gutenberg_preview() ) {
			$image_data = wp_get_attachment_image_src( $instruction['image'], $thumbnail_size );
			if ( $image_data[1] ) {
				$style .= 'max-width: ' . $image_data[1] . 'px;';
			}
		}

		// Maybe force image size.
		if ( $force_size ) {
			$style .= WPRM_Shortcode_Helper::get_force_image_size_style( $size );
		}

		// Add inline CSS to img.
		$img = WPRM_Shortcode_Helper::add_inline_style( $img, $style );

		// Prevent lazy image loading on print page.
		if ( 'print' === WPRM_Context::get() ) {
			$img = str_ireplace( ' class="', ' class="skip-lazy ', $img );
		}

		// Disable instruction image pinning.
		if ( WPRM_Settings::get( 'pinterest_nopin_instruction_image' ) ) {
			$img = str_ireplace( '<img ', '<img data-pin-nopin="true" ', $img );
		}

		// Clickable images (but not in Gutenberg Preview).
		if ( WPRM_Settings::get( 'instruction_image_clickable' ) && ! WPRM_Context::is_gutenberg_preview() ) {
			$settings_size = WPRM_Settings::get( 'clickable_image_size' );

			preg_match( '/^(\d+)x(\d+)$/i', $settings_size, $match );
			if ( ! empty( $match ) ) {
				$size = array( intval( $match[1] ), intval( $match[2] ) );
			} else {
				$size = $settings_size;
			}

			$clickable_image = wp_get_attachment_image_src( $instruction['image'], $size );
			$clickable_image_url = $clickable_image && isset( $clickable_image[0] ) ? $clickable_image[0] : '';
			if ( $clickable_image_url ) {
				$img = '<a href="' . esc_url( $clickable_image_url ) . '" aria-label="' . __( 'Open larger version of the instruction image', 'wp-recipe-maker' ) . '">' . $img . '</a>';
			}
		}

		return $img;
	}
	
	/**
	 * Output an instruction video.
	 *
	 * @since	3.11.0
	 * @param	mixed $recipe		Recipe to output the instruction for.
	 * @param	mixed $instruction	Instruction to output the video for.
	 */
	private static function instruction_video( $recipe, $instruction ) {
		$output = '';

		if ( 'upload' === $instruction['video']['type'] ) {
			$video_id = $instruction['video']['id'];

			if ( $video_id ) {
				$video_data = wp_get_attachment_metadata( $video_id );
				$video_url = wp_get_attachment_url( $video_id );

				// Construct video shortcode.
				$output = '[video';
				$output .= ' width="' . $video_data['width'] . '"';
				$output .= ' height="' . $video_data['height'] . '"';

				if ( in_array( WPRM_Settings::get( 'video_autoplay' ), array( 'instruction', 'all' ) ) ) { $output .= ' autoplay="true"'; }
				if ( in_array( WPRM_Settings::get( 'video_loop' ), array( 'instruction', 'all' ) ) ) { $output .= ' loop="true"'; }
	
				$format = isset( $video_data['fileformat'] ) && $video_data['fileformat'] ? $video_data['fileformat'] : 'mp4';
				$output .= ' ' . $format . '="' . $video_url . '"';
	
				$thumb_size = array( $video_data['width'], $video_data['height'] );

				// Get thumb URL.
				$image_id = get_post_thumbnail_id( $video_id );
				$thumb = wp_get_attachment_image_src( $image_id, $thumb_size );
				$thumb_url = $thumb && isset( $thumb[0] ) ? $thumb[0] : '';
	
				if ( $thumb_url ) {
					$output .= ' poster="' . $thumb_url . '"';
				}
	
				$output .= '][/video]';
			}
		} else if ( 'embed' === $instruction['video']['type'] ) {
			$video_embed = $instruction['video']['embed'];

			if ( $video_embed ) {	
				// Check if it's a regular URL.
				$url = filter_var( $video_embed, FILTER_SANITIZE_URL );
	
				if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
					global $wp_embed;
	
					if ( isset( $wp_embed ) ) {
						$output = $wp_embed->run_shortcode( '[embed]' . $url . '[/embed]' );
					}
				} else {
					$output = $video_embed;
				}
			}
		}

		return do_shortcode( $output );
	}
}

WPRM_SC_Instructions::init();