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

$products = array(
	'id' => 'products',
	'icon' => 'dollar',
	'name' => __( 'eCommerce Products', 'wp-recipe-maker' ),
	'required' => 'elite',
	'description' => __( 'Link your ingredients and equipment to the eCommerce products on your own site to help visitors find and purchase the exact items they need for your recipes.', 'wp-recipe-maker' ) . ' ' . __( 'Currently supported integrations:', 'wp-recipe-maker' ) . ' WooCommerce',
	'documentation' => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/ecommerce-products/',
);
