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

$recipe_template = array(
	'id' => 'recipeTemplate',
	'icon' => 'doc',
	'name' => __( 'Recipe Template', 'wp-recipe-maker' ),
	'dependency' => array(
		'id' => 'recipe_template_mode',
		'value' => 'modern',
	),
	'subGroups' => array(
		array(
			'settings' => array(
				array(
					'id' => 'recipe_template_show_types',
					'name' => __( 'Show non-food recipe types', 'wp-recipe-maker' ),
					'description' => __( 'Enable to change the templates for how-to instruction and other recipes as well as food recipes.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'recipe_template_show_advanced',
					'name' => __( 'Show advanced options', 'wp-recipe-maker' ),
					'description' => __( 'Enable to change the templates for archive, AMP or RSS feed pages.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
		array(
			'name' => __( 'Default Recipe Template', 'wp-recipe-maker' ),
			'description' => __( 'Fully customize these templates in the Template Editor.', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'default_recipe_template_modern',
					'name' => __( 'Food Recipe Template', 'wp-recipe-maker' ),
					'description' => __( 'Default template to use for the food recipes on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'default' => 'chic',
				),
				array(
					'id' => 'default_howto_recipe_template_modern',
					'name' => __( 'How-to Instructions Template', 'wp-recipe-maker' ),
					'description' => __( 'Default template to use for the how-to instructions on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'default' => 'compact-howto',
					'dependency' => array(
						'id' => 'recipe_template_show_types',
						'value' => true,
					),
				),
				array(
					'id' => 'default_other_recipe_template_modern',
					'name' => __( 'Other Recipe Template', 'wp-recipe-maker' ),
					'description' => __( 'Default template to use for the "other (no metadata)" recipes on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'default' => 'chic',
					'dependency' => array(
						'id' => 'recipe_template_show_types',
						'value' => true,
					),
				),
			),
		),
		array(
			'name' => __( 'Advanced Template Options', 'wp-recipe-maker' ),
			'description' => __( 'Use these settings to change how the recipe looks in other parts of your website:', 'wp-recipe-maker' ),
			'dependency' => array(
				'id' => 'recipe_template_show_advanced',
				'value' => true,
			),
			'settings' => array(
				array(
					'id' => 'default_recipe_archive_template',
					'name' => __( 'Archive Template', 'wp-recipe-maker' ),
					'description' => __( 'Default template to use in archives (like home and category pages).', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'options' => array(
						'default_recipe_template' => __( 'Use same as Default Recipe Template', 'wp-recipe-maker' ),
					),
					'default' => 'default_recipe_template',
				),
				array(
					'id' => 'default_recipe_amp_template',
					'name' => __( 'AMP Template', 'wp-recipe-maker' ),
					'description' => __( 'Default template to use for AMP pages.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'options' => array(
						'default_recipe_template' => __( 'Use same as Default Recipe Template', 'wp-recipe-maker' ),
					),
					'default' => 'basic',
				),
				array(
					'id' => 'default_recipe_feed_template',
					'name' => __( 'RSS Feed Template', 'wp-recipe-maker' ),
					'description' => __( 'Default template to use for RSS feeds.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'options' => array(
						'default_recipe_template' => __( 'Use same as Default Recipe Template', 'wp-recipe-maker' ),
					),
					'default' => 'basic',
				),
			),
		),
	),
);

if ( ! $premium_active ) {
	$recipe_template['description'] = __( 'Get access to more recipe templates with WP Recipe Maker Premium.', 'wp-recipe-maker' );
	$recipe_template['documentation'] = 'https://help.bootstrapped.ventures/article/53-template-editor';
}
