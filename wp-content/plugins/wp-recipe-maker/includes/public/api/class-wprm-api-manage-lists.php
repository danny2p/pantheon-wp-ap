<?php
/**
 * API for managing the lists.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 */

/**
 * API for managing the lists.
 *
 * @since      9.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Manage_Lists {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.0.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    9.0.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) {
			register_rest_route( 'wp-recipe-maker/v1', '/manage/list', array(
				'callback' => array( __CLASS__, 'api_manage_lists' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			) );
		}
	}

	/**
	 * Validate ID in API call.
	 *
	 * @since    9.0.0
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
	 * @since    9.0.0
	 */
	public static function api_required_permissions() {
		return current_user_can( WPRM_Settings::get( 'features_manage_access' ) );
	}

	/**
	 * Handle manage lists call to the REST API.
	 *
	 * @since    9.0.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_lists( $request ) {
		// Parameters.
		$params = $request->get_params();

		$page = isset( $params['page'] ) ? intval( $params['page'] ) : 0;
		$page_size = isset( $params['pageSize'] ) ? intval( $params['pageSize'] ) : 25;
		$sorted = isset( $params['sorted'] ) ? $params['sorted'] : array( array( 'id' => 'id', 'desc' => true ) );
		$filtered = isset( $params['filtered'] ) ? $params['filtered'] : array();

		// Starting query args.
		$args = array(
			'post_type' => WPRM_LIST_POST_TYPE,
			'post_status' => array( 'publish', 'future', 'pending', 'draft', 'private' ),
			'posts_per_page' => $page_size,
			'offset' => $page * $page_size,
			'meta_query' => array(
				'relation' => 'AND',
			),
			'tax_query' => array(),
			'lang' => '',
		);

		// Order.
		$args['order'] = $sorted[0]['desc'] ? 'DESC' : 'ASC';
		switch( $sorted[0]['id'] ) {
			case 'date':
				$args['orderby'] = 'date';
				break;
			case 'name':
				$args['orderby'] = 'title';
				break;
			case 'nbr_items':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_nbr_items';
				break;
			case 'nbr_items_internal':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_nbr_items_internal';
				break;
			case 'nbr_items_external':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_nbr_items_external';
				break;
			case 'metadata_name':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wprm_metadata_name';
				break;
			case 'metadata_description':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'wprm_metadata_description';
				break;
			case 'parent_post_id':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'wprm_parent_post_id';
				break;
			default:
				// Default to order by ID.
			 	$args['orderby'] = 'ID';
		}

		// Filter.
		if ( $filtered ) {
			foreach ( $filtered as $filter ) {
				$args = self::update_args_for_filter( $args, $filter['id'], $filter['value'] );
			}
		}

		add_filter( 'posts_where', array( __CLASS__, 'api_manage_lists_query_where' ), 10, 2 );
		$query = new WP_Query( $args );
		remove_filter( 'posts_where', array( __CLASS__, 'api_manage_lists_query_where' ), 10, 2 );

		$lists = array();
		$posts = $query->posts;

		foreach ( $posts as $post ) {
			$list = WPRM_List_Manager::get_list( $post );

			if ( ! $list ) {
				continue;
			}

			$lists[] = $list->get_data_manage();
		}

		// Got total number of lists.
		$total = (array) wp_count_posts( WPRM_LIST_POST_TYPE );
		unset( $total['trash'] );

		// Totals.
		$filtered_lists = intval( $query->found_posts );

		return array(
			'rows' => array_values( $lists ),
			'total' => array_sum( $total ),
			'filtered' => $filtered_lists,
			'pages' => ceil( $filtered_lists / $page_size ),
		);
	}

	/**
	 * Filter the where lists query.
	 *
	 * @since    9.0.0
	 */
	public static function update_args_for_filter( $args, $filter, $value ) {
		switch( $filter ) {
			case 'id':
				$args['wprm_search_id'] = $value;
				break;
			case 'date':
				$args['wprm_search_date'] = $value;
				break;
			case 'name':
				$args['wprm_search_title'] = $value;
				break;
			case 'note':
				$args['wprm_search_content'] = $value;
				break;
			case 'metadata_name':
				if ( '' !== $value ) {
					$args['meta_query'][] = array(
						'key' => 'wprm_metadata_name',
						'compare' => 'LIKE',
						'value' => $value,
					);
				}
				break;
			case 'metadata_description':
				if ( '' !== $value ) {
					$args['meta_query'][] = array(
						'key' => 'wprm_metadata_description',
						'compare' => 'LIKE',
						'value' => $value,
					);
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
		}

		return $args;
	}

	/**
	 * Filter the where lists query.
	 *
	 * @since    9.0.0
	 */
	public static function api_manage_lists_query_where( $where, $wp_query ) {
		global $wpdb;

		$id_search = $wp_query->get( 'wprm_search_id' );
		if ( $id_search ) {
			$where .= ' AND ' . $wpdb->posts . '.ID LIKE \'%' . esc_sql( like_escape( $id_search ) ) . '%\'';
		}

		$date_search = $wp_query->get( 'wprm_search_date' );
		if ( $date_search ) {
			$where .= ' AND DATE_FORMAT(' . $wpdb->posts . '.post_date, \'%Y-%m-%d %T\') LIKE \'%' . esc_sql( like_escape( $date_search ) ) . '%\'';
		}

		$title_search = $wp_query->get( 'wprm_search_title' );
		if ( $title_search ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( $title_search ) ) . '%\'';
		}

		$content_search = $wp_query->get( 'wprm_search_content' );
		if ( $content_search ) {
			$where .= ' AND ' . $wpdb->posts . '.post_content LIKE \'%' . esc_sql( like_escape( $content_search ) ) . '%\'';
		}

		return $where;
	}
}

WPRM_Api_Manage_Lists::init();
