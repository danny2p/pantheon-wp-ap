<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      9.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$amazon_stores = array(
	'australia' => array(
		'label' => 'Australia',
		'host' => 'webservices.amazon.com.au',
		'region' => 'us-west-2',
	),
	'belgium' => array(
		'label' => 'Belgium',
		'host' => 'webservices.amazon.com.be',
		'region' => 'eu-west-1',
	),
	'brazil' => array(
		'label' => 'Brazil',
		'host' => 'webservices.amazon.com.br',
		'region' => 'us-east-1',
	),
	'canada' => array(
		'label' => 'Canada',
		'host' => 'webservices.amazon.ca',
		'region' => 'us-east-1',
	),
	'egypt' => array(
		'label' => 'Egypt',
		'host' => 'webservices.amazon.eg',
		'region' => 'eu-west-1',
	),
	'france' => array(
		'label' => 'France',
		'host' => 'webservices.amazon.fr',
		'region' => 'eu-west-1',
	),
	'germany' => array(
		'label' => 'Germany',
		'host' => 'webservices.amazon.de',
		'region' => 'eu-west-1',
	),
	'india' => array(
		'label' => 'India',
		'host' => 'webservices.amazon.in',
		'region' => 'eu-west-1',
	),
	'italy' => array(
		'label' => 'Italy',
		'host' => 'webservices.amazon.it',
		'region' => 'eu-west-1',
	),
	'japan' => array(
		'label' => 'Japan',
		'host' => 'webservices.amazon.co.jp',
		'region' => 'us-west-2',
	),
	'mexico' => array(
		'label' => 'Mexico',
		'host' => 'webservices.amazon.com.mx',
		'region' => 'us-east-1',
	),
	'netherlands' => array(
		'label' => 'Netherlands',
		'host' => 'webservices.amazon.nl',
		'region' => 'eu-west-1',
	),
	'poland' => array(
		'label' => 'Poland',
		'host' => 'webservices.amazon.pl',
		'region' => 'eu-west-1',
	),
	'singapore' => array(
		'label' => 'Singapore',
		'host' => 'webservices.amazon.sg',
		'region' => 'us-west-2',
	),
	'saudi_arabia' => array(
		'label' => 'Saudi Arabia',
		'host' => 'webservices.amazon.sa',
		'region' => 'eu-west-1',
	),
	'spain' => array(
		'label' => 'Spain',
		'host' => 'webservices.amazon.es',
		'region' => 'eu-west-1',
	),
	'sweden' => array(
		'label' => 'Sweden',
		'host' => 'webservices.amazon.se',
		'region' => 'eu-west-1',
	),
	'turkey' => array(
		'label' => 'Turkey',
		'host' => 'webservices.amazon.com.tr',
		'region' => 'eu-west-1',
	),
	'united_arab_emirates' => array(
		'label' => 'United Arab Emirates',
		'host' => 'webservices.amazon.ae',
		'region' => 'eu-west-1',
	),
	'united_kingdom' => array(
		'label' => 'United Kingdom',
		'host' => 'webservices.amazon.co.uk',
		'region' => 'eu-west-1',
	),
	'united_states' => array(
		'label' => 'United States',
		'host' => 'webservices.amazon.com',
		'region' => 'us-east-1',
	),
);

$amazon_stores_dropdown = array_map( function( $store ) {
	return $store['label'];
}, $amazon_stores );

$amazon = array(
	'id' => 'amazon',
	'icon' => 'shopping-cart',
	'name' => __( 'Amazon Products', 'wp-recipe-maker' ),
	'required' => 'premium',
	'description' => __( 'Use the Amazon Product API to easily search for Amazon products to link to your equipment.', 'wp-recipe-maker' ),
	'documentation' => 'https://help.bootstrapped.ventures/article/336-amazon-products',
	'settings' => array(
		array(
			'id' => 'amazon_store',
			'name' => __( 'Amazon Store', 'wp-recipe-maker' ),
			'description' => __( 'The Amazon store to use for your affiliate links.', 'wp-recipe-maker' ),
			'type' => 'dropdown',
			'options' => $amazon_stores_dropdown,
			'default' => 'united_states',
		),
		array(
			'id' => 'amazon_partner_tag',
			'name' => __( 'Amazon Store ID', 'wp-recipe-maker' ),
			'description' => __( 'Make sure this is the partner tag for the store selected above.', 'wp-recipe-maker' ),
			'type' => 'text',
			'default' => '',
		),
	),
	'subGroups' => array(
		array(
			'name' => __( 'API Details', 'wp-recipe-maker' ),
			'description' => __( 'Your Amazon Product Advertising API details.', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'amazon_access_key',
					'name' => __( 'Amazon Access Key', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
				),
				array(
					'id' => 'amazon_secret_key',
					'name' => __( 'Amazon Secret Key', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
				),
			),
		),
	),
);
