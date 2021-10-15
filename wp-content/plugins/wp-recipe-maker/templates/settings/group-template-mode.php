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

$template_mode = array(
	'id' => 'templateMode',
	'icon' => 'warning',
	'name' => __( 'Template Mode', 'wp-recipe-maker' ),
	'dependency' => array(
		'id' => 'recipe_template_mode',
		'value' => 'legacy',
	),
	'settings' => array(
		array(
			'type' => 'button',
			'description' => __( 'The "Modern" template mode is highly recommended. Use "Legacy" for backwards compatibility only.', 'wp-recipe-maker' ) . ' ' . __( 'The "Template Editor" feature can only work in "Modern" mode.', 'wp-recipe-maker' ),
			'button' => __( 'Learn more in the Migration Guide', 'wp-recipe-maker' ),
			'link' => 'https://help.bootstrapped.ventures/article/111-migrating-from-legacy-to-modern-mode',
		),
		array(
			'id' => 'recipe_template_mode',
			'name' => __( 'Template Mode', 'wp-recipe-maker' ),
			'type' => 'dropdown',
			'options' => array(
				'legacy' => __( 'Legacy', 'wp-recipe-maker' ),
				'modern' => __( 'Modern', 'wp-recipe-maker' ),
			),
			'default' => 'modern',
		),
	),
);
