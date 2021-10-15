<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

if ( ! class_exists( 'WPRM_Template_Helper' ) ) {
	require_once( WPRM_DIR . 'includes/public/deprecated/class-wprm-template-helper.php' );
}

$template_labels = WPRM_Template_Helper::get_default_labels();
ksort( $template_labels );

$label_settings = array();
foreach ( $template_labels as $uid => $default ) {
	$label_setting = array(
		'id' => 'label_' . $uid,
		'name' => ucwords( str_replace( '_', ' ', $uid ) ),
		'type' => 'text',
		'default' => $default,
	);

	if ( 'comment_rating' === $uid ) {
		$label_setting['description'] = __( 'Label used in the comment form.', 'wp-recipe-maker' );
	}

	$label_settings[] = $label_setting;
}

$template_legacy_labels = array(
	'id' => 'labels',
	'icon' => 'text',
	'name' => __( 'Text Labels', 'wp-recipe-maker' ),
	'description' => '',
	'dependency' => array(
		'id' => 'recipe_template_mode',
		'value' => 'legacy',
	),
	'settings' => $label_settings,
);
