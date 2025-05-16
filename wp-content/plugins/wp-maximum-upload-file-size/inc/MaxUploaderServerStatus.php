<?php

class MaxUploaderServerStatus {

	/**
	 * WordPress DB instance
	 *
	 * @var wpdb
	 */
	protected wpdb $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Get server and PHP status info
	 *
	 * @return array
	 */
	public function get_info(): array {
		return array(
		$this->format_item( 'PHP Version', phpversion(), version_compare( phpversion(), '7.4', '>=' ) ),
		$this->format_item( 'Server Software', $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ),
		$this->format_item( 'Memory Limit', ini_get( 'memory_limit' ) ),
		$this->format_item( 'Max Execution Time', ini_get( 'max_execution_time' ) . 's' ),
		$this->format_item( 'Max Input Vars', ini_get( 'max_input_vars' ) ),
		$this->format_item( 'Post Max Size', ini_get( 'post_max_size' ) ),
		$this->format_item( 'Upload Max Filesize', ini_get( 'upload_max_filesize' ) ),
		$this->format_item( 'Display Errors', ini_get( 'display_errors' ) ? 'On' : 'Off', ! ini_get( 'display_errors' ) ),
		$this->format_item( 'cURL Enabled', function_exists( 'curl_version' ) ? 'Yes' : 'No', function_exists( 'curl_version' ) ),
		$this->format_item( 'MBString Enabled', extension_loaded( 'mbstring' ) ? 'Yes' : 'No', extension_loaded( 'mbstring' ) ),
		$this->format_item( 'OpenSSL Enabled', extension_loaded( 'openssl' ) ? 'Yes' : 'No', extension_loaded( 'openssl' ) ),
		$this->format_item( 'Zip Enabled', extension_loaded( 'zip' ) ? 'Yes' : 'No', extension_loaded( 'zip' ) ),
		$this->format_item( 'DOM Enabled', class_exists( 'DOMDocument' ) ? 'Yes' : 'No', class_exists( 'DOMDocument' ) ),
		$this->format_item( 'GD Library', extension_loaded( 'gd' ) ? 'Yes' : 'No', extension_loaded( 'gd' ) ),
		$this->format_item( 'Fileinfo Enabled', extension_loaded( 'fileinfo' ) ? 'Yes' : 'No', extension_loaded( 'fileinfo' ) ),
		$this->format_item( 'Allow URL fopen', ini_get( 'allow_url_fopen' ) ? 'Enabled' : 'Disabled', ini_get( 'allow_url_fopen' ) ),
		);
	}

	/**
	 * Format a single item for display
	 *
	 * @param string $title
	 * @param string $value
	 * @param bool   $is_ok
	 *
	 * @return array
	 */
	protected function format_item( string $title, string $value, bool $is_ok = true ): array {
		return array(
			'title'           => esc_html__( $title, 'wp-maximum-upload-file-size' ),
			'version'         => esc_html( $value ),
			'status'          => $is_ok ? 1 : 0,
			'success_message' => $is_ok ? esc_html__( '- ok', 'wp-maximum-upload-file-size' ) : '',
			'error_message'   => ! $is_ok ? esc_html__( 'Needs attention', 'wp-maximum-upload-file-size' ) : '',
		);
	}
}
