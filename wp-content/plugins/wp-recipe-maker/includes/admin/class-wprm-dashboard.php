<?php
/**
 * Handle the dashboard page.
 *
 * @link       https://bootstrapped.ventures
 * @since      7.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/modal
 */

/**
 * Handle the dashboard page.
 *
 * @since      7.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/modal
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Dashboard {

	/**
	 * Register actions and filters.
	 *
	 * @since    7.4.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_dashboard_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Add the dashboard submenu to the WPRM menu.
	 *
	 * @since	7.4.0
	 */
	public static function add_dashboard_page() {
		$news_label = self::get_news_label();
		add_submenu_page( 'wprecipemaker', __( 'Dashboard', 'wp-recipe-maker' ), __( 'Dashboard', 'wp-recipe-maker' ) . $news_label, WPRM_Settings::get( 'features_dashboard_access' ), 'wprecipemaker', array( __CLASS__, 'page_template' ) );
	}

	/**
	 * Get the template for this submenu.
	 *
	 * @since    7.4.0
	 */
	public static function page_template() {
		echo '<div class="wrap wprm-wrap"><div id="wprm-admin-dashboard">Loading...</div></div>';
	}

	/**
	 * Enqueue stylesheets and scripts.
	 *
	 * @since    7.4.0
	 */
	public static function enqueue() {
		$screen = get_current_screen();

		// Only load on dashboard page.
		if ( 'toplevel_page_wprecipemaker' === $screen->id ) {
			wp_enqueue_style( 'wprm-admin-dashboard', WPRM_URL . 'dist/admin-dashboard.css', array(), WPRM_VERSION, 'all' );
			wp_enqueue_script( 'wprm-admin-dashboard', WPRM_URL . 'dist/admin-dashboard.js', array( 'wprm-admin', 'wprm-admin-modal' ), WPRM_VERSION, true );

			$news = self::get_news();
			self::update_last_news_read( $news['latest_date'] );

			$datetime = new DateTime();
			$today_formatted = $datetime->format( 'M j' );

			$localize_data = apply_filters( 'wprm_admin_dashboard_localize', array(
				'settings' => array(
					'analytics_enabled' => WPRM_Settings::get( 'analytics_enabled' ),
					'honey_home_integration' => WPRM_Settings::get( 'honey_home_integration' ),
				),
				'news' => $news['items'],
				'recipes' => WPRM_Recipe_Manager::get_latest_recipes( 5, 'manage' ),
				'health' => WPRM_Health_Check::get_data(),
				'today_formatted' => $today_formatted,
				'marketing' => false,
				'feedback' => WPRM_Feedback::show_feedback_request(),
			) );
			wp_localize_script( 'wprm-admin-dashboard', 'wprm_admin_dashboard', $localize_data );
		}
	}

	/**
	 * Get news to display on the dashboard.
	 *
	 * @since    7.4.0
	 */
	public static function get_news() {
		$news = array();

		// Sources to get the news from, filterable.
		$sources = apply_filters( 'wprm_dashboard_news_sources', array( WPRM_DIR . 'news.json' ) );

		foreach ( $sources as $source ) {
			if ( file_exists( $source ) ) {
				$str = file_get_contents(
					$source,
					false,
					stream_context_create( array(
						'http' => array(
							'ignore_errors' => true,
						),
					))
				);
				if ( $str ) {
					$json = json_decode( $str, true );

					if ( $json && is_array( $json ) && isset( $json['news'] ) ) {
						$news = array_merge( $news, $json['news'] );
					}
				}
			}
		}

		// Allow filtering to add in news directly as well.
		$news = apply_filters( 'wprm_dashboard_news', $news );

		// Date and title need to be set.
		$news = array_filter( $news, function( $a ) { return isset( $a['date'] ) && $a['date'] && isset( $a['title'] ) && $a['title']; } );

		// Remove exact duplicates.
		$news = array_map( 'unserialize', array_unique( array_map( 'serialize', $news ) ) );

		// Format date & label key and indicate new.
		$latest_date = '';
		$last_read_date = self::get_last_news_read();
		$new_news_count = 0;

		$news = array_map( function( $a ) use ( &$latest_date, &$last_read_date, &$new_news_count ) {
			$datetime = new DateTime( $a['date'] );
			$a['date_formatted'] = $datetime->format( 'M j' );

			if ( isset( $a['label'] ) ) {
				$a['label_key'] = sanitize_key( $a['label'] );
			}

			// Keep track of latest date.
			if ( ! $latest_date || $latest_date < $a['date'] ) {
				$latest_date = $a['date'];
			}

			// Check if new.
			if ( ! $last_read_date || $last_read_date < $a['date'] ) {
				$a['new'] = true;
				$new_news_count++;
			} else {
				$a['new'] = false;
			}

			return $a;
		}, $news );

		// Order news by date, descending.
		usort( $news, function($a, $b) {
			if ( $a['date'] == $b['date'] ) {
				return 0;
			}
			return $a['date'] > $b['date'] ? -1 : 1;
		} );

		return array(
			'items' => $news,
			'latest_date' => $latest_date,
			'last_read_date' => $last_read_date,
			'new_news_count' => $new_news_count,
		);
	}

	/**
	 * Update latest news date read.
	 *
	 * @since    7.4.0
	 */
	public static function update_last_news_read( $date ) {
		update_user_meta( get_current_user_id(), 'wprm_last_news_read', $date );
	}

	/**
	 * Get latest news date read.
	 *
	 * @since    7.4.0
	 */
	public static function get_last_news_read() {
		return get_user_meta( get_current_user_id(), 'wprm_last_news_read', true );
	}

	/**
	 * Get news count.
	 *
	 * @since    7.4.0
	 */
	public static function get_news_count() {
		if ( isset( $_GET['page'] ) && 'wprecipemaker' === $_GET['page'] ) {
			return 0;
		}

		$news = self::get_news();
		return $news['new_news_count'];
	}

	/**
	 * Get news label for menu.
	 *
	 * @since    7.4.0
	 */
	public static function get_news_label() {
		$news_count = self::get_news_count();
		
		$label = '';
		if ( 0 < $news_count ) {
			$news = sprintf( _n( '%s new news item', '%s new news items', $news_count, 'wp-recipe-maker' ), number_format_i18n( $news_count ) );
			$label = ' ' . sprintf( '<span style="float:right" class="update-plugins count-%1$d"><span class="plugin-count" aria-hidden="true">%1$d</span><span class="screen-reader-text">%2$s</span></span>', $news_count, $news );
		}

		return $label;
	}
}

WPRM_Dashboard::init();
