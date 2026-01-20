<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       https://bootstrapped.ventures
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
		'marketplace' => 'www.amazon.com.au',
		'credential_version' => '2.3',
		'auth_region' => 'ap-southeast-1',
	),
	'belgium' => array(
		'label' => 'Belgium',
		'host' => 'webservices.amazon.com.be',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.com.be',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'brazil' => array(
		'label' => 'Brazil',
		'host' => 'webservices.amazon.com.br',
		'region' => 'us-east-1',
		'marketplace' => 'www.amazon.com.br',
		'credential_version' => '2.1',
		'auth_region' => 'us-west-2',
	),
	'canada' => array(
		'label' => 'Canada',
		'host' => 'webservices.amazon.ca',
		'region' => 'us-east-1',
		'marketplace' => 'www.amazon.ca',
		'credential_version' => '2.1',
		'auth_region' => 'us-west-2',
	),
	'egypt' => array(
		'label' => 'Egypt',
		'host' => 'webservices.amazon.eg',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.eg',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'france' => array(
		'label' => 'France',
		'host' => 'webservices.amazon.fr',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.fr',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'germany' => array(
		'label' => 'Germany',
		'host' => 'webservices.amazon.de',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.de',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'india' => array(
		'label' => 'India',
		'host' => 'webservices.amazon.in',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.in',
		'credential_version' => '2.3',
		'auth_region' => 'ap-southeast-1',
	),
	'italy' => array(
		'label' => 'Italy',
		'host' => 'webservices.amazon.it',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.it',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'japan' => array(
		'label' => 'Japan',
		'host' => 'webservices.amazon.co.jp',
		'region' => 'us-west-2',
		'marketplace' => 'www.amazon.co.jp',
		'credential_version' => '2.3',
		'auth_region' => 'ap-southeast-1',
	),
	'mexico' => array(
		'label' => 'Mexico',
		'host' => 'webservices.amazon.com.mx',
		'region' => 'us-east-1',
		'marketplace' => 'www.amazon.com.mx',
		'credential_version' => '2.1',
		'auth_region' => 'us-west-2',
	),
	'netherlands' => array(
		'label' => 'Netherlands',
		'host' => 'webservices.amazon.nl',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.nl',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'poland' => array(
		'label' => 'Poland',
		'host' => 'webservices.amazon.pl',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.pl',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'singapore' => array(
		'label' => 'Singapore',
		'host' => 'webservices.amazon.sg',
		'region' => 'us-west-2',
		'marketplace' => 'www.amazon.sg',
		'credential_version' => '2.3',
		'auth_region' => 'ap-southeast-1',
	),
	'saudi_arabia' => array(
		'label' => 'Saudi Arabia',
		'host' => 'webservices.amazon.sa',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.sa',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'spain' => array(
		'label' => 'Spain',
		'host' => 'webservices.amazon.es',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.es',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'sweden' => array(
		'label' => 'Sweden',
		'host' => 'webservices.amazon.se',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.se',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'turkey' => array(
		'label' => 'Turkey',
		'host' => 'webservices.amazon.com.tr',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.com.tr',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'united_arab_emirates' => array(
		'label' => 'United Arab Emirates',
		'host' => 'webservices.amazon.ae',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.ae',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'united_kingdom' => array(
		'label' => 'United Kingdom',
		'host' => 'webservices.amazon.co.uk',
		'region' => 'eu-west-1',
		'marketplace' => 'www.amazon.co.uk',
		'credential_version' => '2.2',
		'auth_region' => 'eu-west-1',
	),
	'united_states' => array(
		'label' => 'United States',
		'host' => 'webservices.amazon.com',
		'region' => 'us-east-1',
		'marketplace' => 'www.amazon.com',
		'credential_version' => '2.1',
		'auth_region' => 'us-west-2',
	),
);

$amazon_stores_dropdown = array_map( function( $store ) {
	return $store['label'];
}, $amazon_stores );

$amazon = array(
	'id' => 'amazon',
	'icon' => 'basket',
	'name' => __( 'Amazon Products', 'wp-recipe-maker' ),
	'required' => 'premium',
	'description' => __( 'Use the Amazon Product API to easily search for Amazon products to link to your equipment.', 'wp-recipe-maker' ),
	'documentation' => 'https://help.bootstrapped.ventures/article/336-amazon-products',
	'subGroups' => array(
		array(
			'name' => __( 'General', 'wp-recipe-maker' ),
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
					'description' => __( 'Make sure this is the partner tag or tracking ID for the store selected above.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
				),
				array(
					'id' => 'amazon_api_type',
					'name' => __( 'API Type', 'wp-recipe-maker' ),
					'description' => __( 'Choose which Amazon API to use. "Automatically Switch" will detect which credentials you have filled in and use Creators API if available, with PA-API as fallback.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'auto' => __( 'Automatically Switch', 'wp-recipe-maker' ),
						'paapi' => __( 'PA-API (Product Advertising API)', 'wp-recipe-maker' ),
						'creators' => __( 'Creators API', 'wp-recipe-maker' ),
					),
					'default' => 'auto',
				),
			),
		),
		array(
			'name' => __( 'PA-API Details', 'wp-recipe-maker' ),
			'description' => __( 'Your Amazon Product Advertising API (PA-API) credentials.', 'wp-recipe-maker' ),
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
			'dependency' => array(
				'id' => 'amazon_api_type',
				'value' => 'creators',
				'type' => 'inverse',
			),
		),
		array(
			'name' => __( 'Creators API Details', 'wp-recipe-maker' ),
			'description' => __( 'Your Amazon Creators API credentials. Get these from Associates Central > Tools > CreatorsAPI.', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'amazon_credential_id',
					'name' => __( 'Credential ID', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
				),
				array(
					'id' => 'amazon_credential_secret',
					'name' => __( 'Credential Secret', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
				),
				array(
					'id' => 'amazon_credential_version',
					'name' => __( 'Credential Version', 'wp-recipe-maker' ),
					'description' => __( 'Your credential version based on region: 2.1 for North America, 2.2 for Europe, 2.3 for Far East.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
				),
			),
			'dependency' => array(
				'id' => 'amazon_api_type',
				'value' => 'paapi',
				'type' => 'inverse',
			),
		),
	),
);
