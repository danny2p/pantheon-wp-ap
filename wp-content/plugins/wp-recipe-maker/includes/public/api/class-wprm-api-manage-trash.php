<?php
/**
 * API for managing the trash.
 *
 * @link       https://bootstrapped.ventures
 * @since      5.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 */

/**
 * API for managing the trash.
 *
 * @since      5.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Manage_Trash {

	/**
	 * Register actions and filters.
	 *
	 * @since    5.2.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    5.2.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) {
			register_rest_route( 'wp-recipe-maker/v1', '/manage/trash', array(
				'callback' => array( __CLASS__, 'api_manage_trash' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			) );
			register_rest_route( 'wp-recipe-maker/v1', '/manage/trash/bulk', array(
				'callback' => array( __CLASS__, 'api_manage_trash_bulk_edit' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			) );
		}
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since    5.2.0
	 */
	public static function api_required_permissions() {
		return current_user_can( WPRM_Settings::get( 'features_manage_access' ) );
	}

	/**
	 * Handle manage trash call to the REST API.
	 *
	 * @since    5.2.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_trash( $request ) {
		// Parameters.
		$params = $request->get_params();

		$page = isset( $params['page'] ) ? intval( $params['page'] ) : 0;
		$page_size = isset( $params['pageSize'] ) ? intval( $params['pageSize'] ) : 25;
		$sorted = isset( $params['sorted'] ) ? $params['sorted'] : array( array( 'id' => 'id', 'desc' => true ) );
		$filtered = isset( $params['filtered'] ) ? $params['filtered'] : array();

		// Starting query args.
		$args = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => 'trash',
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
			case 'name':
				$args['orderby'] = 'title';
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
					case 'date':
						$args['wprm_search_date'] = $value;
						break;
					case 'name':
						$args['wprm_search_title'] = $value;
						break;
				}
			}
		}

		add_filter( 'posts_where', array( __CLASS__, 'api_manage_trash_query_where' ), 10, 2 );
		$query = new WP_Query( $args );
		remove_filter( 'posts_where', array( __CLASS__, 'api_manage_trash_query_where' ), 10, 2 );

		$recipes = array();
		$posts = $query->posts;
		foreach ( $posts as $post ) {
			$recipe = WPRM_Recipe_Manager::get_recipe( $post );

			if ( ! $recipe ) {
				continue;
			}

			$recipes[] = $recipe->get_data_manage();
		}

		// Got total number of recipes.
		$total = (array) wp_count_posts( WPRM_POST_TYPE );

		return array(
			'rows' => array_values( $recipes ),
			'total' => intval( $total->trash ),
			'filtered' => intval( $query->found_posts ),
			'pages' => ceil( $query->found_posts / $page_size ),
		);
	}

	/**
	 * Filter the where trash query.
	 *
	 * @since    5.2.0
	 */
	public static function api_manage_trash_query_where( $where, $wp_query ) {
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

		return $where;
	}

	/**
	 * Handle trash bulk edit call to the REST API.
	 *
	 * @since    6.7.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_trash_bulk_edit( $request ) {
		// Parameters.
		$params = $request->get_params();

		$ids = isset( $params['ids'] ) ? array_map( 'intval', $params['ids'] ) : array();
		$action = isset( $params['action'] ) ? $params['action'] : false;

		if ( $ids && $action && $action['type'] ) {
			foreach ( $ids as $id ) {
				switch ( $action['type'] ) {
					case 'delete':
						$post = get_post( $id );

						if ( WPRM_POST_TYPE === $post->post_type && current_user_can( 'delete_post', $id ) ) {
							wp_delete_post( $id, true );
						}
						break;
				}
			}

			return true;
		}

		return false;
	}
}

WPRM_Api_Manage_Trash::init();
