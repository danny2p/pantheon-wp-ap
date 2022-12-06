<?php
/**
 * Handle equipment in the WordPress REST API.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle equipment in the WordPress REST API.
 *
 * @since      5.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Equipment {

	/**
	 * Register actions and filters.
	 *
	 * @since	5.0.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );

		add_action( 'rest_insert_wprm_equipment', array( __CLASS__, 'api_insert_update_equipment' ), 10, 3 );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    5.0.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_field( 'wprm_equipment', 'equipment', array(
				'get_callback'    => array( __CLASS__, 'api_get_equipment_meta' ),
				'update_callback' => array( __CLASS__, 'api_update_equipment_meta' ),
				'schema'          => null,
			));
		}
	}
	
	/**
	 * Handle equipment calls to the REST API.
	 *
	 * @since 5.0.0
	 * @param array           $object Details of current post.
	 * @param mixed           $field_name Name of field.
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_equipment_meta( $object, $field_name, $request ) {
		$meta = get_term_meta( $object[ 'id' ] );

		return apply_filters( 'wprm_get_term_meta', array(
			'eafl' => isset( $meta['wprmp_equipment_eafl'] ) ? $meta['wprmp_equipment_eafl'] : '',
			'link' => isset( $meta['wprmp_equipment_link'] ) ? $meta['wprmp_equipment_link'] : '',
			'link_nofollow' => isset( $meta['wprmp_equipment_link_nofollow'] ) ? $meta['wprmp_equipment_link_nofollow'] : '',
			'wpupg_custom_link' => isset( $meta['wpupg_custom_link'] ) ? $meta['wpupg_custom_link'] : '',
			'wpupg_custom_image' => isset( $meta['wpupg_custom_image'] ) ? $meta['wpupg_custom_image'] : '',
		), $object, $meta );
	}
	
	/**
	 * Handle equipment calls to the REST API.
	 *
	 * @since 5.0.0
	 * @param array		$meta	Array of meta parsed from the request.
	 * @param WP_Term	$term 	Term to update.
	 */
	public static function api_update_equipment_meta( $meta, $term ) {
		if ( isset( $meta['eafl'] ) ) {
			$eafl = intval( $meta['eafl'] );
			if ( 0 === $eafl ) {
				delete_term_meta( $term->term_id, 'wprmp_equipment_eafl' );
			} else {
				update_term_meta( $term->term_id, 'wprmp_equipment_eafl', $eafl );
			}
		}
		if ( isset( $meta['link'] ) ) {
			$link = trim( $meta['link'] );
			update_term_meta( $term->term_id, 'wprmp_equipment_link', $link );
		}
		if ( isset( $meta['link_nofollow'] ) ) {
			$nofollow = in_array( $meta['link_nofollow'], array( 'default', 'nofollow', 'follow', 'sponsored' ), true ) ? $meta['link_nofollow'] : 'default';
			update_term_meta( $term->term_id, 'wprmp_equipment_link_nofollow', $nofollow );
		}
		if ( isset( $meta['image_id'] ) ) {
			$image_id = intval( $meta['image_id'] );

			if ( 0 === $image_id ) {
				delete_term_meta( $term->term_id, 'wprmp_equipment_image_id' );
			} else {
				update_term_meta( $term->term_id, 'wprmp_equipment_image_id', $image_id );
			}
		}
		if ( isset( $meta['affiliate_html'] ) ) {
			$affiliate_html = $meta['affiliate_html'];
			update_term_meta( $term->term_id, 'wprmp_equipment_affiliate_html', $affiliate_html );
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
	 * Handle equipment calls to the REST API.
	 *
	 * @since 5.0.0
	 * @param WP_Term         $term     Inserted or updated term object.
	 * @param WP_REST_Request $request  Request object.
	 * @param bool            $creating True when creating a post, false when updating.
	 */
	public static function api_insert_update_equipment( $term, $request, $creating ) {
		$params = $request->get_params();

		// Need to update recipes using this equipment of the name changes.
		if ( false === $creating && isset( $params['name'] ) ) {
			$args = array(
				'post_type' => WPRM_POST_TYPE,
				'post_status' => 'any',
				'nopaging' => true,
				'tax_query' => array(
					array(
						'taxonomy' => 'wprm_equipment',
						'field' => 'id',
						'terms' => $term->term_id,
					),
				)
			);
	
			$query = new WP_Query( $args );
			$posts = $query->posts;
			foreach ( $posts as $post ) {
				$recipe = WPRM_Recipe_Manager::get_recipe( $post );
	
				$new_equipment = array();
				foreach ( $recipe->equipment() as $equipment ) {
					if ( intval( $equipment['id'] ) === $term->term_id ) {
						$equipment['name'] = $term->name;
					}
	
					$new_equipment[] = $equipment;
				}
	
				update_post_meta( $recipe->id(), 'wprm_equipment', $new_equipment );
			}
		}
	}
}

WPRM_Api_Equipment::init();
