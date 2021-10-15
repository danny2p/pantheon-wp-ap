<?php
/**
 * Default nofollow settings change.
 *
 * @link       https://bootstrapped.ventures
 * @since      7.6.0
 *
 * @package    WP_Recipe_Makerå
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$user_settings = WPRM_Settings::get_settings();

$update_settings = array();

if ( isset( $user_settings['term_links_use_nofollow'] ) && true === $user_settings['term_links_use_nofollow'] ) {
	$update_settings['term_links_nofollow'] = 'nofollow';
}
if ( isset( $user_settings['ingredient_links_use_nofollow'] ) && true === $user_settings['ingredient_links_use_nofollow'] ) {
	$update_settings['ingredient_links_nofollow'] = 'nofollow';
}
if ( isset( $user_settings['equipment_links_use_nofollow'] ) && true === $user_settings['equipment_links_use_nofollow'] ) {
	$update_settings['equipment_links_nofollow'] = 'nofollow';
}

if ( $update_settings ) {
	WPRM_Settings::update_settings( $update_settings );
}