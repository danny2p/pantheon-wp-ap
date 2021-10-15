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
		$version_to_check = self::convert_to_number( $version );

		$args = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => 1,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'		=> 'wprm_version',
					'compare'	=> '<',
					'value' 	=> $version_to_check,
				),
				array(
					'key' => 'wprm_version',
					'compare' => 'NOT EXISTS'
				),
			),
			'fields' => 'ids',
		);

		$query = new WP_Query( $args );
		return $query->found_posts;
	}
}
