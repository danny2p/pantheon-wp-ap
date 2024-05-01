<?php
/**
 * Extra user ratings thank you message.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.3.1
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$user_settings = WPRM_Settings::get_settings();

if ( isset( $user_settings['user_ratings_thank_you_message'] ) ) {
	$update_settings = array(
		'user_ratings_thank_you_message_with_comment' => $user_settings['user_ratings_thank_you_message'],
	);
	
	WPRM_Settings::update_settings( $update_settings );
}