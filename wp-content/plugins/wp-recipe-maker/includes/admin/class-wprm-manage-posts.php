<?php
/**
 * Handle the posts manage page.
 *
 * @link      	https://bootstrapped.ventures
 * @since		9.8.0
 *
 * @package    	WP_Recipe_Maker
 * @subpackage 	WP_Recipe_Maker/includes/admin/modal
 */

/**
 * Handle the posts manage page.
 *
 * @since      	9.8.0
 * @package    	WP_Recipe_Maker
 * @subpackage 	WP_Recipe_Maker/includes/admin/modal
 * @author     	Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Manage_Posts {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.8.0
	 */
	public static function init() {
		add_action( 'restrict_manage_posts', array( __CLASS__, 'add_has_recipe_filter' ) );

		add_filter( 'posts_results', array( __CLASS__, 'alter_posts_results' ), 10, 2 );
	}

	/**
	 * Add the recipe filter to the posts manage page.
	 *
	 * @since	9.8.0
	 */
	public static function add_has_recipe_filter( $post_type ) {
		if( 'post' !== $post_type ){
			return;
		}

		$select_id = 'wprm_has_recipe';
		$selected = isset( $_GET[ $select_id ] ) && '-' !== substr( $_GET[ $select_id ], 0, 1 ) ? $_GET[ $select_id ] : '';

		// Options.
		$options = array(
			'0' => __( 'All recipe types', 'wp-recipe-maker' ),
			'no' => __( 'Does not have a recipe', 'wp-recipe-maker' ),
			'any' => __( 'Has any recipe', 'wp-recipe-maker' ),
			'-types' => '----------------',
			'food' => __( 'Has a food recipe', 'wp-recipe-maker' ),
			'howto' => __( 'Has a how-to instruction', 'wp-recipe-maker' ),
			'other' => __( 'Has a recipe of the "other" type', 'wp-recipe-maker' ),
			'-special' => '----------------',
			'more' => __( 'Has more than 1 recipe', 'wp-recipe-maker' ),
			'roundup' => __( 'Has roundup recipe items', 'wp-recipe-maker' ),
		);

		//build a custom dropdown list of values to filter by
		echo '<select id="' . esc_attr( $select_id ) . '" name="' . esc_attr( $select_id ) . '">';

		foreach( $options as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $selected, $key, false ) . '>' . esc_html( $value ) . '</option>';
		}

		echo '</select>';
	}

	/**
	 * Alter the posts result.
	 *
	 * @since	9.8.0
	 */
	public static function alter_posts_results( $posts, $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { 
			return $posts;
		}
		if ( 'post' !== $query->query['post_type'] || ! isset( $_REQUEST['wprm_has_recipe'] ) ) {
			return $posts;
		}

		$recipe_filter = $_REQUEST['wprm_has_recipe'];
		unset( $_REQUEST['wprm_has_recipe'] ); // Prevent infinite loop later on.

		if ( '-' === substr( $recipe_filter, 0, 1 ) || 0 === $recipe_filter || '0' === $recipe_filter ){
			return $posts;
		}

		// Filter posts.
		$posts = self::filter_posts( $posts, $recipe_filter );

		// Check maximum number of posts in query.
		$posts_per_page = $query->get( 'posts_per_page' );

		// Want to append more posts to the query.
		while ( count( $posts ) < $posts_per_page ) {
			$current_page = $query->get( 'paged' );
			$query->set( 'paged', $current_page + 1 );

			$more_posts = $query->get_posts();
			
			// No more posts left? Get out of loop.
			if ( empty( $more_posts ) ) {
				break;
			}

			// Filter the loaded posts and append.
			$more_posts = self::filter_posts( $more_posts, $recipe_filter );
			$posts = array_merge( $posts, $more_posts );
		}
		
		return $posts;
	}

	/**
	 * Alter a list of posts based on a filter.
	 *
	 * @since	9.8.0
	 */
	public static function filter_posts( $posts, $filter ) {
		foreach ( $posts as $key => $post ) {
			$keep_post = false;

			// Check the filter.
			if ( 'roundup' === $filter ) {
				$roundup_items = WPRM_Recipe_Roundup::get_items_from_content( $post->post_content );
				$lists = WPRM_List_Manager::get_list_ids_from_content( $post->post_content );

				$keep_post = ! empty( $roundup_items ) || ! empty( $lists );
			} else {
				$recipe_ids = WPRM_Recipe_Manager::get_recipe_ids_from_content( $post->post_content );
				
				switch ( $filter ) {
					case 'no':
						$keep_post = empty( $recipe_ids );
						break;
					case 'any':
						$keep_post = ! empty( $recipe_ids );
						break;
					case 'food':
					case 'howto':
					case 'other':
						foreach ( $recipe_ids as $recipe_id ) {
							$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );
							if ( $recipe && $filter === $recipe->type() ) {
								$keep_post = true;
								break;
							}
						}
						break;
					case 'more':
						$keep_post = count( $recipe_ids ) > 1;
						break;
				}
			}

			// Remove post if it doesn't match the filter.
			if ( ! $keep_post ) {
				unset( $posts[ $key ] );
			}
		}

		return $posts;
	}
}

WPRM_Manage_Posts::init();
