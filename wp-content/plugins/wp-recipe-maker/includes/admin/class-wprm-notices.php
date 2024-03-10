<?php
/**
 * Responsible for showing admin notices.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for the privacy policy.
 *
 * @since      5.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Notices {

	/**
	 * Register actions and filters.
	 *
	 * @since    5.0.0
	 */
	public static function init() {
		add_filter( 'wprm_admin_notices', array( __CLASS__, 'ingredient_units_notice' ) );
		add_filter( 'wprm_admin_notices', array( __CLASS__, 'user_ratings_notice' ) );
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

				// Check if user has already dismissed notice.
				if ( isset( $notice['id'] ) && self::is_dismissed( $notice['id'] ) ) {
					continue;
				}

				$notices_to_display[] = $notice;
			}
		}

		return $notices_to_display;
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

			if ( $id && in_array( $id, $dismissed_notices ) ) {
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
			if ( WPRM_Addons::is_active( 'premium' ) && WPRM_Settings::get( 'features_user_ratings' ) && 'modal' !== WPRM_Settings::get( 'user_ratings_mode' ) ) {
				$notices[] = array(
					'id' => 'user_ratings_modal',
					'title' => __( 'Try the new User Ratings Modal!', 'wp-recipe-maker' ),
					'text' => '<p>Version 9.2.0 introduced a brand new "Modal" option for the User Ratings feature and we highly recommend you to enable it on the <a href="' . admin_url( 'admin.php?page=wprm_settings#wprm-settings-group-recipeRatings' ) . '">WP Recipe Maker > Settings > Star Ratings page</a>. Our main goal is making recipe ratings more trustworthy, which should benefit both vistors and site owners.</p><p>For now both options are still available, but we do urge you to test the modal to make sure it works as expected. There are indications that Google is reconsidering anonymous ratings for recipes so we might need to remove the old system in a future update.</p><p><a href="https://bootstrapped.ventures/wp-recipe-maker-9-2-0/" target="_blank">Learn more on our blog</a></p>',
				);
			}
		}

		return $notices;
	}

	/**
	 * Show the user ratings notice.
	 *
	 * @since	9.3.0
	 * @param	array $notices Existing notices.
	 */
	public static function user_ratings_notice( $notices ) {
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
}

WPRM_Notices::init();
