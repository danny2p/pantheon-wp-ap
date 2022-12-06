<?php
/**
 * Handle ingredients in the WordPress REST API.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle recipes in the WordPress REST API.
 *
 * @since      5.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Ingredients {

	/**
	 * Register actions and filters.
	 *
	 * @since	5.0.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );

		add_action( 'rest_insert_wprm_ingredient', array( __CLASS__, 'api_insert_update_ingredient' ), 10, 3 );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    5.0.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_field( 'wprm_ingredient', 'ingredient', array(
				'get_callback'    => array( __CLASS__, 'api_get_ingredient_meta' ),
				'update_callback' => array( __CLASS__, 'api_update_ingredient_meta' ),
				'schema'          => null,
			));
		}
	}
	
	/**
	 * Handle ingredient calls to the REST API.
	 *
	 * @since 5.0.0
	 * @param array           $object Details of current post.
	 * @param mixed           $field_name Name of field.
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_ingredient_meta( $object, $field_name, $request ) {
		$meta = get_term_meta( $object[ 'id' ] );

		return apply_filters( 'wprm_get_term_meta', array(
			'plural' => isset( $meta['wprm_ingredient_plural'] ) ? $meta['wprm_ingredient_plural'] : '',
			'group' => isset( $meta['wprmp_ingredient_group'] ) ? $meta['wprmp_ingredient_group'] : '',
			'image_id' => isset( $meta['wprmp_ingredient_image_id'] ) ? $meta['wprmp_ingredient_image_id'] : '',
			'eafl' => isset( $meta['wprmp_ingredient_eafl'] ) ? $meta['wprmp_ingredient_eafl'] : '',
			'link' => isset( $meta['wprmp_ingredient_link'] ) ? $meta['wprmp_ingredient_link'] : '',
			'link_nofollow' => isset( $meta['wprmp_ingredient_link_nofollow'] ) ? $meta['wprmp_ingredient_link_nofollow'] : '',
			'wpupg_custom_link' => isset( $meta['wpupg_custom_link'] ) ? $meta['wpupg_custom_link'] : '',
			'wpupg_custom_image' => isset( $meta['wpupg_custom_image'] ) ? $meta['wpupg_custom_image'] : '',
		), $object, $meta );
	}
	
	/**
	 * Handle ingredient calls to the REST API.
	 *
	 * @since 5.0.0
	 * @param array		$meta	Array of meta parsed from the request.
	 * @param WP_Term	$term 	Term to update.
	 */
	public static function api_update_ingredient_meta( $meta, $term ) {
		if ( isset( $meta['plural'] ) ) {
			$plural = sanitize_text_field( $meta['plural'] );
			update_term_meta( $term->term_id, 'wprm_ingredient_plural', $plural );
		}
		if ( isset( $meta['group'] ) ) {
			$group = sanitize_text_field( $meta['group'] );
			update_term_meta( $term->term_id, 'wprmp_ingredient_group', $group );
		}
		if ( isset( $meta['image_id'] ) ) {
			$image_id = intval( $meta['image_id'] );

			if ( 0 === $image_id ) {
				delete_term_meta( $term->term_id, 'wprmp_ingredient_image_id' );
			} else {
				update_term_meta( $term->term_id, 'wprmp_ingredient_image_id', $image_id );
			}
		}
		if ( isset( $meta['eafl'] ) ) {
			$eafl = intval( $meta['eafl'] );
			if ( 0 === $eafl ) {
				delete_term_meta( $term->term_id, 'wprmp_ingredient_eafl' );
			} else {
				update_term_meta( $term->term_id, 'wprmp_ingredient_eafl', $eafl );
			}
		}
		if ( isset( $meta['link'] ) ) {
			$link = trim( $meta['link'] );
			update_term_meta( $term->term_id, 'wprmp_ingredient_link', $link );
		}
		if ( isset( $meta['link_nofollow'] ) ) {
			$nofollow = in_array( $meta['link_nofollow'], array( 'default', 'nofollow', 'follow', 'sponsored' ), true ) ? $meta['link_nofollow'] : 'default';
			update_term_meta( $term->term_id, 'wprmp_ingredient_link_nofollow', $nofollow );
		}
		if ( isset( $meta['wpupg_custom_link'] ) ) {
			$link = trim( $meta['wpupg_custom_link'] );
			update_term_meta( $term->term_id, 'wpupg_custom_link', $link );
		}
		if ( isset( $meta['wpupg_custom_image'] ) ) {
			$image = intval( $meta['wpupg_custom_image'] );
			if ( 0 === $image ) {
				delete_term_meta( $term->term_id, 'wpupg_custom_image' );
			} else {
				update_term_meta( $term->term_id, 'wpupg_custom_image', $image );
			}
		}

		do_action( 'wprm_update_term_meta', $term, $meta );
	}

	/**
	 * Handle ingredient calls to the REST API.
	 *
	 * @since 5.0.0
	 * @param WP_Term         $term     Inserted or updated term object.
	 * @param WP_REST_Request $request  Request object.
	 * @param bool            $creating True when creating a post, false when updating.
	 */
	public static function api_insert_update_ingredient( $term, $request, $creating ) {
		$params = $request->get_params();

		// Need to update recipes using this ingredient of the name changes.
		if ( false === $creating && isset( $params['name'] ) ) {
			$args = array(
				'post_type' => WPRM_POST_TYPE,
				'post_status' => 'any',
				'nopaging' => true,
				'tax_query' => array(
					array(
						'taxonomy' => 'wprm_ingredient',
						'field' => 'id',
						'terms' => $term->term_id,
					),
				)
			);
	
			$query = new WP_Query( $args );
			$posts = $query->posts;
			foreach ( $posts as $post ) {
				$recipe = WPRM_Recipe_Manager::get_recipe( $post );
	
				$new_ingredients = array();
				foreach ( $recipe->ingredients() as $ingredient_group ) {
					$new_ingredient_group = $ingredient_group;
					$new_ingredient_group['ingredients'] = array();
	
					foreach ( $ingredient_group['ingredients'] as $ingredient ) {
						if ( intval( $ingredient['id'] ) === $term->term_id ) {
							$ingredient['name'] = $term->name;
						}
						$new_ingredient_group['ingredients'][] = $ingredient;
					}
	
					$new_ingredients[] = $new_ingredient_group;
				}
	
				update_post_meta( $recipe->id(), 'wprm_ingredients', $new_ingredients );
			}
		}
	}
}

WPRM_Api_Ingredients::init();
