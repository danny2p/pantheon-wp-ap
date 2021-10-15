<?php
/**
 * Migration for the license key.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.0.3
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

if ( class_exists( 'WPRMP_License' ) ) {
	$products = WPRMP_License::get_products();

	foreach ( $products as $id => $product ) {
		$status = get_option( 'wprm_license_' . $id . '_status', false );

		// Backwards compatibility.
		if ( false === $status ) {
			$status = WPRM_Settings::get( 'license_' . $id . '_status' );
		}

		if ( 'valid' !== $status ) {
			$license = WPRM_Settings::get( 'license_' . $id );

			if ( $license ) {
				// Clear status.
				update_option( 'wprm_license_' . $id . '_status', '', false );

				// Deactivate license.
				$api_params = array(
					'edd_action' => 'deactivate_license',
					'license' 	 => $license,
					'item_name'  => urlencode( $product['name'] ),
					'url'        => home_url(),
				);

				$response = wp_remote_post( 'https://bootstrapped.ventures', array( 'timeout' => 60, 'sslverify' => false, 'body' => $api_params ) );

				// Activate license.
				$api_params = array(
					'edd_action' => 'activate_license',
					'license' 	 => $license,
					'item_name'  => urlencode( $product['name'] ),
					'url'        => home_url(),
				);
		
				// Call the EDD license API.
				$response = wp_remote_post( 'https://bootstrapped.ventures', array( 'timeout' => 60, 'sslverify' => false, 'body' => $api_params ) );
		
				if ( ! is_wp_error( $response ) ) {
					$license_data = json_decode( wp_remote_retrieve_body( $response ) );
					update_option( 'wprm_license_' . $id . '_status', $license_data->license, false );
				}
			}
		}
	}
}
