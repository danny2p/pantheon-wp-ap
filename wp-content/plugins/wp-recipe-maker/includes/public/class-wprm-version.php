<?php
/**
 * Helper functions for the plugin version.
 *
 * @link       https://bootstrapped.ventures
 * @since      7.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Helper functions for the plugin version.
 *
 * @since      7.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Version {
	/**
	 * Register actions and filters.
	 *
	 * @since    10.4.0
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'track_update' ), 1 );
	}

	/**
	 * Convert version to number.
	 *
	 * @since	7.6.0
	 * @param	string $version to convert to a number.
	 */
	public static function convert_to_number( $version = WPRM_VERSION ) {
		$number = 0;

		$version_split = explode( '.', $version );

		$multiplier = count( $version_split ) - 1;
		foreach ( $version_split as $split ) {
			$number += $split * pow( 100, $multiplier );
			$multiplier--;
		}

		return $number;
	}

	/**
	 * Track plugin updates by comparing stored version to current version.
	 * Called on plugins_loaded to record the date of each update.
	 *
	 * The very first entry also records whether this is a new or existing install,
	 * so support staff can tell if the user had WPRM before tracking began.
	 *
	 * @since	10.4.0
	 */
	public static function track_update() {
		$tracked_version = get_option( 'wprm_tracked_version', '' );

		if ( $tracked_version !== WPRM_VERSION ) {
			$history = get_option( 'wprm_update_history', array() );

			$entry = array(
				'version' => WPRM_VERSION,
				'date'    => gmdate( 'c' ),
			);

			// First time tracking: record whether this is a new or existing install.
			if ( empty( $history ) ) {
				$first_recipe_date = self::get_earliest_recipe_date();

				if ( null === $first_recipe_date ) {
					$entry['type'] = 'new_install';
				} else {
					$entry['type']              = 'existing_install';
					$entry['first_recipe_date'] = $first_recipe_date;
				}
			}

			$history[] = $entry;

			update_option( 'wprm_update_history', $history, false );
			update_option( 'wprm_tracked_version', WPRM_VERSION, false );
		}
	}

	/**
	 * Get the ISO 8601 date of the earliest wprm_recipe post, or null if none exist.
	 *
	 * @since	10.4.0
	 */
	private static function get_earliest_recipe_date() {
		$args = array(
			'post_type'      => WPRM_POST_TYPE,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'fields'         => 'ids',
		);

		$query = new WP_Query( $args );

		if ( 0 === $query->found_posts ) {
			return null;
		}

		return get_the_date( 'c', $query->posts[0] );
	}

	/**
	 * Get the stored plugin update history.
	 *
	 * @since	10.4.0
	 */
	public static function get_update_history() {
		return get_option( 'wprm_update_history', array() );
	}

	/**
	 * Check if a migration is needed.
	 *
	 * @since	7.6.0
	 * @param	string $version Version number to check.
	 */
	public static function migration_needed_to( $version ) {
		// If we checked it before and a migration wasn't necessary then, no need to check again.
		$checked_versions = get_option( 'wprm_versions_checked', array() );
		if ( in_array( $version, $checked_versions ) ) {
			return false;
		}

		// Need to do an actual check (resource intensive, checks all recipes).
		$migration_needed = self::check_if_all_recipes_migrated_to( $version );

		// No migration needed? Store this result!
		if ( ! $migration_needed ) {
			$checked_versions[] = $version;
			update_option( 'wprm_versions_checked', $checked_versions, false );
		}

		return $migration_needed;
	}

	/**
	 * Check if all recipes have been migrated to a specific version.
	 *
	 * @since	8.0.0
	 * @param	string $version Version number to check.
	 */
	public static function check_if_all_recipes_migrated_to( $version ) {
		$version_as_number = self::convert_to_number( $version );

		$args = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => 1,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'		=> 'wprm_version',
					'compare'	=> '<',
					'value' 	=> $version_as_number,
				),
				array(
					'key' => 'wprm_version',
					'compare' => 'NOT EXISTS'
				),
			),
			'fields' => 'ids',
		);

		$query = new WP_Query( $args );
		return 0 < $query->found_posts;
	}
}

WPRM_Version::init();
