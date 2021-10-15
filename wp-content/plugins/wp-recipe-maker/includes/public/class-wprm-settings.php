<?php
/**
 * Responsible for the plugin settings.
 *
 * @link       http://bootstrapped.ventures
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
	 * @param	boolean $force_update Wether to force an update of the cache.
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
	public static function update_settings( $settings_to_update ) {
		$old_settings = self::get_settings();

		if ( is_array( $settings_to_update ) ) {
			$settings_to_update = self::sanitize_settings( $settings_to_update );
			$new_settings = array_merge( $old_settings, $settings_to_update );

			$new_settings = apply_filters( 'wprm_settings_update', $new_settings, $old_settings );

			update_option( 'wprm_settings', $new_settings );
			self::$settings = $new_settings;
		}

		return self::get_settings();
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
