<?php
/**
 * Handle the recipe preview.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the recipe preview.
 *
 * @since      9.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Preview {

	private static $previewing_id = false;

	/**
	 * Register actions and filters.
	 *
	 * @since	9.6.0
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_for_preview' ) );
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ), 1 );
	}

	/**
	 * Check if we're in preview mode.
	 *
	 * @since	9.6.0
	 */
	public static function is_preview() {
		return false !== self::$previewing_id;
	}

	/**
	 * Check if we're trying to preview a recipe.
	 *
	 * @since	9.6.0
	 */
	public static function check_for_preview() {
		$slug = 'wprm-recipe-preview';
		preg_match( '/[\/\?]' . $slug . '[\/=](.+?)(\/)?$/', $_SERVER['REQUEST_URI'], $preview_url ); // Input var okay.
		$previewing_id = isset( $preview_url[1] ) ? $preview_url[1] : false;

		if ( false !== $previewing_id ) {
			$user_is_allowed_access = false;
			if ( 'new' === $previewing_id ) {
				$user_is_allowed_access = current_user_can( 'edit_posts' );
			} else {
				$preview_id = intval( $previewing_id );
				$user_is_allowed_access = current_user_can( 'edit_post', $preview_id );
			}

			if ( $user_is_allowed_access ) {
				self::$previewing_id = $previewing_id;
			}
		}
	}

	/**
	 * Maybe set up a fake post and redirect the template.
	 *
	 * @since	9.6.0
	 */
	public static function template_redirect() {
		if ( ! self::is_preview() ) {
			return;
		}

		global $wp, $wp_query;

		$post_id = -4273189; // Just a random number.

		// Create fake post.
		$post = new stdClass();
		$post->ID = $post_id;
		$post->post_author = 1;
		$post->post_date = current_time( 'mysql' );
		$post->post_date_gmt = current_time( 'mysql', 1 );
		$post->post_title = 'WP Recipe Maker';
		$post->post_status = 'publish';
		$post->comment_status = 'closed';
		$post->ping_status = 'closed';
		$post->post_name = 'wprm-recipe-preview-' . self::$previewing_id;
		$post->post_type = 'post';
		$post->filter = 'raw'; // important!

		// Set up post content.
		$post_content = '';
		$post_content .= '<p>';
		$post_content .= 'new' === self::$previewing_id ? __( 'Previewing a new recipe', 'wp-recipe-maker' ) : __( 'Previewing recipe ID:', 'wp-recipe-maker' ) . ' ' . self::$previewing_id;
		$post_content .= '</p>';
		$post_content .= '<p>[wprm-recipe preview="' . esc_attr( self::$previewing_id ) . '"]</p>';

		$post->post_content = $post_content;

		// Convert to WP_Post object
		$wp_post = new WP_Post( $post );

		// Add the fake post to the cache
 		wp_cache_add( $post_id, $wp_post, 'posts' );

		// Update the main query
		$wp_query->post = $wp_post;
		$wp_query->posts = array( $wp_post );
		$wp_query->queried_object = $wp_post;
		$wp_query->queried_object_id = $post_id;
		$wp_query->found_posts = 1;
		$wp_query->post_count = 1;
		$wp_query->max_num_pages = 1; 
		$wp_query->is_page = true;
		$wp_query->is_singular = true; 
		$wp_query->is_single = false; 
		$wp_query->is_attachment = false;
		$wp_query->is_archive = false; 
		$wp_query->is_category = false;
		$wp_query->is_tag = false; 
		$wp_query->is_tax = false;
		$wp_query->is_author = false;
		$wp_query->is_date = false;
		$wp_query->is_year = false;
		$wp_query->is_month = false;
		$wp_query->is_day = false;
		$wp_query->is_time = false;
		$wp_query->is_search = false;
		$wp_query->is_feed = false;
		$wp_query->is_comment_feed = false;
		$wp_query->is_trackback = false;
		$wp_query->is_home = false;
		$wp_query->is_embed = false;
		$wp_query->is_404 = false; 
		$wp_query->is_paged = false;
		$wp_query->is_admin = false; 
		$wp_query->is_preview = false; 
		$wp_query->is_robots = false; 
		$wp_query->is_posts_page = false;
		$wp_query->is_post_type_archive = false;

		$GLOBALS['wp_query'] = $wp_query;
  		$wp->register_globals();
	}

	/**
	 * Get the preview recipe.
	 *
	 * @since	9.6.0
	 * @param	int $preview_id ID of the preview recipe.
	 */
	public static function get_preview_recipe( $preview_id ) {
		$recipe = false;
		$json = get_transient( 'wprm_recipe_preview_' . $preview_id );

		if ( $json && is_array( $json ) ) {
			$sanitized_recipe = WPRM_Recipe_Sanitizer::sanitize( $json );

			// Fix technical fields.
			$sanitized_recipe['id'] = 'preview';
			$sanitized_recipe['parent_url'] = '#';
			$sanitized_recipe['image_url'] = $json['image_url'];
			$sanitized_recipe['pin_image_url'] = $json['pin_image_url'];
			$sanitized_recipe['ingredients_flat'] = $json['ingredients_flat'];
			$sanitized_recipe['instructions_flat'] = $json['instructions_flat'];

			// Fix tags.
			$sanitized_recipe['tags'] = array();
			if ( isset( $json['tags'] ) ) {
				foreach ( $json['tags'] as $taxonomy => $terms ) {
					$sanitized_recipe['tags'][ $taxonomy ] = array();

					foreach ( $terms as $term ) {
						if ( is_array( $term ) || is_object( $term ) ) {
							$term = (array) $term;
							$sanitized_recipe['tags'][ $taxonomy ][] = $term['name'];
						} else {
							$sanitized_recipe['tags'][ $taxonomy ][] = $term;
						}
					}
				}
			}

			// Set some additional fields.
			if ( isset( $json['rating'] ) ) { $sanitized_recipe['rating'] = $json['rating']; }
			$sanitized_recipe['permalink'] = home_url() . '/?wprm-recipe-preview=' . $preview_id;

			$recipe = new WPRM_Recipe_Shell( $sanitized_recipe );
			WPRM_Template_Shortcodes::set_current_recipe_shell( $recipe );

			// Set frontend data.
			$recipe_data = array(
				'recipe-preview' => $recipe->get_data_frontend(),
			);
			echo '<script>window.wprm_recipes = ' . wp_json_encode( $recipe_data ) . '</script>';

			// Prevent other frontend data.
			add_filter( 'wprm_recipes_on_page', '__return_false', 999 );
		} else {
			echo '<script>alert("' . __( 'Something went wrong. The preview could not be loaded.', 'wp-recipe-maker' ) . '");</script>';
		}

		return $recipe;
	}

	/**
	 * Set a recipe for previewing.
	 *
	 * @since	9.6.0
	 */
	public static function set_recipe_for_preview_and_get_url( $recipe ) {
		$preview_id = isset( $recipe['id'] ) ? intval( $recipe['id'] ) : 'new';

		// Store recipe as transient.
		delete_transient( 'wprm_recipe_preview_' . $preview_id );
		set_transient( 'wprm_recipe_preview_' . $preview_id, $recipe, HOUR_IN_SECONDS );

		// Get URL to use for previewing this recipe.
		$home_url = WPRM_Compatibility::get_home_url();
		$query_params = false;

		if ( false !== strpos( $home_url, '?' ) ) {
			$home_url_parts = explode( '?', $home_url, 2 );

			$home_url = trailingslashit( $home_url_parts[0] );
			$query_params = $home_url_parts[1];
		}

		$preview_url = $home_url . '?wprm-recipe-preview=' . $preview_id;

		if ( $query_params ) {
			$print_url .= '&' . $query_params;
		}

		return $preview_url;		
	}
}

WPRM_Preview::init();
