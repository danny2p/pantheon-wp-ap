<?php
/**
 * Different roundup templates by type.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.11.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$roundup_template = WPRM_Settings::get( 'recipe_roundup_template' );

$settings = array(
	'howto_recipe_roundup_template' => $roundup_template,
	'other_recipe_roundup_template' => $roundup_template,
);

WPRM_Settings::update_settings( $settings );