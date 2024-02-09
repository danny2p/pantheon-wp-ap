<?php
/**
 * Track changes happening to recipes.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Track changes happening to recipes.
 *
 * @since      9.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Changelog {

	/**
	 * List of change types with their name.
	 *
	 * @since 	9.2.0
	 * @access  private
	 * @var     mixed $types List of change types.
	 */
	private static $types = array();

	/**
	 * Register actions and filters.
	 *
	 * @since	9.2.0
	 */
	public static function init() {
		self::$types = array(
			'recipe_created' => __( 'Recipe Created', 'wp-recipe-maker' ),
			'recipe_edited' => __( 'Recipe Edited', 'wp-recipe-maker' ),
			'recipe_trashed' => __( 'Recipe Trashed', 'wp-recipe-maker' ),
			'recipe_deleted' => __( 'Recipe Deleted', 'wp-recipe-maker' ),
		);

		add_action( 'wprm_daily_cron', array( __CLASS__, 'remove_old_logs' ) );
	}

	/**
	 * Log a specific change.
	 *
	 * @since    9.2.0
	 * @param    string $type 		Type of action to log.
	 * @param    mixed 	$object_id 	ID being affected.
	 * @param    mixed 	$data 		Data related to the log.
	 */
	public static function log( $type, $object_id, $data = array() ) {
		if ( WPRM_Settings::get( 'changelog_enabled' ) ) {

			// Get object.
			$object_meta = self::get_object_meta( $object_id );

			// Add/sanitize meta.
			$sanitized_meta = array();

			switch ( $type ) {
				case 'recipe_trashed':
					if ( isset( $data['previous_status'] ) ) {
						$sanitized_meta['previous_status'] = $data['previous_status'];
					}
					break;
			}

			// Construct and log change.
			$change = array(
				'type' => $type,
				'meta' => $sanitized_meta,
				'object_id' => $object_id,
				'object_meta' => $object_meta,
				'user_meta' => self::get_user_meta(),
			);

			WPRM_Changelog_Database::add( $change );

			// Maybe send email nofication.
			self::maybe_send_email_notification( $type );
		}
	}

	/**
	 * Maybe send email notification about change.
	 *
	 * @since    9.2.0
	 * @param    string $type Type of change.
	 */
	public static function maybe_send_email_notification( $type ) {
		$to = WPRM_Settings::get( 'changelog_admin_email' );
		$types = WPRM_Settings::get( 'changelog_email_notification_types' );

		if ( $to && is_email( $to ) && in_array( $type, $types ) ) {
			$manage_link = admin_url( 'admin.php?page=wprm_manage#changelog' );

			$subject = __( 'WP Recipe Maker Change:', 'wp-recipe-maker' ) . ' ' . $type;
			$message = __( 'Change detected that you wanted to be notified about.', 'wp-recipe-maker' );
			$message .= "\n";
			$message .= __( 'Find out more', 'wp-recipe-maker' );
			$message .= ': ' . $manage_link;
			$message .= "\n";

			wp_mail( $to, $subject, $message );
		}
	}

	/**
	 * Get object information.
	 *
	 * @since    9.2.0
	 * @param    string $id	ID of the object.
	 */
	public static function get_object_meta( $id ) {
		$meta = array();

		if ( $id ) {
			$post_type = get_post_type( $id );

			if ( $post_type && WPRM_POST_TYPE === $post_type ) {
				$recipe = WPRM_Recipe_Manager::get_recipe( $id );

				if ( $recipe ) {
					$recipe_data = $recipe->get_data();
					$recipe_size = strlen( json_encode( $recipe_data ) );

					$meta['type'] = 'recipe';
					$meta['name'] = $recipe->name();
					$meta['size'] = $recipe_size;
				}
			}
		}

		return $meta;
	}

	/**
	 * Get user information.
	 *
	 * @since    9.2.0
	 */
	public static function get_user_meta() {
		$user = array(
			'ip' => self::get_user_ip(),
		);

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		}

		return $user;
	}

	/**
	 * Get the IP address of the current user.
	 * Source: http://stackoverflow.com/questions/6717926/function-to-get-user-ip-address
	 *
	 * @since    9.2.0
	 */
	public static function get_user_ip() {
		foreach ( array( 'REMOTE_ADDR', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED' ) as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( array_map( 'trim', explode( ',', $_SERVER[ $key ] ) ) as $ip ) { // Input var ok.
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}
		return 'unknown';
	}

	/**
	 * Remove old changelogs.
	 *
	 * @since	9.2.0
	 */
	public static function remove_old_logs() {
		if ( WPRM_Settings::get( 'changelog_enabled' ) ) {
			$days_to_keep = WPRM_Settings::get( 'changelog_days_to_keep' );
			
			if ( 'unlimited' !== $days_to_keep ) {
				$days_to_keep = intval( $days_to_keep );
				$days_to_keep = 0 < $days_to_keep ? $days_to_keep : 90;

				WPRM_Changelog_Database::purge( $days_to_keep );
			}
		}
	}
}

WPRM_Changelog::init();
