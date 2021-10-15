<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$recipe_times = array(
	'id' => 'recipeTimes',
	'icon' => 'clock',
	'name' => __( 'Recipe Times', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'Appearance', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'recipe_times_zero_values',
					'name' => __( 'Show values when 0', 'wp-recipe-maker' ),
					'description' => __( 'Show time when it has a value of 0.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
	),
);
