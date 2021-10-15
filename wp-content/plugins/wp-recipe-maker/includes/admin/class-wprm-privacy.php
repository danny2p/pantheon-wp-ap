<?php
/**
 * Responsible for the privacy policy.
 *
 * @link       http://bootstrapped.ventures
 * @since      2.5.2
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for the privacy policy.
 *
 * @since      2.5.2
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Privacy {

	/**
	 * Register actions and filters.
	 *
	 * @since    2.5.2
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'privacy_policy' ) );
	}

	/**
	 * Add text to the privacy policy suggestions.
	 *
	 * @since    2.5.2
	 */
	public static function privacy_policy() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		ob_start();
		include( WPRM_DIR . 'templates/admin/privacy.php' );
		$content = ob_get_contents();
		ob_end_clean();

		wp_add_privacy_policy_content(
			'WP Recipe Maker',
			wp_kses_post( wpautop( $content, false ) )
		);
	}
}

WPRM_Privacy::init();
