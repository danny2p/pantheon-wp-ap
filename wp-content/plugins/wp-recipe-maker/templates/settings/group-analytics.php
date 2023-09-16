<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$analytics = array(
	'id' => 'analytics',
	'icon' => 'chart',
	'name' => __( 'Analytics', 'wp-recipe-maker' ),
	'subGroups' => array(
		array(
			'name' => __( 'Local Tracking', 'wp-recipe-maker' ),
			'description' => __( 'Store actions locally in your own database. Does not send data to other servers.', 'wp-recipe-maker' ),
			'documentation' => 'https://help.bootstrapped.ventures/article/333-recipe-analytics',
			'settings' => array(
				array(
					'id' => 'analytics_enabled',
					'name' => __( 'Enable Analytics', 'wp-recipe-maker' ),
					'description' => __( 'Track different visitor actions related to recipes. Might require changes to your cookie or privacy policy!', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'analytics_days_to_keep',
					'name' => __( 'Days to Keep', 'wp-recipe-maker' ),
					'description' => __( 'How many days of data should stay stored in the database.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'unlimited' => __( 'Unlimited (not recommended for database growth)', 'wp-recipe-maker' ),
						'365' => __( '365 Days', 'wp-recipe-maker' ),
						'180' => __( '180 Days', 'wp-recipe-maker' ),
						'90' => __( '90 Days', 'wp-recipe-maker' ),
						'30' => __( '30 Days', 'wp-recipe-maker' ),
						'7' => __( '7 Days', 'wp-recipe-maker' ),
					),
					'default' => '90',
					'dependency' => array(
						'id' => 'analytics_enabled',
						'value' => true,
					),
				),
				array(
					'id' => 'analytics_exclude_ips',
					'name' => __( 'Exclude IPs', 'wp-recipe-maker' ),
					'description' => __( 'Do not track any analytics for these IP addresses. One address or range per line.', 'wp-recipe-maker' ),
					'type' => 'textarea',
					'default' => '',
					'dependency' => array(
						'id' => 'analytics_enabled',
						'value' => true,
					),
				),
			),
		),
		array(
			'name' => __( 'Google Analytics Tracking', 'wp-recipe-maker' ),
			'description' => __( 'Track actions using gtag.js events for usage in Google Analytics. Make sure gtag.js is already loaded on your site.', 'wp-recipe-maker' ),
			'documentation' => 'https://help.bootstrapped.ventures/article/334-google-analytics-event-tracking',
			'settings' => array(
				array(
					'id' => 'google_analytics_enabled',
					'name' => __( 'Enable GA Tracking', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
			),
		),
	),
);

// 2022-08-11 DailyGrub not active anymore.
// $analytics['settings'][] = array(
// 	'id' => 'honey_home_integration',
// 	'name' => __( 'DailyGrub Integration', 'wp-recipe-maker' ),
// 	'description' => __( 'Advanced recipe and audience analytics.', 'wp-recipe-maker' ),
// 	'documentation' => 'https://dailygrub.com',
// 	'type' => 'toggle',
// 	'default' => false,
// 	'dependency' => array(
// 		'id' => 'analytics_enabled',
// 		'value' => true,
// 	),
// );

// $hh_integration_status = get_option( 'hh_integration_status', false );

// $description = __( 'Add your DailyGrub tracking ID to enable syncing data with the platform.', 'wp-recipe-maker' );
// if ( false !== $hh_integration_status ) {
// 	if ( $hh_integration_status['success'] ) {
// 		$description = __( 'The integration is currently active.', 'wp-recipe-maker' );
// 	} else {
// 		$description = __( 'There was a problem with activating the integration:', 'wp-recipe-maker' ) . ' ' . $hh_integration_status['message'];
// 	}
// }

// $analytics['settings'][] = array(
// 	'id' => 'honey_home_token',
// 	'name' => __( 'DailyGrub Tracking ID', 'wp-recipe-maker' ),
// 	'description' => $description,
// 	'type' => 'text',
// 	'default' => '',
// 	'sanitize' => function( $value ) {
// 		return trim( sanitize_text_field( $value ) );
// 	},
// 	'dependency' => array(
// 		array(
// 			'id' => 'analytics_enabled',
// 			'value' => true,
// 		),
// 		array(
// 			'id' => 'honey_home_integration',
// 			'value' => true,
// 		),
// 	),
// );