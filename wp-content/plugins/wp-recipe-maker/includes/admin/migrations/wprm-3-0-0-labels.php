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

$default_labels = WPRM_Template_Helper::get_default_labels();
$saved_labels = get_option( 'wprm_labels', array() );
$labels = array_merge( $default_labels, $saved_labels );

$settings = array();
foreach ( $labels as $id => $label ) {
	$settings[ 'label_' . $id ] = $label;
}

WPRM_Settings::update_settings( $settings );
