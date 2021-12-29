<?php
/**
 * Helper functions for the plugin version.
 *
 * @link       http://bootstrapped.ventures
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
