<?php

class MaxUploaderWordPressStatus {

	public function get_info() {
		$plugin_data = get_plugin_data( WMUFS_PLUGIN_URL );

		$wp_version         = get_bloginfo( 'version' );
		$min_wp_version     = $plugin_data['RequiresWP'] ?? '4.4';
		$wp_version_status  = version_compare( $wp_version, $min_wp_version, '>=' ) ? 1 : 0;

		$wc_version = class_exists( 'woocommerce' ) ? WC()->version : 'Not Active Woocommerce';
		$min_wc_version = $plugin_data['WC requires at least'] ?? '3.2';
		$wc_status = is_numeric( $wc_version ) && version_compare( $wc_version, $min_wc_version, '>=' ) ? 1 : 0;

		$min_upload_size = '40MB';
		$upload_size_wp  = $this->wp_min_upload_size();
		$upload_size_host = $this->wp_upload_size_from_host();

		$upload_size_status_wp = $this->convertToBytes( $upload_size_wp ) < $this->convertToBytes( $min_upload_size ) ? 0 : 1;
		$upload_size_status_host = $this->convertToBytes( $upload_size_host ) < $this->convertToBytes( $min_upload_size ) ? 0 : 1;

		$min_limit_time = 120;
		$php_limit_time = ini_get( 'max_execution_time' );
		$php_limit_time_status = $php_limit_time >= $min_limit_time ? 1 : 0;

		return [
			[
				'title' => esc_html__( 'WordPress Version', 'wp-maximum-upload-file-size' ),
				'version' => $wp_version,
				'status' => $wp_version_status,
				'success_message' => esc_html__( '- ok', 'wp-maximum-upload-file-size' ),
				'error_message' => esc_html__( 'Recommend : ', 'wp-maximum-upload-file-size') . $min_wp_version,
			],
			[
				'title' => esc_html__( 'Woocommerce Version', 'wp-maximum-upload-file-size' ),
				'version' => $wc_version,
				'status' => $wc_status,
				'success_message' => esc_html__( '- ok', 'wp-maximum-upload-file-size' ),
				'error_message' => esc_html__( 'Recommend : ', 'wp-maximum-upload-file-size') . $min_wc_version,
			],
			[
				'title' => esc_html__( 'Maximum Upload Limit set by WordPress', 'wp-maximum-upload-file-size' ),
				'version' => $upload_size_wp,
				'status' => $upload_size_status_wp,
				'success_message' => esc_html__( '- ok', 'wp-maximum-upload-file-size' ),
				'error_message' => esc_html__( 'Recommend : ', 'wp-maximum-upload-file-size') . $min_upload_size,
			],
			[
				'title' => esc_html__( 'Maximum Upload Limit Set By Hosting Provider', 'wp-maximum-upload-file-size' ),
				'version' => $upload_size_host,
				'status' => $upload_size_status_host,
				'success_message' => esc_html__( '- ok', 'wp-maximum-upload-file-size' ),
				'error_message' => esc_html__( 'Recommend : ', 'wp-maximum-upload-file-size') . $min_upload_size,
			],
			[
				'title' => esc_html__( 'PHP Limit Time', 'wp-maximum-upload-file-size' ),
				'version' => esc_html__('Current Limit Time: ', 'wp-maximum-upload-file-size') . $php_limit_time,
				'status' => $php_limit_time_status,
				'success_message' => esc_html__( '- ok', 'wp-maximum-upload-file-size' ),
				'error_message' => esc_html__( 'Recommend : ', 'wp-maximum-upload-file-size') . $min_limit_time,
			],
		];
	}

	private function wp_min_upload_size(): string {
		$size = wp_max_upload_size();
		return $size ? size_format($size) : 'unknown';
	}

	private function wp_upload_size_from_host(): string {
		$ini = ini_get('upload_max_filesize') ?: 'unknown';
		if (is_numeric($ini)) return $ini . ' bytes';
		return $ini . 'B';
	}

	private function convertToBytes(string $from): ?int {
		$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
		$number = (float) preg_replace('/[^\d.]/', '', $from);
		$suffix = strtoupper(trim(str_replace($number, '', $from)));

		$index = array_search($suffix, $units);
		if ($index === false) return null;

		return (int) ($number * (1024 ** $index));
	}
}
