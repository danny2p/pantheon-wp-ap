<?php
/**
 * Open up recipe templates in the WordPress REST API.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Open up recipe templates in the WordPress REST API.
 *
 * @since      4.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Templates {

	/**
	 * Register actions and filters.
	 *
	 * @since    4.0.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    4.0.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_route( 'wp-recipe-maker/v1', '/template', array(
				'callback' => array( __CLASS__, 'api_get_templates' ),
				'methods' => 'GET',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/template', array(
				'callback' => array( __CLASS__, 'api_update_template' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/template', array(
				'callback' => array( __CLASS__, 'api_delete_template' ),
				'methods' => 'DELETE',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
			register_rest_route( 'wp-recipe-maker/v1', '/template/preview', array(
				'callback' => array( __CLASS__, 'api_preview_template' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
		}
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since 4.0.0
	 */
	public static function api_required_permissions() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Handle get template call to the REST API.
	 *
	 * @since 4.0.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_templates( $request ) {
		return WPRM_Template_Manager::get_templates();
	}

	/**
	 * Handle update template call to the REST API.
	 *
	 * @since 4.0.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_update_template( $request ) {
		$params = $request->get_params();
		$template = isset( $params['template'] ) ? $params['template'] : array();
		return WPRM_Template_Editor::prepare_template_for_editor( WPRM_Template_Manager::save_template( $template ) );
	}
	
	/**
	 * Handle delete template call to the REST API.
	 *
	 * @since 4.0.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_delete_template( $request ) {
		$params = $request->get_params();
		$slug = isset( $params['slug'] ) ? $params['slug'] : false;
		return WPRM_Template_Manager::delete_template( $slug );
	}

	/**
	 * Handle preview template call to the REST API.
	 *
	 * @since 4.0.3
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_preview_template( $request ) {
		$params = $request->get_params();
		$recipe_id = isset( $params['recipeId'] ) ? $params['recipeId'] : false;
		$shortcodes = isset( $params['shortcodes'] ) ? (array) $params['shortcodes'] : array();

		$preview = array();
		$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

		if ( $recipe ) {
			WPRM_Template_Shortcodes::set_current_recipe_id( $recipe->id() );

			foreach ( $shortcodes as $uid => $shortcode ) {
				$preview[ $uid ] = do_shortcode( $shortcode );
			}
		}

		return array(
			'preview' => (object) $preview,
		);
	}
}

WPRM_Api_Templates::init();
