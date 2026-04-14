<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$favorites = array(
	'id' => 'favorites',
	'icon' => 'heart',
	'name' => __( 'Favorites', 'wp-recipe-maker' ),
	'required' => 'premium',
	'description' => __( 'Allow visitors to keep track of their favorite recipes with a simple toggle button and dedicated favorites view.', 'wp-recipe-maker' ),
	'documentation' => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/favorite-recipes/',
);
