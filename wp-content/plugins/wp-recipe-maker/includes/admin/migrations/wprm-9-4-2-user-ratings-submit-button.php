<?php
/**
 * User ratings different submit button.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.4.2
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$user_settings = WPRM_Settings::get_settings();

if ( isset( $user_settings['user_ratings_modal_submit_comment_button'] ) ) {
	$update_settings = array(
		'user_ratings_modal_submit_no_comment_button' => $user_settings['user_ratings_modal_submit_comment_button'],
	);
	
	WPRM_Settings::update_settings( $update_settings );
}