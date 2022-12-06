<?php
/**
 * Handle taxonomies in the WordPress REST API.
 *
 * @link       https://bootstrapped.ventures
 * @since      6.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle taxonomies in the WordPress REST API.
 *
 * @since      6.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Taxonomies {

	/**
	 * Register actions and filters.
	 *
	 * @since	6.4.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    6.4.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			$taxonomies = WPRM_Taxonomies::get_taxonomies();
			foreach ( $taxonomies as $taxonomy => $options ) {
				$key = substr( $taxonomy, 5 );

				register_rest_field( $taxonomy, $key, array(
					'get_callback'    => array( __CLASS__, 'api_get_term_meta' ),
					'update_callback' => array( __CLASS__, 'api_update_term_meta' ),
					'schema'          => null,
				));
			}
		}
	}
	
	/**
	 * Handle taxonomy calls to the REST API.
	 *
	 * @since 6.4.0
	 * @param array           $object Details of current post.
	 * @param mixed           $field_name Name of field.
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_get_term_meta( $object, $field_name, $request ) {
		$meta = get_term_meta( $object[ 'id' ] );

		return apply_filters( 'wprm_get_term_meta', array(
			'eafl' => isset( $meta['wprmp_term_eafl'] ) ? $meta['wprmp_term_eafl'] : '',
			'link' => isset( $meta['wprmp_term_link'] ) ? $meta['wprmp_term_link'] : '',
			'link_nofollow' => isset( $meta['wprmp_term_link_nofollow'] ) ? $meta['wprmp_term_link_nofollow'] : '',
			'wpupg_custom_link' => isset( $meta['wpupg_custom_link'] ) ? $meta['wpupg_custom_link'] : '',
			'wpupg_custom_image' => isset( $meta['wpupg_custom_image'] ) ? $meta['wpupg_custom_image'] : '',
		), $object, $meta );
	}
	
	/**
	 * Handle taxonomy calls to the REST API.
	 *
	 * @since 6.4.0
	 * @param array		$meta	Array of meta parsed from the request.
	 * @param WP_Term	$term 	Term to update.
	 */
	public static function api_update_term_meta( $meta, $term ) {
		if ( isset( $meta['eafl'] ) ) {
			$eafl = intval( $meta['eafl'] );
			if ( 0 === $eafl ) {
				delete_term_meta( $term->term_id, 'wprmp_term_eafl' );
			} else {
				update_term_meta( $term->term_id, 'wprmp_term_eafl', $eafl );
			}
		}
		if ( isset( $meta['image_id'] ) ) {
			$image_id = intval( $meta['image_id'] );

			if ( 0 === $image_id ) {
				delete_term_meta( $term->term_id, 'wprmp_term_image_id' );
			} else {
				update_term_meta( $term->term_id, 'wprmp_term_image_id', $image_id );
			}
		}
		if ( isset( $meta['link'] ) ) {
			$link = trim( $meta['link'] );
			update_term_meta( $term->term_id, 'wprmp_term_link', $link );
		}
		if ( isset( $meta['link_nofollow'] ) ) {
			$nofollow = in_array( $meta['link_nofollow'], array( 'default', 'nofollow', 'follow', 'sponsored' ), true ) ? $meta['link_nofollow'] : 'default';
			update_term_meta( $term->term_id, 'wprmp_term_link_nofollow', $nofollow );
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
}

WPRM_Api_Taxonomies::init();
