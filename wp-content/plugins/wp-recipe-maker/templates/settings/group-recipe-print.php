<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$recipe_print = array(
	'id' => 'recipePrint',
	'icon' => 'printer',
	'name' => __( 'Print Version', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'Default Print Template', 'wp-recipe-maker' ),
			'description' => __( 'Fully customize these templates in the Template Editor.', 'wp-recipe-maker' ),
			'dependency' => array(
				'id' => 'recipe_template_mode',
				'value' => 'modern',
			),
			'settings' => array(
				array(
					'id' => 'default_print_template_modern',
					'name' => __( 'Food Recipe Print Template', 'wp-recipe-maker' ),
					'description' => __( 'Default print template to use for the food recipes on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'options' => array(
						'default_recipe_template' => __( 'Use same as Default Recipe Template', 'wp-recipe-maker' ),
					),
					'default' => 'default_recipe_template',
				),
				array(
					'id' => 'default_howto_print_template_modern',
					'name' => __( 'How-to Instructions Print Template', 'wp-recipe-maker' ),
					'description' => __( 'Default print template to use for the how-to instructions on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'options' => array(
						'default_recipe_template' => __( 'Use same as Default Recipe Template', 'wp-recipe-maker' ),
					),
					'default' => 'default_recipe_template',
					'dependency' => array(
						'id' => 'recipe_template_show_types',
						'value' => true,
					),
				),
				array(
					'id' => 'default_other_print_template_modern',
					'name' => __( 'Other Recipe Print Template', 'wp-recipe-maker' ),
					'description' => __( 'Default print template to use for the "other (no metadata)" recipes on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'options' => array(
						'default_recipe_template' => __( 'Use same as Default Recipe Template', 'wp-recipe-maker' ),
					),
					'default' => 'default_recipe_template',
					'dependency' => array(
						'id' => 'recipe_template_show_types',
						'value' => true,
					),
				),
			),
		),
		array(
			'name' => __( 'Appearance', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'default_print_template',
					'name' => __( 'Default Print Template', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateLegacy',
					'default' => 'clean',
					'dependency' => array(
						'id' => 'recipe_template_mode',
						'value' => 'legacy',
					),
				),
				array(
					'id' => 'print_accent_color',
					'name' => __( 'Accent Color', 'wp-recipe-maker' ),
					'description' => __( 'Should work as a background for white text', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#444444',
				),
				array(
					'id' => 'print_remove_links',
					'name' => __( 'Remove links', 'wp-recipe-maker' ),
					'description' => __( 'Remove any links inside the recipe.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'print_recipe_page_break',
					'name' => __( 'Every recipe on its own page', 'wp-recipe-maker' ),
					'description' => __( 'Try to force a page break after every recipe when printing.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'print_credit_use_html',
					'name' => __( 'Use HTML for Print Credit', 'wp-recipe-maker' ),
					'description' => __( 'Enable for an advanced HTML editor for the Print Credit field.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'print_credit',
					'name' => __( 'Print Credit', 'wp-recipe-maker' ),
					'description' => __( 'Optional text to show at the bottom of the print page. You can use the following placeholders:', 'wp-recipe-maker' ) . ' %recipe_name% %recipe_url% %recipe_date%',
					'type' => 'richTextarea',
					'default' => '',
					'dependency' => array(
						'id' => 'print_credit_use_html',
						'value' => false,
					),
				),
				array(
					'id' => 'print_credit_html',
					'name' => __( 'Print Credit', 'wp-recipe-maker' ),
					'description' => __( 'Optional text to show at the bottom of the print page. You can use HTML code and the following placeholders:', 'wp-recipe-maker' ) . ' %recipe_name% %recipe_url% %recipe_date%',
					'type' => 'code',
					'code' => 'html',
					'default' => '',
					'dependency' => array(
						'id' => 'print_credit_use_html',
						'value' => true,
					),
				),
				array(
					'id' => 'print_footer_ad',
					'name' => __( 'Print Footer Ad', 'wp-recipe-maker' ),
					'description' => __( 'Optional ad to show in the footer of the print page. Does not get printed. Use any HTML code.', 'wp-recipe-maker' ),
					'type' => 'code',
					'code' => 'html',
					'default' => '',
				),
			),
		),
		array(
			'name' => __( 'Functionality', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'print_email_link_button',
					'name' => __( 'Email Link Button', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				// array(
				// 	'id' => 'print_download_pdf_button',
				// 	'name' => __( 'Download PDF Button', 'wp-recipe-maker' ),
				// 	'description' => __( 'This is an experimental feature and might not look as expected with every recipe template.', 'wp-recipe-maker' ),
				// 	'type' => 'toggle',
				// 	'default' => false,
				// ),
				array(
					'id' => 'print_show_recipe_image',
					'name' => __( 'Default Show Recipe Image', 'wp-recipe-maker' ),
					'description' => __( 'Default value for the checkbox that allows visitors to toggle the image.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'print_show_instruction_images',
					'name' => __( 'Default Show Instruction Images', 'wp-recipe-maker' ),
					'description' => __( 'Default value for the checkbox that allows visitors to toggle the image.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'print_show_notes',
					'name' => __( 'Default Show Recipe Notes', 'wp-recipe-maker' ),
					'description' => __( 'Default value for the checkbox that allows visitors to toggle the recipe notes.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'print_show_nutrition',
					'name' => __( 'Default Show Nutrition', 'wp-recipe-maker' ),
					'description' => __( 'Default value for the checkbox that allows visitors to toggle the nutrition facts.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'print_adjustable_servings',
					'required' => 'premium',
					'name' => __( 'Adjustable Servings', 'wp-recipe-maker' ),
					'description' => __( 'Allow visitors to change servings on the print page.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'print_unit_conversion',
					'required' => 'pro',
					'name' => __( 'Unit conversion', 'wp-recipe-maker' ),
					'description' => __( 'Allow visitors to change unit systems (if set) on the print page.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'print_size_options',
					'name' => __( 'Size Options', 'wp-recipe-maker' ),
					'description' => __( 'Allow visitors to change the print size.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
			),
		),
		array(
			'name' => __( 'Advanced', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'print_new_tab',
					'name' => __( 'Open Print links in New Tab', 'wp-recipe-maker' ),
					'description' => __( 'Enable to open links to the print page in a new tab.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'print_slug',
					'name' => __( 'Print Slug', 'wp-recipe-maker' ),
					'description' => __( 'Slug used in the URL for print pages. Make sure there is no conflict with other pages!', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => 'wprm_print',
					'sanitize' => function( $value ) {
						return preg_replace( '/[^a-z0-9-_]/i', '', $value );
					},
				),
				array(
					'id' => 'metadata_pinterest_disable_print_page',
					'name' => __( 'Disable pinning on print page', 'wp-recipe-maker' ),
					'description' => __( 'Enable this setting if you want to prevent people from pinning your print page to Pinterest.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'print_page_redirect',
					'name' => __( 'Redirect to parent post', 'wp-recipe-maker' ),
					'description' => __( 'Force a redirect to the parent post of the recipe if someone lands directly on the print page.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
	),
);
