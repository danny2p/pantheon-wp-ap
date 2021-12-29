<?php
/**
 * Responsible for handling migrations when updating the plugin.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.7.1
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for handling migrations when updating the plugin.
 *
 * @since      1.7.1
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Migrations {

	/**
	 *  Notices to show after migrating.
	 *
	 * @since    1.10.0
	 * @access   private
	 * @var      array $notices Notices to show.
	 */
	private static $notices = array();

	/**
	 * Array containing the specific migrations that have been performed.
	 *
	 * @since    2.2.0
	 * @access   private
	 * @var      array $links Array containing the specific migrations that have been performed.
	 */
	private static $specific_migrations = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    1.7.1
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'check_if_migration_needed' ) );
		add_action( 'admin_notices', array( __CLASS__, 'migration_notices' ) );
	}

	/**
	 * Add the import submenu to the WPRM menu.
	 *
	 * @since    1.7.1
	 */
	public static function check_if_migration_needed() {
		$migrated_to_version = get_option( 'wprm_migrated_to_version', '0.0.0' );

		if ( version_compare( $migrated_to_version, '1.10.0' ) < 0 ) {
			require_once( WPRM_DIR . 'includes/admin/migrations/wprm-1-10-0-wpurp.php' );
		}

		if ( '0.0.0' === $migrated_to_version ) {
			self::$notices = array();
			self::set_migrated_to( 'ratings_db' );
			self::set_migrated_to( 'ratings_db_post_id' );
		} else {
			// Only do these migrations when coming from an older version.
			if ( version_compare( $migrated_to_version, '1.7.1' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-1-7-1-ingredient-ids.php' );
			}
			if ( version_compare( $migrated_to_version, '1.19.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-1-19-0-nutrition-label.php' );
			}
			if ( version_compare( $migrated_to_version, '1.23.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-1-23-0-categories.php' );
			}
			if ( version_compare( $migrated_to_version, '1.25.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-1-25-0-metadata.php' );
			}
			if ( version_compare( $migrated_to_version, '1.27.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-1-27-0-facebook.php' );
			}
			if ( version_compare( $migrated_to_version, '3.0.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-3-0-0-labels.php' );
			}
			if ( version_compare( $migrated_to_version, '3.0.3' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-3-0-3-license.php' );
			}
			if ( version_compare( $migrated_to_version, '3.2.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-3-2-0-clean-template.php' );
			}
			if ( version_compare( $migrated_to_version, '4.0.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-4-0-0-template-mode.php' );
			}
			if ( version_compare( $migrated_to_version, '4.0.4' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-4-0-4-cutout-template.php' );
			}
			if ( version_compare( $migrated_to_version, '4.2.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-4-2-0-comment-stars.php' );
			}
			if ( version_compare( $migrated_to_version, '5.2.1' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-5-2-1-type-templates.php' );
			}
			if ( version_compare( $migrated_to_version, '5.3.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-5-3-0-nutrition-daily-values.php' );
			}
			if ( version_compare( $migrated_to_version, '5.8.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-5-8-0-snippet-templates.php' );
			}
			if ( version_compare( $migrated_to_version, '5.11.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-5-11-0-roundup-templates.php' );
			}
			if ( version_compare( $migrated_to_version, '6.8.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-6-8-0-nutrition-style.php' );
			}
			if ( version_compare( $migrated_to_version, '7.3.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-7-3-0-cls-settings.php' );
			}
			if ( version_compare( $migrated_to_version, '7.6.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-7-6-0-nofollow-settings.php' );
			}
			if ( version_compare( $migrated_to_version, '8.0.0' ) < 0 ) {
				require_once( WPRM_DIR . 'includes/admin/migrations/wprm-8-0-0-collection-layout.php' );
			}

			// Specific migrations.
			if ( ! self::is_migrated_to( 'ratings_db' ) ) {
				$notice = 'A migration for your recipe ratings is required for them to display.<br/>';
				$notice .= '<a href="' . esc_url( admin_url( 'admin.php?page=wprm_finding_ratings' ) ) . '" target="_blank">Click here to migrate your ratings now</a>.';

				self::$notices[] = $notice;
			}
			if ( ! self::is_migrated_to( 'ratings_db_post_id' ) ) {
				$notice = 'A performance update for your comment ratings is required.<br/>';
				$notice .= '<a href="' . esc_url( admin_url( 'admin.php?page=wprm_finding_ratings' ) ) . '" target="_blank">Click here to update your comment ratings now</a>. Run twice to clear the cache!';

				self::$notices[] = $notice;
			}
		}

		update_option( 'wprm_migrated_to_version', WPRM_VERSION );
	}

	/**
	 * Show any migration notices that might have been set.
	 *
	 * @since    1.10.0
	 */
	public static function migration_notices() {
		foreach ( self::$notices as $notice ) {
			echo '<div class="notice notice-warning is-dismissible">';
			echo '<p><strong>WP Recipe Maker</strong><br/>';
			echo wp_kses_post( $notice );
			echo '</p>';
			echo '</div>';
		}
	}

	/**
	 * Get all specific migrations.
	 *
	 * @since    2.2.0
	 */
	public static function get_specific_migrations() {
		if ( ! is_array( isset( self::$specific_migrations ) ) ) {
			self::$specific_migrations = get_option( 'wprm_specific_migrations', array() );
		}

		return self::$specific_migrations;
	}

	/**
	 * Check if a specific migration has been performed.
	 *
	 * @since    2.2.0
	 * @param    mixed $migration Name of the migration to check.
	 */
	public static function is_migrated_to( $migration ) {
		$migrations = self::get_specific_migrations();
		return isset( $migrations[ $migration ] ) && $migrations[ $migration ];
	}

	/**
	 * Set a specific migration as performed.
	 *
	 * @since    2.2.0
	 * @param    mixed $migration Name of the migration.
	 */
	public static function set_migrated_to( $migration ) {
		$migrations = self::get_specific_migrations();
		$migrations[ $migration ] = true;
		self::$specific_migrations = $migrations;
		update_option( 'wprm_specific_migrations', $migrations, false );
	}
}

WPRM_Migrations::init();
