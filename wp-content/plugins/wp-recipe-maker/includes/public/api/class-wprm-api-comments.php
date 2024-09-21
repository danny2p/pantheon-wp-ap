<?php
/**
 * Handle comments in the WordPress REST API.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle comments in the WordPress REST API.
 *
 * @since      9.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Comments {

	/**
	 * Register actions and filters.
	 *
	 * @since	9.6.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since	9.6.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_field( 'comment', 'meta', array(
				'get_callback'    => array( __CLASS__, 'api_get_comment_meta' ),
				'update_callback' => null,
				'schema'          => null,
			));
		}
	}

		
	/**
	 * Handle comment calls to the REST API.
	 *
	 * @since 9.6.0
	 * @param array           $object Details of current post.
	 * @param mixed           $field_name Name of field.
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_comment_meta( $object, $field_name, $request ) {
		$data = array();

		$rating = intval( get_comment_meta( $object['id'], 'wprm-comment-rating', true ) );
		if ( $rating ) {
			$data['wprm_comment_rating'] = $rating;
		}

		return $data;
	}
}

WPRM_Api_Comments::init();
