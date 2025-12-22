<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       https://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$permissions = array(
	'id' => 'permissions',
	'icon' => 'lock',
	'name' => __( 'Permissions', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'Frontend Access', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'print_published_recipes_only',
					'name' => __( 'Prevent printing of non-published recipes', 'wp-recipe-maker' ),
					'description' => __( 'Redirect visitors to the homepage when trying to print a recipe that has not been published yet. Can cause problems if the parent post is not set correctly.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'print_recipes_in_parent_content_only',
					'name' => __( 'Prevent printing of restricted recipes', 'wp-recipe-maker' ),
					'description' => __( 'Checks if a recipe is in the post content of its parent post. Can be used in combination with membership plugins.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'api_allow_published_only',
					'name' => __( 'Only allow Published Recipes in API', 'wp-recipe-maker' ),
					'description' => __( 'Only allow published recipes to be returned by the API. When disabled, some data on all your recipes can be accessed by anyone via the API.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
			),
		),
		array(
			'name' => __( 'Embed API', 'wp-recipe-maker' ),
			'description' => __( 'Control access to the recipe embedding API. When enabled, recipes can be embedded on external sites using the API endpoint.', 'wp-recipe-maker' ),
			'documentation' => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/recipe-embed-api/',
			'required' => 'premium',
			'settings' => array(
				array(
					'id' => 'embed_api_enabled',
					'name' => __( 'Enable Embed API', 'wp-recipe-maker' ),
					'description' => __( 'Allow recipes to be embedded on external sites via the REST API.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'embed_api_auth_method',
					'name' => __( 'Authentication Method', 'wp-recipe-maker' ),
					'description' => __( 'Choose how external sites authenticate with the embed API.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'default' => 'none',
					'options' => array(
						'none' => __( 'No Authentication', 'wp-recipe-maker' ),
						'signature' => __( 'HMAC Signature (Recommended)', 'wp-recipe-maker' ),
						'passkey' => __( 'Simple Passkey (Less Secure)', 'wp-recipe-maker' ),
					),
					'dependency' => array(
						'id' => 'embed_api_enabled',
						'value' => true,
					),
				),
				array(
					'id' => 'embed_api_secret_key',
					'name' => __( 'Embed API Secret Key', 'wp-recipe-maker' ),
					'description' => __( 'Secret key used to generate secure signatures for API access. Generate a strong, random key (32+ characters).', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '',
					'sanitize' => function( $value ) {
						return sanitize_text_field( $value );
					},
					'dependency' => array(
						array(
							'id' => 'embed_api_enabled',
							'value' => true,
						),
						array(
							'id' => 'embed_api_auth_method',
							'value' => 'none',
							'type' => 'inverse',
						),
					),
				),
				array(
					'id' => 'embed_api_include_metadata',
					'name' => __( 'Include Recipe Metadata', 'wp-recipe-maker' ),
					'description' => __( 'Include recipe JSON-LD metadata in the embed API response.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
					'dependency' => array(
						'id' => 'embed_api_enabled',
						'value' => true,
					),
				),
			),
		),
		array(
			'name' => __( 'Backend Access', 'wp-recipe-maker' ),
			'description' => __( 'Accepts one value only. Set the minimum capability required to access specific features. For example, set to edit_others_posts to provide access to editors and administrators.', 'wp-recipe-maker' ),
			'documentation' => 'https://wordpress.org/documentation/article/roles-and-capabilities/',
			'settings' => array(
				array(
					'id' => 'features_dashboard_access',
					'name' => __( 'Access to Dashboard Page', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => 'manage_options',
					'sanitize' => function( $value ) {
						return preg_replace( '/[,\s]/', '', $value );
					},
				),
				array(
					'id' => 'features_manage_access',
					'name' => __( 'Access to Manage Page', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => 'manage_options',
					'sanitize' => function( $value ) {
						return preg_replace( '/[,\s]/', '', $value );
					},
				),
				array(
					'id' => 'features_tools_access',
					'name' => __( 'Access to Tools Page', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => 'manage_options',
					'sanitize' => function( $value ) {
						return preg_replace( '/[,\s]/', '', $value );
					},
				),
				array(
					'id' => 'features_reports_access',
					'name' => __( 'Access to Reports Page', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => 'manage_options',
					'sanitize' => function( $value ) {
						return preg_replace( '/[,\s]/', '', $value );
					},
				),
				array(
					'id' => 'features_import_access',
					'name' => __( 'Access to Import Page', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => 'manage_options',
					'sanitize' => function( $value ) {
						return preg_replace( '/[,\s]/', '', $value );
					},
				),
				array(
					'id' => 'features_faq_access',
					'name' => __( 'Access to FAQ & Support Page', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => 'manage_options',
					'sanitize' => function( $value ) {
						return preg_replace( '/[,\s]/', '', $value );
					},
				),
				array(
					'id' => 'manage_page_show_uneditable',
					'name' => __( 'Show recipes that cannot be edited', 'wp-recipe-maker' ),
					'description' => __( 'Show all recipes on the Manage page, even if a user will not be able to edit them.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'admin_bar_menu_item',
					'name' => __( 'Show Admin Bar Menu Item', 'wp-recipe-maker' ),
					'description' => __( 'Show WP Recipe Maker in Admin Bar on frontend for easy editing and shortcuts.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
			),
		),
	),
);
