<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       https://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$pdf_download = array(
	'id' => 'pdfDownload',
	'required' => 'premium',
	'icon' => 'floppy-disk',
	'name' => __( 'PDF Download', 'wp-recipe-maker' ),
	'description' => __( 'Allow visitors to download a PDF version of the recipe with one click.', 'wp-recipe-maker' ),
	'documentation' => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/download-pdf-button/',
	'dependency' => array(
		'id' => 'recipe_template_mode',
		'value' => 'modern',
	),
	'subGroups' => array(
		array(
			'name' => __( 'Functionality', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'pdf_download_enabled',
					'name' => __( 'Enable PDF Download', 'wp-recipe-maker' ),
					'description' => __( 'Enable to allow visitors to download PDF versions of recipes.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
		array(
			'name' => __( 'Default PDF Template', 'wp-recipe-maker' ),
			'description' => __( 'Fully customize these templates in the Template Editor.', 'wp-recipe-maker' ),
			'dependency' => array(
				'id' => 'pdf_download_enabled',
				'value' => true,
			),
			'settings' => array(
				array(
					'id' => 'default_pdf_template_modern',
					'name' => __( 'Food Recipe PDF Template', 'wp-recipe-maker' ),
					'description' => __( 'Default PDF template to use for the food recipes on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'options' => array(
						'default_print_template' => __( 'Use same as Default Print Template', 'wp-recipe-maker' ),
					),
					'default' => 'default_print_template',
				),
				array(
					'id' => 'default_howto_pdf_template_modern',
					'name' => __( 'How-to Instructions PDF Template', 'wp-recipe-maker' ),
					'description' => __( 'Default PDF template to use for the how-to instructions on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'options' => array(
						'default_print_template' => __( 'Use same as Default Print Template', 'wp-recipe-maker' ),
					),
					'default' => 'default_print_template',
					'dependency' => array(
						'id' => 'recipe_template_show_types',
						'value' => true,
					),
				),
				array(
					'id' => 'default_other_pdf_template_modern',
					'name' => __( 'Other Recipe PDF Template', 'wp-recipe-maker' ),
					'description' => __( 'Default PDF template to use for the "other (no metadata)" recipes on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'options' => array(
						'default_print_template' => __( 'Use same as Default Print Template', 'wp-recipe-maker' ),
					),
					'default' => 'default_print_template',
					'dependency' => array(
						'id' => 'recipe_template_show_types',
						'value' => true,
					),
				),
			),
		),
	),
);
