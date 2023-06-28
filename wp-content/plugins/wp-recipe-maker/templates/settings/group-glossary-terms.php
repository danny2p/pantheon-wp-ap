<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      8.9.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$glossary_terms = array(
	'id' => 'glossaryTerms',
	'icon' => 'question-box',
	'name' => __( 'Glossary Terms', 'wp-recipe-maker' ),
	'description' => __( 'Add glossary terms through the WP Recipe Maker > Manage > Features > Glossary Terms page. Wherever you insert them, a tooltip can show up to explain these terms to your readers.', 'wp-recipe-maker' ),
	'documentation' => 'https://help.bootstrapped.ventures/article/330-glossary-terms',
	'subGroups' => array(
		array(
			'name' => __( 'Manually Adding Terms', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'name' => '',
					'description' => __( 'Insert the [wprm-glossary id=123] shortcode wherever you want a term to appear. The term ID can be found through the Manage page.', 'wp-recipe-maker' ),
					'type' => 'button',
					'button' => __( 'Manage Glossary Terms', 'wp-recipe-maker' ),
					'link' => admin_url( 'admin.php?page=wprm_manage#glossary' ),
				),
			),
		),
		array(
			'name' => __( 'Automatically Adding Terms', 'wp-recipe-maker' ),
			'description' => __( 'With these settings enabled, it will automatically search for exact name matches and display them as glossary terms with a tooltip.', 'wp-recipe-maker' ),
			'required' => 'premium',
			'settings' => array(
				array(
					'id' => 'glossary_terms_automatic_summary',
					'name' => __( 'Recipe Summary', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'glossary_terms_automatic_equipment',
					'name' => __( 'Recipe Equipment', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'glossary_terms_automatic_ingredient_headers',
					'name' => __( 'Recipe Ingredient Group Headers', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'glossary_terms_automatic_ingredients',
					'name' => __( 'Recipe Ingredients', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'glossary_terms_automatic_instruction_headers',
					'name' => __( 'Recipe Instruction Group Headers', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'glossary_terms_automatic_instruction_steps',
					'name' => __( 'Recipe Instruction Steps', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'glossary_terms_automatic_notes',
					'name' => __( 'Recipe Notes', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'glossary_terms_automatic_matching',
					'name' => __( 'Matching', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'exact' => __( 'Exact match only', 'wp-recipe-maker' ),
						'insensitive' => __( 'Case insensitive matching', 'wp-recipe-maker' ),
					),
					'default' => 'insensitive',
				),
			),
		),
		array(
			'name' => __( 'Appearance', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'glossary_terms_styling',
					'name' => __( 'Styling for glossary terms', 'wp-recipe-maker' ),
					'description' => __( 'Disable if you want to style these yourself using CSS', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
				),
				array(
					'id' => 'glossary_terms_text_color',
					'name' => __( 'Text Color', 'wp-recipe-maker' ),
					'type' => 'color',
					'default' => '#5A822B',
					'dependency' => array(
						'id' => 'glossary_terms_styling',
						'value' => true,
					),
				),
				array(
					'id' => 'glossary_terms_underline',
					'name' => __( 'Underline Style', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'none' => __( 'No Underline', 'wp-recipe-maker' ),
						'regular' => __( 'Regular', 'wp-recipe-maker' ),
						'dotted' => __( 'Dotted', 'wp-recipe-maker' ),
						'dashed' => __( 'Dashed', 'wp-recipe-maker' ),
					),
					'default' => 'regular',
					'dependency' => array(
						'id' => 'glossary_terms_styling',
						'value' => true,
					),
				),
				array(
					'id' => 'glossary_terms_hover_cursor',
					'name' => __( 'Hover Cursor', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'none' => __( 'No Icon', 'wp-recipe-maker' ),
						'help' => __( 'Help Icon', 'wp-recipe-maker' ),
					),
					'default' => 'help',
					'dependency' => array(
						'id' => 'glossary_terms_styling',
						'value' => true,
					),
				),
			),
		),
	),
);
