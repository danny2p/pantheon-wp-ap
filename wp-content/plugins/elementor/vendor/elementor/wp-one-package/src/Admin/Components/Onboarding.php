<?php

namespace ElementorOne\Admin\Components;

use ElementorOne\Admin\Helpers\Utils;
use ElementorOne\Connect\Facade;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Onboarding
 * Handles onboarding page actions
 */
class Onboarding {

	const SCOPE_SHARE_USAGE_DATA = 'share_usage_data';
	const SETTING_SHARE_USAGE_DATA = Fields::SETTING_PREFIX . 'share_usage_data';
	const SETTING_ONBOARDING_COMPLETED = Fields::SETTING_PREFIX . 'onboarding_completed';

	/**
	 * Instance
	 * @var Onboarding|null
	 */
	private static ?Onboarding $instance = null;

	/**
	 * Get instance
	 * @return Onboarding|null
	 */
	public static function instance(): ?Onboarding {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * On connect
	 * @param Facade $facade
	 * @return void
	 */
	public function on_connect( Facade $facade ): void {
		$jwt_payload = Utils::decode_jwt( $facade->data()->get_access_token() );
		if ( $jwt_payload ) {
			$share_usage_data = in_array( self::SCOPE_SHARE_USAGE_DATA, $jwt_payload['scp'] ?? [], true );
			update_option( self::SETTING_SHARE_USAGE_DATA, $share_usage_data ? 'yes' : 'no' );
		}

		$option_updated = update_option( self::SETTING_ONBOARDING_COMPLETED, true );
		if ( true === $option_updated ) {
			wp_safe_redirect( $facade->utils()->get_admin_url() . '#/home/onboarding' );
			exit;
		}
	}

	/**
	 * Onboarding constructor
	 * @return void
	 */
	private function __construct() {
		add_action( 'elementor_one/elementor_one_connected', [ $this, 'on_connect' ] );
	}
}
