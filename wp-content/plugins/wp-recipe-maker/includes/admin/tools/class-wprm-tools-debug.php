<?php
/**
 * Responsible for generating and downloading debug information.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/tools
 */

/**
 * Responsible for generating and downloading debug information.
 *
 * @since      10.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/tools
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_Debug {

	/**
	 * Register actions and filters.
	 *
	 * @since 10.4.0
	 */
	public static function init() {
		add_action( 'wp_ajax_wprm_download_debug_info', array( __CLASS__, 'ajax_download_debug_info' ) );
	}

	/**
	 * Download debug information as a JSON file through AJAX.
	 *
	 * @since 10.4.0
	 */
	public static function ajax_download_debug_info() {
		if ( ! check_ajax_referer( 'wprm', 'security', false ) ) {
			wp_die();
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$payload = array(
			'generated_at' => gmdate( 'c' ),
			'site_url'     => get_site_url(),
			'wprm'         => self::get_wprm_data(),
			'wordpress'    => self::get_wordpress_data(),
			'server'       => self::get_server_data(),
			'theme'        => self::get_theme_data(),
			'plugins'      => self::get_plugins_data(),
		);

		$domain   = wp_parse_url( get_site_url(), PHP_URL_HOST );
		$domain   = $domain ? sanitize_file_name( $domain ) : 'site';
		$filename = 'wprm-debug-' . $domain . '-' . gmdate( 'Y-m-d' ) . '.json';

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die();
	}

	/**
	 * Gather WP Recipe Maker-specific data.
	 *
	 * @since 10.4.0
	 */
	private static function get_wprm_data() {
		// Versions.
		$versions = array(
			'free' => WPRM_VERSION,
		);

		if ( WPRM_Addons::is_active( 'elite' ) ) {
			$versions['elite'] = defined( 'WPRMP_VERSION' ) ? WPRMP_VERSION : 'unknown';
		} elseif ( WPRM_Addons::is_active( 'pro' ) ) {
			$versions['pro'] = defined( 'WPRMP_VERSION' ) ? WPRMP_VERSION : 'unknown';
		} elseif ( WPRM_Addons::is_active( 'premium' ) ) {
			$versions['premium'] = defined( 'WPRMP_VERSION' ) ? WPRMP_VERSION : 'unknown';
		}

		// Recipe counts.
		$recipe_counts_raw = wp_count_posts( WPRM_POST_TYPE );
		$recipe_counts     = array(
			'publish' => (int) $recipe_counts_raw->publish,
			'future'  => (int) $recipe_counts_raw->future,
			'draft'   => (int) $recipe_counts_raw->draft,
			'pending' => (int) $recipe_counts_raw->pending,
			'private' => (int) $recipe_counts_raw->private,
			'trash'   => (int) $recipe_counts_raw->trash,
		);

		// List (roundup) counts.
		$list_counts_raw = wp_count_posts( WPRM_LIST_POST_TYPE );
		$list_counts     = array(
			'publish' => (int) $list_counts_raw->publish,
			'future'  => (int) $list_counts_raw->future,
			'draft'   => (int) $list_counts_raw->draft,
			'pending' => (int) $list_counts_raw->pending,
			'private' => (int) $list_counts_raw->private,
			'trash'   => (int) $list_counts_raw->trash,
		);

		// Settings that differ from defaults.
		$current_settings  = WPRM_Settings::get_settings();
		$default_settings  = WPRM_Settings::get_defaults();
		$changed_settings  = array();

		foreach ( $current_settings as $key => $value ) {
			if ( ! isset( $default_settings[ $key ] ) || $default_settings[ $key ] !== $value ) {
				$changed_settings[ $key ] = $value;
			}
		}

		return array(
			'versions'         => $versions,
			'update_history'   => WPRM_Version::get_update_history(),
			'recipe_counts'    => $recipe_counts,
			'list_counts'      => $list_counts,
			'settings_changed' => $changed_settings,
		);
	}

	/**
	 * Gather WordPress environment data.
	 *
	 * @since 10.4.0
	 */
	private static function get_wordpress_data() {
		$timezone_string = get_option( 'timezone_string', '' );
		if ( ! $timezone_string ) {
			$gmt_offset      = get_option( 'gmt_offset', 0 );
			$timezone_string = 'UTC' . ( $gmt_offset >= 0 ? '+' : '' ) . $gmt_offset;
		}

		return array(
			'version'      => get_bloginfo( 'version' ),
			'is_multisite' => is_multisite(),
			'debug_mode'   => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'timezone'     => $timezone_string,
			'language'     => get_locale(),
		);
	}

	/**
	 * Gather server / PHP environment data.
	 *
	 * @since 10.4.0
	 */
	private static function get_server_data() {
		global $wpdb;

		$web_server = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'unknown';

		return array(
			'php_version'        => phpversion(),
			'database_version'   => $wpdb->db_version(),
			'memory_limit'       => ini_get( 'memory_limit' ),
			'max_execution_time' => (int) ini_get( 'max_execution_time' ),
			'max_upload_size'    => size_format( wp_max_upload_size() ),
			'web_server'         => $web_server,
		);
	}

	/**
	 * Gather active theme data.
	 *
	 * @since 10.4.0
	 */
	private static function get_theme_data() {
		$theme        = wp_get_theme();
		$is_child     = $theme->parent() ? true : false;
		$parent_theme = null;

		if ( $is_child ) {
			$parent       = $theme->parent();
			$parent_theme = array(
				'name'    => $parent->get( 'Name' ),
				'version' => $parent->get( 'Version' ),
				'author'  => $parent->get( 'Author' ),
			);
		}

		return array(
			'name'         => $theme->get( 'Name' ),
			'version'      => $theme->get( 'Version' ),
			'author'       => $theme->get( 'Author' ),
			'is_child'     => $is_child,
			'parent_theme' => $parent_theme,
		);
	}

	/**
	 * Gather active plugins data.
	 *
	 * @since 10.4.0
	 */
	private static function get_plugins_data() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_slugs   = get_option( 'active_plugins', array() );
		$updates        = get_site_transient( 'update_plugins' );
		$plugins_output = array();

		foreach ( $active_slugs as $slug ) {
			if ( ! isset( $all_plugins[ $slug ] ) ) {
				continue;
			}

			$plugin     = $all_plugins[ $slug ];
			$is_outdated = isset( $updates->response[ $slug ] );

			$plugins_output[] = array(
				'name'        => $plugin['Name'],
				'slug'        => $slug,
				'version'     => $plugin['Version'],
				'is_outdated' => $is_outdated,
			);
		}

		return $plugins_output;
	}
}

WPRM_Tools_Debug::init();
