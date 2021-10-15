<?php
/**
 * Different snippet templates by type.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.8.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$snippet_template = WPRM_Settings::get( 'recipe_snippets_template' );

$settings = array(
	'howto_recipe_snippets_template' => $snippet_template,
	'other_recipe_snippets_template' => $snippet_template,
);

WPRM_Settings::update_settings( $settings );