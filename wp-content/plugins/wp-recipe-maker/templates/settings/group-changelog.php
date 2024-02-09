<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$changelog = array(
	'id' => 'changelog',
	'icon' => 'timeline',
	'name' => __( 'Changelog', 'wp-recipe-maker' ),
	'description' => __( 'Keep track of recipe related changes.', 'wp-recipe-maker' ),
	'documentation' => 'https://help.bootstrapped.ventures/article/337-recipe-changelog',
	'settings' => array(
		array(
			'id' => 'changelog_enabled',
			'name' => __( 'Enable Changelog', 'wp-recipe-maker' ),
			'type' => 'toggle',
			'default' => true,
		),
		array(
			'id' => 'changelog_admin_email',
			'name' => __( 'Send email to', 'wp-recipe-maker-premium' ),
			'description' => __( 'Optional email address to notify when a specific change occurs.', 'wp-recipe-maker-premium' ),
			'type' => 'email',
			'default' => '',
		),
		array(
			'id' => 'changelog_email_notification_types',
			'name' => __( 'Receive Notifications For', 'wp-recipe-maker' ),
			'description' => __( 'Change types to receive the notification for.', 'wp-recipe-maker' ),
			'type' => 'dropdownMultiselect',
			'options' => array(
				'recipe_created' => __( 'Recipe Created', 'wp-recipe-maker' ),
				'recipe_edited' => __( 'Recipe Edited', 'wp-recipe-maker' ),
				'recipe_trashed' => __( 'Recipe Trashed', 'wp-recipe-maker' ),
				'recipe_deleted' => __( 'Recipe Deleted', 'wp-recipe-maker' ),
			),
			'default' => array( 'recipe_trashed', 'recipe_deleted' ),
			'dependency' => array(
				array(
					'id' => 'changelog_enabled',
					'value' => true,
				),
				array(
					'id' => 'changelog_admin_email',
					'value' => '',
					'type' => 'inverse',
				),
			),
		),
		array(
			'id' => 'changelog_days_to_keep',
			'name' => __( 'Days to Keep', 'wp-recipe-maker' ),
			'description' => __( 'How many days of data should stay stored in the database.', 'wp-recipe-maker' ),
			'type' => 'dropdown',
			'options' => array(
				'unlimited' => __( 'Unlimited (not recommended for database growth)', 'wp-recipe-maker' ),
				'365' => __( '365 Days', 'wp-recipe-maker' ),
				'180' => __( '180 Days', 'wp-recipe-maker' ),
				'90' => __( '90 Days', 'wp-recipe-maker' ),
				'30' => __( '30 Days', 'wp-recipe-maker' ),
				'7' => __( '7 Days', 'wp-recipe-maker' ),
			),
			'default' => '90',
			'dependency' => array(
				'id' => 'changelog_enabled',
				'value' => true,
			),
		),
	),
);