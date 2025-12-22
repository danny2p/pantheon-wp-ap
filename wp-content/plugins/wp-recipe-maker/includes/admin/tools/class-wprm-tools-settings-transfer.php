<?php
/**
 * Responsible for handling settings import/export tools.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/tools
 */

/**
 * Responsible for handling settings import/export tools.
 *
 * @since      10.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/tools
 */
class WPRM_Tools_Settings_Transfer {
	/**
	 * Settings that should never be exported/imported.
	 *
	 * @since 10.2.0
	 * @access private
	 * @var array $excluded_settings
	 */
	private static $excluded_settings = array(
		'license_premium',
		'license_pro',
		'license_elite',
	);

	/**
	 * Register actions and filters.
	 *
	 * @since 10.2.0
	 */
	public static function init() {
		add_action( 'wp_ajax_wprm_export_settings', array( __CLASS__, 'ajax_export_settings' ) );
		add_action( 'wp_ajax_wprm_import_settings', array( __CLASS__, 'ajax_import_settings' ) );
	}

	/**
	 * Export settings as JSON through AJAX.
	 *
	 * @since 10.2.0
	 */
	public static function ajax_export_settings() {
		if ( ! check_ajax_referer( 'wprm', 'security', false ) ) {
			wp_die();
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$settings = self::get_filtered_settings();
		$payload  = array(
			'version'     => defined( 'WPRM_VERSION' ) ? WPRM_VERSION : 'unknown',
			'exported_at' => current_time( 'mysql' ),
			'site_url'    => get_site_url(),
			'settings'    => $settings,
		);

		$filename = 'wprm-settings-export-' . gmdate( 'Y-m-d-His' ) . '.json';

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die();
	}

	/**
	 * Import settings through AJAX.
	 *
	 * @since 10.2.0
	 */
	public static function ajax_import_settings() {
		if ( ! check_ajax_referer( 'wprm', 'security', false ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid request.', 'wp-recipe-maker' ),
				)
			);
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You are not allowed to import settings.', 'wp-recipe-maker' ),
				)
			);
		}

		if ( empty( $_FILES['wprm_settings_file'] ) || empty( $_FILES['wprm_settings_file']['tmp_name'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No file uploaded.', 'wp-recipe-maker' ),
				)
			);
		}

		$file_contents = file_get_contents( $_FILES['wprm_settings_file']['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$file_contents = mb_convert_encoding( $file_contents, 'UTF-8', 'UTF-8' );
		$data          = json_decode( $file_contents, true );

		if ( ! $data || ! is_array( $data ) || empty( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid settings file.', 'wp-recipe-maker' ),
				)
			);
		}

		$settings = $data['settings'];
		$settings = self::filter_excluded_settings( $settings );

		list( $settings, $warnings ) = self::validate_template_settings( $settings );

		WPRM_Settings::update_settings( $settings );

		$has_warnings = ! empty( $warnings );
		$response = array(
			'message' => $has_warnings ? __( 'Settings imported successfully with the following exceptions:', 'wp-recipe-maker' ) : __( 'Settings imported successfully.', 'wp-recipe-maker' ),
		);

		if ( $has_warnings ) {
			$response['warnings'] = $warnings;
		}

		wp_send_json_success( $response );
	}

	/**
	 * Retrieve settings without excluded ones.
	 *
	 * @since 10.2.0
	 */
	private static function get_filtered_settings() {
		$settings = WPRM_Settings::get_settings();

		return self::filter_excluded_settings( $settings );
	}

	/**
	 * Remove excluded settings from a settings array.
	 *
	 * @since 10.2.0
	 * @param array $settings Settings array to filter.
	 */
	private static function filter_excluded_settings( $settings ) {
		$excluded = apply_filters( 'wprm_settings_export_exclusions', self::$excluded_settings );

		foreach ( $excluded as $key ) {
			if ( isset( $settings[ $key ] ) ) {
				unset( $settings[ $key ] );
			}
		}

		return $settings;
	}

	/**
	 * Validate dropdown template settings and collect warnings.
	 *
	 * @since 10.2.0
	 * @param array $settings Settings array to validate.
	 */
	private static function validate_template_settings( $settings ) {
		$warnings = array();

		if ( empty( $settings ) ) {
			return array( $settings, $warnings );
		}

		$details = WPRM_Settings::get_details();
		$available_templates = array(
			'modern' => self::get_available_template_slugs( 'modern' ),
			'legacy' => self::get_available_template_slugs( 'legacy' ),
		);

		foreach ( $settings as $id => $value ) {
			if ( ! isset( $details[ $id ] ) ) {
				continue;
			}

			$setting_details = $details[ $id ];
			$type = isset( $setting_details['type'] ) ? $setting_details['type'] : '';

			if ( 'dropdownTemplateModern' === $type || 'dropdownTemplateLegacy' === $type ) {
				$mode = 'dropdownTemplateLegacy' === $type ? 'legacy' : 'modern';
				$available_slugs = isset( $available_templates[ $mode ] ) ? $available_templates[ $mode ] : array();

				if ( self::is_valid_template_choice( $value, $setting_details, $available_slugs ) ) {
					continue;
				}

				unset( $settings[ $id ] );

				$setting_label = isset( $setting_details['name'] ) ? wp_strip_all_tags( $setting_details['name'] ) : $id;
				$template_label = is_string( $value ) && $value ? sanitize_text_field( $value ) : __( 'Unknown template', 'wp-recipe-maker' );

				/* translators: 1: Setting label. 2: Template slug. */
				$warnings[] = sprintf( __( '"%1$s" was not updated because the "%2$s" template does not exist on this site.', 'wp-recipe-maker' ), $setting_label, $template_label );

				continue;
			}

			if ( 'dropdownRecipe' === $type ) {
				if ( self::is_valid_recipe_choice( $value ) ) {
					continue;
				}

				unset( $settings[ $id ] );

				$setting_label = isset( $setting_details['name'] ) ? wp_strip_all_tags( $setting_details['name'] ) : $id;
				$recipe_label = self::get_recipe_warning_label( $value );

				/* translators: 1: Setting label. 2: Recipe name or ID. */
				$warnings[] = sprintf( __( '"%1$s" was not updated because the recipe %2$s does not exist on this site.', 'wp-recipe-maker' ), $setting_label, $recipe_label );
			}
		}

		return array( $settings, $warnings );
	}

	/**
	 * Get available template slugs for this site.
	 *
	 * @since 10.2.0
	 * @param string $mode Template mode (modern|legacy).
	 */
	private static function get_available_template_slugs( $mode = 'modern' ) {
		$templates = WPRM_Template_Manager::get_templates();
		$slugs = array();

		$mode = 'legacy' === $mode ? 'legacy' : 'modern';

		if ( isset( $templates[ $mode ] ) && is_array( $templates[ $mode ] ) ) {
			foreach ( $templates[ $mode ] as $slug => $template ) {
				$is_premium = ! empty( $template['premium'] );

				if ( ! $is_premium || WPRM_Addons::is_active( 'premium' ) ) {
					$slugs[] = $slug;
				}
			}
		}

		return $slugs;
	}

	/**
	 * Validate a single dropdown template choice.
	 *
	 * @since 10.2.0
	 * @param mixed  $value            Value being imported.
	 * @param array  $setting_details  Setting details metadata.
	 * @param array  $available_slugs  Template slugs available on this site.
	 */
	private static function is_valid_template_choice( $value, $setting_details, $available_slugs ) {
		if ( empty( $value ) || ! is_string( $value ) ) {
			return true;
		}

		if ( isset( $setting_details['options'] ) && array_key_exists( $value, $setting_details['options'] ) ) {
			return true;
		}

		return in_array( $value, $available_slugs, true );
	}

	/**
	 * Validate a dropdownRecipe choice.
	 *
	 * @since 10.2.0
	 * @param mixed $value           Value being imported.
	 * @param array $existing_recipes Recipe IDs available on this site.
	 */
	private static function is_valid_recipe_choice( $value ) {
		if ( empty( $value ) ) {
			return true;
		}

		$recipe_id = false;

		if ( is_array( $value ) ) {
			if ( isset( $value['id'] ) ) {
				$recipe_id = $value['id'];
			}
		} else {
			$recipe_id = $value;
		}

		if ( 'demo' === $recipe_id ) {
			return true;
		}

		$recipe_id = intval( $recipe_id );

		if ( ! $recipe_id ) {
			return false;
		}

		return WPRM_POST_TYPE === get_post_type( $recipe_id );
	}

	/**
	 * Get recipe warning label text.
	 *
	 * @since 10.2.0
	 * @param mixed $value Value that failed validation.
	 */
	private static function get_recipe_warning_label( $value ) {
		if ( is_array( $value ) ) {
			if ( ! empty( $value['text'] ) ) {
				return '"' . sanitize_text_field( $value['text'] ) . '"';
			}

			if ( isset( $value['id'] ) ) {
				return '#' . intval( $value['id'] );
			}
		}

		if ( 'demo' === $value ) {
			return __( 'Demo Recipe', 'wp-recipe-maker' );
		}

		return '#' . intval( $value );
	}

}

WPRM_Tools_Settings_Transfer::init();

