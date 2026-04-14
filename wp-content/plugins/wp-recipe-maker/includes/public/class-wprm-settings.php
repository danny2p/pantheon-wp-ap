<?php
/**
 * Responsible for the plugin settings.
 *
 * @link       https://bootstrapped.ventures
 * @since      1.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the plugin settings.
 *
 * @since      1.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Settings {
	/**
	 * Cached version of the settings structure.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      array    $structure    Array containing the settings structure.
	 */
	private static $structure = array();

	/**
	 * Cached version of the plugin settings.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      array    $settings    Array containing the plugin settings.
	 */
	private static $settings = array();

	/**
	 * Cached version of the settings defaults.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      array    $defaults    Default values for unset settings.
	 */
	private static $defaults = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    1.2.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
	}

	/**
	 * Add the settings submenu to the WPRM menu.
	 *
	 * @since    1.2.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( 'wprecipemaker', __( 'WPRM Settings', 'wp-recipe-maker' ), __( 'Settings', 'wp-recipe-maker' ), 'manage_options', 'wprm_settings', array( __CLASS__, 'settings_page_template' ) );
	}

	/**
	 * Get the template for the settings page.
	 *
	 * @since    1.2.0
	 */
	public static function settings_page_template() {
		wp_localize_script( 'wprm-admin', 'wprm_settings', array(
			'structure' => array_values( self::get_structure() ),
			'settings' => self::get_settings_with_defaults(),
			'defaults' => self::get_defaults(),
		) );

		require_once( WPRM_DIR . 'templates/admin/settings.php' );
	}

	/**
	 * Get the value for a specific setting.
	 *
	 * @since	1.2.0
	 * @param	mixed $setting Setting to get the value for.
	 */
	public static function get( $setting ) {
		$settings = self::get_settings();

		if ( isset( $settings[ $setting ] ) ) {
			$value = $settings[ $setting ];

			if ( is_string( $value ) ) {
				return apply_filters( 'wpml_translate_single_string', $value, 'wp-recipe-maker', 'Setting - ' . $setting, null );
			} else {
				return $value;
			}
		} else {
			return self::get_default( $setting );
		}
	}

	/**
	 * Get the settings structure.
	 *
	 * @since    3.0.0
	 */
	public static function get_structure() {
		if ( empty( self::$structure ) ) {
			require_once( WPRM_DIR . 'templates/settings/structure.php' );

			// Associate IDs.
			$structure = array();

			$index = 1;
			foreach ( $settings_structure as $group ) {
				if ( isset( $group['id'] ) ) {
					$id = $group['id'];
				} else {
					$id = 'group_' . $index;
					$index++;
				}

				$structure[ $id ] = $group;
			}

			self::$structure = $structure;
		}

		return apply_filters( 'wprm_settings_structure', self::$structure );
	}

	/**
	 * Get the default for a specific setting.
	 *
	 * @since    1.7.0
	 * @param	 mixed $setting Setting to get the default for.
	 */
	public static function get_default( $setting ) {
		$defaults = self::get_defaults();
		if ( isset( $defaults[ $setting ] ) ) {
			return $defaults[ $setting ];
		} else {
			// Force defaults cache update.
			$defaults = self::get_defaults( true );
			if ( isset( $defaults[ $setting ] ) ) {
				return $defaults[ $setting ];
			} else {
				return false;
			}
		}
	}

	/**
	 * Get the default settings.
	 *
	 * @since   1.5.0
	 * @param	boolean $force_update Whether to force an update of the cache.
	 */
	public static function get_defaults( $force_update = false ) {
		if ( $force_update || empty( self::$defaults ) ) {
			$defaults = array();
			$structure = self::get_structure();

			// Loop over structure to find settings and defaults.
			foreach ( $structure as $group ) {
				if ( isset( $group['settings'] ) ) {
					foreach ( $group['settings'] as $setting ) {
						if ( isset( $setting['id'] ) && isset( $setting['default'] ) ) {
							$defaults[ $setting['id'] ] = $setting['default'];
						}
					}
				}

				if ( isset( $group['subGroups'] ) ) {
					foreach ( $group['subGroups'] as $sub_group ) {
						if ( isset( $sub_group['settings'] ) ) {
							foreach ( $sub_group['settings'] as $setting ) {
								if ( isset( $setting['id'] ) && isset( $setting['default'] ) ) {
									$defaults[ $setting['id'] ] = $setting['default'];
								}
							}
						}
					}
				}
			}

			self::$defaults = $defaults;
		}

		return self::$defaults;
	}

	/**
	 * Get all the settings.
	 *
	 * @since    1.2.0
	 */
	public static function get_settings() {
		// Lazy load settings.
		if ( empty( self::$settings ) ) {
			self::load_settings();
		}

		return self::$settings;
	}

	/**
	 * Get all the settings with defaults if not set.
	 *
	 * @since    3.0.0
	 */
	public static function get_settings_with_defaults() {
		$settings = self::get_settings();
		$defaults = self::get_defaults();

		return array_merge( $defaults, $settings );
	}

	/**
	 * Load all the plugin settings.
	 *
	 * @since    1.2.0
	 */
	private static function load_settings() {
		$settings = get_option( 'wprm_settings', array() );
		$settings = is_array( $settings ) ? $settings : array();

		self::$settings = apply_filters( 'wprm_settings', $settings );
	}

	/**
	 * Update the plugin settings.
	 *
	 * @since    1.5.0
	 * @param		 array $settings_to_update Settings to update.
	 */
	public static function update_settings( $settings_to_update, $context = array() ) {
		$old_settings = self::get_settings();

		if ( is_array( $settings_to_update ) ) {
			$settings_to_update = self::sanitize_settings( $settings_to_update );
			$new_settings = array_merge( $old_settings, $settings_to_update );

			$new_settings = apply_filters( 'wprm_settings_update', $new_settings, $old_settings );

			update_option( 'wprm_settings', $new_settings );
			self::$settings = $new_settings;

			if ( self::should_log_settings_change( $context ) ) {
				self::maybe_log_settings_change( $old_settings, $new_settings, $context );
			}
		}

		return self::get_settings();
	}

	/**
	 * Check if a settings update should be logged to the changelog.
	 *
	 * @since	10.6.0
	 * @param	array $context Optional context for the settings update.
	 */
	private static function should_log_settings_change( $context ) {
		return is_array( $context ) && ! empty( $context['log_change'] );
	}

	/**
	 * Maybe log a settings update to the changelog.
	 *
	 * @since	10.6.0
	 * @param	array $old_settings Settings before the update.
	 * @param	array $new_settings Settings after the update.
	 * @param	array $context      Optional context for the settings update.
	 */
	private static function maybe_log_settings_change( $old_settings, $new_settings, $context ) {
		$changes = self::get_settings_change_log_entries( $old_settings, $new_settings );

		if ( ! $changes ) {
			return;
		}

		$source = isset( $context['source'] ) ? sanitize_key( $context['source'] ) : 'unknown';

		WPRM_Changelog::log(
			'settings_updated',
			0,
			array(
				'changes' => $changes,
				'object_meta' => array(
					'type' => 'settings',
					'name' => __( 'Plugin Settings', 'wp-recipe-maker' ),
					'source' => $source,
					'changed_count' => count( $changes ),
				),
			)
		);
	}

	/**
	 * Get change log entries for changed settings.
	 *
	 * @since	10.6.0
	 * @param	array $old_settings Settings before the update.
	 * @param	array $new_settings Settings after the update.
	 */
	private static function get_settings_change_log_entries( $old_settings, $new_settings ) {
		$changes = array();
		$details = self::get_details();

		$keys = array_unique(
			array_merge(
				array_keys( $details ),
				array_keys( $old_settings ),
				array_keys( $new_settings )
			)
		);

		foreach ( $keys as $setting_id ) {
			$old_exists = array_key_exists( $setting_id, $old_settings );
			$new_exists = array_key_exists( $setting_id, $new_settings );

			if ( ! $old_exists && ! $new_exists ) {
				continue;
			}

			$old_value = $old_exists ? $old_settings[ $setting_id ] : null;
			$new_value = $new_exists ? $new_settings[ $setting_id ] : null;

			if ( maybe_serialize( $old_value ) === maybe_serialize( $new_value ) ) {
				continue;
			}

			$setting_details = isset( $details[ $setting_id ] ) ? $details[ $setting_id ] : array();
			$masked = self::is_sensitive_setting( $setting_id );

			$changes[] = array(
				'id' => $setting_id,
				'label' => self::get_setting_change_label( $setting_id, $setting_details ),
				'before' => self::summarize_setting_value( $setting_id, $old_value, $setting_details, $masked ),
				'after' => self::summarize_setting_value( $setting_id, $new_value, $setting_details, $masked ),
				'masked' => $masked,
			);
		}

		return $changes;
	}

	/**
	 * Get a human-readable label for a setting.
	 *
	 * @since	10.6.0
	 * @param	string $setting_id      Setting ID.
	 * @param	array  $setting_details Setting details.
	 */
	private static function get_setting_change_label( $setting_id, $setting_details ) {
		if ( isset( $setting_details['name'] ) && $setting_details['name'] ) {
			return wp_strip_all_tags( $setting_details['name'] );
		}

		return sanitize_text_field( $setting_id );
	}

	/**
	 * Check if a setting contains sensitive information.
	 *
	 * @since	10.6.0
	 * @param	string $setting_id Setting ID.
	 */
	private static function is_sensitive_setting( $setting_id ) {
		return (bool) preg_match( '/license|token|secret|key|password|email/i', $setting_id );
	}

	/**
	 * Prepare a setting value for display.
	 *
	 * @since	10.6.0
	 * @param	string $setting_id Setting ID.
	 * @param	mixed  $value      Value to prepare.
	 */
	private static function prepare_setting_value_for_display( $setting_id, $value ) {
		if ( 'import_units' === $setting_id && is_array( $value ) ) {
			return implode( "\n", $value );
		}

		if ( 'unit_conversion_units' === $setting_id && is_array( $value ) ) {
			$prepared = array();

			foreach ( $value as $unit => $details ) {
				$prepared[ $unit ] = $details;

				if ( isset( $details['aliases'] ) && is_array( $details['aliases'] ) ) {
					$prepared[ $unit ]['aliases'] = implode( ';', $details['aliases'] );
				}
			}

			return $prepared;
		}

		return $value;
	}

	/**
	 * Get the label for a setting option.
	 *
	 * @since	10.6.0
	 * @param	array $setting_details Setting details.
	 * @param	mixed $value           Setting value.
	 */
	private static function get_setting_option_label( $setting_details, $value ) {
		if ( ! isset( $setting_details['options'] ) || ! is_array( $setting_details['options'] ) ) {
			return false;
		}

		$option_key = (string) $value;

		if ( array_key_exists( $option_key, $setting_details['options'] ) ) {
			return wp_strip_all_tags( $setting_details['options'][ $option_key ] );
		}

		return false;
	}

	/**
	 * Shorten a text value for display.
	 *
	 * @since	10.6.0
	 * @param	string $text       Text to shorten.
	 * @param	int    $max_length Maximum text length.
	 */
	private static function truncate_setting_text( $text, $max_length = 36 ) {
		$text = (string) $text;

		if ( strlen( $text ) <= $max_length ) {
			return $text;
		}

		return substr( $text, 0, $max_length - 1 ) . '...';
	}

	/**
	 * Summarize a setting value for changelog storage.
	 *
	 * @since	10.6.0
	 * @param	string $setting_id      Setting ID.
	 * @param	mixed  $value           Setting value.
	 * @param	array  $setting_details Setting details.
	 * @param	bool   $masked          Whether this value should be masked.
	 */
	private static function summarize_setting_value( $setting_id, $value, $setting_details, $masked = false ) {
		if ( $masked ) {
			return __( 'Redacted', 'wp-recipe-maker' );
		}

		$value = self::prepare_setting_value_for_display( $setting_id, $value );
		$type = isset( $setting_details['type'] ) ? $setting_details['type'] : '';

		if ( 'toggle' === $type || is_bool( $value ) ) {
			return $value ? __( 'On', 'wp-recipe-maker' ) : __( 'Off', 'wp-recipe-maker' );
		}

		if ( 'dropdownRecipe' === $type && is_array( $value ) ) {
			$recipe_text = isset( $value['text'] ) ? sanitize_text_field( $value['text'] ) : '';

			if ( $recipe_text ) {
				return '"' . self::truncate_setting_text( $recipe_text ) . '"';
			}

			$recipe_id = isset( $value['id'] ) ? $value['id'] : '';
			if ( $recipe_id ) {
				return sprintf( __( 'Recipe #%s', 'wp-recipe-maker' ), sanitize_text_field( $recipe_id ) );
			}

			return __( 'Empty', 'wp-recipe-maker' );
		}

		if ( is_array( $value ) ) {
			if ( ! $value ) {
				return __( 'None', 'wp-recipe-maker' );
			}

			if ( isset( $setting_details['options'] ) && is_array( $setting_details['options'] ) ) {
				$items = array();

				foreach ( $value as $item ) {
					$item_label = self::get_setting_option_label( $setting_details, $item );
					$items[] = $item_label ? $item_label : self::truncate_setting_text( sanitize_text_field( (string) $item ), 20 );
				}

				if ( count( $items ) > 3 ) {
					return implode( ', ', array_slice( $items, 0, 3 ) ) . sprintf( ' +%d more', count( $items ) - 3 );
				}

				return implode( ', ', $items );
			}

			return sprintf(
				/* translators: %d: number of items. */
				_n( '%d item', '%d items', count( $value ), 'wp-recipe-maker' ),
				count( $value )
			);
		}

		if ( is_object( $value ) ) {
			$value = (array) $value;

			if ( ! $value ) {
				return __( 'Empty', 'wp-recipe-maker' );
			}

			return sprintf(
				/* translators: %d: number of items. */
				_n( '%d item', '%d items', count( $value ), 'wp-recipe-maker' ),
				count( $value )
			);
		}

		$option_label = self::get_setting_option_label( $setting_details, $value );
		if ( $option_label ) {
			return $option_label;
		}

		if ( null === $value ) {
			return __( 'Empty', 'wp-recipe-maker' );
		}

		if ( in_array( $type, array( 'code', 'textarea', 'richTextarea' ), true ) ) {
			$normalized = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $value ) ) );
			return $normalized ? __( 'Content updated', 'wp-recipe-maker' ) : __( 'Empty', 'wp-recipe-maker' );
		}

		if ( is_string( $value ) ) {
			$normalized = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $value ) ) );

			if ( ! strlen( $normalized ) ) {
				return __( 'Empty', 'wp-recipe-maker' );
			}

			return '"' . self::truncate_setting_text( $normalized ) . '"';
		}

		if ( is_scalar( $value ) ) {
			return sanitize_text_field( (string) $value );
		}

		return __( 'Updated', 'wp-recipe-maker' );
	}

	/**
	 * Get the settings details.
	 *
	 * @since	3.0.0
	 */
	public static function get_details() {
		$details = array();
		$structure = self::get_structure();

		// Loop over structure to find settings.
		foreach ( $structure as $group ) {
			if ( isset( $group['settings'] ) ) {
				foreach ( $group['settings'] as $setting ) {
					if ( isset( $setting['id'] ) ) {
						$details[ $setting['id'] ] = $setting;
					}
				}
			}

			if ( isset( $group['subGroups'] ) ) {
				foreach ( $group['subGroups'] as $sub_group ) {
					if ( isset( $sub_group['settings'] ) ) {
						foreach ( $sub_group['settings'] as $setting ) {
							if ( isset( $setting['id'] ) ) {
								$details[ $setting['id'] ] = $setting;
							}
						}
					}
				}
			}
		}

		return $details;
	}

	/**
	 * Sanitize the plugin settings.
	 *
	 * @since	3.0.0
	 * @param	array $settings Settings to sanitize.
	 */
	public static function sanitize_settings( $settings ) {
		$sanitized_settings = array();
		$settings_details = self::get_details();

		foreach ( $settings as $id => $value ) {
			if ( array_key_exists( $id, $settings_details ) ) {
				$details = $settings_details[ $id ];

				$sanitized_value = NULL;

				// Check for custom sanitization function.
				if ( isset( $details['sanitize'] ) && is_callable( $details['sanitize'] ) ) {
					$sanitized_value = call_user_func( $details['sanitize'], $value );
				}
				
				// Default sanitization based on type.
				if ( is_null( $sanitized_value ) && isset( $details['type'] ) ) {	
					switch ( $details['type'] ) {
						case 'code':
							$sanitized_value = WPRM_Recipe_Sanitizer::sanitize_html( $value );

							// Fix for CSS code.
							$sanitized_value = str_replace( '&gt;', '>', $sanitized_value );
							break;
						case 'color':
							$sanitized_value = sanitize_text_field( $value );
							break;
						case 'dropdown':
							if ( array_key_exists( $value, $details['options'] ) ) {
								$sanitized_value = $value;
							}
							break;
						case 'dropdownMultiselect':
							$sanitized_value = array();

							if ( is_array( $value ) ) {
								foreach ( $value as $option ) {
									if ( array_key_exists( $option, $details['options'] ) ) {
										$sanitized_value[] = $option;
									}
								}
							}
							break;
						case 'dropdownRecipe':
							$value_id = is_array( $value ) ? $value['id'] : $value;
							$value_text = is_array( $value ) ? $value['text'] : $value;

							$sanitized_value = array(
								'id' => 'demo' === $value_id ? 'demo' : intval( $value_id ),
								'text' => sanitize_text_field( $value_text ),
							);
							break;
						case 'dropdownTemplateLegacy':
						case 'dropdownTemplateModern':
							$sanitized_value = sanitize_text_field( $value );
							do_action( 'wpml_register_single_string', 'wp-recipe-maker', 'Setting - ' . $id, $sanitized_value );
							break;
						case 'email':
							$sanitized_value = sanitize_email( $value );
							break;
						case 'number':
							$sanitized_value = sanitize_text_field( $value );
							break;
						case 'richTextarea':
							$sanitized_value = wp_kses_post( $value );
							do_action( 'wpml_register_single_string', 'wp-recipe-maker', 'Setting - ' . $id, $sanitized_value );
							break;
						case 'text':
							$sanitized_value = sanitize_text_field( $value );
							do_action( 'wpml_register_single_string', 'wp-recipe-maker', 'Setting - ' . $id, $sanitized_value );
							break;
						case 'textarea':
							$sanitized_value = wp_kses_post( $value );
							do_action( 'wpml_register_single_string', 'wp-recipe-maker', 'Setting - ' . $id, $sanitized_value );
							break;
						case 'toggle':
							$sanitized_value = $value ? true : false;
							break;
					}
				}

				if ( ! is_null( $sanitized_value ) ) {
					$sanitized_settings[ $id ] = $sanitized_value;
				}
			}
		}

		return $sanitized_settings;
	}
}

WPRM_Settings::init();
