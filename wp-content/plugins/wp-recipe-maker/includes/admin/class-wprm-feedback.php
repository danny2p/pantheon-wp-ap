<?php
/**
 * Asks for feedback.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.27.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Asks for feedback.
 *
 * @since      1.27.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Feedback {

	/**
	 * Feedback key to use for the user meta.
	 *
	 * @since	7.6.0
	 * @access  private
	 * @var     string    $feedback_key    Feedback key.
	 */
	private static $feedback_key = 'wprm_feedback_20210906';

	/**
	 * Wether to show feedback request.
	 *
	 * @since	7.6.0
	 */
	public static function show_feedback_request() {
		if ( current_user_can( 'manage_options' ) && '' === self::get_feedback() ) {
			// Prevent issue when using older version of Premium plugin.
			if ( defined( 'WPRMP_VERSION' ) && version_compare( WPRMP_VERSION, '7.6.0' ) < 0 ) {
				return false;
			}

			// Check number of published recipes.
			$count = wp_count_posts( WPRM_POST_TYPE )->publish;

			if ( 5 <= intval( $count ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get feedback given by current user.
	 *
	 * @since	7.6.0
	 */
	public static function get_feedback() {
		return get_user_meta( get_current_user_id(), self::$feedback_key, true );
	}

	/**
	 * Set feedback given by current user.
	 *
	 * @since	7.6.0
	 */
	public static function set_feedback( $feedback ) {
		update_user_meta( get_current_user_id(), self::$feedback_key, $feedback );
	}
}