<?php
/**
 * Responsible for handling template import/export tools.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/tools
 */

/**
 * Responsible for handling template import/export tools.
 *
 * @since      10.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/tools
 */
class WPRM_Tools_Templates_Transfer {
	/**
	 * Register actions and filters.
	 *
	 * @since 10.2.0
	 */
	public static function init() {
		add_action( 'wp_ajax_wprm_export_templates', array( __CLASS__, 'ajax_export_templates' ) );
		add_action( 'wp_ajax_wprm_import_templates', array( __CLASS__, 'ajax_import_templates' ) );
	}

	/**
	 * Export templates as JSON through AJAX.
	 *
	 * @since 10.2.0
	 */
	public static function ajax_export_templates() {
		if ( ! check_ajax_referer( 'wprm', 'security', false ) ) {
			wp_die();
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$templates = self::get_custom_templates();
		$payload   = array(
			'version'     => defined( 'WPRM_VERSION' ) ? WPRM_VERSION : 'unknown',
			'exported_at' => current_time( 'mysql' ),
			'site_url'    => get_site_url(),
			'templates'   => $templates,
		);

		$filename = 'wprm-templates-export-' . gmdate( 'Y-m-d-His' ) . '.json';

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die();
	}

	/**
	 * Import templates through AJAX.
	 *
	 * @since 10.2.0
	 */
	public static function ajax_import_templates() {
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
					'message' => __( 'You are not allowed to import templates.', 'wp-recipe-maker' ),
				)
			);
		}

		if ( empty( $_FILES['wprm_templates_file'] ) || empty( $_FILES['wprm_templates_file']['tmp_name'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No file uploaded.', 'wp-recipe-maker' ),
				)
			);
		}

		$file_contents = file_get_contents( $_FILES['wprm_templates_file']['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$file_contents = mb_convert_encoding( $file_contents, 'UTF-8', 'UTF-8' );
		$data          = json_decode( $file_contents, true );

		if ( ! $data || ! is_array( $data ) || empty( $data['templates'] ) || ! is_array( $data['templates'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid templates file.', 'wp-recipe-maker' ),
				)
			);
		}

		$result = self::import_templates( $data['templates'] );

		if ( 0 === $result['imported'] ) {
			wp_send_json_error(
				array(
					'message'  => __( 'No templates were imported.', 'wp-recipe-maker' ),
					'warnings' => $result['warnings'],
				)
			);
		}

		/* translators: %s: Number of templates imported. */
		$message = sprintf( _n( '%s template imported successfully.', '%s templates imported successfully.', $result['imported'], 'wp-recipe-maker' ), number_format_i18n( $result['imported'] ) );

		wp_send_json_success(
			array(
				'message'  => $message,
				'warnings' => $result['warnings'],
			)
		);
	}

	/**
	 * Get all custom templates stored in the database.
	 *
	 * @since 10.2.0
	 */
	private static function get_custom_templates() {
		$templates = array();
		$slugs     = get_option( 'wprm_templates', array() );

		foreach ( $slugs as $slug ) {
			$template = get_option( 'wprm_template_' . $slug, false );

			if ( $template && is_array( $template ) && self::is_exportable_template( $template ) ) {
				$templates[ $slug ] = $template;
			}
		}

		/**
		 * Filter the templates that will be exported.
		 *
		 * @param array $templates Templates to be exported.
		 */
		return apply_filters( 'wprm_template_export_payload', $templates );
	}

	/**
	 * Check if a template should be exported.
	 *
	 * @since 10.2.0
	 * @param array $template Template data.
	 */
	private static function is_exportable_template( $template ) {
		if ( empty( $template['mode'] ) || 'modern' !== $template['mode'] ) {
			return false;
		}

		if ( empty( $template['location'] ) || 'database' !== $template['location'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Import templates from payload data.
	 *
	 * @since 10.2.0
	 * @param array $templates Templates to import.
	 */
	private static function import_templates( $templates ) {
		$imported = 0;
		$warnings = array();

		foreach ( $templates as $key => $template ) {
			if ( ! is_array( $template ) ) {
				$warnings[] = __( 'Skipped a template because the data was invalid.', 'wp-recipe-maker' );
				continue;
			}

			if ( empty( $template['slug'] ) && is_string( $key ) ) {
				$template['slug'] = $key;
			}

			$template_label = self::get_template_label( $template );

			if ( empty( $template['mode'] ) || 'modern' !== $template['mode'] ) {
				$warnings[] = sprintf(
					/* translators: %s: Template name or slug. */
					__( '"%s" was skipped because only modern templates can be imported.', 'wp-recipe-maker' ),
					$template_label
				);
				continue;
			}

			$prepared = self::prepare_template_for_import( $template );

			if ( is_wp_error( $prepared ) ) {
				$warnings[] = sprintf(
					/* translators: 1: Template name or slug. 2: Error message. */
					__( '"%1$s" could not be imported: %2$s', 'wp-recipe-maker' ),
					$template_label,
					$prepared->get_error_message()
				);
				continue;
			}

			if ( self::template_slug_exists( $prepared['slug'] ) ) {
				$warnings[] = sprintf(
					/* translators: %s: Template name or slug. */
					__( '"%s" was skipped because a template with this slug already exists.', 'wp-recipe-maker' ),
					$template_label
				);
				continue;
			}

			$saved = WPRM_Template_Manager::save_template( $prepared );

			if ( $saved ) {
				$imported++;
			} else {
				$warnings[] = sprintf(
					/* translators: %s: Template name or slug. */
					__( '"%s" could not be saved.', 'wp-recipe-maker' ),
					$template_label
				);
			}
		}

		return array(
			'imported' => $imported,
			'warnings' => $warnings,
		);
	}

	/**
	 * Prepare template data for import.
	 *
	 * @since 10.2.0
	 * @param array $template Template data.
	 */
	private static function prepare_template_for_import( $template ) {
		$slug = isset( $template['slug'] ) ? WPRM_Template_Manager::slugify( $template['slug'] ) : false;

		if ( ! $slug ) {
			return new WP_Error( 'invalid_template_slug', __( 'Missing template slug.', 'wp-recipe-maker' ) );
		}

		$name = isset( $template['name'] ) ? sanitize_text_field( $template['name'] ) : '';

		if ( ! $name ) {
			return new WP_Error( 'invalid_template_name', __( 'Missing template name.', 'wp-recipe-maker' ) );
		}

		$html = isset( $template['html'] ) ? $template['html'] : '';
		$css  = isset( $template['css'] ) ? $template['css'] : '';

		if ( ! is_string( $html ) || '' === trim( $html ) || ! is_string( $css ) || '' === trim( $css ) ) {
			return new WP_Error( 'invalid_template_content', __( 'Missing template HTML or CSS.', 'wp-recipe-maker' ) );
		}

		$fonts = array();
		if ( ! empty( $template['fonts'] ) && is_array( $template['fonts'] ) ) {
			$fonts = array_map( 'sanitize_text_field', $template['fonts'] );
		}

		return array(
			'oldSlug' => $slug,
			'slug'    => $slug,
			'name'    => $name,
			'type'    => isset( $template['type'] ) ? sanitize_key( $template['type'] ) : 'recipe',
			'premium' => ! empty( $template['premium'] ),
			'css'     => $css,
			'html'    => $html,
			'fonts'   => $fonts,
		);
	}

	/**
	 * Get label to use for warnings.
	 *
	 * @since 10.2.0
	 * @param array $template Template data.
	 */
	private static function get_template_label( $template ) {
		if ( ! empty( $template['name'] ) ) {
			return wp_strip_all_tags( $template['name'] );
		}

		if ( ! empty( $template['slug'] ) ) {
			return sanitize_key( $template['slug'] );
		}

		return __( 'Unnamed template', 'wp-recipe-maker' );
	}

	/**
	 * Check if a template slug already exists.
	 *
	 * @since 10.2.0
	 * @param string $slug Template slug.
	 */
	private static function template_slug_exists( $slug ) {
		if ( ! $slug ) {
			return false;
		}

		$templates = WPRM_Template_Manager::get_templates();

		return isset( $templates['modern'][ $slug ] );
	}
}

WPRM_Tools_Templates_Transfer::init();

