<?php
/**
 * Responsible for showing admin notices.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the privacy policy.
 *
 * @since      5.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Notices {

	/**
	 * Register actions and filters.
	 *
	 * @since    5.0.0
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'check_for_dismiss' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );

		add_filter( 'wprm_admin_notices', array( __CLASS__, 'ingredient_units_notice' ) );
	}

	/**
	 * Get all notices to show.
	 *
	 * @since    5.0.0
	 */
	public static function get_notices() {
		$notices_to_display = array();
		$current_user_id = get_current_user_id();

		if ( $current_user_id ) {
			$notices = apply_filters( 'wprm_admin_notices', array() );

			foreach ( $notices as $notice ) {
				// Check capability.
				if ( isset( $notice['capability'] ) && ! current_user_can( $notice['capability'] ) ) {
					continue;
				}

				// Check if notice is dismissable.
				$notice['dismissable'] = isset( $notice['dismissable'] ) ? $notice['dismissable'] : true;

				// Check if user has already dismissed notice.
				if ( isset( $notice['id'] ) && $notice['dismissable'] && self::is_dismissed( $notice['id'] ) ) {
					continue;
				}

				$notices_to_display[] = $notice;
			}
		}

		return $notices_to_display;
	}

	/**
	 * Check if a notice should be dismissed.
	 *
	 * @since	9.8.0
	 */
	public static function check_for_dismiss() {
		if ( isset( $_GET['wprm_dismiss'] ) ) {
			$notice_id = sanitize_text_field( wp_unslash( $_GET['wprm_dismiss'] ) );
			self::dismiss( $notice_id );
		}
	}

	/**
	 * Dissmiss a specific notice.
	 *
	 * @since	9.8.0
	 * @param	mixed $id Notice to dismiss.
	 * @param	int   $user_id Optional. User ID to dismiss the notice for. Default current user.
	 */
	public static function dismiss( $id, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( $id && $user_id ) {
			add_user_meta( $user_id, 'wprm_dismissed_notices', $id );
			return true;
		}

		return false;
	}

	/**
	 * Check if notice has been dismissed.
	 *
	 * @since    8.0.0
	 * @param	mixed $id Notice to check for dismissal.
	 */
	public static function is_dismissed( $id ) {
		$current_user_id = get_current_user_id();

		if ( $current_user_id ) {
			$dismissed_notices = get_user_meta( $current_user_id, 'wprm_dismissed_notices', false );

			// Notice has been dismissed.
			if ( $id && in_array( $id, $dismissed_notices ) ) {
				return true;
			}

			// Could be dismissing right now.
			if ( isset( $_GET['wprm_dismiss'] ) && $id === $_GET['wprm_dismiss'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Show the ingredient units notice.
	 *
	 * @since	7.6.0
	 * @param	array $notices Existing notices.
	 */
	public static function ingredient_units_notice( $notices ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

		// Only load on manage page.
		if ( $screen && 'wp-recipe-maker_page_wprm_manage' === $screen->id ) {
			if ( WPRM_Version::migration_needed_to( '7.6.0' ) ) {
				$notices[] = array(
					'id' => 'ingredient_units',
					'title' => __( 'Ingredient Units', 'wp-recipe-maker' ),
					'text' => 'Version 7.6.0 introduced a new WP Recipe Maker > Manage > Recipe Fields > Ingredient Units screen. To make sure all units are there, run the <a href="' . admin_url( 'admin.php?page=wprm_find_ingredient_units' ) . '" target="_blank">"Find Ingredient Units" tool</a>.',
				);
			}
		}

		return $notices;
	}

	/**
	 * Show notices on plugins page.
	 * 
	 * @since	9.8.1
	 */
	public static function admin_notices() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		$notices = self::get_notices();
		$current_user_id = get_current_user_id();

		if ( $screen && 'plugins' === $screen->id ) {
			foreach ( $notices as $notice ) {
				// Check if notice should be displayed on this page.
				if ( isset( $notice['location'] ) && is_array( $notice['location'] ) && in_array( 'plugins', $notice['location'], true ) ) {
					$dismissable = isset( $notice['dismissable'] ) ? $notice['dismissable'] : true;
					$notice_id = isset( $notice['id'] ) ? $notice['id'] : '';
					$title = isset( $notice['title'] ) ? $notice['title'] : '';
					$text = isset( $notice['text'] ) ? $notice['text'] : '';
					
					echo '<div class="notice notice-error wprm-notice' . ( $dismissable ? ' is-dismissible' : '' ) . '" data-notice-id="' . esc_attr( $notice_id ) . '" data-user-id="' . esc_attr( $current_user_id ) . '">';
					
					if ( $title ) {
						echo '<p><strong>' . esc_html( $title ) . '</strong></p>';
					}
					
					echo '<p>' . wp_kses_post( $text ) . '</p>';
					echo '</div>';
				}
			}
		}
	}
}

WPRM_Notices::init();