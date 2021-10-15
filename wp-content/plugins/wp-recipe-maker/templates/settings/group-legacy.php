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

$legacy = array(
	'id' => 'legacy',
	'icon' => 'undo',
	'name' => __( 'Legacy Settings', 'wp-recipe-maker' ),
	'description' => __( 'These settings are around for backwards compatibility only. We highly recommend leaving them to their default values.', 'wp-recipe-maker' ),
	'settings' => array(
		array(
			'id' => 'recipe_template_mode',
			'name' => __( 'Template Mode', 'wp-recipe-maker' ),
			'description' => __( 'The "Modern" template mode is highly recommended. Use "Legacy" for backwards compatibility only.', 'wp-recipe-maker' ),
			'type' => 'dropdown',
			'options' => array(
				'legacy' => __( 'Legacy', 'wp-recipe-maker' ),
				'modern' => __( 'Modern', 'wp-recipe-maker' ),
			),
			'default' => 'modern',
		),
	),
);
