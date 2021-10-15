<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$links = array(
	'id' => 'links',
	'icon' => 'link',
	'name' => __( 'Links', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'General', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'external_links_noreferrer',
					'name' => __( 'Use noreferrer for external links', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'external_links_noopener',
					'name' => __( 'Use noopener for external links', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
		array(
			'name' => __( 'Archive Page Links', 'wp-recipe-maker' ),
			'description' => __( 'Enable links to automatic archive pages, if those have been enabled on the WP Recipe Maker Manage > Your Custom Fields > Recipe Taxonomies page.', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'ingredient_links_archive_pages',
					'name' => __( 'Enable for Ingredients', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'equipment_links_archive_pages',
					'name' => __( 'Enable for Equipment', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'term_links_archive_pages',
					'name' => __( 'Enable for other Taxonomies ', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
		array(
			'name' => __( 'Author Links', 'wp-recipe-maker' ),
			'description' => __( 'This setting only applies when the recipe uses "Post Author". Check the WP Recipe Maker > Settings > Recipe Defaults page to change the default.', 'wp-recipe-maker' ),
			'required' => 'premium',
			'settings' => array(
				array(
					'id' => 'post_author_link',
					'name' => __( 'Use link for Post Author', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'post_author_link_use',
					'name' => __( 'Link to use for Post Author', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'profile' => __( "Use the website link as set on the user's profile page", 'wp-recipe-maker' ),
						'archive' => __( 'Link to the archive page for this user', 'wp-recipe-maker' ),
					),
					'default' => 'profile',
					'dependency' => array(
						'id' => 'post_author_link',
						'value' => true,
					),
				),
				array(
					'id' => 'post_author_link_new_tab',
					'name' => __( 'Open in new tab', 'wp-recipe-maker' ),
					'description' => __( 'Open the Post Author link in a new tab.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
					'dependency' => array(
						'id' => 'post_author_link',
						'value' => true,
					),
				),
			),
		),
		array(
			'name' => __( 'Term Links', 'wp-recipe-maker' ),
			'description' => __( 'Term links can be set through the WP Recipe Maker > Manage > Recipe Fields page.', 'wp-recipe-maker' ),
			'required' => 'premium',
			'settings' => array(
				array(
					'id' => 'term_links_open_in_new_tab',
					'name' => __( 'Open in New Tab', 'wp-recipe-maker' ),
					'description' => __( 'Open custom term links in a new tab.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'term_links_nofollow',
					'name' => __( 'Default Nofollow Attribute', 'wp-recipe-maker' ),
					'description' => __( 'Optional rel attribute to add to custom links by default.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'follow' => __( "Don't Use Nofollow", 'wp-recipe-maker' ),
						'nofollow' => __( 'Use Nofollow', 'wp-recipe-maker' ),
						'sponsored' => __( 'Use Sponsored', 'wp-recipe-maker' ),
					),
					'default' => 'follow',
				),
			),
		),
		array(
			'name' => __( 'Ingredient Links', 'wp-recipe-maker' ),
			'description' => __( 'Ingredient links can be set when editing a recipe or through the WP Recipe Maker > Manage > Recipe Fields > Ingredients page.', 'wp-recipe-maker' ),
			'required' => 'premium',
			'documentation' => 'https://help.bootstrapped.ventures/article/29-ingredient-links',
			'settings' => array(
				array(
					'id' => 'ingredient_links_open_in_new_tab',
					'name' => __( 'Open in New Tab', 'wp-recipe-maker' ),
					'description' => __( 'Open custom ingredient links in a new tab.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'ingredient_links_nofollow',
					'name' => __( 'Default Nofollow Attribute', 'wp-recipe-maker' ),
					'description' => __( 'Optional rel attribute to add to custom links by default.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'follow' => __( "Don't Use Nofollow", 'wp-recipe-maker' ),
						'nofollow' => __( 'Use Nofollow', 'wp-recipe-maker' ),
						'sponsored' => __( 'Use Sponsored', 'wp-recipe-maker' ),
					),
					'default' => 'follow',
				),
			),
		),
		array(
			'name' => __( 'Equipment Links', 'wp-recipe-maker' ),
			'description' => __( 'Equipment links can be set on the WP Recipe Maker > Manage > Recipe Fields > Equipment page.', 'wp-recipe-maker' ),
			'required' => 'premium',
			'documentation' => 'https://help.bootstrapped.ventures/article/193-equipment-links',
			'settings' => array(
				array(
					'id' => 'equipment_links_open_in_new_tab',
					'name' => __( 'Open in New Tab', 'wp-recipe-maker' ),
					'description' => __( 'Open custom equipment links in a new tab.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'equipment_links_nofollow',
					'name' => __( 'Default Nofollow Attribute', 'wp-recipe-maker' ),
					'description' => __( 'Optional rel attribute to add to custom links by default.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'follow' => __( "Don't Use Nofollow", 'wp-recipe-maker' ),
						'nofollow' => __( 'Use Nofollow', 'wp-recipe-maker' ),
						'sponsored' => __( 'Use Sponsored', 'wp-recipe-maker' ),
					),
					'default' => 'follow',
				),
			),
		),
	),
);
