<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$social_sharing = array(
	'id' => 'socialSharing',
	'icon' => 'share',
	'name' => __( 'Social Sharing', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'Pinterest', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'metadata_pinterest_optout',
					'name' => __( 'Opt out of Rich Pins', 'wp-recipe-maker' ),
					'description' => __( 'Tell Pinterest NOT to display pins as rich pins on recipe pages.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'pinterest_nopin_recipe_image',
					'name' => __( 'Disable recipe image pinning', 'wp-recipe-maker' ),
					'description' => __( 'Add the nopin attribute to the recipe image.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'pinterest_nopin_term_image',
					'name' => __( 'Disable taxonomy term image pinning', 'wp-recipe-maker' ),
					'description' => __( 'Add the nopin attribute to the category images.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'pinterest_nopin_equipment_image',
					'name' => __( 'Disable equipment image pinning', 'wp-recipe-maker' ),
					'description' => __( 'Add the nopin attribute to the equipment images.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'pinterest_nopin_ingredient_image',
					'name' => __( 'Disable ingredient image pinning', 'wp-recipe-maker' ),
					'description' => __( 'Add the nopin attribute to the ingredient images.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'pinterest_nopin_instruction_image',
					'name' => __( 'Disable instruction image pinning', 'wp-recipe-maker' ),
					'description' => __( 'Add the nopin attribute to the instruction images.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'pinterest_nopin_external_roundup_image',
					'name' => __( 'Disable external recipe roundup image pinning', 'wp-recipe-maker' ),
					'description' => __( 'Add the nopin attribute to the external recipe roundup item image.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
		array(
			'name' => __( 'Pinterest - Pin Recipe button', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'pinterest_pin_method',
					'name' => __( 'Pin Method', 'wp-recipe-maker' ),
					'description' => __( 'Choose the pin method to use when clicking the Pin Recipe button.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'pinitjs' => __( 'Load pinit.js (required for Repin ID pinning)', 'wp-recipe-maker' ),
						'link' => __( 'Link only (no extra JavaScript loaded from Pinterest)', 'wp-recipe-maker' ),
					),
					'default' => 'pinitjs',
				),
				array(
					'id' => 'pinterest_use_for_image',
					'name' => __( 'Pin Image', 'wp-recipe-maker' ),
					'description' => __( 'Image to use for the Pin Recipe shortcode.', 'wp-recipe-maker' ),
					'documentation' => 'https://help.bootstrapped.ventures/article/49-pin-recipe-image',
					'type' => 'dropdown',
					'options' => array(
						'recipe_image' => __( 'Recipe Image', 'wp-recipe-maker' ),
						'custom' => __( 'Custom Image per Recipe', 'wp-recipe-maker' ) . $premium_only,
						'custom_or_recipe_image' => __( 'Custom Image if set, otherwise recipe image', 'wp-recipe-maker' ) . $premium_only,
					),
					'default' => 'custom_or_recipe_image',
				),
				array(
					'description' => __( 'You can set the pin image when editing a recipe.', 'wp-recipe-maker' ),
					'required' => 'premium',
					'dependency' => array(
						'id' => 'pinterest_use_for_image',
						'value' => 'recipe_image',
						'type' => 'inverse',
					),
				),
				array(
					'id' => 'pinterest_use_for_description',
					'name' => __( 'Pin Description', 'wp-recipe-maker' ),
					'description' => __( 'What to use for the pin description.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'recipe_name' => __( 'Recipe Name', 'wp-recipe-maker' ),
						'recipe_summary' => __( 'Recipe Summary', 'wp-recipe-maker' ),
						'image_title' => __( 'Image Title', 'wp-recipe-maker' ),
						'image_caption' => __( 'Image Caption', 'wp-recipe-maker' ),
						'image_description' => __( 'Image Description', 'wp-recipe-maker' ),
						'custom' => __( 'Custom Text', 'wp-recipe-maker' ),
					),
					'default' => 'recipe_name',
				),
				array(
					'id' => 'pinterest_custom_description',
					'name' => __( 'Custom Description', 'wp-recipe-maker' ),
					'description' => __( 'You can use the following placeholders:', 'wp-recipe-maker' ) . ' %recipe_name% %recipe_summary%',
					'type' => 'textarea',
					'default' => '',
					'dependency' => array(
						'id' => 'pinterest_use_for_description',
						'value' => 'custom',
					),
				),
			),
		),
	),
);
