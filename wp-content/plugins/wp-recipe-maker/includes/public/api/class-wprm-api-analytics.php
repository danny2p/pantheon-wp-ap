<?php
/**
 * Open up recipe analytics in the WordPress REST API.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Open up recipe analytics in the WordPress REST API.
 *
 * @since      6.5.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Analytics {

	/**
	 * Register actions and filters.
	 *
	 * @since    6.5.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    6.5.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_route( 'wp-recipe-maker/v1', '/analytics', array(
				'callback' => array( __CLASS__, 'api_create_action' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_create_permission' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/analytics/(?P<id>\d+)', array(
				'callback' => array( __CLASS__, 'api_delete_action' ),
				'methods' => 'DELETE',
				'args' => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/analytics/sync', array(
				'callback' => array( __CLASS__, 'api_sync' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_sync_permission' ),
			));
		}
	}

	/**
	 * Validate ID in API call.
	 *
	 * @since 6.5.0
	 * @param mixed           $param Parameter to validate.
	 * @param WP_REST_Request $request Current request.
	 * @param mixed           $key Key.
	 */
	public static function api_validate_numeric( $param, $request, $key ) {
		return is_numeric( $param );
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since 6.5.0
	 */
	public static function api_required_permissions() {
		return current_user_can( WPRM_Settings::get( 'features_manage_access' ) );
	}

	/**
	 * Handle delete rating call to the REST API.
	 *
	 * @since 2.4.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_delete_action( $request ) {
		return WPRM_Analytics_Database::delete( $request['id'] );
	}

	/**
	 * Required permissions for the create action API.
	 *
	 * @since 6.6.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_create_permission( $request ) {
		$params = $request->get_params();
		$nonce = isset( $params['nonce'] ) ? $params['nonce'] : '';

		if ( $nonce && wp_verify_nonce( $nonce, 'wprm' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Handle delete rating call to the REST API.
	 *
	 * @since 2.4.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_create_action( $request ) {
		$params = $request->get_params();

		$recipe_id = isset( $params['recipeId'] ) ? intval( $params['recipeId'] ) : 0;
		$post_id = isset( $params['postId'] ) ? intval( $params['postId'] ) : 0;
		$type = isset( $params['type'] ) ? sanitize_key( $params['type'] ) : '';
		$meta = isset( $params['meta'] ) ? $params['meta'] : array();
		$uid = isset( $params['uid'] ) ? sanitize_key( $params['uid'] ) : '';

		return WPRM_Analytics::register_action( $recipe_id, $post_id, $type, $meta, $uid );
	}

	/**
	 * Required permissions for syncing the API.
	 *
	 * @since 6.6.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_sync_permission( $request ) {
		$params = $request->get_params();

		$token = isset( $params['token'] ) ? trim( sanitize_text_field( $params['token'] ) ) : false;

		if ( $token ) {
			$token_check = WPRM_Settings::get( 'honey_home_token' );

			if ( WPRM_Settings::get( 'honey_home_integration' ) && $token_check && $token === $token_check ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Handle sync call to the REST API.
	 *
	 * @since 6.6.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_sync( $request ) {
		$params = $request->get_params();
		
		$limit = isset( $params['limit'] ) ? intval( $params['limit'] ) : 100;
		$page = isset( $params['page'] ) ? intval( $params['page'] ) : 0;
		$timestamp = isset( $params['timestamp'] ) ? intval( $params['timestamp'] ) : 0;
		$order = isset( $params['order'] ) && in_array( strtoupper( $params['order'] ), array( 'ASC', 'DESC' ) ) ? strtoupper( $params['order'] ) : 'ASC';

		$date = date( 'Y-m-d H:i:s', $timestamp );
		$date_where = 'DESC' === $order ? '>' : '<';

		$args = array(
			'limit' => $limit,
			'offset' => $page * $limit,
			'where' => '"' . $date . '" ' . $date_where . ' created_at',
			'orderby' => 'created_at',
			'order' => $order,
		);

		$sync = WPRM_Analytics_Database::get( $args );

		$sync_total = intval( $sync['total'] );
		$sync_loaded = count( $sync['actions'] );

		$sync['loaded'] = $sync_loaded;
		$sync['done'] = $sync_loaded >= $sync_total || $sync_total <= $limit * ( $page + 1 );
		$sync['order'] = $order; // Confirm order that was used for this sync.

		// Add details.
		foreach ( $sync['actions'] as $action ) {
			$post_details = null;
			if ( $action->post_id ) {
				$post = get_post( $action->post_id );
				
				if ( $post ) {
					$post_details = array(
						'id' => $post->ID,
						'title' => $post->post_title,
						'url' => get_permalink( $post ),
					);
				}
			}

			$recipe_details = null;
			if ( $action->recipe_id ) {
				$recipe = WPRM_Recipe_Manager::get_recipe( $action->recipe_id );

				if ( $recipe ) {
					$recipe_details = array(
						'id' => $recipe->id(),
						'name' => $recipe->name(),
						'image_url' => $recipe->image_url( 'full' ),
						'course' => $recipe->tags( 'course', true ),
						'cuisine' => $recipe->tags( 'cuisine', true ),
					);
				}
			}

			$action->post = $post_details;
			$action->recipe = $recipe_details;
		} 

		return $sync;
	}
}

WPRM_Api_Analytics::init();
