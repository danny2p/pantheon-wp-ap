<?php
/**
 * Register the idea post type.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Register the Idea post type.
 *
 * @since      10.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Idea_Post_Type {

	/**
	 * Register actions and filters.
	 *
	 * @since    10.4.0
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 1 );
	}

	/**
	 * Register the Idea post type.
	 *
	 * @since    10.4.0
	 */
	public static function register_post_type() {
		$labels = array(
			'name'          => _x( 'Ideas', 'post type general name', 'wp-recipe-maker' ),
			'singular_name' => _x( 'Idea', 'post type singular name', 'wp-recipe-maker' ),
		);

		$args = apply_filters( 'wprm_idea_post_type_arguments', array(
			'labels'                => $labels,
			'public'                => false,
			'rewrite'               => false,
			'capability_type'       => 'post',
			'query_var'             => false,
			'has_archive'           => false,
			'supports'              => array( 'title', 'editor', 'author' ),
			'show_in_rest'          => true,
			'rest_base'             => WPRM_IDEA_POST_TYPE,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		) );

		register_post_type( WPRM_IDEA_POST_TYPE, $args );
	}
}

WPRM_Idea_Post_Type::init();
