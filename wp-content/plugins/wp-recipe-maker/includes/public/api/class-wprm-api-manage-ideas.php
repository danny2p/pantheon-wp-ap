<?php
/**
 * API for managing ideas.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 */

/**
 * API for managing ideas.
 *
 * @since      10.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Manage_Ideas {

	/**
	 * Register actions and filters.
	 *
	 * @since    10.4.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    10.4.0
	 */
	public static function api_register_data() {
		register_rest_route(
			'wp-recipe-maker/v1',
			'/manage/idea',
			array(
				'callback'            => array( __CLASS__, 'api_manage_ideas' ),
				'methods'             => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			)
		);
		register_rest_route(
			'wp-recipe-maker/v1',
			'/manage/idea/bulk',
			array(
				'callback'            => array( __CLASS__, 'api_manage_ideas_bulk_edit' ),
				'methods'             => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			)
		);
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since    10.4.0
	 */
	public static function api_required_permissions() {
		return current_user_can( WPRM_Settings::get( 'features_manage_access' ) );
	}

	/**
	 * Handle manage ideas call to the REST API.
	 *
	 * @since    10.4.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_ideas( $request ) {
		$params = $request->get_params();

		$page = isset( $params['page'] ) ? intval( $params['page'] ) : 0;
		$page_size = isset( $params['pageSize'] ) ? intval( $params['pageSize'] ) : 25;
		$sorted = isset( $params['sorted'] ) ? $params['sorted'] : array( array( 'id' => 'last_updated', 'desc' => true ) );
		$filtered = isset( $params['filtered'] ) ? $params['filtered'] : array();

		$args = array(
			'post_type'      => WPRM_IDEA_POST_TYPE,
			'post_status'    => array( 'publish', 'future', 'pending', 'draft', 'private' ),
			'posts_per_page' => $page_size,
			'offset'         => $page * $page_size,
			'meta_query'     => array(
				'relation' => 'AND',
			),
			'lang'           => '',
		);

		$args['order'] = $sorted[0]['desc'] ? 'DESC' : 'ASC';
		switch ( $sorted[0]['id'] ) {
			case 'name':
				$args['orderby'] = 'title';
				break;
			case 'date':
				$args['orderby'] = 'date';
				break;
			case 'last_updated':
				$args['orderby'] = 'modified';
				break;
			case 'type':
			case 'status':
			case 'source':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wprm_' . $sorted[0]['id'];
				break;
			default:
				$args['orderby'] = 'modified';
		}

		if ( $filtered ) {
			foreach ( $filtered as $filter ) {
				$args = self::update_args_for_filter( $args, $filter['id'], $filter['value'] );
			}
		}

		add_filter( 'posts_where', array( __CLASS__, 'api_manage_ideas_query_where' ), 10, 2 );
		$query = new WP_Query( $args );
		remove_filter( 'posts_where', array( __CLASS__, 'api_manage_ideas_query_where' ), 10, 2 );

		$ideas = array();

		foreach ( $query->posts as $post ) {
			$idea = WPRM_Idea_Manager::get_idea( $post );

			if ( ! $idea ) {
				continue;
			}

			$ideas[] = $idea->get_data_manage();
		}

		$total = (array) wp_count_posts( WPRM_IDEA_POST_TYPE );
		unset( $total['trash'] );

		$filtered_ideas = intval( $query->found_posts );

		return rest_ensure_response(
			array(
				'rows'     => array_values( $ideas ),
				'total'    => array_sum( $total ),
				'filtered' => $filtered_ideas,
				'pages'    => ceil( $filtered_ideas / $page_size ),
			)
		);
	}

	/**
	 * Update query args for a filter.
	 *
	 * @since    10.4.0
	 * @param    array  $args Query args.
	 * @param    string $filter Filter key.
	 * @param    mixed  $value Filter value.
	 */
	public static function update_args_for_filter( $args, $filter, $value ) {
		switch ( $filter ) {
			case 'id':
				$args['wprm_search_id'] = $value;
				break;
			case 'date':
				$args['wprm_search_date'] = $value;
				break;
			case 'last_updated':
				$args['wprm_search_last_updated'] = $value;
				break;
			case 'name':
				$args['wprm_search_title'] = $value;
				break;
			case 'summary':
				$args['wprm_search_summary'] = $value;
				break;
			case 'notes':
				$args['wprm_search_content'] = $value;
				break;
			case 'type':
				if ( 'all' !== $value ) {
					$args['meta_query'][] = array(
						'key'     => 'wprm_type',
						'compare' => '=',
						'value'   => $value,
					);
				}
				break;
			case 'status':
				if ( 'all' !== $value ) {
					if ( 'not-started' === $value ) {
						$args['meta_query'][] = array(
							'key'     => 'wprm_status',
							'compare' => 'IN',
							'value'   => array( 'idea', 'planned' ),
						);
					} else {
						$args['meta_query'][] = array(
							'key'     => 'wprm_status',
							'compare' => '=',
							'value'   => $value,
						);
					}
				}
				break;
			case 'source':
				if ( 'all' !== $value ) {
					$args['meta_query'][] = array(
						'key'     => 'wprm_' . $filter,
						'compare' => '=',
						'value'   => $value,
					);
				}
				break;
		}

		return $args;
	}

	/**
	 * Filter the where ideas query.
	 *
	 * @since    10.4.0
	 * @param    string   $where Current where SQL.
	 * @param    WP_Query $wp_query Current query object.
	 */
	public static function api_manage_ideas_query_where( $where, $wp_query ) {
		global $wpdb;

		$id_search = $wp_query->get( 'wprm_search_id' );
		if ( $id_search ) {
			$where .= ' AND ' . $wpdb->posts . '.ID LIKE \'%' . esc_sql( $wpdb->esc_like( $id_search ) ) . '%\'';
		}

		$date_search = $wp_query->get( 'wprm_search_date' );
		if ( $date_search ) {
			$where .= ' AND DATE_FORMAT(' . $wpdb->posts . '.post_date, \'%Y-%m-%d %T\') LIKE \'%' . esc_sql( $wpdb->esc_like( $date_search ) ) . '%\'';
		}

		$last_updated_search = $wp_query->get( 'wprm_search_last_updated' );
		if ( $last_updated_search ) {
			$where .= ' AND DATE_FORMAT(' . $wpdb->posts . '.post_modified, \'%Y-%m-%d %T\') LIKE \'%' . esc_sql( $wpdb->esc_like( $last_updated_search ) ) . '%\'';
		}

		$title_search = $wp_query->get( 'wprm_search_title' );
		if ( $title_search ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $title_search ) ) . '%\'';
		}

		$summary_search = $wp_query->get( 'wprm_search_summary' );
		if ( $summary_search ) {
			$where .= ' AND ' . $wpdb->posts . '.post_excerpt LIKE \'%' . esc_sql( $wpdb->esc_like( $summary_search ) ) . '%\'';
		}

		$content_search = $wp_query->get( 'wprm_search_content' );
		if ( $content_search ) {
			$where .= ' AND ' . $wpdb->posts . '.post_content LIKE \'%' . esc_sql( $wpdb->esc_like( $content_search ) ) . '%\'';
		}

		return $where;
	}

	/**
	 * Handle ideas bulk edit call to the REST API.
	 *
	 * @since    10.4.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_ideas_bulk_edit( $request ) {
		$params = $request->get_params();

		$ids = isset( $params['ids'] ) ? array_map( 'intval', $params['ids'] ) : array();
		$action = isset( $params['action'] ) ? $params['action'] : false;

		if ( $ids && $action && isset( $action['type'] ) ) {
			foreach ( $ids as $id ) {
				$post = get_post( $id );

				if ( ! $post || WPRM_IDEA_POST_TYPE !== $post->post_type ) {
					continue;
				}

				switch ( $action['type'] ) {
					case 'change-status':
						if ( current_user_can( 'edit_post', $id ) ) {
							update_post_meta( $id, 'wprm_status', sanitize_text_field( $action['options'] ) );
							WPRM_Idea_Manager::invalidate_idea( $id );
							wp_update_post( array( 'ID' => $id ) );
						}
						break;
					case 'delete':
						if ( current_user_can( 'delete_post', $id ) ) {
							wp_delete_post( $id, true );
						}
						break;
				}
			}

			return rest_ensure_response( true );
		}

		return rest_ensure_response( false );
	}
}

WPRM_Api_Manage_Ideas::init();
