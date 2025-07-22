<?php
/**
 * Handle the proxy server.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.0.4
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the proxy server.
 *
 * @since      10.0.4
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Proxy {

	/**
	 * Call the proxy server.
	 *
	 * @since    10.0.4
	 */
	public static function call( $endpoint_key, $data = array(), $headers = array() ) {
		// Valid endpoints.
		$endpoints = array(
			'instacart' => array(
				'url' => 'api/instacart',
				'method' => 'POST',
			),
		);

		// Check if endpoint exists.
		if ( ! isset( $endpoints[ $endpoint_key ] ) ) {
			return false;
		}
		$endpoint = $endpoints[ $endpoint_key ];

		// Maybe add additional headers.
		$headers = array_merge( array(
			'accept' => 'application/json',
			'content-type' => 'application/json',
			'X-Plugin-Version'  => WPRM_VERSION,
			'X-Site-URL'        => home_url(),
			'X-Proxy-Secret'    => 'Sit4aliQUa1eiUsMod2eXERc',
		), $headers );

		// Call proxy server.
		$response = wp_remote_post( 'https://proxy.bootstrapped.ventures/' . $endpoint['url'], array(
			'timeout' => 60,
			'sslverify' => false,
			'headers' => $headers,
			'body' => json_encode( $data ),
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}
}
