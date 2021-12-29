<?php
/**
 * Register the Recipe post type.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Register the Recipe post type.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Post_Type {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 1 );

		add_filter( 'comments_open', array( __CLASS__, 'comments_open' ), 10, 2 );
		add_filter( 'post_type_link', array( __CLASS__, 'recipe_permalink' ), 10, 2 );
	}

	/**
	 * Register the Recipe post type.
	 *
	 * @since    1.0.0
	 */
	public static function register_post_type() {
		$labels = array(
			'name'               => _x( 'Recipes', 'post type general name', 'wp-recipe-maker' ),
			'singular_name'      => _x( 'Recipe', 'post type singular name', 'wp-recipe-maker' ),
		);

		$args = apply_filters( 'wprm_recipe_post_type_arguments', array(
			'labels'             => $labels,
			'public'             => false,
			'show_ui'            => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'query_var'          => false,
			'has_archive'        => false,
			'supports' 			 => array( 'title', 'editor', 'author', 'revisions', 'thumbnail' ),
		));

		// Special case: public recipe post type.
		if ( 'public' === WPRM_Settings::get( 'post_type_structure' ) ) {
			$slug = trim( WPRM_Settings::get( 'post_type_slug' ) );
			$has_archive = WPRM_Settings::get( 'post_type_has_archive' ) ? true : false;

			$args['public'] = true;
			$args['publicly_queryable'] = true;
			$args['show_in_nav_menus'] = true;
			$args['query_var'] = WPRM_POST_TYPE;
			$args['exclude_from_search'] = false;
			$args['has_archive'] = $has_archive;
			$args['rewrite'] = array(
				'slug' => $slug ? $slug : 'recipe',
			);

			if ( WPRM_Settings::get( 'post_type_comments' ) ) {
				$args['supports'][] = 'comments';
			}
		}

		// WP Ultimate Post Grid text search compatibility.
		if ( check_ajax_referer( 'wpupg_grid', 'security', false ) && isset( $_POST['search'] ) ) { // Input var okay.
			$args['exclude_from_search'] = false;
		}

		register_post_type( WPRM_POST_TYPE, $args );
	}

	/**
	 * Set comments open for recipes when enabled.
	 *
	 * @since	7.5.0
	 * @param	boolean	$open Wether or not the comments are open.
	 * @param	int		$post_id The post ID.
	 */
	public static function comments_open( $open, $post_id ) {
		if ( 'public' === WPRM_Settings::get( 'post_type_structure' ) && WPRM_Settings::get( 'post_type_comments' ) ) {
			$post = get_post( $post_id );

			if ( WPRM_POST_TYPE === $post->post_type ) {
				return true;
			}
		}

		return $open;
	}

	/**
	 * Alter the recipe permalink.
	 *
	 * @since	1.0.0
	 * @param	mixed  $url 			 The post URL.
	 * @param	object $post 		 The post object.
	 */
	public static function recipe_permalink( $url, $post ) {
		if ( WPRM_POST_TYPE === $post->post_type ) {
			if ( 'public' !== WPRM_Settings::get( 'post_type_structure' ) || 'parent' === WPRM_Settings::get( 'post_type_permalink_priority' ) ) {
				$recipe = WPRM_Recipe_Manager::get_recipe( $post );
				$parent_post_id = $recipe->parent_post_id();

				if ( $parent_post_id ) {
					$url = get_permalink( $parent_post_id );
				}
			}
		}
		return $url;
	}
}

WPRM_Post_Type::init();
