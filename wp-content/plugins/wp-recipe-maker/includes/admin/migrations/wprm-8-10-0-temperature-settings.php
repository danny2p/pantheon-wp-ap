<?php
/**
 * Default unit conversion temperature settings change.
 *
 * @link       https://bootstrapped.ventures
 * @since      8.10.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$user_settings = WPRM_Settings::get_settings();

$update_settings = array();

if ( isset( $user_settings['unit_conversion_temperature_conversion'] ) && true === $user_settings['unit_conversion_temperature_conversion'] ) {
	$update_settings['unit_conversion_temperature'] = 'change';
}

if ( $update_settings ) {
	WPRM_Settings::update_settings( $update_settings );
}