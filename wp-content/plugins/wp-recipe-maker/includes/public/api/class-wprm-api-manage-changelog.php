<?php
/**
 * API for managing the changelog.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 */

/**
 * API for managing the changelog.
 *
 * @since      9.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/api
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Api_Manage_Changelog {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.2.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'api_register_data' ) );
	}

	/**
	 * Register data for the REST API.
	 *
	 * @since    9.2.0
	 */
	public static function api_register_data() {
		if ( function_exists( 'register_rest_field' ) ) {
			register_rest_route( 'wp-recipe-maker/v1', '/manage/changelog', array(
				'callback' => array( __CLASS__, 'api_manage_changelog' ),
				'methods' => 'POST',
				'permission_callback' => array( __CLASS__, 'api_required_permissions' ),
			) );
		}
	}

	/**
	 * Required permissions for the API.
	 *
	 * @since    9.2.0
	 */
	public static function api_required_permissions() {
		return current_user_can( WPRM_Settings::get( 'features_manage_access' ) );
	}

	/**
	 * Handle manage taxonomies call to the REST API.
	 *
	 * @since    9.2.0
	 * @param    WP_REST_Request $request Current request.
	 */
	public static function api_manage_changelog( $request ) {
		// Parameters.
		$params = $request->get_params();

		$page = isset( $params['page'] ) ? intval( $params['page'] ) : 0;
		$page_size = isset( $params['pageSize'] ) ? intval( $params['pageSize'] ) : 25;
		$sorted = isset( $params['sorted'] ) ? $params['sorted'] : array( array( 'id' => 'id', 'desc' => true ) );
		$filtered = isset( $params['filtered'] ) ? $params['filtered'] : array();

		// Starting query args.
		$args = array(
			'limit' => $page_size,
			'offset' => $page * $page_size,
			'filter' => array(),
		);

		// Order.
		$args['order'] = $sorted[0]['desc'] ? 'DESC' : 'ASC';
		$args['orderby'] = $sorted[0]['id'];

		// Filter.
		if ( $filtered ) {
			foreach ( $filtered as $filter ) {
				$value = $filter['value'];
				switch( $filter['id'] ) {
					case 'created_at':
						$args['filter'][] = 'DATE_FORMAT(created_at, "%Y-%m-%d %T") LIKE "%' . esc_sql( like_escape( esc_attr( $value ) ) ) . '%"';
						break;
					case 'type':
						$args['filter'][] = 'type LIKE "%' . esc_sql( like_escape( $value ) ) . '%"';
						break;
					case 'object_id':
						$args['filter'][] = 'object_id LIKE "%' . esc_sql( like_escape( intval( $value ) ) ). '%"';
						break;
					case 'user_id':
						$args['filter'][] = 'user_id LIKE "%' . esc_sql( like_escape( intval( $value ) ) ) . '%"';
						break;
				}
			}

			if ( $args['filter'] ) {
				$args['where'] = implode( ' AND ', $args['filter'] );
			}
		}
		
		$query = WPRM_Changelog_Database::get( $args );

		$total = $query['total'] ? $query['total'] : 0;
		$rows = $query['rows'] ? array_values( $query['rows'] ) : array();

		// Add extra infromation for the manage page.
		foreach ( $rows as $row ) {
			// Add user data.
			if ( 0 < $row->user_id ) {
				$user = get_userdata( $row->user_id );

				if ( $user ) {
					$row->user = $user->display_name;
					$row->user_link = get_edit_user_link( $row->user_id );
				}
			}

			// Add recipe data.
			if ( $row->object_id ) {
				$recipe = WPRM_Recipe_Manager::get_recipe( $row->object_id );
				if ( $recipe ) {
					$row->recipe = $recipe->name();
				}
			}
		}

		$data = array(
			'rows' => $rows,
			'total' => WPRM_Changelog_Database::count(),
			'filtered' => $total,
			'pages' => ceil( $total / $page_size ),
		);

		return rest_ensure_response( $data );
	}
}

WPRM_Api_Manage_Changelog::init();
