<?php
/**
 * Open up recipe ratings in the WordPress REST API.
 *
 * @link       http://bootstrapped.ventures
 * @since      2.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Open up recipe ratings in the WordPress REST API.
 *
 * @since      2.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Rating {

	/**
	 * Register actions and filters.
	 *
	 * @since    2.4.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    2.4.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_route( 'wp-recipe-maker/v1', '/rating', array(
				'callback' => array( __CLASS__, 'api_get_ratings' ),
				'methods' => 'GET',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/rating', array(
				'callback' => array( __CLASS__, 'api_add_or_update_rating' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/rating/(?P<id>\d+)', array(
				'callback' => array( __CLASS__, 'api_get_rating' ),
				'methods' => 'GET',
				'args' => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/rating/(?P<id>\d+)', array(
				'callback' => array( __CLASS__, 'api_delete_rating' ),
				'methods' => 'DELETE',
				'args' => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/rating/recipe/(?P<id>\d+)', array(
				'callback' => array( __CLASS__, 'api_get_ratings_for_recipe' ),
				'methods' => 'GET',
				'args' => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/rating/recipe/(?P<id>\d+)', array(
				'callback' => array( __CLASS__, 'api_delete_ratings_for_recipe' ),
				'methods' => 'DELETE',
				'args' => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/rating/comment/(?P<id>\d+)', array(
				'callback' => array( __CLASS__, 'api_get_rating_for_comment' ),
				'methods' => 'GET',
				'args' => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/rating/comment/(?P<id>\d+)', array(
				'callback' => array( __CLASS__, 'api_update_rating_for_comment' ),
				'methods' => 'POST',
				'args' => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/rating/comment/(?P<id>\d+)', array(
				'callback' => array( __CLASS__, 'api_delete_rating_for_comment' ),
				'methods' => 'DELETE',
				'args' => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
		}
	}

	/**
	 * Validate ID in API call.
	 *
	 * @since 2.4.0
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
	 * @since 2.4.0
	 */
	public static function api_required_permissions() {
		return current_user_can( 'moderate_comments' );
	}

	/**
	 * Handle get ratings call to the REST API.
	 *
	 * @since 2.4.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_ratings( $request ) {
		return WPRM_Rating_Database::get_ratings( array() );
	}

	/**
	 * Handle add or update rating call to the REST API.
	 *
	 * @since 2.4.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_add_or_update_rating( $request ) {
		$params = $request->get_params();
		$rating = isset( $params['rating'] ) ? $params['rating'] : array();
		return WPRM_Rating_Database::add_or_update_rating( $rating );
	}

	/**
	 * Handle get rating call to the REST API.
	 *
	 * @since 2.4.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_rating( $request ) {
		return WPRM_Rating_Database::get_rating(array(
			'where' => 'id = ' . $request['id'],
		));
	}

	/**
	 * Handle delete rating call to the REST API.
	 *
	 * @since 2.4.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_delete_rating( $request ) {
		return WPRM_Rating_Database::delete_rating( $request['id'] );
	}

	/**
	 * Handle get ratings for recipe call to the REST API.
	 *
	 * @since 2.4.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_ratings_for_recipe( $request ) {
		return WPRM_Rating_Database::get_ratings(array(
			'where' => 'recipe_id = ' . $request['id'],
		));
	}

	/**
	 * Handle delete ratings for recipe call to the REST API.
	 *
	 * @since 2.4.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_delete_ratings_for_recipe( $request ) {
		return WPRM_Rating_Database::delete_ratings_for( $request['id'] );
	}

	/**
	 * Handle get rating for comment call to the REST API.
	 *
	 * @since 2.4.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_rating_for_comment( $request ) {
		return WPRM_Rating_Database::get_rating(array(
			'where' => 'comment_id = ' . $request['id'],
		));
	}

	/**
	 * Handle update rating for comment call to the REST API.
	 *
	 * @since 7.6.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_update_rating_for_comment( $request ) {
		$params = $request->get_params();
		$rating = isset( $params['rating'] ) ? intval( $params['rating'] ) : false;

		$comment_id = intval( $request['id'] );

		if ( $comment_id && false !== $rating ) {
			WPRM_Comment_Rating::add_or_update_rating_for( $comment_id, $rating );
			return array(
				'rating' => $rating,
			);
		}

		return false;
	}

	/**
	 * Handle delete rating for comment call to the REST API.
	 *
	 * @since 2.4.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_delete_rating_for_comment( $request ) {
		return WPRM_Rating_Database::delete_ratings_for_comment( $request['id'] );
	}
}

WPRM_Api_Rating::init();
