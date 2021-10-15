<?php
/**
 * Migration for the new template mode.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

// Set template mode to legacy for existing users.
$settings = array(
	'recipe_template_mode' => 'legacy',
);

// Match existing recipe templates to new names.
$match_templates = array(
	'clean' => 'basic',
	'simple' => 'classic',
	'tastefully-simple' => 'compact',
	'colorful' => 'boxes',
	'side-by-side' => 'columns',
	'wide' => 'columns',
);

$recipe_template = WPRM_Settings::get( 'default_recipe_template' );
$print_template = WPRM_Settings::get( 'default_print_template' );

if ( array_key_exists( $recipe_template, $match_templates ) ) {
	$settings['default_recipe_template_modern'] = $match_templates[ $recipe_template ];
}
if ( array_key_exists( $print_template, $match_templates ) ) {
	$settings['default_print_template_modern'] = $match_templates[ $print_template ];
}

WPRM_Settings::update_settings( $settings );

$notice = 'We have introduced a brand new template mode with its own Template Editor.<br/>';
$notice .= 'Learn more <a href="https://help.bootstrapped.ventures/article/111-migrating-from-legacy-to-modern-mode" target="_blank">in the Template Migration Guide</a>.';

self::$notices[] = $notice;