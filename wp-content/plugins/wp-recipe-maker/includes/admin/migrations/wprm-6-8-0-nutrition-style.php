<?php
/**
 * Nutrition Label Style.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.8.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */


$settings = array(
	'nutrition_label_style' => 'legacy',
);

WPRM_Settings::update_settings( $settings );