<?php
/**
 * Migration for cleaning up potential WP Ultimate Recipe leftovers.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.10.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

// Delete options.
delete_option( 'wpurp_custom_templates' );
delete_option( 'wpurp_cache' );
delete_option( 'wpurp_cache_temp' );
delete_option( 'wpurp_cache_resetting' );
delete_option( 'wpurp_custom_template_preview' );

// Disable autoload for custom templates.
$mapping = get_option( 'wpurp_custom_template_mapping', array() );

foreach ( $mapping as $id => $name ) {
	$id = intval( $id );

	$template = get_option( 'wpurp_custom_template_' . $id, array() );
	update_option( 'wpurp_custom_template_' . $id, array(), false );
	update_option( 'wpurp_custom_template_' . $id, $template, false );
}

// Disable autoload for nutritional information.
$nutritional = get_option( 'wpurp_nutritional_information', false );

if ( $nutritional ) {
	update_option( 'wpurp_nutritional_information', array(), false );
	update_option( 'wpurp_nutritional_information', $nutritional, false );
}

// Notice regarding template changes.
self::$notices[] = 'We introduced an easy way to set the recipe template colors on the <em>WP Recipe Maker > Settings</em> page. Please check your recipe template and make adjustments if needed.';
