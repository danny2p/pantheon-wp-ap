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

$recipe_defaults = array(
	'id' => 'recipeDefaults',
	'icon' => 'edit',
	'name' => __( 'Recipe Editing', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'Defaults', 'wp-recipe-maker' ),
			'description' => __( 'These settings change the default value when you start creating a new recipe.', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'recipe_name_from_post_title',
					'name' => __( 'Use Post Title for Recipe Name', 'wp-recipe-maker' ),
					'description' => __( 'When creating a new recipe inside a post, use its title as the default name value.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'recipe_image_use_featured',
					'name' => __( 'Use image from parent post', 'wp-recipe-maker' ),
					'description' => __( 'Use featured image of parent post if no recipe image is set.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'recipe_author_display_default',
					'name' => __( 'Default Author', 'wp-recipe-maker' ),
					'description' => __( 'Default value for the Recipe Author field when creating a new recipe.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'disabled' => __( "Don't show", 'wp-recipe-maker' ),
						'post_author' => __( 'Name of post author', 'wp-recipe-maker' ),
						'custom' => __( 'Custom author per recipe', 'wp-recipe-maker' ),
						'same' => __( 'Same author for every recipe', 'wp-recipe-maker' ),
					),
					'default' => 'disabled',
				),
				array(
					'id' => 'recipe_author_custom_default',
					'name' => __( 'Default Custom Author Name', 'wp-recipe-maker' ),
					'type' => 'text',
					'dependency' => array(
						'id' => 'recipe_author_display_default',
						'value' => 'custom',
					),
					'default' => '',
				),
				array(
					'id' => 'recipe_author_same_name',
					'name' => __( 'Author Name', 'wp-recipe-maker' ),
					'type' => 'text',
					'dependency' => array(
						'id' => 'recipe_author_display_default',
						'value' => 'same',
					),
					'default' => '',
				),
				array(
					'id' => 'recipe_author_same_link',
					'name' => __( 'Author Link', 'wp-recipe-maker' ),
					'description' => __( 'Leave blank to not use a link.', 'wp-recipe-maker' ),
					'type' => 'text',
					'required' => 'premium',
					'dependency' => array(
						'id' => 'recipe_author_display_default',
						'value' => 'same',
					),
					'default' => '',
				),
				array(
					'id' => 'recipe_author_same_link_new_tab',
					'name' => __( 'Open Author Link in New Tab', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'required' => 'premium',
					'dependency' => array(
						'id' => 'recipe_author_display_default',
						'value' => 'same',
					),
					'default' => false,
				),
			),
		),
		array(
			'name' => __( 'Other', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'automatic_amount_fraction_symbols',
					'name' => __( 'Automatic Amount Fraction Symbols', 'wp-recipe-maker' ),
					'description' => __( 'When using fractions in the ingredient amount field, automatically replace with their symbol if available. Recommended for accessibility.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
	),
);
