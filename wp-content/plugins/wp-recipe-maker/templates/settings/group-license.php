<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$license = array(
	'id' => 'licenseKey',
	'icon' => 'key',
	'name' => __( 'License Key', 'wp-recipe-maker' ),
	'description' => __( 'You have the free version of the plugin installed right now, which does not need a license key. If interested, you can learn more about our paid bundles here:', 'wp-recipe-maker' ),
	'documentation' => 'https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/',
	'settings' => array(
		array(
			'name' => __( 'Installing WP Recipe Maker Premium', 'wp-recipe-maker' ),
			'description' => __( 'Are you trying to activate your license key? Only the free plugin is installed right now, so you would need to install the Premium plugin files first.', 'wp-recipe-maker' ),
			'type' => 'button',
			'button' => __( 'How to install WP Recipe Maker Premium', 'wp-recipe-maker' ),
			'link' => 'https://help.bootstrapped.ventures/article/63-installing-wp-recipe-maker',
			'required' => 'premium',
		),
	),
);
