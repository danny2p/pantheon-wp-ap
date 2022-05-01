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

if ( ! class_exists( 'WPRM_Recipe_Parser' ) ) {
	require_once( WPRM_DIR . 'includes/public/class-wprm-recipe-parser.php' );
}
$default_import_units = WPRM_Recipe_Parser::parse_ingredient_units();

$import = array(
	'id' => 'import',
	'icon' => 'import',
	'name' => __( 'Import', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'Ingredient Parsing', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'import_range_keyword',
					'name' => __( 'Range Keyword', 'wp-recipe-maker' ),
					'type' => 'text',
					'description' => __( 'Keyword used when defining quantity ranges. For example: to when using 1 to 2.', 'wp-recipe-maker' ),
					'default' => __( 'to', 'wp-recipe-maker' ),
				),
				array(
					'id' => 'import_units',
					'name' => __( 'Import Units', 'wp-recipe-maker' ),
					'description' => __( 'Units that will be recognized. One per line.', 'wp-recipe-maker' ),
					'type' => 'textarea',
					'rows' => 10,
					'default' => $default_import_units,
					'sanitize' => function( $value ) {
						return array_map( 'sanitize_text_field', $value );
					},
				),
				array(
					'id' => 'import_notes_identifier',
					'name' => __( 'Ingredient Notes Identifier', 'wp-recipe-maker' ),
					'description' => __( 'How to recognize if it should be part of the ingredient notes.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'comma' => __( 'Everything after the first comma', 'wp-recipe-maker' ),
						'parentheses' => __( 'Everything inside parentheses', 'wp-recipe-maker' ),
						'both' => __( 'Comma or parentheses, whichever comes first', 'wp-recipe-maker' ),
						'none' => __( 'Do not import to ingredient notes', 'wp-recipe-maker' ),
					),
					'default' => 'both',
				),
				array(
					'id' => 'import_notes_remove_identifier',
					'name' => __( 'Remove Identifier', 'wp-recipe-maker' ),
					'description' => __( 'Remove the above ingredient notes identifier from the notes after importing.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => true,
					'dependency' => array(
						'id' => 'import_notes_identifier',
						'value' => 'none',
						'type' => 'inverse',
					),
				),
			),
		),
		array(
			'name' => __( 'Instruction Parsing', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'import_instructions_split',
					'name' => __( 'Split Instruction Steps', 'wp-recipe-maker' ),
					'description' => __( 'When to split the instructions in separate steps when using the import from text feature.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'linebreak' => __( 'Split on linebreaks', 'wp-recipe-maker' ),
						'punctuation' => __( 'Split on linebreaks and punctuation (.!?)', 'wp-recipe-maker' ),
					),
					'default' => 'linebreak',
				),
			),
		),
	),
);
