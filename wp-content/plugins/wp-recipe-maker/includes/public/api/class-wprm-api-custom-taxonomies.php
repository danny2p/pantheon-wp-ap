<?php
/**
 * Handle taxonomies in the WordPress REST API.
 *
 * @link       https://bootstrapped.ventures
 * @since      7.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle taxonomies in the WordPress REST API.
 *
 * @since      7.1.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Custom_Taxonomies {

	/**
	 * Register actions and filters.
	 *
	 * @since	7.1.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    7.1.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_route( 'wp-recipe-maker/v1', '/manage/taxonomies', array(
				'callback' => array( __CLASS__, 'api_manage_taxonomies' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			) );
			register_rest_route( 'wp-recipe-maker/v1', '/custom-taxonomies', array(
				'callback' => array( __CLASS__, 'api_update_taxonomy' ),
				'methods' => 'PUT',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			) );
		}
	}
	
	/**
	 * Required permissions for the API.
	 *
	 * @since 7.1.0
	 */
	public static function api_required_permissions() {
		return current_user_can( WPRM_Settings::get( 'features_manage_access' ) );
	}

	/**
	 * Handle manage taxonomies call to the REST API.
	 *
	 * @since    7.1.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_taxonomies( $request ) {
		// Parameters.
		$params = $request->get_params();

		$page = isset( $params['page'] ) ? intval( $params['page'] ) : 0;
		$page_size = isset( $params['pageSize'] ) ? intval( $params['pageSize'] ) : 25;

		$starting_index = $page * $page_size;
		$ending_index = $starting_index + $page_size;
		
		$rows = array();
		$taxonomies = WPRM_Taxonomies::get_taxonomies_to_register();

		$counter = 0;
		foreach ( $taxonomies as $key => $options ) {
			if ( $starting_index <= $counter && $counter < $ending_index ) {
				$rows[] = $options;
			}
			$counter++;
		}

		return array(
			'rows' => $rows,
			'total' => count( $taxonomies ),
			'filtered' => count( $taxonomies ),
			'pages' => ceil( count( $taxonomies ) / $page_size ),
		);
	}

	/**
	 * Handle update taxonomy call to the REST API.
	 *
	 * @since    7.1.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_update_taxonomy( $request ) {
		// Parameters.
		$params = $request->get_params();

		$key = isset( $params['key'] ) ? sanitize_key( $params['key'] ) : '';
		$singular_name = isset( $params['singular_name'] ) ? sanitize_text_field( $params['singular_name'] ) : '';
		$name = isset( $params['name'] ) ? sanitize_text_field( $params['name'] ) : '';
		$slug = isset( $params['slug'] ) ? sanitize_key( $params['slug'] ) : '';
		$archive = isset( $params['archive'] ) && $params['archive'] ? true : false;

		if ( $key && $singular_name && $name ) {
			$key = 'wprm_' . $key;
			$taxonomies = get_option( 'wprm_custom_taxonomies', array() );

			$taxonomies[ $key ] = array(
				'name' => $name,
				'singular_name' => $singular_name,
				'slug' => $slug,
				'archive' => $archive,
			);
			update_option( 'wprm_custom_taxonomies', $taxonomies );
			set_transient( 'wprm_custom_taxonomies_flush_needed', true, 60 * 60 * 24 );

			return array(
				'key' => $key,
				'singular_name' => $singular_name,
				'name' => $name,
				'slug' => $slug,
				'archive' => $archive,
			);
		}

		return false;
	}
}

WPRM_Api_Custom_Taxonomies::init();
