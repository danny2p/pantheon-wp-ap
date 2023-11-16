<?php
/**
 * Register the list post type.
 *
 * @link       http://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Register the list post type.
 *
 * @since      9.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_List_Post_Type {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.0.0
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 1 );
	}

	/**
	 * Register the List post type.
	 *
	 * @since    9.0.0
	 */
	public static function register_post_type() {
		$labels = array(
			'name'               => _x( 'Lists', 'post type general name', 'wp-recipe-maker' ),
			'singular_name'      => _x( 'List', 'post type singular name', 'wp-recipe-maker' ),
		);

		$args = apply_filters( 'wprm_list_post_type_arguments', array(
			'labels'            	=> $labels,
			'public'            	=> false,
			'rewrite'           	=> false,
			'capability_type'   	=> 'post',
			'query_var'         	=> false,
			'has_archive'       	=> false,
			'supports' 				=> array( 'title', 'editor', 'author' ),
			'show_in_rest'			=> true,
			'rest_base'				=> WPRM_LIST_POST_TYPE,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		));

		register_post_type( WPRM_LIST_POST_TYPE, $args );
	}
}

WPRM_List_Post_Type::init();
