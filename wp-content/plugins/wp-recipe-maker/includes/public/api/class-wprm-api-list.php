<?php
/**
 * Open up lists in the WordPress REST API.
 *
 * @link       http://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Open up lists in the WordPress REST API.
 *
 * @since      9.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_List {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.0.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_list_data' ) );
		add_action( 'rest_insert_' . WPRM_LIST_POST_TYPE, array( __CLASS__, 'api_insert_update_list' ), 10, 3 );

		// Make sure this gets added.
		add_filter( 'wprm_list_post_type_arguments', array( __CLASS__, 'list_post_type_arguments' ), 99 );
	}

	/**
	 * Register list data for the REST API.
	 *
	 * @since    9.0.0
	 */
	public static function api_register_list_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_field( WPRM_LIST_POST_TYPE, 'list', array(
				'get_callback'    => array( __CLASS__, 'api_get_list_data' ),
				'update_callback' => null,
				'schema'          => null,
			));
		}
	}

	/**
	 * Handle list calls to the REST API.
	 *
	 * @since 9.0.0
	 * @param array           $object Details of current post.
	 * @param mixed           $field_name Name of field.
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_list_data( $object, $field_name, $request ) {
		// Make sure we're getting the most recent data by invalidating first.
		WPRM_List_Manager::invalidate_list( $object['id'] );
		$list = WPRM_List_Manager::get_list( $object['id'] );

		return $list->get_data();
	}

	/**
	 * Handle list calls to the REST API.
	 *
	 * @since 1.27.0
	 * @param WP_Post         $post     Inserted or updated post object.
	 * @param WP_REST_Request $request  Request object.
	 * @param bool            $creating True when creating a post, false when updating.
	 */
	public static function api_insert_update_list( $post, $request, $creating ) {
		$params = $request->get_params();
		$list = isset( $params['list'] ) ? WPRM_List_Saver::sanitize( $params['list'] ) : array();
		$list_id = $post->ID;

		WPRM_List_Saver::update_list( $list_id, $list );
	}

	/**
	 * Add REST API options to the list post type arguments.
	 *
	 * @since    9.0.0
	 * @param	 	 array $args Post type arguments.
	 */
	public static function list_post_type_arguments( $args ) {
		$args['show_in_rest'] = true;
		$args['rest_base'] = WPRM_LIST_POST_TYPE;
		$args['rest_controller_class'] = 'WP_REST_Posts_Controller';

		return $args;
	}
}

WPRM_Api_List::init();
