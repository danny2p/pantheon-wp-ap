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

$custom_style = array(
	'id' => 'customStyle',
	'icon' => 'brush',
	'name' => __( 'Custom Style', 'wp-recipe-maker' ),
	'settings' => array(
		array(
			'id' => 'features_custom_style',
			'name' => __( 'Use Custom Styling', 'wp-recipe-maker' ),
			'description' => __( "Disable if you don't want to output inline CSS.", 'wp-recipe-maker' ) . ' ' . __( 'If you do so, styling changes will have to be made elsewhere and not from this settings page.', 'wp-recipe-maker' ),
			'type' => 'toggle',
			'default' => true,
		),
		array(
			'id' => 'recipe_css',
			'name' => __( 'Recipe CSS', 'wp-recipe-maker' ),
			'description' => __( 'This custom styling will be output on your website.', 'wp-recipe-maker' ),
			'type' => 'code',
			'code' => 'css',
			'default' => '',
			'dependency' => array(
				'id' => 'features_custom_style',
				'value' => true,
			),
		),
		array(
			'id' => 'print_css',
			'name' => __( 'Recipe Print CSS', 'wp-recipe-maker' ),
			'description' => __( 'This custom styling will be output on the recipe print page.', 'wp-recipe-maker' ),
			'type' => 'code',
			'code' => 'css',
			'default' => '',
		),
	),
);
