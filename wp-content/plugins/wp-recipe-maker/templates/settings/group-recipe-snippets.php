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

$recipe_snippets = array(
	'id' => 'recipeSnippets',
	'icon' => 'button-click',
	'name' => __( 'Recipe Snippets', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'description' => __( 'Use the [wprm-recipe-snippet] shortcode or automatically add a snippet at the top of your post with the setting. Can be used for the Jump to Recipe and Print Recipe buttons, for example.', 'wp-recipe-maker' ),
			'documentation' => 'https://help.bootstrapped.ventures/article/28-recipe-snippets',
			'dependency' => array(
				'id' => 'recipe_template_mode',
				'value' => 'modern',
			),
			'settings' => array(
				array(
					'id' => 'recipe_snippets_automatically_add_modern',
					'name' => __( 'Automatically add snippets', 'wp-recipe-maker' ),
					'description' => __( 'Automatically have the default snippet template appear at the start of posts that include a recipe.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'recipe_snippets_automatically_add_placement',
					'name' => __( 'Snippet Placement', 'wp-recipe-maker' ),
					'description' => __( 'Preferred placement for the recipe snippets. Will default to start of the post content.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'start' => __( 'At the start of the post content', 'wp-recipe-maker' ),
						'after_first_paragraph' => __( 'After first paragraph', 'wp-recipe-maker' ),
						'after_first_image' => __( 'After first image', 'wp-recipe-maker' ),
						'after_first_div' => __( 'After first div container element', 'wp-recipe-maker' ),
					),
					'default' => 'start',
					'dependency' => array(
						'id' => 'recipe_snippets_automatically_add_modern',
						'value' => true,
					),
				),
			),
		),
		array(
			'name' => __( 'Default Snippet Template', 'wp-recipe-maker' ),
			'description' => __( 'Fully customize these templates in the Template Editor.', 'wp-recipe-maker' ) . ' ' . __( 'Make sure to pick a snippet template or the full recipe might get displayed twice.', 'wp-recipe-maker' ),
			'dependency' => array(
				'id' => 'recipe_template_mode',
				'value' => 'modern',
			),
			'settings' => array(
				array(
					'id' => 'recipe_snippets_template',
					'name' => __( 'Food Recipe Snippet Template', 'wp-recipe-maker' ),
					'description' => __( 'Default snippet template to use for the food recipes on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'default' => 'snippet-basic-buttons',
				),
				array(
					'id' => 'howto_recipe_snippets_template',
					'name' => __( 'How-to Instructions Snippet Template', 'wp-recipe-maker' ),
					'description' => __( 'Default snippet template to use for the how-to instructions on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'default' => 'snippet-basic-buttons',
					'dependency' => array(
						'id' => 'recipe_template_show_types',
						'value' => true,
					),
				),
				array(
					'id' => 'other_recipe_snippets_template',
					'name' => __( 'Other Recipe Snippet Template', 'wp-recipe-maker' ),
					'description' => __( 'Default snippet template to use for the "other (no metadata)" recipes on your website.', 'wp-recipe-maker' ),
					'type' => 'dropdownTemplateModern',
					'default' => 'snippet-basic-buttons',
					'dependency' => array(
						'id' => 'recipe_template_show_types',
						'value' => true,
					),
				),
			),
		),
		array(
			'description' => __( 'Use the [wprm-recipe-snippet] shortcode or automatically add a snippet at the top of your post with the setting. Can be used for the Jump to Recipe and Print Recipe buttons, for example.', 'wp-recipe-maker' ),
			'documentation' => 'https://help.bootstrapped.ventures/article/28-recipe-snippets',
			'dependency' => array(
				'id' => 'recipe_template_mode',
				'value' => 'legacy',
			),
			'settings' => array(
				array(
					'id' => 'recipe_snippets_automatically_add',
					'name' => __( 'Automatically add snippets', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'recipe_snippets_text',
					'name' => __( 'Text to output', 'wp-recipe-maker' ),
					'description' => __( 'Use shortcodes where you want the snippets to appear.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => '[wprm-recipe-jump] [wprm-recipe-print]',
				),
				array(
					'id' => 'recipe_snippets_alignment',
					'name' => __( 'Snippet Alignment', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'left' => __( 'Left', 'wp-recipe-maker' ),
						'center' => __( 'Center', 'wp-recipe-maker' ),
						'right' => __( 'Right', 'wp-recipe-maker' ),
					),
					'default' => 'center',
				),
				array(
					'id' => 'recipe_snippets_background_color',
					'name' => __( 'Background Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#2c3e50',
					'dependency' => array(
						array(
							'id' => 'recipe_snippets_automatically_add',
							'value' => true,
						),
						array(
							'id' => 'features_custom_style',
							'value' => true,
						),
					),
				),
				array(
					'id' => 'recipe_snippets_text_color',
					'name' => __( 'Text Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#ffffff',
					'dependency' => array(
						array(
							'id' => 'recipe_snippets_automatically_add',
							'value' => true,
						),
						array(
							'id' => 'features_custom_style',
							'value' => true,
						),
					),
				),
			),
		),
		array(
			'name' => __( 'Advanced', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'jump_to_recipe_use_custom_hash',
					'name' => __( 'Use Custom Hash for First Recipe on Page', 'wp-recipe-maker' ),
					'description' => __( 'Enable to have the "Jump to Recipe" button in the snippet template jump to #recipe instead of something like #wprm-recipe-container-46783', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'jump_to_recipe_custom_hash',
					'name' => __( 'Custom Hash', 'wp-recipe-maker' ),
					'description' => __( 'Hash to use for the first recipe on the page. This will become part of the URL after clicking the "Jump to Recipe" button.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => 'recipe',
					'sanitize' => function( $value ) {
						return preg_replace( '/[^\w\-]/', '', $value );
					},
					'dependency' => array(
						'id' => 'jump_to_recipe_use_custom_hash',
						'value' => true,
					),
				),
				array(
					'id' => 'jump_to_video_use_custom_hash',
					'name' => __( 'Use Custom Hash for First Video on Page', 'wp-recipe-maker' ),
					'description' => __( 'Enable to have the "Jump to Video" button in the snippet template jump to #recipe-video instead of something like #wprm-recipe-video-container-46783', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'jump_to_video_custom_hash',
					'name' => __( 'Custom Hash', 'wp-recipe-maker' ),
					'description' => __( 'Hash to use for the first video on the page. This will become part of the URL after clicking the "Jump to Recipe" button.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => 'recipe-video',
					'sanitize' => function( $value ) {
						return preg_replace( '/[^\w\-]/', '', $value );
					},
					'dependency' => array(
						'id' => 'jump_to_video_use_custom_hash',
						'value' => true,
					),
				),
			),
		),
	),
);
