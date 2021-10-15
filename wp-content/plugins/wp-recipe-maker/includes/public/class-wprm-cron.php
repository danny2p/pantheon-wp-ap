<?php
/**
 * Handle any cron jobs.
 *
 * @link       https://bootstrapped.ventures
 * @since      7.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle any cron jobs.
 *
 * @since      7.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Cron {

	/**
	 * Register actions and filters.
	 *
	 * @since    7.4.0
	 */
	public static function init() {
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_interval' ) );

		add_action( 'admin_init', array( __CLASS__, 'schedule_cron' ) );
		// add_action( 'wprm_daily_cron', array( __CLASS__, 'execute_daily_cron' ) );
	}

	/**
	 * Add interval to cron schedules.
	 *
	 * @since    7.4.0
	 * @param	 mixed $schedules Current cron schedules.
	 */
	public static function add_cron_interval( $schedules ) {
		$schedules['wprm_daily'] = array(
			'interval' => 24 * 60 * 60, // Daily.
			'display'  => 'WP Recipe Maker',
		);
		
		return $schedules;
	}

	/**
	 * Schedule cron tasks.
	 *
	 * @since    7.4.0
	 */
	public static function schedule_cron() {
		$next_event = wp_next_scheduled( 'wprm_daily_cron' );

		if ( ! $next_event ) {
			// Nothing scheduled, add schedule.
			wp_schedule_event( time(), 'wprm_daily', 'wprm_daily_cron' );
		}
	}
}

WPRM_Cron::init();
