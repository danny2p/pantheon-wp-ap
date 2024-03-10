<?php
/**
 * API for utilities.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.11.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * API for utilities.
 *
 * @since      5.11.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Utilities {

	/**
	 * Register actions and filters.
	 *
	 * @since    5.11.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    5.11.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_route( 'wp-recipe-maker/v1', '/utilities/save_image', array(
				'callback' => array( __CLASS__, 'api_save_image' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_permissions_author' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/utilities/feedback', array(
				'callback' => array( __CLASS__, 'api_give_feedback' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_permissions_administrator' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/utilities/post_summary/(?P<id>\d+)', array(
				'callback' => array( __CLASS__, 'api_get_post_summary' ),
				'methods' => 'GET',
				'args' => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => array( __CLASS__, 'api_permissions_author' ),
			));
		}
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since 5.11.0
	 */
	public static function api_permissions_author() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since 5.11.0
	 */
	public static function api_permissions_administrator() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Validate ID in API call.
	 *
	 * @since 9.0.0
	 * @param mixed           $param Parameter to validate.
	 * @param WP_REST_Request $request Current request.
	 * @param mixed           $key Key.
	 */
	public static function api_validate_numeric( $param, $request, $key ) {
		return is_numeric( $param );
	}

	/**
	 * Handle save image call to the REST API.
	 *
	 * @since 5.11.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_save_image( $request ) {
		// Parameters.
		$params = $request->get_params();

		$url = isset( $params['url'] ) ? esc_url( $params['url'] ): '';
		$url = str_replace( array( "\n", "\t", "\r" ), '', $url );

		// Need to include correct files for media_sideload_image.
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$image_url = media_sideload_image( $url, null, null, 'src' );
		$image_id = attachment_url_to_postid( $image_url );

		if ( ! $image_id ) {
			$image_url = '';
		}

		$data = array(
			'id' => $image_id,
			'url' => $image_url,
		);

		return rest_ensure_response( $data );
	}

	/**
	 * Handle give feedback call to the REST API.
	 *
	 * @since 7.6.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_give_feedback( $request ) {
		// Parameters.
		$params = $request->get_params();

		$feedback = isset( $params['feedback'] ) ? sanitize_key( $params['feedback'] ): false;

		if ( false !== $feedback ) {
			require_once( WPRM_DIR . 'includes/admin/class-wprm-feedback.php' );

			WPRM_Feedback::set_feedback( $feedback );
			return rest_ensure_response( true );
		}

		return rest_ensure_response( false );
	}

	/**
	 * Handle get post summary to the REST API.
	 *
	 * @since 9.0.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_post_summary( $request ) {
		$post_id = intval( $request['id'] );
		$name = '';
		$image_url = '';

		$post = get_post( $post_id );

		if ( $post ) {
			$name = $post->post_title;
			$image_url = get_the_post_thumbnail_url( $post_id, 'full' );
		}

		$data = array(
			'post' => array(
				'id' => $post_id,
				'name' => $name,
				'image_url' => $image_url,
			),
		);

		return rest_ensure_response( $data );
	}
}

WPRM_Api_Utilities::init();
