<?php
/**
 * Health check for the dashboard page.
 *
 * @link       https://bootstrapped.ventures
 * @since      7.7.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Health check for the dashboard page.
 *
 * @since      7.7.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Health_Check {
	/**
	 * Get the Health Check data to display on the dashboard page.
	 *
	 * @since	7.6.0
	 */
	public static function get_data() {
		$health_check = get_option( 'wprm_health_check', false );

		if ( ! $health_check ) {
			$health_check = array(
				'items' => array(),
				'date' => false,
			);
		}

		// Format date and get urgency.
		$health_check['date_formatted'] = __( 'Never', 'wp-recipe-maker' );
		$health_check['urgency'] = 'asap';

		if ( $health_check['date'] ) {
			$datetime = new DateTime();
			$datetime->setTimestamp( $health_check['date'] );
			$health_check['date_formatted'] = $datetime->format( 'M j, Y' );

			if ( strtotime( '-1 week' ) < $health_check['date'] ) {
				$health_check['urgency'] = 'ok';
			} elseif ( strtotime( '-4 weeks' ) < $health_check['date'] ) {
				$health_check['urgency'] = 'fair';
			} elseif ( strtotime( '-2 months' ) < $health_check['date'] ) {
				$health_check['urgency'] = 'bad';
			}
		}

		return $health_check;
	}
}