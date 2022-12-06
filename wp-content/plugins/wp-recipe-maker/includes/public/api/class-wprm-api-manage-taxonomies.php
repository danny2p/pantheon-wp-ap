<?php
/**
 * API for managing the taxonomies.
 *
 * @link       https://bootstrapped.ventures
 * @since      5.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 */

/**
 * API for managing the taxonomies.
 *
 * @since      5.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Manage_Taxonomies {

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
		if ( function_exists( 'register_rest_field' ) ) {
			register_rest_route( 'wp-recipe-maker/v1', '/manage/taxonomy', array(
				'callback' => array( __CLASS__, 'api_manage_taxonomies' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			) );
			register_rest_route( 'wp-recipe-maker/v1', '/manage/taxonomy/merge', array(
				'callback' => array( __CLASS__, 'api_manage_taxonomies_merge' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			) );
			register_rest_route( 'wp-recipe-maker/v1', '/manage/taxonomy/label', array(
				'callback' => array( __CLASS__, 'api_manage_taxonomies_label' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			) );
			register_rest_route( 'wp-recipe-maker/v1', '/manage/taxonomy/bulk', array(
				'callback' => array( __CLASS__, 'api_manage_taxonomies_bulk_edit' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			) );
		}
	}

	/**
	 * Validate ID in API call.
	 *
	 * @since    5.0.0
	 * @param    mixed           $param Parameter to validate.
	 * @param    WP_REST_Request $request Current request.
	 * @param    mixed           $key Key.
	 */
	public static function api_validate_numeric( $param, $request, $key ) {
		return is_numeric( $param );
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since    5.0.0
	 */
	public static function api_required_permissions() {
		return current_user_can( WPRM_Settings::get( 'features_manage_access' ) );
	}

	/**
	 * Handle manage taxonomies call to the REST API.
	 *
	 * @since    5.0.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_taxonomies( $request ) {
		// Parameters.
		$params = $request->get_params();

		$type = isset( $params['type'] ) ? sanitize_key( $params['type'] ) : '';
		$taxonomy = 'wprm_' . $type;

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		// Different type for links.
		if ( in_array( $type, array( 'ingredient', 'equipment' ) ) ) {
			$link_type = $type;
		} else {
			$link_type = 'term';
		}

		$page = isset( $params['page'] ) ? intval( $params['page'] ) : 0;
		$page_size = isset( $params['pageSize'] ) ? intval( $params['pageSize'] ) : 25;
		$sorted = isset( $params['sorted'] ) ? $params['sorted'] : array( array( 'id' => 'id', 'desc' => true ) );
		$filtered = isset( $params['filtered'] ) ? $params['filtered'] : array();

		// Starting query args.
		$args = array(
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'number' => $page_size,
			'offset' => $page * $page_size,
			'count' => true,
		);

		// Order.
		$args['order'] = $sorted[0]['desc'] ? 'DESC' : 'ASC';
		switch( $sorted[0]['id'] ) {
			case 'name':
				$args['orderby'] = 'title';
				break;
			case 'slug':
				$args['orderby'] = 'slug';
				break;
			case 'count':
				$args['orderby'] = 'count';
				break;
			case 'plural':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wprm_ingredient_plural';
				break;
			case 'group':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wprmp_ingredient_group';
				break;
			case 'eafl':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprmp_' . $link_type . '_eafl';
				break;
			case 'link':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wprmp_' . $link_type . '_link';
				break;
			case 'link_nofollow':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wprmp_' . $link_type . '_link_nofollow';
				break;
			case 'wpupg_custom_link':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wpupg_custom_link';
				break;
			case 'wpupg_custom_image':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wpupg_custom_image';
				break;
			default:
			 	$args['orderby'] = 'ID';
		}

		// Filter.
		if ( $filtered ) {
			foreach ( $filtered as $filter ) {
				$value = $filter['value'];
				switch( $filter['id'] ) {
					case 'id':
						$args['wprm_search_id'] = $value;
						break;
					case 'name':
					case 'slug':
						// Special characters are stored encoded in the database because of the rich text editor.
						$value = htmlspecialchars( $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );

						$args['search'] = $value;
						break;
					case 'plural':
						if ( '' !== $value ) {
							$args['meta_query'][] = array(
								'key' => 'wprm_ingredient_plural',
								'compare' => 'LIKE',
								'value' => $value,
							);
						}
						break;
					case 'group':
						if ( '' !== $value ) {
							$args['meta_query'][] = array(
								'key' => 'wprmp_ingredient_group',
								'compare' => 'LIKE',
								'value' => $value,
							);
						}
						break;
					case 'eafl':
						if ( '' !== $value ) {
							$args['meta_query'][] = array(
								'key' => 'wprmp_' . $link_type . '_eafl',
								'compare' => 'LIKE',
								'value' => $value,
							);
						}
						break;
					case 'link':
						if ( '' !== $value ) {
							$args['meta_query'][] = array(
								'key' => 'wprmp_' . $link_type . '_link',
								'compare' => 'LIKE',
								'value' => $value,
							);
						}
						break;
					case 'link_nofollow':
						if ( 'all' !== $value ) {
							$args['meta_query'][] = array(
								'key' => 'wprmp_' . $link_type . '_link_nofollow',
								'compare' => '=',
								'value' => $value,
							);

							// Only when link exists.
							$args['meta_query'][] = array(
								'key' => 'wprmp_' . $type . '_link',
								'compare' => '!=',
								'value' => '',
							);
						}
						break;
					case 'image_id':
						if ( 'all' !== $value ) {
							$compare = 'yes' === $value ? 'EXISTS' : 'NOT EXISTS';

							$image_field = 'equipment' === $type || 'ingredient' === $type ? $type : 'term';

							$args['meta_query'][] = array(
								'key' => 'wprmp_' . $image_field . '_image_id',
								'compare' => $compare,
							);
						}
						break;
					case 'wpupg_custom_link':
						if ( '' !== $value ) {
							$args['meta_query'][] = array(
								'key' => 'wpupg_custom_link',
								'compare' => 'LIKE',
								'value' => $value,
							);
						}
						break;
					case 'wpupg_custom_image':
						if ( 'all' !== $value ) {
							if ( 'yes' === $value ) {
								$args['meta_query'][] = array(
									'key' => 'wpupg_custom_image',
									'compare' => 'EXISTS',
								);
							} else {
								$args['meta_query'][] = array(
									'key' => 'wpupg_custom_image',
									'compare' => 'NOT EXISTS',
								);
							}
						}
						break;
				}
			}
		}

		add_filter( 'terms_clauses', array( __CLASS__, 'api_manage_taxonomies_query' ), 10, 3 );
		$query = new WP_Term_Query( $args );

		unset( $args['number'] );
		unset( $args['offset'] );
		$filtered_terms = wp_count_terms( $taxonomy, $args );
		remove_filter( 'terms_clauses', array( __CLASS__, 'api_manage_taxonomies_query' ), 10, 3 );

		$total_terms = wp_count_terms( $taxonomy, array( 'hide_empty' => false ) );
		$rows = $query->terms ? array_values( $query->terms ) : array();

		// Extra information needed.
		if ( 'nutrition_ingredient' === $type ) {
			// Nutrition ingredient is a special case.
			foreach( $rows as $index => $row ) {
				$nutrition = WPRMPN_Ingredient_Manager::get_nutrition( $row->term_id );

				if ( $nutrition ) {
					$row->amount = $nutrition['amount'];
					$row->unit = $nutrition['unit'];
					$row->facts = $nutrition['nutrients'];
				} else {
					$row->amount = '';
					$row->unit = '';
					$row->facts = array();
				}
			}
		} else {
			// Only get permalinks if has archive pages.
			$has_archive_pages = WPRM_Taxonomies::has_archive_pages( $taxonomy );

			// Extra information needed for most other taxonomies.
			foreach( $rows as $index => $row ) {
				// Permalink if archive is enabled.
				$row->permalink = false;
				if ( $has_archive_pages ) {
					$permalink = get_term_link( $row->term_id, $taxonomy );
					$row->permalink = is_wp_error( $permalink ) ? false : $permalink;
				}

				// Special meta key naming for ingredient and equipment.
				$meta_key = 'term';
				if ( 'ingredient' === $type ) { $meta_key = 'ingredient'; }
				if ( 'equipment' === $type ) { $meta_key = 'equipment'; }

				// Term image.
				$row->image_id = get_term_meta( $row->term_id, 'wprmp_' . $meta_key . '_image_id', true );
				$row->image_url = '';

				if ( $row->image_id ) {
					$thumb = wp_get_attachment_image_src( $row->image_id, array( 150, 999 ) );

					if ( $thumb && isset( $thumb[0] ) ) {
						$row->image_url = $thumb[0];
					}
				}

				// EAFL integration.
				$row->eafl = get_term_meta( $row->term_id, 'wprmp_' . $meta_key . '_eafl', true );

				if ( $row->eafl && class_exists( 'EAFL_Link_Manager' ) ) {
					$link = EAFL_Link_Manager::get_link( $row->eafl );

					if ( $link ) {
						$row->eafl_details = $link->get_data_manage();	
					}
				}

				// Term link.
				$row->link = get_term_meta( $row->term_id, 'wprmp_' . $meta_key . '_link', true );
				$row->link_nofollow = '';
				if ( $row->link ) {
					$link_nofollow = get_term_meta( $row->term_id, 'wprmp_' . $meta_key . '_link_nofollow', true );
					$row->link_nofollow = in_array( $link_nofollow, array( 'default', 'nofollow', 'follow', 'sponsored' ) ) ? $link_nofollow : 'default';
				}

				// Grid integration.
				$row->wpupg_custom_link = get_term_meta( $row->term_id, 'wpupg_custom_link', true );
				$row->wpupg_custom_image = get_term_meta( $row->term_id, 'wpupg_custom_image', true );
				$row->wpupg_custom_image_url = '';

				if ( $row->wpupg_custom_image ) {
					$thumb = wp_get_attachment_image_src( $row->wpupg_custom_image, array( 150, 999 ) );

					if ( $thumb && isset( $thumb[0] ) ) {
						$row->wpupg_custom_image_url = $thumb[0];
					}
				}

				// Type specific fields.
				switch ( $type ) {
					case 'ingredient':
						$row->plural = get_term_meta( $row->term_id, 'wprm_ingredient_plural', true );
						$row->group = get_term_meta( $row->term_id, 'wprmp_ingredient_group', true );
						$row->product = class_exists( 'WPRMPP_Meta' ) ? WPRMPP_Meta::get_product_from_term_id( $row->term_id ) : false;
						break;
					case 'equipment':
						$row->affiliate_html = get_term_meta( $row->term_id, 'wprmp_equipment_affiliate_html', true );
						$row->product = class_exists( 'WPRMPP_Meta' ) ? WPRMPP_Meta::get_product_from_term_id( $row->term_id ) : false;
						break;
					case 'suitablefordiet':
						$row->label = get_term_meta( $row->term_id, 'wprm_term_label', true );
						break;
				}
			}
		}

		return array(
			'rows' => $rows,
			'total' => intval( $total_terms ),
			'filtered' => intval( $filtered_terms ),
			'pages' => ceil( $filtered_terms / $page_size ),
		);
	}

	/**
	 * Filter the where taxonomies query.
	 *
	 * @since	5.0.0
	 */
	public static function api_manage_taxonomies_query( $pieces, $taxonomies, $args ) {		
		$id_search = isset( $args['wprm_search_id'] ) ? $args['wprm_search_id'] : false;
		if ( $id_search ) {
			$pieces['where'] .= ' AND t.term_id LIKE \'%' . esc_sql( like_escape( $id_search ) ) . '%\'';
		}

		return $pieces;
	}

	/**
	 * Handle taxonomies merge call to the REST API.
	 *
	 * @since    5.0.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_taxonomies_merge( $request ) {
		// Parameters.
		$params = $request->get_params();

		$type = isset( $params['type'] ) ? sanitize_key( $params['type'] ) : '';
		$taxonomy = 'wprm_' . $type;

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		$old_id = isset( $params['oldId'] ) ? intval( $params['oldId'] ) : false;
		$new_id = isset( $params['newId'] ) ? intval( $params['newId'] ) : false;

		if ( $old_id && $new_id ) {
			$old_term = get_term( $old_id, $taxonomy );
			$new_term = get_term( $new_id, $taxonomy );

			if ( $old_term && ! is_wp_error( $old_term ) && $new_term && ! is_wp_error( $new_term ) ) {
				// Add new term ID to recipes using the old term ID.
				$args = array(
					'post_type' => WPRM_POST_TYPE,
					'post_status' => 'any',
					'nopaging' => true,
					'tax_query' => array(
						array(
							'taxonomy' => $old_term->taxonomy,
							'field' => 'id',
							'terms' => $old_term->term_id,
						),
					)
				);
		
				$query = new WP_Query( $args );
				$posts = $query->posts;
				foreach ( $posts as $post ) {
					if ( 'wprm_ingredient' === $old_term->taxonomy ) {
						$recipe = WPRM_Recipe_Manager::get_recipe( $post );
		
						$new_ingredients = array();
						$new_ingredient_ids = array();
						foreach ( $recipe->ingredients() as $ingredient_group ) {
							$new_ingredient_group = $ingredient_group;
							$new_ingredient_group['ingredients'] = array();
		
							foreach ( $ingredient_group['ingredients'] as $ingredient ) {
								if ( intval( $ingredient['id'] ) === $old_term->term_id ) {
									$ingredient['id'] = $new_term->term_id;
									$new_name = $new_term->name;

									// Check if we need to use plural.
									if ( '' === $ingredient['unit'] && 1.0 !== floatval( $ingredient['amount'] ) ) {
										$plural = get_term_meta( $new_term->term_id, 'wprm_ingredient_plural', true );

										if ( $plural ) {
											$new_name = $plural;
										}
									}

									$ingredient['name'] = $new_name;
								}
								$new_ingredient_ids[] = intval( $ingredient['id'] );
								$new_ingredient_group['ingredients'][] = $ingredient;
							}
		
							$new_ingredients[] = $new_ingredient_group;
						}
		
						$new_ingredient_ids = array_unique( $new_ingredient_ids );
						wp_set_object_terms( $recipe->id(), $new_ingredient_ids, 'wprm_ingredient', false );
		
						update_post_meta( $recipe->id(), 'wprm_ingredients', $new_ingredients );
					} else if ( 'wprm_ingredient_unit' === $old_term->taxonomy ) {
						$recipe = WPRM_Recipe_Manager::get_recipe( $post );
		
						$new_ingredients = array();
						$new_ingredient_unit_ids = array();
						foreach ( $recipe->ingredients() as $ingredient_group ) {
							$new_ingredient_group = $ingredient_group;
							$new_ingredient_group['ingredients'] = array();
		
							foreach ( $ingredient_group['ingredients'] as $ingredient ) {
								if ( isset( $ingredient['unit_id'] ) && intval( $ingredient['unit_id'] ) === $old_term->term_id ) {
									$ingredient['unit_id'] = $new_term->term_id;
									$ingredient['unit'] = $new_term->name;

									$new_ingredient_unit_ids[] = intval( $ingredient['unit_id'] );
								}

								// Check converted as well.
								if ( isset( $ingredient['converted'] ) ) {
									foreach ( $ingredient['converted'] as $system => $conversion ) {
										if ( isset( $ingredient['converted'][ $system ]['unit_id'] ) && intval( $ingredient['converted'][ $system ]['unit_id'] ) === $old_term->term_id ) {
											$ingredient['converted'][ $system ]['unit_id'] = $new_term->term_id;
											$ingredient['converted'][ $system ]['unit'] = $new_term->name;

											$new_ingredient_unit_ids[] = intval( $ingredient['converted'][ $system ]['unit_id'] );
										}
									}
								}

								$new_ingredient_group['ingredients'][] = $ingredient;
							}
		
							$new_ingredients[] = $new_ingredient_group;
						}
		
						$new_ingredient_unit_ids = array_unique( $new_ingredient_unit_ids );
						wp_set_object_terms( $recipe->id(), $new_ingredient_unit_ids, 'wprm_ingredient_unit', false );
		
						update_post_meta( $recipe->id(), 'wprm_ingredients', $new_ingredients );
					} else if ( 'wprm_equipment' === $old_term->taxonomy ) {
						$recipe = WPRM_Recipe_Manager::get_recipe( $post );
		
						$new_equipment = array();
						$new_equipment_ids = array();
						foreach ( $recipe->equipment() as $equipment ) {
							if ( intval( $equipment['id'] ) === $old_term->term_id ) {
								$equipment['id'] = $new_term->term_id;
								$equipment['name'] = $new_term->name;
							}
							$new_equipment_ids[] = intval( $equipment['id'] );
							$new_equipment[] = $equipment;
						}
		
						$new_equipment_ids = array_unique( $new_equipment_ids );
						wp_set_object_terms( $recipe->id(), $new_equipment_ids, 'wprm_equipment', false );
		
						update_post_meta( $recipe->id(), 'wprm_equipment', $new_equipment );
					} else {
						// Append new term.
						wp_set_object_terms( $post->ID, $new_term->term_id, $new_term->taxonomy, true );
					}
				}

				// Delete old term.
				wp_delete_term( $old_term->term_id, $taxonomy );
				return true;
			}
		}

		return false;
	}

	/**
	 * Handle taxonomies label call to the REST API.
	 *
	 * @since    5.9.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_taxonomies_label( $request ) {
		// Parameters.
		$params = $request->get_params();

		$type = isset( $params['type'] ) ? sanitize_key( $params['type'] ) : '';
		$taxonomy = 'wprm_' . $type;

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		$id = isset( $params['id'] ) ? intval( $params['id'] ) : false;
		$label = isset( $params['label'] ) ? sanitize_text_field( $params['label'] ) : false;

		if ( $id && $label ) {
			update_term_meta( $id, 'wprm_term_label', $label );
			return true;
		}

		return false;
	}

	/**
	 * Handle taxonomies bulk edit call to the REST API.
	 *
	 * @since    5.0.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_taxonomies_bulk_edit( $request ) {
		// Parameters.
		$params = $request->get_params();

		$type = isset( $params['type'] ) ? sanitize_key( $params['type'] ) : '';
		$taxonomy = 'wprm_' . $type;

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		$ids = isset( $params['ids'] ) ? array_map( 'intval', $params['ids'] ) : array();
		$action = isset( $params['action'] ) ? $params['action'] : false;

		if ( $ids && $action && $action['type'] ) {
			// Do once.
			if ( 'export' === $action['type'] ) {
				if ( class_exists( 'WPRMP_Export_Taxonomies' ) ) {
					return WPRMP_Export_Taxonomies::bulk_edit_export( $ids );
				} else {
					return false;
				}
			}

			// Do per term.
			$args = array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
				'include' => $ids,
			);

			$query = new WP_Term_Query( $args );
			$terms = $query->terms ? array_values( $query->terms ) : array();

			foreach ( $terms as $term ) {
				switch ( $action['type'] ) {
					case 'change-group':
						if ( 'wprm_ingredient' === $taxonomy ) {
							$group = sanitize_text_field( $action['options'] );
							update_term_meta( $term->term_id, 'wprmp_ingredient_group', $group );
						}
						break;
					case 'change-link':
						if ( 'wprm_ingredient' === $taxonomy ) {
							$link = trim( $action['options'] );
							update_term_meta( $term->term_id, 'wprmp_ingredient_link', $link );
						}
						if ( 'wprm_equipment' === $taxonomy ) {
							$link = trim( $action['options'] );
							update_term_meta( $term->term_id, 'wprmp_equipment_link', $link );
						}
						break;
					case 'change-nofollow':
						if ( 'wprm_ingredient' === $taxonomy ) {
							$nofollow = in_array( $action['options'], array( 'default', 'nofollow', 'follow', 'sponsored' ), true ) ? $action['options'] : 'default';
							update_term_meta( $term->term_id, 'wprmp_ingredient_link_nofollow', $nofollow );
						}
						if ( 'wprm_equipment' === $taxonomy ) {
							$nofollow = in_array( $action['options'], array( 'default', 'nofollow', 'follow', 'sponsored' ), true ) ? $action['options'] : 'default';
							update_term_meta( $term->term_id, 'wprmp_equipment_link_nofollow', $nofollow );
						}
						break;
					case 'change-html':
						if ( 'wprm_equipment' === $taxonomy ) {
							$html = trim( $action['options'] );
							update_term_meta( $term->term_id, 'wprmp_equipment_affiliate_html', $html );
						}
						break;
					case 'create-nutrition':
						// Sanitize name before lookup.
						$name = WPRM_Recipe_Sanitizer::sanitize_html( $term->name );

						// Find or create term.
						$nutrition_term = term_exists( $name, 'wprm_nutrition_ingredient' );

						if ( 0 === $nutrition_term || null === $nutrition_term ) {
							wp_insert_term( $name, 'wprm_nutrition_ingredient' );
						}
						break;
					case 'delete':
						wp_delete_term( $term->term_id, $taxonomy );
						break;
				}
			}

			return true;
		}

		return false;
	}
}

WPRM_Api_Manage_Taxonomies::init();
