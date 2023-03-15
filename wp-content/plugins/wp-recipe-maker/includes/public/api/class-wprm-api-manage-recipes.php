<?php
/**
 * API for managing the recipes.
 *
 * @link       https://bootstrapped.ventures
 * @since      5.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 */

/**
 * API for managing the recipes.
 *
 * @since      5.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Manage_Recipes {

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
			register_rest_route( 'wp-recipe-maker/v1', '/manage/recipe', array(
				'callback' => array( __CLASS__, 'api_manage_recipes' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			) );
			register_rest_route( 'wp-recipe-maker/v1', '/manage/recipe/bulk', array(
				'callback' => array( __CLASS__, 'api_manage_recipes_bulk_edit' ),
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
	 * Handle manage recipes call to the REST API.
	 *
	 * @since    5.0.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_recipes( $request ) {
		// Parameters.
		$params = $request->get_params();

		$page = isset( $params['page'] ) ? intval( $params['page'] ) : 0;
		$page_size = isset( $params['pageSize'] ) ? intval( $params['pageSize'] ) : 25;
		$sorted = isset( $params['sorted'] ) ? $params['sorted'] : array( array( 'id' => 'id', 'desc' => true ) );
		$filtered = isset( $params['filtered'] ) ? $params['filtered'] : array();
		$fixed_filter = isset( $params['filter'] ) ? $params['filter'] : false;

		// Exclude recipe submissions.
		$post_status = array( 'publish', 'future', 'draft', 'private' );
		if ( ! WPRM_Addons::is_active( 'recipe-submission' ) ) {
			$post_status[] = 'pending';
		}

		// Starting query args.
		$args = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => $post_status,
			'posts_per_page' => $page_size,
			'offset' => $page * $page_size,
			'meta_query' => array(
				'relation' => 'AND',
			),
			'tax_query' => array(),
		);

		// Order.
		$args['order'] = $sorted[0]['desc'] ? 'DESC' : 'ASC';
		switch( $sorted[0]['id'] ) {
			case 'date':
				$args['orderby'] = 'date';
				break;
			case 'seo':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_seo_priority';
				break;
			case 'slug':
				$args['orderby'] = 'post_name';
				break;
			case 'name':
				$args['orderby'] = 'title';
				break;
			case 'pin_image_repin_id':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wprm_pin_image_repin_id';
				break;
			case 'post_author':
				$args['orderby'] = 'post_author';
				break;
			case 'type':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wprm_type';
				break;
			case 'author_display':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wprm_author_display';
				break;
			case 'parent_post_id':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_parent_post_id';
				break;
			case 'rating':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_rating_average';
				break;
			case 'prep_time':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_prep_time';
				break;
			case 'cook_time':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_cook_time';
				break;
			case 'custom_time':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_custom_time';
				break;
			case 'total_time':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_total_time';
				break;
			case 'servings':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_servings';
				break;
			default:
				// Check if custom field. 
				if ( 'custom_field_' === substr( $sorted[0]['id'], 0, 13 ) ) {
					$custom_field_key = substr( $sorted[0]['id'], 13 );

					$args['orderby'] = 'meta_value';
					$args['meta_key'] = 'wprm_custom_field_' . $custom_field_key;
					break;
				}

				// Default to order by ID.
			 	$args['orderby'] = 'ID';
		}

		// Filter.
		if ( $filtered ) {
			foreach ( $filtered as $filter ) {
				$args = self::update_args_for_filter( $args, $filter['id'], $filter['value'] );
			}
		}

		// Extra fixed filter.
		if ( false !== $fixed_filter && is_array( $fixed_filter ) && 2 === count( $fixed_filter ) ) {
			$taxonomy = 'wprm_' . $fixed_filter[0];
			$term_id = intval( $fixed_filter[1] );

			if ( $term_id && taxonomy_exists( $taxonomy ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field' => 'term_id',
					'terms' => $term_id,
				);
			} else {
				$args = self::update_args_for_filter( $args, $fixed_filter[0], urldecode( $fixed_filter[1] ) );
			}
		}

		add_filter( 'posts_where', array( __CLASS__, 'api_manage_recipes_query_where' ), 10, 2 );
		$query = new WP_Query( $args );
		remove_filter( 'posts_where', array( __CLASS__, 'api_manage_recipes_query_where' ), 10, 2 );

		$recipes = array();
		$posts = $query->posts;
		$no_permission_total = 0;

		foreach ( $posts as $post ) {
			$recipe = WPRM_Recipe_Manager::get_recipe( $post );

			if ( ! $recipe ) {
				continue;
			}

			if ( false === WPRM_Settings::get( 'manage_page_show_uneditable' ) && ! current_user_can( 'edit_post', $recipe->id() ) ) {
				$no_permission_total++;
				continue;
			}

			$recipes[] = $recipe->get_data_manage();
		}

		// Got total number of recipes.
		$total = (array) wp_count_posts( WPRM_POST_TYPE );
		unset( $total['trash'] );

		// Remove recipe submissions from total.
		if ( WPRM_Addons::is_active( 'recipe-submission' ) ) {
			unset( $total['pending'] );
		}

		// Totals.
		$total_recipes = array_sum( $total ) - $no_permission_total;
		$filtered_recipes = intval( $query->found_posts ) - $no_permission_total;

		return array(
			'rows' => array_values( $recipes ),
			'total' => $total_recipes,
			'filtered' => $filtered_recipes,
			'pages' => ceil( $filtered_recipes / $page_size ),
		);
	}

	/**
	 * Filter the where recipes query.
	 *
	 * @since    8.0.0
	 */
	public static function update_args_for_filter( $args, $filter, $value ) {
		switch( $filter ) {
			case 'seo':
				$seo_types = array(
					'bad' 		=> 5,
					'warning' 	=> 10,
					'rating' 	=> 15,
					'good' 		=> 20,
					'other' 	=> 25,
				);
				if ( 'all' !== $value && array_key_exists( $value, $seo_types ) ) {
					$args['meta_query'][] = array(
						'key' => 'wprm_seo_priority',
						'value' => $seo_types[ $value ],
					);
				}
				break;
			case 'id':
				$args['wprm_search_id'] = $value;
				break;
			case 'date':
				$args['wprm_search_date'] = $value;
				break;
			case 'name':
				$args['wprm_search_title'] = $value;
				break;
			case 'slug':
				$args['wprm_search_slug'] = $value;
				break;
			case 'summary':
				$args['wprm_search_content'] = $value;
				break;
			case 'post_author':
				if ( 'all' !== $value ) {
					$args['author'] = $value;
				}
				break;
			case 'type':
				if ( 'all' !== $value ) {
					if ( 'other' === $value ) {
						// Used non-food before other.
						$args['meta_query'][] = array(
							'key' => 'wprm_type',
							'compare' => 'IN',
							'value' => array( 'other', 'non-food' ),
						);
					} else {
						$args['meta_query'][] = array(
							'key' => 'wprm_type',
							'compare' => '=',
							'value' => $value,
						);
					}
				}
				break;
			case 'image':
				if ( 'all' !== $value ) {
					if ( 'yes' === $value ) {
						$args['meta_query'][] = array(
							'key' => '_thumbnail_id',
							'compare' => 'EXISTS'
						);
					} elseif ( 'no' === $value ) {
						$args['meta_query'][] = array(
							'key' => '_thumbnail_id',
							'compare' => 'NOT EXISTS'
						);
					}
				}
				break;
			case 'pin_image':
				if ( 'all' !== $value ) {
					if ( 'yes' === $value ) {
						$args['meta_query'][] = array(
							'key' => 'wprm_pin_image_id',
						'compare' => '>',
								'value' => '0',
						);
					} elseif ( 'no' === $value ) {
						$args['meta_query'][] = array(
							'relation' => 'OR',
							array(
								'key' => 'wprm_pin_image_id',
								'compare' => '=',
								'value' => '0',
							),
							array(
								'key' => 'wprm_pin_image_id',
								'compare' => 'NOT EXISTS'
							),
						);
					}
				}
				break;
			case 'pin_image_repin_id':
				$args['meta_query'][] = array(
					'key' => 'wprm_pin_image_repin_id',
					'compare' => 'LIKE',
					'value' => $value,
				);
				break;
			case 'video':
				if ( 'all' !== $value ) {
					if ( 'yes' === $value ) {
						$args['meta_query'][] = array(
							'relation' => 'OR',
							array(
								'key' => 'wprm_video_id',
								'compare' => '>',
								'value' => '0',
							),
							array(
								'key' => 'wprm_video_embed',
								'compare' => '!=',
								'value' => '',
							),
						);
					} elseif ( 'id' === $value ) {
						$args['meta_query'][] = array(
							'key' => 'wprm_video_id',
							'compare' => '>',
							'value' => '0',
						);
					} elseif ( 'embed' === $value ) {
						$args['meta_query'][] = array(
							'relation' => 'OR',
							array(
								'key' => 'wprm_video_id',
								'compare' => 'NOT EXISTS',
							),
							array(
								'key' => 'wprm_video_id',
								'compare' => '<=',
								'value' => '0',
							),
						);
						$args['meta_query'][] = array(
							'key' => 'wprm_video_embed',
							'compare' => '!=',
							'value' => '',
						);
					} elseif ( 'no' === $value ) {
						$args['meta_query'][] = array(
							'relation' => 'OR',
							array(
								'key' => 'wprm_video_id',
								'compare' => 'NOT EXISTS',
							),
							array(
								'key' => 'wprm_video_id',
								'compare' => '<=',
								'value' => '0',
							),
						);
						$args['meta_query'][] = array(
							'relation' => 'OR',
							array(
								'key' => 'wprm_video_embed',
								'compare' => 'NOT EXISTS',
							),
							array(
								'key' => 'wprm_video_embed',
								'compare' => '=',
								'value' => '',
							),
						);
					}
				}
				break;
			case 'author_display':
				if ( 'all' !== $value ) {
					$args['meta_query'][] = array(
						'key' => 'wprm_author_display',
						'compare' => '=',
						'value' => $value,
					);
				}
				break;
			case 'status':
				if ( 'all' !== $value ) {
					$args['post_status'] = $value;
				}
				break;
			case 'parent_post_id':
				if ( '' !== $value ) {
					$args['meta_query'][] = array(
						'key' => 'wprm_parent_post_id',
						'compare' => 'LIKE',
						'value' => $value,
					);
				}
				break;
			case 'parent_post':
				if ( 'all' !== $value ) {
					$compare = 'yes' === $value ? 'EXISTS' : 'NOT EXISTS';

					$args['meta_query'][] = array(
						'key' => 'wprm_parent_post_id',
						'compare' => $compare,
					);
				}
				break;
			case 'parent_post_language':
				if ( 'all' !== $value ) {
					// Not directly related to recipe, so needs an extra query.
					$multilingual = WPRM_Compatibility::multilingual();

					if ( $multilingual ) {
						if ( 'wpml' === $multilingual['plugin'] ) {
							do_action( 'wpml_switch_language', $value ); 

							// Get all possible parent post IDs.
							$parent_posts_query = new WP_Query( array(
								'post_type' => 'any',
								'posts_per_page' => -1,
								'post_status' => 'any',
								'suppress_filters' => false,
								'fields' => 'ids',
							) );
						
							$parent_posts_ids = $parent_posts_query->posts;

							$args['meta_query'][] = array(
								'key' => 'wprm_parent_post_id',
								'compare' => 'IN',
								'value' => $parent_posts_ids,
							);
						}

						// Polylang.
						if ( 'polylang' === $multilingual['plugin'] ) {
							// Get all possible parent post IDs.
							$parent_posts_query = new WP_Query( array(
								'post_type' => 'any',
								'posts_per_page' => -1,
								'post_status' => 'any',
								'lang' => $value,
								'fields' => 'ids',
							) );
						
							$parent_posts_ids = $parent_posts_query->posts;

							$args['meta_query'][] = array(
								'key' => 'wprm_parent_post_id',
								'compare' => 'IN',
								'value' => $parent_posts_ids,
							);
						}
					}
				}
				break;
			case 'rating':
				if ( 'all' !== $value ) {
					if ( 'none' === $value ) {
						$args['meta_query'][] = array(
							'key' => 'wprm_rating_average',
							'compare' => '=',
							'value' => '0',
						);
					} elseif ( 'any' === $value ) {
						$args['meta_query'][] = array(
							'key' => 'wprm_rating_average',
							'compare' => '!=',
							'value' => '0',
						);
					} else {
						$stars = intval( $value );

						$args['meta_query'][] = array(
							'key' => 'wprm_rating_average',
							'compare' => '>',
							'value' => $value - 1,
						);

						$args['meta_query'][] = array(
							'key' => 'wprm_rating_average',
							'compare' => '<=',
							'value' => $value,
						);
					}
				}
				break;
			case 'prep_time':
				if ( '' !== $value ) {
					$args['meta_query'][] = array(
						'key' => 'wprm_prep_time',
						'compare' => '=',
						'value' => self::parse_time( $value ),
					);
				}
				break;
			case 'cook_time':
				if ( '' !== $value ) {
					$args['meta_query'][] = array(
						'key' => 'wprm_cook_time',
						'compare' => '=',
						'value' => self::parse_time( $value ),
					);
				}
				break;
			case 'custom_time':
				if ( '' !== $value ) {
					$args['meta_query'][] = array(
						'key' => 'wprm_custom_time',
						'compare' => '=',
						'value' => self::parse_time( $value ),
					);
				}
				break;
			case 'total_time':
				if ( '' !== $value ) {
					$args['meta_query'][] = array(
						'key' => 'wprm_total_time',
						'compare' => '=',
						'value' => self::parse_time( $value ),
					);
				}
				break;
			case 'servings':
				if ( '' !== $value ) {
					$args['meta_query'][] = array(
						'key' => 'wprm_servings',
						'compare' => 'LIKE',
						'value' => $value,
					);
				}
				break;
			case 'equipment':
				if ( '' !== $value ) {
					$equipment_ids = get_terms(array(
						'taxonomy' => 'wprm_equipment',
						'name__like' => $value,
						'hide_empty' => false,
						'fields' => 'ids',
					));

					$args['tax_query'][] = array(
						'taxonomy' => 'wprm_equipment',
						'field' => 'term_id',
						'terms' => $equipment_ids,
						'operator' => 'IN',
					);
				}
				break;
			case 'ingredient':
				if ( '' !== $value ) {
					$ingredient_ids = get_terms(array(
						'taxonomy' => 'wprm_ingredient',
						'name__like' => $value,
						'hide_empty' => false,
						'fields' => 'ids',
					));

					$args['tax_query'][] = array(
						'taxonomy' => 'wprm_ingredient',
						'field' => 'term_id',
						'terms' => $ingredient_ids,
						'operator' => 'IN',
					);
				}
				break;
			case 'submission_author':
				if ( 'all' !== $value ) {
					$compare = 'yes' === $value ? 'EXISTS' : 'NOT EXISTS';

					$args['meta_query'][] = array(
						'key' => 'wprm_submission_user',
						'compare' => $compare,
					);
				}
				break;
			default:
				if ( 'custom_field_' === substr( $filter, 0, 13 ) ) {
					$custom_field_key = substr( $filter, 13 );

					if ( '' !== $value ) {
						$args['meta_query'][] = array(
							'key' => 'wprm_custom_field_' . $custom_field_key,
							'compare' => 'LIKE',
							'value' => $value,
						);
					}
					break;
				}

				// Assume it's a taxonomy if it doesn't match anything else.
				if ( 'all' !== $value ) {
					$taxonomy = 'wprm_' . substr( $filter, 4 ); // Starts with tag_

					if ( 'none' === $value ) {
						$args['tax_query'][] = array(
							'taxonomy' => $taxonomy,
							'operator' => 'NOT EXISTS',
						);
					} elseif ( 'any' === $value ) {
						$args['tax_query'][] = array(
							'taxonomy' => $taxonomy,
							'operator' => 'EXISTS',
						);
					} else {
						$args['tax_query'][] = array(
							'taxonomy' => $taxonomy,
							'field' => 'term_id',
							'terms' => intval( $value ),
						);
					}
				}
		}

		return $args;
	}

	/**
	 * Filter the where recipes query.
	 *
	 * @since    5.0.0
	 */
	public static function api_manage_recipes_query_where( $where, $wp_query ) {
		global $wpdb;

		$id_search = $wp_query->get( 'wprm_search_id' );
		if ( $id_search ) {
			$where .= ' AND ' . $wpdb->posts . '.ID LIKE \'%' . esc_sql( like_escape( $id_search ) ) . '%\'';
		}

		$date_search = $wp_query->get( 'wprm_search_date' );
		if ( $date_search ) {
			$where .= ' AND ' . $wpdb->posts . '.post_date LIKE \'%' . esc_sql( like_escape( $date_search ) ) . '%\'';
		}

		$title_search = $wp_query->get( 'wprm_search_title' );
		if ( $title_search ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( $title_search ) ) . '%\'';
		}

		$slug_search = $wp_query->get( 'wprm_search_slug' );
		if ( $slug_search ) {
			$where .= ' AND ' . $wpdb->posts . '.post_name LIKE \'%' . esc_sql( like_escape( $slug_search ) ) . '%\'';
		}

		$content_search = $wp_query->get( 'wprm_search_content' );
		if ( $content_search ) {
			$where .= ' AND ' . $wpdb->posts . '.post_content LIKE \'%' . esc_sql( like_escape( $content_search ) ) . '%\'';
		}

		return $where;
	}

	/**
	 * Parse the time filter.
	 *
	 * @since    5.0.0
	 */
	public static function parse_time( $time ) {
		// Assume a number is minutes.
		if ( '' . $time === '' . intval( $time ) ) {
			$time = "{$time} minutes";
		}

		$time .= ' ';

		// Common units.
		$time = str_ireplace( 'd ', 'day ', $time );
		$time = str_ireplace( 'hrs ', 'hours ', $time );
		$time = str_ireplace( 'hr ', 'hour ', $time );
		$time = str_ireplace( 'h ', 'hour ', $time );
		$time = str_ireplace( 'mins ', 'minutes ', $time );
		$time = str_ireplace( 'min ', 'minutes ', $time );
		$time = str_ireplace( 'm ', 'minutes ', $time );

		$now = time();
		$time = strtotime( $time, $now );

		return ( $time - $now ) / 60;
	}

	/**
	 * Handle recipe bulk edit call to the REST API.
	 *
	 * @since    5.0.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_recipes_bulk_edit( $request ) {
		// Parameters.
		$params = $request->get_params();

		$ids = isset( $params['ids'] ) ? array_map( 'intval', $params['ids'] ) : array();
		$action = isset( $params['action'] ) ? $params['action'] : false;

		if ( $ids && $action && $action['type'] ) {
			// Do once.
			if ( 'export' === $action['type'] ) {
				if ( WPRM_Addons::is_active( 'premium' ) ) {
					return WPRMP_Export_JSON::bulk_edit_export( $ids, $action['options'] );
				} else {
					return false;
				}
			} elseif ( 'print' === $action['type'] ) {
				$url = WPRM_Print::bulk_print_url( $ids );

				if ( $url ) {
					return array(
						'result' => __( 'Follow this link to print all of the selected recipes:', 'wp-recipe-maker' ) . '<br/><a href="' . esc_url( $url ) . '" target="_blank">' . $url . '</a>',
					);
				} else {
					return false;
				}
			}

			// Do per post.
			$args = array(
				'post_type' => WPRM_POST_TYPE,
				'post_status' => 'any',
				'nopaging' => true,
				'post__in' => $ids,
				'ignore_sticky_posts' => true,
			);

			$query = new WP_Query( $args );
			$posts = $query->posts;
			foreach ( $posts as $post ) {
				$recipe = WPRM_Recipe_Manager::get_recipe( $post->ID );
				$recipe_data = $recipe->get_data();

				switch ( $action['type'] ) {
					case 'remove-terms':
						$remove_terms = array_map( function( $option ) {
							$term_id = intval( $option['term_id'] );

							if ( 0 === $term_id ) {
								$term = get_term_by( 'name', $option['term_id'], 'eafl_category' );

								if ( $term && ! is_wp_error( $term ) ) {
									$term_id = $term->term_id;
								}
							}

							return $term_id;
						}, $action['options']['terms'] );

						$new_terms = array_filter( $recipe_data['tags'][ $action['options']['taxonomy'] ], function( $term ) use ( $remove_terms ) {
							return ! in_array( $term->term_id, $remove_terms );
						});

						if ( count( $new_terms ) !== count( $recipe_data['tags'][ $action['options']['taxonomy'] ] ) ) {
							$recipe_data['tags'][ $action['options']['taxonomy'] ] = $new_terms;
						} else {
							$recipe_data = false;
						}
						break;
					case 'add-terms':
						$recipe_data['tags'][ $action['options']['taxonomy'] ] = array_merge( $recipe_data['tags'][ $action['options']['taxonomy'] ], $action['options']['terms'] );
						break;
					case 'change-status':
						$recipe_data['post_status'] = $action['options'];
						break;
					case 'change-password':
						$recipe_data['post_password'] = $action['options'];
						break;
					case 'change-language':
						$recipe_data['language'] = $action['options'];
						break;
					case 'change-type':
						$recipe_data['type'] = $action['options'];
						break;
					case 'change-post-author':
						$author_id = intval( $action['options'] );

						if ( $author_id ) {
							$recipe_data['post_author'] = $author_id;
						}
						break;
					case 'change-author':
						$recipe_data['author_display'] = $action['options']['author'];
						$recipe_data['author_name'] = $action['options']['author_name'];
						$recipe_data['author_link'] = $action['options']['author_link'];
						break;
					case 'change-servings':
						$recipe_data['servings'] = $action['options']['servings'];
						$recipe_data['servings_unit'] = $action['options']['servings_unit'];
						break;
					case 'recalculate-time':
						$recipe_data['total_time'] = intval( $recipe_data['prep_time'] ) + intval( $recipe_data['cook_time'] ) + intval( $recipe_data['custom_time'] );
						break;
					case 'user-rating':
						$recipe_data = false;
						$rating = intval( $action['options'] );

						if ( 0 === $rating ) {
							$user_rating = WPRM_Rating_Database::get_rating( array(
								'where' => 'ip = "wprm-author-rating" AND recipe_id = "' . intval( $recipe->id() ) . '"',
							) );

							if ( $user_rating ) {
								WPRM_Rating_Database::delete_ratings( array( $user_rating->id ) );
							}
						} else {
							$user_rating = array(
								'recipe_id' => $recipe->id(),
								'user_id' => 0,
								'ip' => 'wprm-author-rating',
								'rating' => $rating,
							);
				
							WPRM_Rating_Database::add_or_update_rating( $user_rating );
						}
						break;
					case 'custom-nutrition-ingredient':
						$recipe_data = false;

						if ( class_exists( 'WPRMPN_Ingredient_Manager' ) ) {
							$amount = 1;
							$unit = '';
							$name = $recipe->name();
							$nutrients = array();

							$recipe_nutrition = $recipe->nutrition();
							$nutrition_fields = WPRM_Nutrition::get_fields();
							unset( $nutrition_fields['serving_size'] );

							foreach ( $nutrition_fields as $nutrient => $options ) {
								if ( ! isset( $options['type'] ) || 'calculated' !== $options['type'] ) {
									if ( isset( $recipe_nutrition[ $nutrient ] ) ) {
										$nutrients[ $nutrient ] = $recipe_nutrition[ $nutrient ];
									}
								}
							}

							WPRMPN_Ingredient_Manager::save_ingredient( 0, $amount, $unit, $name, $nutrients );
						}
						break;
					case 'switch-unit-system':
						$new_ingredients = array();
						foreach ( $recipe->ingredients() as $ingredient_group ) {
							$new_ingredient_group = $ingredient_group;
							$new_ingredient_group['ingredients'] = array();
			
							foreach ( $ingredient_group['ingredients'] as $ingredient ) {
								// Make sure converted exists
								if ( ! isset( $ingredient['converted'] ) ) {
									$ingredient['converted'] = array();
								}
								if ( ! isset( $ingredient['converted'][2] ) ) {
									$ingredient['converted'][2] = array(
										'amount' => '',
										'unit' => '',
									);
								}

								// Store in temp variables.
								$original_amount = $ingredient['amount'];
								$original_unit = $ingredient['unit'];
								$converted_amount = $ingredient['converted'][2]['amount'];
								$converted_unit = $ingredient['converted'][2]['unit'];

								// Switch around.
								$ingredient['amount'] = $converted_amount;
								$ingredient['unit'] = $converted_unit;
								$ingredient['converted'][2]['amount'] = $original_amount;
								$ingredient['converted'][2]['unit'] = $original_unit;

								$new_ingredient_group['ingredients'][] = $ingredient;
							}
			
							$new_ingredients[] = $new_ingredient_group;
						}

						// Store as new ingredients and make sure outdated ingredients_flat is ignored.
						$recipe_data['ingredients'] = $new_ingredients;
						unset( $recipe_data['ingredients_flat'] );
						break;
					case 'change-unit-system':
						$recipe_data['unit_system'] = $action['options'];
						break;
					case 'delete':
						$recipe_data = false;
						wp_trash_post( $recipe->id() );
						break;
				}

				if ( $recipe_data ) {
					$recipe_data = WPRM_Recipe_Sanitizer::sanitize( $recipe_data );
					WPRM_Recipe_Saver::update_recipe( $recipe->id(), $recipe_data );
				}
			}

			return true;
		}

		return false;
	}
}

WPRM_Api_Manage_Recipes::init();
