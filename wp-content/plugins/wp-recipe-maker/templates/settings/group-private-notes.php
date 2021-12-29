<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      8.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$private_notes = array(
	'id' => 'privateNotes',
	'icon' => 'edit',
	'name' => __( 'Private Notes', 'wp-recipe-maker' ),
	'description' => __( 'Allow visitors to add their own private notes to your recipes.', 'wp-recipe-maker' ),
	'required' => 'premium',
	'settings' => array(
		array(
			'id' => 'private_notes_access',
			'name' => __( 'Access to Private Notes', 'wp-recipe-maker' ),
			'description' => __( "When a visitor is not logged in the data is stored in their own browser's local storage. For users it gets stored in the database.", 'wp-recipe-maker' ),
			'type' => 'dropdown',
			'options' => array(
				'everyone' => __( 'Everyone', 'wp-recipe-maker' ),
				'logged_in' => __( 'Logged In Users', 'wp-recipe-maker' ),
			),
			'default' => 'everyone',
		),
		array(
			'id' => 'private_notes_not_logged_in',
			'name' => __( 'When not logged in', 'wp-recipe-maker' ),
			'description' => __( 'What to do with the "Private Notes" section when the visitor is not logged in.', 'wp-recipe-maker' ),
			'type' => 'dropdown',
			'options' => array(
				'hide' => __( 'Hide the section', 'wp-recipe-maker' ),
				'message' => __( 'Show a custom message', 'wp-recipe-maker' ),
			),
			'default' => 'hide',
			'dependency' => array(
				'id' => 'private_notes_access',
				'value' => 'logged_in',
			),
		),
		array(
			'id' => 'private_notes_not_logged_in_message',
			'name' => __( 'Message to show', 'wp-recipe-maker' ),
			'description' => __( 'Message to show when the visitor is not logged in.', 'wp-recipe-maker' ),
			'type' => 'richTextarea',
			'default' => '',
			'dependency' => array(
				array(
					'id' => 'private_notes_access',
					'value' => 'logged_in',
				),
				array(
					'id' => 'private_notes_not_logged_in',
					'value' => 'message',
				),
			),
		),
	),
);