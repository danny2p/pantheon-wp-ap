<?php
/**
 * Migration for the labels to settings.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$recipe_template = WPRM_Settings::get( 'default_recipe_template' );
$print_template = WPRM_Settings::get( 'default_print_template' );

$settings = array();

if ( 'clean-print' === $recipe_template || 'clean-print-with-image' === $recipe_template ) {
	$settings['default_recipe_template'] = 'clean';
}

switch ( $print_template ) {
	case 'clean-print':
		$settings['default_print_template'] = 'clean';
		break;
	case 'clean-print-with-image':
		$settings['default_print_template'] = 'clean';
		$settings['print_show_recipe_image'] = true;
		break;
	default:
		$settings['print_show_recipe_image'] = true;
		$settings['print_show_instruction_images'] = true;
}

// Prevent bug in Premium versions prior to 3.0.4.
if ( class_exists( 'WPRMP_License' ) ) {
	remove_filter( 'wprm_settings_update', array( 'WPRMP_License', 'check_license_key_on_settings_update' ), 10, 2 );
	WPRM_Settings::update_settings( $settings );
	add_filter( 'wprm_settings_update', array( 'WPRMP_License', 'check_license_key_on_settings_update' ), 10, 2 );
} else {
	WPRM_Settings::update_settings( $settings );
}
