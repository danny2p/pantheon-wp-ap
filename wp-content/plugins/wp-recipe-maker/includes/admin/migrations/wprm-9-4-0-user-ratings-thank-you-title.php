<?php
/**
 * Extra user ratings thank you title setting.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$user_settings = WPRM_Settings::get_settings();

if ( isset( $user_settings['user_ratings_modal_title'] ) ) {
	$update_settings = array(
		'user_ratings_thank_you_title' => $user_settings['user_ratings_modal_title'],
	);
	
	WPRM_Settings::update_settings( $update_settings );
}