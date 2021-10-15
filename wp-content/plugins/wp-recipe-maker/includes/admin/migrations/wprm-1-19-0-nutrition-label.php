<?php
/**
 * Migration for the show nutrition label setting.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.19.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$current = WPRM_Settings::get( 'show_nutrition_label' );

$settings = array(
	'show_nutrition_label' => $current ? 'left' : 'disabled',
);

WPRM_Settings::update_settings( $settings );
