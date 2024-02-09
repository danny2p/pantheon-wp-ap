<?php
/**
 * Default user ratings mode settings change.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$user_settings = WPRM_Settings::get_settings();

$update_settings = array();

if ( isset( $user_settings['features_user_ratings'] ) && true === $user_settings['features_user_ratings'] ) {
	// User ratings were already enabled, so make sure mode stays the same.
	$update_settings['user_ratings_mode'] = 'click';
}

if ( $update_settings ) {
	WPRM_Settings::update_settings( $update_settings );
}