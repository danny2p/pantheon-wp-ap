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
			),
		),
		array(
			'name' => __( 'Backend Access', 'wp-recipe-maker' ),
			'description' => __( 'Accepts one value only. Set the minimum capability required to access specific features. For example, set to edit_others_posts to provide access to editors and administrators.', 'wp-recipe-maker' ),
			'documentation' => 'https://codex.wordpress.org/Roles_and_Capabilities',
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
			),
		),
	),
);
