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

$template_editor = array(
	'id' => 'templateEditor',
	'icon' => 'crane',
	'name' => __( 'Template Editor', 'wp-recipe-maker' ),
	'description' => __( 'Use the Template Editor to manage and customize all templates on your website. Use it to alter the recipe box, recipe snippets, print version, ... to match your style!', 'wp-recipe-maker' ),
	'dependency' => array(
		'id' => 'recipe_template_mode',
		'value' => 'modern',
	),
	'settings' => array(
		array(
			'name' => __( 'Template Editor', 'wp-recipe-maker' ),
			'documentation' => 'https://help.bootstrapped.ventures/article/53-template-editor',
			'type' => 'button',
			'button' => __( 'Open the Template Editor', 'wp-recipe-maker' ),
			'link' => admin_url( 'admin.php?page=wprm_template_editor' ),
		),
		array(
			'id' => 'template_editor_preview_recipe',
			'name' => __( 'Default Preview Recipe', 'wp-recipe-maker' ),
			'description' => __( 'Default recipe to use for the Template Editor preview.', 'wp-recipe-maker' ),
			'type' => 'dropdownRecipe',
			'options' => array(
				'demo' => __( 'Use WPRM Demo Recipe'),
			),
			'default' => 'demo',
		),
	),
);