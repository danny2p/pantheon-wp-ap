<?php
$performance = array(
	'id' => 'performance',
	'icon' => 'speed',
	'name' => __( 'Performance', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'General', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'performance_use_combined_stars',
					'name' => __( 'Output Combined Stars in Comments', 'wp-recipe-maker' ),
					'description' => __( 'Reduce DOM nodes by using one image for stars in comments. Disable to be able to use the comment star color setting.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'only_load_assets_when_needed',
					'name' => __( 'Only load Assets when needed', 'wp-recipe-maker' ),
					'description' => __( 'Only load JS and CSS files when a recipe is found on the page. Disable to always load WPRM assets.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'load_admin_assets_everywhere',
					'name' => __( 'Load admin assets everywhere', 'wp-recipe-maker' ),
					'description' => __( 'By default only loads admin assets on edit post pages. Enable to increase compatibility.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
		array(
			'name' => __( 'Template Assets Location', 'wp-recipe-maker' ),
			'description' => __( 'Load template assets in footer to improve page loading speeds (recommended). Disabling can prevent CLS issues but also cause compatibility styling problems. When disabling, consider disabling "Only load Assets when needed" as well.', 'wp-recipe-maker' ),
			'documentation' => 'https://help.bootstrapped.ventures/article/281-prevent-cls-issues',
			'dependency' => array(
				'id' => 'recipe_template_mode',
				'value' => 'modern',
			),
			'settings' => array(
				array(
					'id' => 'snippet_templates_in_footer',
					'name' => __( 'Default Snippet Templates in Footer', 'wp-recipe-maker' ),
					'description' => __( 'Load the default snippet templates in the footer to improve performance.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'recipe_templates_in_footer',
					'name' => __( 'Default Recipe Templates in Footer', 'wp-recipe-maker' ),
					'description' => __( 'Load the default recipe templates in the footer to improve performance.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
	),
);
