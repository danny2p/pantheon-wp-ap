<?php
/**
 * Open up recipes in the WordPress REST API.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Open up recipes in the WordPress REST API.
 *
 * @since      1.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Recipe{

	/**
	 * Register actions and filters.
	 *
	 * @since    1.4.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_recipe_data' ) );
		add_action( 'rest_insert_' . WPRM_POST_TYPE, array( __CLASS__, 'api_insert_update_recipe' ), 10, 3 );

		// Make sure this gets added.
		add_filter( 'wprm_recipe_post_type_arguments', array( __CLASS__, 'recipe_post_type_arguments' ), 99 );
	}

	/**
	 * Register recipe data for the REST API.
	 *
	 * @since    1.4.0
	 */
	public static function api_register_recipe_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_field( WPRM_POST_TYPE, 'recipe', array(
				'get_callback'    => array( __CLASS__, 'api_get_recipe_data' ),
				'update_callback' => null,
				'schema'          => null,
			));
		}
	}

	/**
	 * Handle recipe calls to the REST API.
	 *
	 * @since 1.4.0
	 * @param array           $object Details of current post.
	 * @param mixed           $field_name Name of field.
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_recipe_data( $object, $field_name, $request ) {
		// Make sure we're getting the most recent data by invalidating first.
		WPRM_Recipe_Manager::invalidate_recipe( $object['id'] );
		$recipe = WPRM_Recipe_Manager::get_recipe( $object['id'] );

		return $recipe->get_data( 'api' );
	}

	/**
	 * Handle recipe calls to the REST API.
	 *
	 * @since 1.27.0
	 * @param WP_Post         $post     Inserted or updated post object.
	 * @param WP_REST_Request $request  Request object.
	 * @param bool            $creating True when creating a post, false when updating.
	 */
	public static function api_insert_update_recipe( $post, $request, $creating ) {
		$params = $request->get_params();
		$recipe_data = isset( $params['recipe'] ) ? $params['recipe'] : array();

		$recipe = WPRM_Recipe_Sanitizer::sanitize( $recipe_data );
		$recipe_id = $post->ID;

		// Allow images to be passed as URLs instead of IDs, but only when IDs had not been set explicitly.
		require_once( WPRM_DIR . 'includes/admin/class-wprm-import-helper.php' );
		if ( isset( $recipe_data['image_url'] ) && $recipe_data['image_url'] && ! isset( $recipe['image_id'] ) ) {
			$attachment_id = WPRM_Import_Helper::get_or_upload_attachment( $recipe_id, $recipe_data['image_url'] );
			if ( $attachment_id ) {
				$recipe['image_id'] = $attachment_id;
			}
		}
		if ( isset( $recipe_data['pin_image_url'] ) && $recipe_data['pin_image_url'] && ! isset( $recipe['pin_image_id'] ) ) {
			$attachment_id = WPRM_Import_Helper::get_or_upload_attachment( $recipe_id, $recipe_data['pin_image_url'] );
			if ( $attachment_id ) {
				$recipe['pin_image_id'] = $attachment_id;
			}
		}

		// Save recipe data.
		$ignore_edit_log = $creating;
		WPRM_Recipe_Saver::update_recipe( $recipe_id, $recipe, $ignore_edit_log );

		if ( $creating ) {
			WPRM_Changelog::log( 'recipe_created', $recipe_id );
		}
	}

	/**
	 * Add REST API options to the recipe post type arguments.
	 *
	 * @since    1.4.0
	 * @param	 	 array $args Post type arguments.
	 */
	public static function recipe_post_type_arguments( $args ) {
		$args['show_in_rest'] = true;
		$args['rest_base'] = WPRM_POST_TYPE;
		$args['rest_controller_class'] = 'WP_REST_Posts_Controller';

		return $args;
	}
}

WPRM_Api_Recipe::init();
