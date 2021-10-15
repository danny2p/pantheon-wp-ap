<?php
/**
 * Different recipe templates by type.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.2.1
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$recipe_template = WPRM_Settings::get( 'default_recipe_template_modern' );
$print_template = WPRM_Settings::get( 'default_print_template_modern' );

$settings = array(
	'default_howto_recipe_template_modern' => $recipe_template,
	'default_other_recipe_template_modern' => $recipe_template,
	'default_howto_print_template_modern' => $print_template,
	'default_other_print_template_modern' => $print_template,
);

WPRM_Settings::update_settings( $settings );