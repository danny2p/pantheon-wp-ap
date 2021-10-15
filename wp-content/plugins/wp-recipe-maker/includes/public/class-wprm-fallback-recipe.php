<?php
/**
 * Replace shortcode with fallback recipe for when plugin is deactivated.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Replace shortcode with fallback recipe for when plugin is deactivated.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Fallback_Recipe {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register_rest_prepare' ) );

		add_filter( 'the_content', array( __CLASS__, 'replace_imported_shortcodes' ), 1 );
		add_filter( 'the_content', array( __CLASS__, 'replace_fallback_with_shortcode' ), 0 );
		add_filter( 'content_edit_pre', array( __CLASS__, 'replace_fallback_with_shortcode' ) );

		add_filter( 'content_save_pre', array( __CLASS__, 'replace_shortcode_with_fallback' ) );
	}

	/**
	 * Register REST prepare hooks.
	 *
	 * @since    5.7.0
	 */
	public static function register_rest_prepare() {
		$post_types = get_post_types();
		foreach ( $post_types as $key => $label ) {
			add_filter( 'rest_prepare_' . $key, array( __CLASS__, 'replace_fallback_rest_api' ), 10, 3 );
		}
	}

	/**
	 * Replace shortcode with fallback recipe.
	 *
	 * @since    1.0.0
	 * @param		 mixed $content Content that is being saved.
	 */
	public static function replace_shortcode_with_fallback( $content ) {
		$recipe_shortcodes = array();
		$pattern = get_shortcode_regex( array( 'wprm-recipe' ) );

		if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) && array_key_exists( 2, $matches ) ) {
			foreach ( $matches[2] as $key => $value ) {
				if ( 'wprm-recipe' === $value ) {
					$recipe_shortcodes[ $matches[0][ $key ] ] = shortcode_parse_atts( stripslashes( $matches[3][ $key ] ) );
				}
			}
		}

		foreach ( $recipe_shortcodes as $shortcode => $shortcode_options ) {
			$recipe_id = isset( $shortcode_options['id'] ) ? intval( $shortcode_options['id'] ) : 0;

			if ( $recipe_id ) {
				unset( $shortcode_options['id'] );
				$fallback_recipe = self::get_fallback_recipe( $recipe_id, $shortcode_options );

				$content = str_replace( $shortcode, $fallback_recipe, $content );
			}
		}

		return $content;
	}

	/**
	 * Replace fallback recipe with shortcode for the content editor.
	 *
	 * @since    1.0.0
	 * @param		 mixed $content Content we want to filter before it gets passed along.
	 */
	public static function replace_fallback_with_shortcode( $content ) {
		preg_match_all( self::get_fallback_regex(), $content, $matches );
		foreach ( $matches[0] as $key => $match ) {
			$id = $matches[1][ $key ];
			preg_match_all( '/<!--WPRM Recipe ' . $id . '-->.?<!--(.+?)-->/ms', $match, $args );

			$shortcode_options = isset( $args[1][0] ) ? ' ' . $args[1][0] : '';

			// Divi compatibility.
			$shortcode_options = str_ireplace( '[et_pb_line_break_holder]', '', $shortcode_options );

			$content = str_replace( $match, '[wprm-recipe id="' . $id . '"' . $shortcode_options . ']', $content );
		}

		return $content;
	}

	/**
	 * Replace fallback recipe with shortcode in the rest API.
	 *
	 * @since	2.4.0
	 * @param WP_REST_Response $response The response object.
	 * @param WP_Post          $post     Post object.
	 * @param WP_REST_Request  $request  Request object.
	 */
	public static function replace_fallback_rest_api( $response, $post, $request ) {
		$params = $request->get_params();

		if ( isset( $params['context'] ) && 'edit' === $params['context'] ) {
			if ( isset( $response->data['content']['raw'] ) ) {
				$response->data['content']['raw'] = self::replace_fallback_with_shortcode( $response->data['content']['raw'] );
			}
		}
		return $response;
	}

	/**
	 * Replace imported shortcodes to make sure recipes are displayed.
	 *
	 * @since    1.7.0
	 * @param	 mixed $content Content we want to filter before it gets passed along.
	 */
	public static function replace_imported_shortcodes( $content ) {
		// BigOven.
		if ( defined( 'BO_RECIPES_VERSION' ) ) {
			$recipe_shortcodes = array();
			$pattern = get_shortcode_regex( array( 'seo_recipe' ) );

			if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) && array_key_exists( 2, $matches ) ) {
				foreach ( $matches[2] as $key => $value ) {
					if ( 'seo_recipe' === $value ) {
						$recipe_shortcodes[ $matches[0][ $key ] ] = shortcode_parse_atts( stripslashes( $matches[3][ $key ] ) );
					}
				}
			}

			foreach ( $recipe_shortcodes as $shortcode => $shortcode_options ) {
				$recipe_id = isset( $shortcode_options['id'] ) ? intval( $shortcode_options['id'] ) : 0;

				if ( WPRM_POST_TYPE === get_post_type( $recipe_id ) ) {
					$content = str_replace( $shortcode, '[wprm-recipe id="' . $recipe_id . '"]', $content );
				}
			}
		}

		return $content;
	}

	/**
	 * Get fallback HTML for a specific recipe.
	 *
	 * @since    1.0.0
	 * @param	 int   $recipe_id ID of the recipe we want to get the fallback HTML for.
	 * @param	 array $args      Shortcode arguments to pass along in the fallback recipe.
	 */
	public static function get_fallback_recipe( $recipe_id, $args = array() ) {
		$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

		if ( $recipe ) {
			ob_start();
			require( WPRM_DIR . 'templates/public/fallback-recipe.php' );
			$fallback = ob_get_contents();
			ob_end_clean();
		} else {
			$fallback = '';
		}

		return trim( $fallback );
	}

	/**
	 * Get the regex pattern to find fallback recipes.
	 *
	 * @since    1.0.0
	 */
	public static function get_fallback_regex() {
		return '/<!--WPRM Recipe (\d+)-->.+?<!--End WPRM Recipe-->/ms';
	}
}

WPRM_Fallback_Recipe::init();
