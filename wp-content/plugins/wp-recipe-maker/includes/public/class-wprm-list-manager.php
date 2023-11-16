<?php
/**
 * Responsible for returning lists.
 *
 * @link       http://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for returning lists.
 *
 * @since      9.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_List_Manager {

	/**
	 * Lists that have already been requested for easy subsequent access.
	 *
	 * @since    9.0.0
	 * @access   private
	 * @var      array    $lists    Array containing lists that have already been requested for easy access.
	 */
	private static $lists = array();

	/**
	 * Array of posts with the lists in them.
	 *
	 * @since    9.0.0
	 * @access   private
	 * @var      array	$posts    Array containing posts with lists in them.
	 */
	private static $posts = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    9.0.0
	 */
	public static function init() {
		add_action( 'wp_ajax_wprm_get_list', array( __CLASS__, 'ajax_get_list' ) );
		add_action( 'wp_ajax_wprm_search_lists', array( __CLASS__, 'ajax_search_lists' ) );
	}

	/**
	 * Get the x latest lists.
	 *
	 * @since	9.0.0
	 * @param	int $limit Number of lists to get, defaults to 10.
	 * @param	mixed $display How to display the lists.
	 */
	public static function get_latest_lists( $limit = 10, $display = 'name' ) {
		$lists = array();

		$args = array(
				'post_type' => WPRM_LIST_POST_TYPE,
				'post_status' => 'any',
				'orderby' => 'date',
				'order' => 'DESC',
				'posts_per_page' => $limit,
				'offset' => 0,
				'suppress_filters' => true,
				'lang' => '',
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			$posts = $query->posts;

			foreach ( $posts as $post ) {
				// Special case.
				if ( 'manage' === $display ) {
					$list = self::get_list( $post );

					if ( $list ) {
						$lists[] = $list->get_data_manage();
					}

					continue;
				}

				switch ( $display ) {
					case 'id':
						$text = $post->ID . ' ' . $post->post_title;
						break;
					default:
						$text = $post->post_title;
				}

				$lists[] = array(
					'id' =>  $post->ID,
					'text' => $text,
				);
			}
		}

		return $lists;
	}

	/**
	 * Search for lists by keyword.
	 *
	 * @since    9.0.0
	 */
	public static function ajax_search_lists() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // Input var okay.

			$lists = array();
			$lists_with_id = array();

			$args = array(
				'post_type' => WPRM_LIST_POST_TYPE,
				'post_status' => 'any',
				'posts_per_page' => 100,
				's' => $search,
				'suppress_filters' => true,
				'lang' => '',
			);

			$query = new WP_Query( $args );

			$posts = $query->posts;

			// If searching for number, include exact result first.
			if ( is_numeric( $search ) ) {
				$id = abs( intval( $search ) );

				if ( $id > 0 ) {
					$args = array(
						'post_type' => WPRM_LIST_POST_TYPE,
						'post_status' => 'any',
						'posts_per_page' => 100,
						'post__in' => array( $id ),
					);
	
					$query = new WP_Query( $args );
	
					$posts = array_merge( $query->posts, $posts );
				}
			}

			foreach ( $posts as $post ) {
				$lists[] = array(
					'id' => $post->ID,
					'text' => $post->post_title,
				);

				$lists_with_id[] = array(
					'id' => $post->ID,
					'text' => $post->ID . ' - ' . $post->post_title,
				);
			}

			wp_send_json_success( array(
				'lists' => $lists,
				'lists_with_id' => $lists_with_id,
			) );
		}

		wp_die();
	}

	/**
	 * Get list object by ID.
	 *
	 * @since	9.0.0
	 * @param	mixed $post_or_list_id ID or Post Object for the list we want.
	 */
	public static function get_list( $post_or_list_id ) {
		$list_id = is_object( $post_or_list_id ) && $post_or_list_id instanceof WP_Post ? $post_or_list_id->ID : intval( $post_or_list_id );

		// Only get new list object if it hasn't been retrieved before.
		if ( ! array_key_exists( $list_id, self::$lists ) ) {
			$post = is_object( $post_or_list_id ) && $post_or_list_id instanceof WP_Post ? $post_or_list_id : get_post( intval( $post_or_list_id ) );

			if ( $post instanceof WP_Post && WPRM_LIST_POST_TYPE === $post->post_type ) {
				$list = new WPRM_List( $post );
			} else {
				$list = false;
			}

			self::$lists[ $list_id ] = $list;
		}

		return self::$lists[ $list_id ];
	}

	/**
	 * Get an array of list IDs that are in a specific post.
	 *
	 * @since	9.0.0
	 * @param	mixed $post_id Optional post ID. Uses current post if not set.
	 */
	public static function get_list_ids_from_post( $post_id = false ) {
		// Default to current post ID and sanitize.
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$post_id = intval( $post_id );

		// Search through post content if not in cache only.
		if ( ! isset( self::$posts[ $post_id ] ) ) {
			$post = get_post( $post_id );

			if ( $post ) {
				if ( WPRM_LIST_POST_TYPE === $post->post_type ) {
					self::$posts[ $post_id ] = array( $post_id );
				} else {
					self::$posts[ $post_id ] = self::get_list_ids_from_content( $post->post_content );
				}
			} else {
				// Fail now and give another chance to find ids later.
				return false;
			}
		}

		return self::$posts[ $post_id ];
	}

	/**
	 * Get an array of list IDs that are in the content.
	 *
	 * @since	9.0.0
	 * @param	mixed $content Content we want to check for lists.
	 */
	public static function get_list_ids_from_content( $content ) {
		// Gutenberg.
		$gutenberg_matches = array();
		$gutenberg_patern = '/<!--\s+wp:(wp\-recipe\-maker\/list)(\s+(\{.*?\}))?\s+(\/)?-->/';
		preg_match_all( $gutenberg_patern, $content, $matches );

		if ( isset( $matches[3] ) ) {
			foreach ( $matches[3] as $block_attributes_json ) {
				if ( ! empty( $block_attributes_json ) ) {
					$attributes = json_decode( $block_attributes_json, true );
					if ( ! is_null( $attributes ) ) {
						if ( isset( $attributes['id'] ) ) {
							$gutenberg_matches[] = intval( $attributes['id'] );
						}
					}
				}
			}
		}

		// Classic Editor.
		$classic_matches = array();
		$pattern = get_shortcode_regex( array( 'wprm-list' ) );

		if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) && array_key_exists( 2, $matches ) ) {
			foreach ( $matches[2] as $key => $value ) {
				if ( 'wprm-list' === $value ) {
					$shortcode_options = shortcode_parse_atts( stripslashes( $matches[3][ $key ] ) );
					$list_id = isset( $shortcode_options['id'] ) ? intval( $shortcode_options['id'] ) : 0;

					if ( WPRM_LIST_POST_TYPE === get_post_type( $list_id ) ) {
						$classic_matches[] = $list_id;
					}
				}
			}
		}

		return $gutenberg_matches + $classic_matches;
	}

	/**
	 * Invalidate cached list.
	 *
	 * @since	9.0.0
	 * @param	int $list_id ID of the list to invalidate.
	 */
	public static function invalidate_list( $list_id ) {
		if ( array_key_exists( $list_id, self::$lists ) ) {
			unset( self::$lists[ $list_id ] );
		}
	}
}

WPRM_List_Manager::init();
