<?php
/**
 * Responsible for promoting the plugin.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.8.1
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for promoting the plugin.
 *
 * @since      5.8.1
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Marketing {

	private static $campaign = false;

	/**
	 * Register actions and filters.
	 *
	 * @since    5.8.1
	 */
	public static function init() {
		$campaigns = array(
			'birthday-2023' => array(
				'start' => new DateTime( '2023-01-24 10:00:00', new DateTimeZone( 'Europe/Brussels' ) ),
				'end' => new DateTime( '2023-01-31 10:00:00', new DateTimeZone( 'Europe/Brussels' ) ),
				'notice_title' => 'Celebrating my birthday',
				'notice_text' => 'Get a 30% discount right now!',
				'page_title' => 'Birthday Discount!',
				'page_text' => 'Good news: I\'m celebrating my birthday with a <strong>30% discount on any of our plugins</strong>.',
				'url' => 'https://bootstrapped.ventures/birthday-discount/',
			),
			'black-friday-2023' => array(
				'start' => new DateTime( '2023-11-22 10:00:00', new DateTimeZone( 'Europe/Brussels' ) ),
				'end' => new DateTime( '2023-11-29 10:00:00', new DateTimeZone( 'Europe/Brussels' ) ),
				'notice_title' => 'Black Friday & Cyber Monday Deal',
				'notice_text' => 'Get a 30% discount right now!',
				'page_title' => 'Black Friday Discount!',
				'page_text' => 'Good news: we\'re having a Black Friday & Cyber Monday sale and you can get a <strong>30% discount on any of our plugins</strong>.',
				'url' => 'https://bootstrapped.ventures/black-friday/',
			),
			'birthday-2024' => array(
				'start' => new DateTime( '2024-01-24 10:00:00', new DateTimeZone( 'Europe/Brussels' ) ),
				'end' => new DateTime( '2024-01-31 10:00:00', new DateTimeZone( 'Europe/Brussels' ) ),
				'notice_title' => 'Celebrating my birthday',
				'notice_text' => 'Get a 30% discount right now!',
				'page_title' => 'Birthday Discount!',
				'page_text' => 'Good news: I\'m celebrating my birthday with a <strong>30% discount on any of our plugins</strong>.',
				'url' => 'https://bootstrapped.ventures/birthday-discount/',
			),
		);

		$now = new DateTime();

		foreach ( $campaigns as $id => $campaign ) {
			if ( $campaign['start'] < $now && $now < $campaign['end'] ) {
				$campaign['id'] = $id;

				// Add parameters to URL.
				$campaign['url'] .= '?utm_source=wprm&utm_medium=plugin&utm_campaign=' . urlencode( $id );

				// Get countdown interval.
				$now = new DateTime();
				$interval = $now->diff( $campaign['end'] );

				$campaign['countdown'] = array(
					'days' => $interval->days,
					'hours' => $interval->h,
					'minutes' => $interval->i,
					'seconds' => $interval->s,
				);

				self::$campaign = $campaign;
				break;
			}
		}

		if ( false !== self::$campaign ) {
			add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 99 );
			add_filter( 'wprm_admin_dashboard_localize', array( __CLASS__, 'dashboard_marketing' ) );
			add_filter( 'wprm_admin_notices', array( __CLASS__, 'marketing_notice' ) );
		}
	}

	/**
	 * Add the marketing menu page.
	 *
	 * @since    5.8.1
	 */
	public static function add_submenu_page() {
		if ( ! WPRM_Addons::is_active( 'elite' ) ) {
			add_submenu_page( 'wprecipemaker', 'WPRM Discount', '~ 30% Discount! ~', 'manage_options', 'wprm_marketing', array( __CLASS__, 'page_template' ) );
		}
	}

	/**
	 * Template for the marketing page.
	 *
	 * @since    5.8.1
	 */
	public static function page_template() {
		echo '<div class="wrap">';
		echo '<h1>' . self::$campaign['page_title'] . '</h1>';
		echo '<p style="font-size: 14px; max-width: 600px;">' . self::$campaign['page_text'] . '</p>';

		// Countdown.
		echo '<p style="color: darkred; font-size: 14px;"><strong>Don\'t miss out!</strong><br/>Only ';
		printf( _n( '%s day', '%s days', self::$campaign['countdown']['days'], 'wp-recipe-maker' ), number_format_i18n( self::$campaign['countdown']['days'] ) );
		echo ' ';
		printf( _n( '%s hour', '%s hours', self::$campaign['countdown']['hours'], 'wp-recipe-maker' ), number_format_i18n( self::$campaign['countdown']['hours'] ) );
		echo ' ';
		printf( _n( '%s minute', '%s minutes', self::$campaign['countdown']['minutes'], 'wp-recipe-maker' ), number_format_i18n( self::$campaign['countdown']['minutes'] ) );
		echo ' left.</p>';

		// CTA.
		echo '<a href="' . esc_url( self::$campaign['url'] ) . '" target="_blank" class="button button-primary" style="font-size: 14px;">Learn more about the sale!</a>';
		
		echo '</div>';
	}

	/**
	 * Add marketing to the dashboard page.
	 *
	 * @since   7.6.0
	 * @param	array $dashboard Data passed along to the dashboard.
	 */
	public static function dashboard_marketing( $dashboard ) {
		$dashboard['marketing'] = false;

		if ( false !== self::$campaign ) {
			if ( ! WPRM_Notices::is_dismissed( 'dashboard_' . self::$campaign['id'] ) ) {
				$dashboard['marketing'] = self::$campaign;
			}
		}

		return $dashboard;
	}

	/**
	 * Show the marketing notice.
	 *
	 * @since    5.8.1
	 * @param	array $notices Existing notices.
	 */
	public static function marketing_notice( $notices ) {
		if ( ! WPRM_Addons::is_active( 'elite' ) ) {
			$notices[] = array(
				'id' => 'marketing_' . self::$campaign['id'],
				'title' => self::$campaign['notice_title'],
				'text' => '<a href="' . esc_url( self::$campaign['url'] ) . '" target="_blank">' . self::$campaign['notice_text'] . '</a>',
			);
		}

		return $notices;
	}
}

WPRM_Marketing::init();
