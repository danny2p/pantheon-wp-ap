<?php
/**
 * Responsible for the recipe analytics.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the recipe analytics.
 *
 * @since      6.5.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Analytics {

	/**
	 * List of action types with their name.
	 *
	 * @since    7.4.0
	 * @access   private
	 * @var      mixed $types List of action types.
	 */
	private static $types = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    6.7.0
	 */
	public static function init() {
		self::$types = array(
			'print' => __( 'Print', 'wp-recipe-maker' ),

			'equipment-link' => __( 'Equipment Link', 'wp-recipe-maker' ),
			'ingredient-link' => __( 'Ingredient Link', 'wp-recipe-maker' ),
			'instruction-link' => __( 'Instruction Link', 'wp-recipe-maker' ),

			'adjust-servings' => __( 'Adjust Servings', 'wp-recipe-maker' ),
			'unit-conversion' => __( 'Unit Conversion', 'wp-recipe-maker' ),

			'comment' => __( 'Comment', 'wp-recipe-maker' ),
			'user-rating' => __( 'User Rating', 'wp-recipe-maker' ),

			'jump-to-recipe' => __( 'Jump to Recipe', 'wp-recipe-maker' ),
			'jump-to-video' => __( 'Jump to Video', 'wp-recipe-maker' ),

			'pin-button' => __( 'Pin Button', 'wp-recipe-maker' ),
			'facebook-share-button' => __( 'Facebook Share', 'wp-recipe-maker' ),
			'twitter-share-button' => __( 'Twitter Share', 'wp-recipe-maker' ),
			'text-share-button' => __( 'Text Share', 'wp-recipe-maker' ),
			'email-share-button' => __( 'Email Share', 'wp-recipe-maker' ),

			'add-to-collections-button' => __( 'Add to Recipe Collections', 'wp-recipe-maker' ),
			'add-to-shopping-list-button' => __( 'Add to Quick Access Shopping List', 'wp-recipe-maker' ),
		);

		add_filter( 'wprm_settings_update', array( __CLASS__, 'check_hh_token_on_settings_update' ), 10, 2 );

		add_action( 'wprm_daily_cron', array( __CLASS__, 'remove_old_actions' ) );
	}

	/**
	 * Check if the Honey & Home integration is enabled and token has changed.
	 *
	 * @since    6.7.0
	 * @param    array $new_settings Settings after update.
	 * @param    array $old_settings Settings before update.
	 */
	public static function check_hh_token_on_settings_update( $new_settings, $old_settings ) {
		if ( isset( $new_settings['honey_home_integration'] ) && $new_settings['honey_home_integration'] ) {
			$old_token = isset( $old_settings['honey_home_token'] ) ? $old_settings['honey_home_token'] : '';
			$new_token = isset( $new_settings['honey_home_token'] ) ? $new_settings['honey_home_token'] : '';

			if ( $new_token !== $old_token ) {
				// Activate new token.
				if ( $new_token ) {
					$domain = $_SERVER['SERVER_NAME'];
					$response = wp_remote_get( 'https://dailygrub.com/api/verifyDomain?id=' . urlencode( $new_token ) . '&domain=' . urlencode( $domain ) );

					$status = false;
					if ( ! is_wp_error( $response ) ) {
						$status = (array) json_decode( wp_remote_retrieve_body( $response ) );
					}

					update_option( 'hh_integration_status', $status, false );

					if ( ! $status || ! isset( $status['success'] ) ) {
						$status = array(
							'success' => false,
							'message' => 'Unknown error',
						);
					}

					if ( ! $status['success'] ) {
						$new_settings['honey_home_token'] = '';
					}
				}
			}
		}

		return $new_settings;
	}

	/**
	 * Register a specific action.
	 *
	 * @since    6.5.0
	 * @param    int $recipe_id Recipe to register the action for.
	 * @param    int $post_id 	Post to register the action for.
	 * @param    string $type 	Type of action to register.
	 * @param    mixed $meta 	Meta for the action.
	 * @param    string $uid 	Visitor UID.
	 */
	public static function register_action( $recipe_id, $post_id, $type, $meta = array(), $uid = '' ) {
		if ( WPRM_Settings::get( 'analytics_enabled' ) ) {
			// Respect "Do Not Track" request.
			if ( isset( $_SERVER['HTTP_DNT'] ) && 1 == $_SERVER['HTTP_DNT'] ) {
				return;
			}

			// Add/sanitize meta.
			$sanitized_meta = array();

			switch ( $type ) {
				case 'print':
					$sanitized_meta['location'] = isset( $meta['location'] ) ? sanitize_key( $meta['location'] ) : 'unknown';
					break;
				case 'equipment-link':
					$sanitized_meta['type'] = isset( $meta['type'] ) ? sanitize_key( $meta['type'] ) : 'unknown';
				case 'ingredient-link':
					$sanitized_meta['name'] = isset( $meta['name'] ) ? sanitize_text_field( $meta['name'] ) : 'unknown';
				case 'instruction-link':
					$sanitized_meta['url'] = isset( $meta['url'] ) && ! is_array( $meta['url'] ) ? $meta['url'] : 'unknown';
					break;
				case 'adjust-servings':
				case 'unit-conversion':
					$sanitized_meta['type'] = isset( $meta['type'] ) ? sanitize_key( $meta['type'] ) : 'unknown';
					break;
				case 'comment':
					$sanitized_meta['comment_id'] = isset( $meta['comment_id'] ) ? intval( $meta['comment_id'] ) : 'unknown';
				case 'user-rating':
					$sanitized_meta['rating'] = isset( $meta['rating'] ) ? intval( $meta['rating'] ) : 'unknown';
					break;
			}

			// Visitor UID.
			$visitor_id = $uid ? sanitize_key( $uid ) : self::get_visitor_id();
			$visitor = self::get_visitor();

			// Should exclude this IP?
			if ( isset( $visitor['ip'] ) && self::should_exclude_ip( $visitor['ip'] ) ) {
				return;
			}

			// Construct and register action.
			$action = array(
				'recipe_id' => $recipe_id,
				'post_id' => $post_id,
				'type' => $type,
				'meta' => $sanitized_meta,
				'visitor_id' => $visitor_id,
				'visitor' => $visitor,
			);

			WPRM_Analytics_Database::add( $action );
		}
	}

	/**
	 * Get visitor ID.
	 *
	 * @since    6.5.0
	 */
	public static function get_visitor_id() {
		$visitor_id = isset( $_COOKIE[ 'wprm_analytics_visitor' ] ) ? sanitize_key( $_COOKIE[ 'wprm_analytics_visitor' ] ) : false;

		if ( ! $visitor_id ) {
			$visitor_id = self::set_visitor_id();
		}

		return $visitor_id;
	}

	/**
	 * Set the visitor ID.
	 *
	 * @since    6.5.0
	 */
	public static function set_visitor_id() {
		$visitor_id = uniqid( '', true );
		setcookie( 'wprm_analytics_visitor', $visitor_id, 2147483647, '/' );

		return $visitor_id;
	}

	/**
	 * Get visitor information.
	 *
	 * @since    6.5.0
	 */
	public static function get_visitor() {
		$visitor = array(
			'ip' => self::get_user_ip(),
		);

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$visitor['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		}

		return $visitor;
	}

	/**
	 * Get the IP address of the current user.
	 * Source: http://stackoverflow.com/questions/6717926/function-to-get-user-ip-address
	 *
	 * @since    6.5.0
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
	 * Check if a specific IP address should be excluded.
	 *
	 * @since    7.1.0
	 * @param    string $ip IP address to check.
	 */
	public static function should_exclude_ip( $ip ) {
		// IP addresses to exclude.
		$exclude_ips_raw = WPRM_Settings::get( 'analytics_exclude_ips' );
		$exclude_ips_raw = preg_split( "/\r\n|\n|\r/", $exclude_ips_raw );

		$exclude_ips = array();
		$exclude_ip_ranges = array();

		foreach ( $exclude_ips_raw as $exclude_ip ) {
			if ( strpos( $exclude_ip, '-' ) ) {
				$range_ips = explode( '-', $exclude_ip );

				if ( 2 === count( $range_ips ) ) {
					$from = ip2long( trim( $range_ips[0] ) );
					$to = ip2long( trim( $range_ips[1] ) );

					if ( $from && $to && $from <= $to ) {
						$exclude_ip_ranges[] = array(
							'from' => $from,
							'to' => $to,
						);
					}
				}
			} else {
				$exclude_ips[] = trim( $exclude_ip );
			}
		}

		// Check if should be excluded.
		if ( in_array( $ip, $exclude_ips ) ) {
			return true;
		} else {
			$ip_long = ip2long( $ip );

			if ( $ip_long ) {
				foreach ( $exclude_ip_ranges as $range ) {
					if ( $ip_long >= $range['from'] && $ip_long <= $range['to'] ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get optional meta that was set in the frontend.
	 *
	 * @since    6.5.0
	 */
	public static function get_frontend_meta() {
		$cookie = isset( $_COOKIE[ 'wprm_analytics_meta' ] ) ? $_COOKIE[ 'wprm_analytics_meta' ] : false;

		if ( $cookie ) {
			$decoded = json_decode( stripslashes( $_COOKIE[ 'wprm_analytics_meta' ] ), true );

			if ( $decoded ) {
				return $decoded;
			}
		}

		return array();
	}

	/**
	 * Remove old actions.
	 *
	 * @since    7.4.0
	 */
	public static function remove_old_actions() {
		if ( WPRM_Settings::get( 'analytics_enabled' ) ) {
			$days_to_keep = WPRM_Settings::get( 'analytics_days_to_keep' );
			
			if ( 'unlimited' !== $days_to_keep ) {
				$days_to_keep = intval( $days_to_keep );
				$days_to_keep = 0 < $days_to_keep ? $days_to_keep : 90;

				WPRM_Analytics_Database::purge( $days_to_keep );
			}
		}
	}

	/**
	 * Get analytics data for the dashboard charts.
	 *
	 * @since    7.4.0
	 */
	public static function get_dashboard_chart_data() {
		$total_actions = 0;
		$per_day = array();
		$per_type = array();
		$per_recipe = array();

		$date_format = 'M j';

		for ( $i = 7; $i >= 0; $i-- ) {
			$datetime = new DateTime( $i . ' days ago' );
			$date = $datetime->format( $date_format );

			$per_day_date = array(
				'date' => $date,
				'total' => 0,
			);

			$actions = WPRM_Analytics_Database::get_aggregated_actions_for( $date );

			foreach ( $actions as $action ) {
				$total = intval( $action->total );
				$unique = intval( $action->total_unique );

				// Per day.
				$per_day_date[ 'total' ] += $total;
				$total_actions += $total;

				// Per action type.
				if ( $action->type ) {
					if ( ! isset( $per_type[ $action->type ] ) ) {
						$per_type[ $action->type ] = array(
							'total' => 0,
							'unique' => 0,
						);
					}
					$per_type[ $action->type ]['total'] += $total;
					$per_type[ $action->type ]['unique'] += $unique;
				}

				// Per recipe.
				$recipe_id = intval( $action->recipe_id );
				if ( $recipe_id ) {
					if ( ! isset( $per_recipe[ '' . $recipe_id ] ) ) {
						$per_recipe[ '' . $recipe_id ] = array(
							'total' => 0,
							'unique' => 0,
						);
					}
					$per_recipe[ '' . $recipe_id ]['total'] += $total;
					$per_recipe[ '' . $recipe_id ]['unique'] += $unique;
				}
			}

			$per_day[] = $per_day_date;
		}

		// Sort by total interactions.
		uasort( $per_type, function($a, $b) {
			if ( $a['total'] == $b['total'] ) {
				return 0;
			}
			return $a['total'] > $b['total'] ? -1 : 1;
		} );

		uasort( $per_recipe, function($a, $b) {
			if ( $a['total'] == $b['total'] ) {
				return 0;
			}
			return $a['total'] > $b['total'] ? -1 : 1;
		} );

		// Get names for top types.
		$top_types = array();

		foreach ( $per_type as $type => $interactions ) {
			if ( array_key_exists( $type, self::$types ) ) {
				$top_types[] = array(
					'id' => $type,
					'name' => self::$types[ $type ],
					'total' => $interactions['total'],
					'unique' => $interactions['unique'],
				);
			}
		}

		// Get names for top recipes.
		$top_recipes = array();

		foreach ( $per_recipe as $recipe_id => $interactions ) {
			$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

			if ( $recipe ) {
				$top_recipes[] = array(
					'id' => $recipe->id(),
					'name' => $recipe->name(),
					'total' => $interactions['total'],
					'unique' => $interactions['unique'],
				);
			}

			// Stop after 5 recipes.
			if ( 5 === count( $top_recipes ) ) {
				break;
			}
		}

		return array(
			'total' => $total_actions,
			'per_day' => $per_day,
			'per_type' => $top_types,
			'per_recipe' => $top_recipes,
		);
	}
}
WPRM_Analytics::init();
