<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$cook_mode = array(
	'id' => 'cook_mode',
	'icon' => 'whisk',
	'name' => __( 'Cook Mode', 'wp-recipe-maker' ),
	'required' => 'premium',
	'description' => __( 'Allow visitors to start cooking the recipe directly from the recipe page, with step-by-step instructions and timers.', 'wp-recipe-maker' ),
	'documentation' => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/cook-mode-popup/',
);
