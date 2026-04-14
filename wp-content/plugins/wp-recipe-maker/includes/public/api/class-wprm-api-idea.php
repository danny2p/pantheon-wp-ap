<?php
/**
 * Open up ideas in the WordPress REST API.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Open up ideas in the WordPress REST API.
 *
 * @since      10.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Idea {

	/**
	 * Register actions and filters.
	 *
	 * @since    10.4.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_idea_data' ) );
		add_action( 'rest_insert_' . WPRM_IDEA_POST_TYPE, array( __CLASS__, 'api_insert_update_idea' ), 10, 3 );
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_import_route' ) );

		add_filter( 'wprm_idea_post_type_arguments', array( __CLASS__, 'idea_post_type_arguments' ), 99 );
	}

	/**
	 * Register idea data for the REST API.
	 *
	 * @since    10.4.0
	 */
	public static function api_register_idea_data() {
		if ( function_exists( 'register_rest_field' ) ) {
			register_rest_field(
				WPRM_IDEA_POST_TYPE,
				'idea',
				array(
					'get_callback'    => array( __CLASS__, 'api_get_idea_data' ),
					'update_callback' => null,
					'schema'          => null,
				)
			);
		}
	}

	/**
	 * Register custom import route for AI-generated ideas.
	 *
	 * @since    10.4.0
	 */
	public static function api_register_import_route() {
		register_rest_route(
			'wp-recipe-maker/v1',
			'/idea/import',
			array(
				'callback'            => array( __CLASS__, 'api_import_ideas' ),
				'methods'             => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			)
		);
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since    10.4.0
	 */
	public static function api_required_permissions() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Handle idea field calls to the REST API.
	 *
	 * @since    10.4.0
	 * @param    array           $object Current post object.
	 * @param    string          $field_name Field name.
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_get_idea_data( $object, $field_name, $request ) {
		WPRM_Idea_Manager::invalidate_idea( $object['id'] );
		$idea = WPRM_Idea_Manager::get_idea( $object['id'] );

		return $idea ? $idea->get_data() : false;
	}

	/**
	 * Handle idea save calls to the REST API.
	 *
	 * @since    10.4.0
	 * @param    WP_Post         $post Inserted or updated post object.
	 * @param    WP_REST_Request $request Request object.
	 * @param    bool            $creating True when creating a post.
	 */
	public static function api_insert_update_idea( $post, $request, $creating ) {
		$params = $request->get_params();
		$idea = isset( $params['idea'] ) ? WPRM_Idea_Saver::sanitize( $params['idea'] ) : array();

		WPRM_Idea_Saver::update_idea( $post->ID, $idea );
	}

	/**
	 * Import AI-generated ideas into the same system as manual ideas.
	 *
	 * @since    10.4.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_import_ideas( $request ) {
		$params = $request->get_params();
		$ideas = isset( $params['ideas'] ) && is_array( $params['ideas'] ) ? $params['ideas'] : array();

		$created = array();
		$duplicates = array();

		foreach ( $ideas as $idea ) {
			$idea['source'] = 'ai';
			$idea['status'] = isset( $idea['status'] ) ? $idea['status'] : 'idea';
			$idea['ai_generated_at'] = isset( $idea['ai_generated_at'] ) ? $idea['ai_generated_at'] : gmdate( 'Y-m-d H:i:s' );

			$sanitized_idea = WPRM_Idea_Saver::sanitize( $idea );
			$title_matches = WPRM_Idea_Manager::find_title_matches( isset( $sanitized_idea['name'] ) ? $sanitized_idea['name'] : '' );

			if ( ! empty( $title_matches['ideas'] ) || ! empty( $title_matches['recipes'] ) ) {
				$duplicates[] = array(
					'name'    => isset( $sanitized_idea['name'] ) ? $sanitized_idea['name'] : '',
					'matches' => $title_matches,
				);
			}

			$idea_id = wp_insert_post(
				array(
					'post_type'   => WPRM_IDEA_POST_TYPE,
					'post_status' => 'draft',
				)
			);

			if ( $idea_id && ! is_wp_error( $idea_id ) ) {
				WPRM_Idea_Saver::update_idea( $idea_id, $sanitized_idea );
				$created_idea = WPRM_Idea_Manager::get_idea( $idea_id );

				if ( $created_idea ) {
					$created[] = $created_idea->get_data_manage();
				}
			}
		}

		return rest_ensure_response(
			array(
				'created'    => $created,
				'duplicates' => $duplicates,
			)
		);
	}

	/**
	 * Add REST API options to the idea post type arguments.
	 *
	 * @since    10.4.0
	 * @param    array $args Post type arguments.
	 */
	public static function idea_post_type_arguments( $args ) {
		$args['show_in_rest'] = true;
		$args['rest_base'] = WPRM_IDEA_POST_TYPE;
		$args['rest_controller_class'] = 'WP_REST_Posts_Controller';

		return $args;
	}
}

WPRM_Api_Idea::init();
