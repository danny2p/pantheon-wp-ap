<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.8.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$template_legacy = array(
	'id' => 'templateLegacy',
	'icon' => 'doc',
	'name' => __( 'Recipe Template', 'wp-recipe-maker' ),
	'dependency' => array(
		'id' => 'recipe_template_mode',
		'value' => 'legacy',
	),
	'settings' => array(
		array(
			'id' => 'default_recipe_template',
			'name' => __( 'Default Recipe Template', 'wp-recipe-maker' ),
			'description' => __( 'Default template to use for the recipes on your website.', 'wp-recipe-maker' ),
			'type' => 'dropdownTemplateLegacy',
			'default' => 'simple',
		),
	),
	'subGroups' => array(
		array(
			'name' => __( 'Template Options', 'wp-recipe-maker' ),
			'description' => __( 'Note: not all options will affect every recipe template.', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'template_font_size',
					'name' => __( 'Base Font Size', 'wp-recipe-maker' ),
					'description' => __( 'Leave blank to use the template default.', 'wp-recipe-maker' ),
					'type' => 'number',
					'suffix' => 'px',
					'default' => '',
					'dependency' => array(
						'id' => 'features_custom_style',
						'value' => true,
					),
				),
				array(
					'id' => 'template_font_header',
					'name' => __( 'Header Font', 'wp-recipe-maker' ),
					'description' => __( "Type the name of the font you'd like to use. Make sure the font is already loaded.", 'wp-recipe-maker' ) . ' ' . __( 'Leave blank to use the template default.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
					'dependency' => array(
						'id' => 'features_custom_style',
						'value' => true,
					),
				),
				array(
					'id' => 'template_font_regular',
					'name' => __( 'Regular Font', 'wp-recipe-maker' ),
					'description' => __( "Type the name of the font you'd like to use. Make sure the font is already loaded.", 'wp-recipe-maker' ) . ' ' . __( 'Leave blank to use the template default.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
					'dependency' => array(
						'id' => 'features_custom_style',
						'value' => true,
					),
				),
				array(
					'id' => 'template_recipe_image',
					'name' => __( 'Recipe Image Size', 'wp-recipe-maker' ),
					'description' => __( 'Leave blank to use the template default.', 'wp-recipe-maker' ) . ' ' . __( 'Type the name of a thumbnail size or the exact size you want.', 'wp-recipe-maker' ) . ' ' . __( 'For example:', 'wp-recipe-maker' ) . ' thumbnail or 200x200',
					'type' => 'text',
					'default' => '',
				),
				array(
					'id' => 'template_instruction_image',
					'name' => __( 'Instruction Image Size', 'wp-recipe-maker' ),
					'description' => __( 'Leave blank to use the template default.', 'wp-recipe-maker' ) . ' ' . __( 'Type the name of a thumbnail size or the exact size you want.', 'wp-recipe-maker' ) . ' ' . __( 'For example:', 'wp-recipe-maker' ) . ' thumbnail or 200x200',
					'type' => 'text',
					'default' => '',
				),
				array(
					'id' => 'template_instruction_image_alignment',
					'name' => __( 'Instruction Image Alignment', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'left' => __( 'Left', 'wp-recipe-maker' ),
						'center' => __( 'Center', 'wp-recipe-maker' ),
						'right' => __( 'Right', 'wp-recipe-maker' ),
					),
					'default' => 'left',
					'dependency' => array(
						'id' => 'features_custom_style',
						'value' => true,
					),
				),
				array(
					'id' => 'template_ingredient_list_style',
					'name' => __( 'Ingredient List Style', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'none' => __( 'None', 'wp-recipe-maker' ),
						'checkbox' => __( 'Checkbox', 'wp-recipe-maker' ) . $premium_only,
						'circle' => __( 'Circle', 'wp-recipe-maker' ),
						'disc' => __( 'Disc', 'wp-recipe-maker' ),
						'square' => __( 'Square', 'wp-recipe-maker' ),
						'decimal' => __( 'Decimal', 'wp-recipe-maker' ),
						'decimal-leading-zero' => __( 'Decimal with leading zero', 'wp-recipe-maker' ),
						'lower-roman' => __( 'Lower Roman', 'wp-recipe-maker' ),
						'upper-roman' => __( 'Upper Roman', 'wp-recipe-maker' ),
						'lower-latin' => __( 'Lower Latin', 'wp-recipe-maker' ),
						'upper-latin' => __( 'Upper Latin', 'wp-recipe-maker' ),
						'lower-greek' => __( 'Lower Greek', 'wp-recipe-maker' ),
						'armenian' => __( 'Armenian', 'wp-recipe-maker' ),
						'georgian' => __( 'Georgian', 'wp-recipe-maker' ),
					),
					'default' => 'disc',
					'dependency' => array(
						'id' => 'features_custom_style',
						'value' => true,
					),
				),
				array(
					'id' => 'template_instruction_list_style',
					'name' => __( 'Instruction List Style', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'none' => __( 'None', 'wp-recipe-maker' ),
						'checkbox' => __( 'Checkbox', 'wp-recipe-maker' ) . $premium_only,
						'circle' => __( 'Circle', 'wp-recipe-maker' ),
						'disc' => __( 'Disc', 'wp-recipe-maker' ),
						'square' => __( 'Square', 'wp-recipe-maker' ),
						'decimal' => __( 'Decimal', 'wp-recipe-maker' ),
						'decimal-leading-zero' => __( 'Decimal with leading zero', 'wp-recipe-maker' ),
						'lower-roman' => __( 'Lower Roman', 'wp-recipe-maker' ),
						'upper-roman' => __( 'Upper Roman', 'wp-recipe-maker' ),
						'lower-latin' => __( 'Lower Latin', 'wp-recipe-maker' ),
						'upper-latin' => __( 'Upper Latin', 'wp-recipe-maker' ),
						'lower-greek' => __( 'Lower Greek', 'wp-recipe-maker' ),
						'armenian' => __( 'Armenian', 'wp-recipe-maker' ),
						'georgian' => __( 'Georgian', 'wp-recipe-maker' ),
					),
					'default' => 'decimal',
					'dependency' => array(
						'id' => 'features_custom_style',
						'value' => true,
					),
				),
			),
		),
		array(
			'name' => __( 'Template Colors', 'wp-recipe-maker' ),
			'dependency' => array(
				array(
					'id' => 'features_custom_style',
					'value' => true,
				),
			),
			'settings' => array(
				array(
					'id' => 'template_color_background',
					'name' => __( 'Background Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#ffffff',
				),
				array(
					'id' => 'template_color_border',
					'name' => __( 'Border Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#aaaaaa',
				),
				array(
					'id' => 'template_color_text',
					'name' => __( 'Text Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#333333',
				),
				array(
					'id' => 'template_color_link',
					'name' => __( 'Link Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#3498db',
				),
				array(
					'id' => 'template_color_header',
					'name' => __( 'Header Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#000000',
				),
				array(
					'id' => 'template_color_icon',
					'name' => __( 'Icon Color', 'wp-recipe-maker' ),
					'description' => __( 'Used for the color of the star ratings and other icons.', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#343434',
				),
				array(
					'id' => 'template_color_accent',
					'name' => __( 'Accent Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#2c3e50',
				),
				array(
					'id' => 'template_color_accent_text',
					'name' => __( 'Accent Text Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#ffffff',
				),
				array(
					'id' => 'template_color_accent2',
					'name' => __( 'Accent 2 Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#3498db',
				),
				array(
					'id' => 'template_color_accent2_text',
					'name' => __( 'Accent 2 Text Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#ffffff',
				),
			),
		),
	),
);

if ( ! $premium_active ) {
	$template_legacy['description'] = __( 'Get access to more recipe templates with WP Recipe Maker Premium.', 'wp-recipe-maker' );
	$template_legacy['documentation'] = 'https://help.bootstrapped.ventures/article/53-template-editor';
}
