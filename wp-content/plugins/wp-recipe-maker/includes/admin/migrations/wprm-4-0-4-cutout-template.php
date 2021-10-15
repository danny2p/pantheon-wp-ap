<?php
/**
 * Notice about the cutout template.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

// Check if cutout template is being used.
$settings = array(
	'recipe_template_mode' => 'legacy',
);

$recipe_template = WPRM_Settings::get( 'default_recipe_template_modern' );
$print_template = WPRM_Settings::get( 'default_print_template_modern' );

if ( 'cutout' === $recipe_template || 'cutout' === $print_template ) {
	$notice = 'We had to make a change to the Cutout template that you are using.<br/>';
	$notice .= 'Learn more <a href="https://help.bootstrapped.ventures/article/145-version-4-0-4-changing-the-look-of-the-cutout-template" target="_blank">in this announcement</a>.';

	self::$notices[] = $notice;
}