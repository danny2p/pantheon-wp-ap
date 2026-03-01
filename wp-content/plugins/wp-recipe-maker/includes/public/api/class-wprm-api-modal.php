<?php
/**
 * API for the recipe modal.
 *
 * @link       https://bootstrapped.ventures
 * @since      5.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * API for the recipe modal.
 *
 * @since      5.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Modal {

	/**
	 * Register actions and filters.
	 *
	 * @since    5.0.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    5.0.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) { // Prevent issue with Jetpack.
			register_rest_route( 'wp-recipe-maker/v1', '/modal/suggest', array(
				'callback' => array( __CLASS__, 'api_modal_suggest' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			));
		register_rest_route( 'wp-recipe-maker/v1', '/modal/ingredient/parse', array(
			'callback' => array( __CLASS__, 'api_modal_parse_ingredients' ),
			'methods' => 'POST',
			'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
		));
		register_rest_route( 'wp-recipe-maker/v1', '/modal/categories', array(
			'callback' => array( __CLASS__, 'api_modal_categories' ),
			'methods' => 'POST',
			'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
		));
		}
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since 5.0.0
	 */
	public static function api_required_permissions() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Handle suggest call to the REST API.
	 *
	 * @since 5.0.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_modal_suggest( $request ) {
		// Parameters.
		$params = $request->get_params();

		$type = isset( $params['type'] ) ? $params['type'] : 'ingredient';
		$search = isset( $params['search'] ) ? $params['search'] : '';
		$search = trim( strip_tags( $search ) );

		// ' is stored differently in the database. Make sure picks up.
		$search = str_replace( '&#39;', '&#039;', $search );

		// Get taxonomy.
		switch ( $type ) {
			case 'ingredient-unit':
				$taxonomy = 'wprm_ingredient_unit';
				break;
			case 'equipment':
				$taxonomy = 'wprm_equipment';
				break;
			default:
				$taxonomy = 'wprm_ingredient';
				break;
		}

		// Regular search.
		$args = array(
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'number' => 10,
			'offset' => 0,
			'count' => true,
			'orderby' => 'count',
			'order' => 'DESC',
			'search' => $search,
		);

		$query = new WP_Term_Query( $args );
		$terms = $query->get_terms();
		$suggestions = array();

		// Search plural for ingredients and ingredient units.
		if ( 'wprm_ingredient' === $taxonomy || 'wprm_ingredient_unit' === $taxonomy ) {
			$plural_key = $taxonomy . '_plural';

			$args = array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
				'number' => 10,
				'offset' => 0,
				'count' => true,
				'orderby' => 'count',
				'order' => 'DESC',
				'meta_query' => array(
					array(
						'key' => $plural_key,
						'compare' => 'LIKE',
						'value' => $search,
					),
				),
			);
	
			$query = new WP_Term_Query( $args );
			$plural_terms = $query->get_terms();

			if ( $plural_terms && is_array( $plural_terms ) ) {
				foreach ( $plural_terms as $plural_term ) {
					$plural = get_term_meta( $plural_term->term_id, $plural_key, true );

					if ( $plural ) {
						$suggestions[ $plural ] = array(
							'name' => $plural,
							'count' => $plural_term->count,
						);
					}
				}
			}
		}

		// Get suggestions from terms.
		if ( $terms && is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( ! array_key_exists( $term->name, $suggestions ) ) {
					$suggestions[ $term->name ] = array(
						'name' => $term->name,
						'count' => $term->count,
					);
				}
			}
		}

		$suggestions = array_values( $suggestions);

		// Reorder based on count.
		usort( $suggestions, function($a, $b) {
			if ( $a['count'] == $b['count'] ) {
				return 0;
			}
			return $a['count'] > $b['count'] ? -1 : 1;
		} );

		$data = array(
			'suggestions' => array_slice( $suggestions, 0, 10 ),
		);

		return rest_ensure_response( $data );
	}

	/**
	 * Handle parse ingredients call to the REST API.
	 *
	 * @since 5.0.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_modal_parse_ingredients( $request ) {
		// Parameters.
		$params = $request->get_params();

		$ingredients = isset( $params['ingredients'] ) ? $params['ingredients'] : '';
		$parsed = array();

		foreach ( $ingredients as $index => $ingredient ) {
			$parsed[ $index ] = WPRM_Recipe_Parser::parse_ingredient( $ingredient );
		}

		$data = array(
			'parsed' => $parsed,
		);

		return rest_ensure_response( $data );
	}

	/**
	 * Handle categories call to the REST API.
	 *
	 * @since 8.10.0
	 * @param WP_REST_Request $request Current request.
	 */
	public static function api_modal_categories( $request ) {
		// Parameters.
		$params = $request->get_params();

		$taxonomy_key = isset( $params['taxonomy'] ) ? sanitize_text_field( $params['taxonomy'] ) : '';
		$search = isset( $params['search'] ) ? sanitize_text_field( $params['search'] ) : '';
		
		// Limit search string length to prevent abuse.
		if ( strlen( $search ) > 100 ) {
			$search = substr( $search, 0, 100 );
		}
		$term_ids = isset( $params['term_ids'] ) && is_array( $params['term_ids'] ) ? array_map( 'absint', $params['term_ids'] ) : array();

		if ( ! $taxonomy_key ) {
			return new WP_Error( 'missing_taxonomy', __( 'Taxonomy parameter is required.', 'wp-recipe-maker' ), array( 'status' => 400 ) );
		}

		// Get full taxonomy name.
		$taxonomy = 'wprm_' . $taxonomy_key;
		$wprm_taxonomies = WPRM_Taxonomies::get_taxonomies();

		if ( ! isset( $wprm_taxonomies[ $taxonomy ] ) ) {
			return new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy.', 'wp-recipe-maker' ), array( 'status' => 400 ) );
		}

		// Limit term_ids array size to prevent DoS attacks or performance issues.
		// 100 is a reasonable limit for selected terms when editing a recipe.
		if ( count( $term_ids ) > 100 ) {
			$term_ids = array_slice( $term_ids, 0, 100 );
		}

		// Remove any zero or invalid IDs after sanitization.
		$term_ids = array_filter( $term_ids, function( $id ) {
			return $id > 0;
		} );

		$terms = array();

		// If specific term IDs are requested (for selected values), fetch those first.
		if ( ! empty( $term_ids ) ) {
			$args = array(
				'taxonomy' => $taxonomy,
				'include' => $term_ids,
				'hide_empty' => false,
				'count' => true,
			);

			$query = new WP_Term_Query( $args );
			$fetched_terms = $query->get_terms();

			if ( $fetched_terms && is_array( $fetched_terms ) ) {
				foreach ( $fetched_terms as $term ) {
					$terms[ $term->term_id ] = $term;
				}
			}
		}

		// If search is provided, fetch matching terms.
		if ( $search ) {
			$args = array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
				'number' => 50, // Limit results to prevent large queries.
				'count' => true,
				'search' => $search,
				'orderby' => 'name',
				'order' => 'ASC',
			);

			$query = new WP_Term_Query( $args );
			$search_terms = $query->get_terms();

			if ( $search_terms && is_array( $search_terms ) ) {
				foreach ( $search_terms as $term ) {
					// Don't duplicate if already fetched.
					if ( ! isset( $terms[ $term->term_id ] ) ) {
						$terms[ $term->term_id ] = $term;
					}
				}
			}
		}

		// Convert to array format expected by frontend.
		$terms_array = array_values( $terms );

		$data = array(
			'terms' => $terms_array,
		);

		return rest_ensure_response( $data );
	}
}

WPRM_Api_Modal::init();
