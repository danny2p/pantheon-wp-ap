<?php
/**
 * API for utilities.
 *
 * @link       https://bootstrapped.ventures
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
				'permission_callback' => array( __CLASS__, 'api_permissions_post_summary' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/utilities/preview', array(
				'callback' => array( __CLASS__, 'api_preview_recipe' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_permissions_author' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/utilities/preview/(?P<id>\d+)', array(
				'callback' => array( __CLASS__, 'api_preview_recipe_html' ),
				'methods' => 'POST',
				'args' => array(
					'id' => array(
						'validate_callback' => array( __CLASS__, 'api_validate_numeric' ),
					),
				),
				'permission_callback' => '__return_true',
			));
			register_rest_route( 'wp-recipe-maker/v1', '/utilities/sanitize', array(
				'callback' => array( __CLASS__, 'api_sanitize' ),
				'methods' => 'POST',
				'permission_callback' => '__return_true',
			));
			register_rest_route( 'wp-recipe-maker/v1', '/utilities/divi5-builder-data', array(
				'callback' => array( __CLASS__, 'api_get_divi5_builder_data' ),
				'methods' => 'GET',
				'permission_callback' => array( __CLASS__, 'api_permissions_divi5_builder_data' ),
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
	 * Required permissions for the post summary API.
	 *
	 * @since 10.2.2
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_permissions_post_summary( $request ) {
		$post_id = isset( $request['id'] ) ? intval( $request['id'] ) : 0;

		if ( ! $post_id ) {
			return new WP_Error( 'wprm_invalid_post', __( 'Invalid post ID.', 'wp-recipe-maker' ), array( 'status' => 400 ) );
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error( 'wprm_post_not_found', __( 'Post not found.', 'wp-recipe-maker' ), array( 'status' => 404 ) );
		}

		if ( self::can_user_access_post_summary( $post_id, $post ) ) {
			return true;
		}

		return new WP_Error( 'wprm_forbidden_post', __( 'You are not allowed to access this post.', 'wp-recipe-maker' ), array( 'status' => 403 ) );
	}

	/**
	 * Check if current user can access the post summary for a post.
	 *
	 * Publicly viewable posts without a password are allowed for everyone.
	 * For all other posts, the user must be able to read or edit the post.
	 *
	 * @since 10.2.2
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	private static function can_user_access_post_summary( $post_id, $post ) {
		if ( is_post_publicly_viewable( $post ) && ! post_password_required( $post ) ) {
			return true;
		}

		if ( current_user_can( 'edit_post', $post_id ) || current_user_can( 'read_post', $post_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Required permissions for Divi 5 builder data API.
	 * Allows cookie-based authentication for logged-in users.
	 *
	 * @since 9.7.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_permissions_divi5_builder_data( $request ) {
		// First, try standard WordPress REST API authentication
		// This should work with cookies if user is logged in
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			return true;
		}

		// For Visual Builder context, also check if we have a valid nonce in the request
		// This handles cases where cookies might not work but nonce is provided
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( $nonce && wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			// Nonce is valid, verify user can edit posts
			if ( current_user_can( 'edit_posts' ) ) {
				return true;
			}
		}

		// As a fallback for Visual Builder, check if we have WordPress auth cookies
		// This handles cases where REST API auth isn't working but user is logged in
		$cookie = '';
		if ( isset( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
			$cookie = $_COOKIE[ LOGGED_IN_COOKIE ];
		}
		if ( $cookie ) {
			$user_id = wp_validate_auth_cookie( $cookie, 'logged_in' );
			if ( $user_id ) {
				$user = get_userdata( $user_id );
				if ( $user && user_can( $user, 'edit_posts' ) ) {
					return true;
				}
			}
		}

		return false;
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

		if ( $post && self::can_user_access_post_summary( $post_id, $post ) ) {
			$name = $post->post_title;
			$image_url = get_the_post_thumbnail_url( $post_id, 'full' );
		} elseif ( ! $post ) {
			return new WP_Error( 'wprm_post_not_found', __( 'Post not found.', 'wp-recipe-maker' ), array( 'status' => 404 ) );
		} else {
			return new WP_Error( 'wprm_forbidden_post', __( 'You are not allowed to access this post.', 'wp-recipe-maker' ), array( 'status' => 403 ) );
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

	/**
	 * Handle preview recipe call to the REST API.
	 *
	 * @since 9.6.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_preview_recipe( $request ) {
		// Parameters.
		$params = $request->get_params();

		$json = isset( $params['json'] ) ? $params['json'] : false;

		if ( false !== $json ) {
			$json_recipe = json_decode( $json, true );

			if ( $json_recipe ) {
				$preview_url = WPRM_Preview::set_recipe_for_preview_and_get_url( $json_recipe );
				return rest_ensure_response( $preview_url );
			}
		}

		return rest_ensure_response( false );
	}

	/**
	 * Handle preview recipe HTML call to the REST API.
	 *
	 * @since 9.7.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_preview_recipe_html( $request ) {
		$recipe_id = intval( $request['id'] );

		// Check if we should continue with request.
		$params = $request->get_params();
		$template_slug = isset( $params['template'] ) ? sanitize_key( $params['template'] ) : false;

		// Allow if current user can edit posts.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return rest_ensure_response( false );
		}

		// Get recipe.
		$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

		if ( $recipe ) {
			$template = false;

			if ( $template_slug ) {
				$template = WPRM_Template_Manager::get_template_by_slug( $template_slug );
			}

			if ( ! $template ) {
				$template = WPRM_Template_Manager::get_template_by_type( 'single', $recipe->type() );
			}

			$html = '';
			$style = WPRM_Template_Manager::get_template_css( $template );

			if ( $style ) {
				$html .= '<style type="text/css">' . $style . '</style>';
			}

			$html .= '<div id="wprm-recipe-container-' . esc_attr( $recipe->id() ) . '" class="wprm-recipe-container" data-recipe-id="' . esc_attr( $recipe->id() ) . '" data-servings="' . esc_attr( $recipe->servings() ) . '">';
			$html .= do_shortcode( WPRM_Template_Manager::get_template( $recipe, 'single', $template['slug'] ) );
			$html .= '</div>';

			return rest_ensure_response( $html );
		}

		return rest_ensure_response( false );
	}

	/**
	 * Handle sanitize call to the REST API.
	 *
	 * @since 9.8.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_sanitize( $request ) {
		// Parameters.
		$params = $request->get_params();

		$text = isset( $params['text'] ) ? $params['text'] : '';

		$sanitized = WPRM_Recipe_Sanitizer::sanitize_html( $text );

		return rest_ensure_response( $sanitized );
	}

	/**
	 * Handle get Divi 5 builder data call to the REST API.
	 *
	 * @since 9.7.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_divi5_builder_data( $request ) {
		$data = array(
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'endpoints' => array(
				'preview' => trailingslashit( rest_url( 'wp-recipe-maker/v1/utilities/preview' ) ),
			),
			'latestRecipes'   => WPRM_Recipe_Manager::get_latest_recipes( 20, 'id' ),
		);

		return rest_ensure_response( $data );
	}
}

WPRM_Api_Utilities::init();
