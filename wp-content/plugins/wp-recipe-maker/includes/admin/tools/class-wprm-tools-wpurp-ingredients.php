<?php
/**
 * Responsible for handling the import WP Ultimate Recipe ingredients tools.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the WPRM tools.
 *
 * @since      5.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_WPURP_Ingredients {

	/**
	 * Register actions and filters.
	 *
	 * @since	5.6.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_wpurp_ingredients', array( __CLASS__, 'ajax_wpurp_ingredients' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	5.6.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Importing Ingredients', 'wp-recipe-maker' ), __( 'Importing Ingredients', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_wpurp_ingredients', array( __CLASS__, 'wpurp_ingredients' ) );
	}

	/**
	 * Get the template for the import ingredients from WP Ultimate Recipe page.
	 *
	 * @since	5.6.0
	 */
	public static function wpurp_ingredients() {
		$field = isset( $_GET['field'] ) ? sanitize_key( $_GET['field'] ) : false;

		if ( ! in_array( $field, array( 'link', 'plural', 'group', 'nutrition' ) ) ) {
			wp_die( 'Unknown field to import.' );
		}

		$args = array(
			'taxonomy' => 'ingredient',
			'hide_empty' => false,
			'fields' => 'ids',
		);

		$query = new WP_Term_Query( $args );
		$ingredients = $query->terms ? array_values( $query->terms ) : array();

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::import_ingredients( $ingredients, $field ); // Input var okay.
			var_dump( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'wpurp_ingredients',
			'posts' => $ingredients,
			'args' => array(
				'field' => $field,
			),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/wpurp-ingredients.php' );
	}

	/**
	 * Import WP Ultimate Recipe ingredients through AJAX.
	 *
	 * @since    2.1.0
	 */
	public static function ajax_wpurp_ingredients() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.
			$args = isset( $_POST['args'] ) ? wp_unslash( $_POST['args'] ) : array();

			$field = $args && isset( $args['field'] ) ? $args['field'] : false;

			$posts_left = array();
			$posts_processed = array();

			if ( count( $posts ) > 0 && in_array( $field, array( 'link', 'plural', 'group', 'nutrition' ) ) ) {
				$posts_left = $posts;
				$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

				$result = self::import_ingredients( $posts_processed, $field );

				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array(
						'redirect' => add_query_arg( array( 'sub' => 'advanced' ), admin_url( 'admin.php?page=wprm_tools' ) ),
					) );
				}
			}

			wp_send_json_success( array(
				'posts_processed' => $posts_processed,
				'posts_left' => $posts_left,
			) );
		}

		wp_die();
	}

	/**
	 * Import WP Ultimate Recipe ingredients.
	 *
	 * @since	5.6.0
	 * @param	array $ingredients 	IDs of ingredients to search.
	 * @param	mixed $field 		Ingredient field to import.
	 */
	public static function import_ingredients( $ingredients, $field ) {
		foreach ( $ingredients as $ingredient_id ) {
			$ingredient = get_term( $ingredient_id, 'ingredient' );

			switch ( $field ) {
				case 'link':
					$result = self::import_ingredient_link( $ingredient );
					break;
				case 'plural':
					$result = self::import_ingredient_plural( $ingredient );
					break;
				case 'group':
					$result = self::import_ingredient_group( $ingredient );
					break;
			}

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}
	}

	/**
	 * Import ingredient link from WP Ultimate Recipe.
	 *
	 * @since	5.6.0
	 * @param	array $ingredient Ingredient to import.
	 */
	public static function import_ingredient_link( $ingredient ) {
		$url = WPURP_Taxonomy_MetaData::get( 'ingredient', $ingredient->slug, 'link' );

		if ( $url ) {
			$term_id = self::get_or_create_ingredient( $ingredient->name );

			// Update term meta.
			if ( $term_id ) {
				$link = array(
					'url' => $url,
					'nofollow' => '1' === WPURP_Taxonomy_MetaData::get( 'ingredient', $ingredient->slug, 'nofollow_link' ) ? 'nofollow' : 'follow',
				);

				update_term_meta( $term_id, 'wprmp_ingredient_link', $link['url'] );
				update_term_meta( $term_id, 'wprmp_ingredient_link_nofollow', $link['nofollow'] );
			}
		}

		return true;
	}
	
	/**
	 * Import ingredient plural from WP Ultimate Recipe.
	 *
	 * @since	7.0.0
	 * @param	array $ingredient Ingredient to import.
	 */
	public static function import_ingredient_plural( $ingredient ) {
		$plural = WPURP_Taxonomy_MetaData::get( 'ingredient', $ingredient->slug, 'plural' );

		if ( $plural ) {
			$term_id = self::get_or_create_ingredient( $ingredient->name );

			// Update term meta.
			if ( $term_id ) {
				$plural = sanitize_text_field( $plural );
				update_term_meta( $term_id, 'wprm_ingredient_plural', $plural );
			}
		}

		return true;
	}

	/**
	 * Import ingredient groups from WP Ultimate Recipe.
	 *
	 * @since	5.6.0
	 * @param	array $ingredient Ingredient to import.
	 */
	public static function import_ingredient_group( $ingredient ) {
		$group = WPURP_Taxonomy_MetaData::get( 'ingredient', $ingredient->slug, 'group' );

		if ( $group ) {
			$term_id = self::get_or_create_ingredient( $ingredient->name );

			// Update term meta.
			if ( $term_id ) {
				$group = sanitize_text_field( $group );
				update_term_meta( $term_id, 'wprmp_ingredient_group', $group );
			}
		}

		return true;
	}

	/**
	 * Get or create WPRM ingredient.
	 *
	 * @since	5.6.0
	 * @param	mixed $name Name for the ingredient.
	 */
	public static function get_or_create_ingredient( $name ) {
		// Sanitize name before lookup.
		$name = WPRM_Recipe_Sanitizer::sanitize_html( $name );

		// Find or create term.
		$term = term_exists( $name, 'wprm_ingredient' );

		if ( 0 === $term || null === $term ) {
			$term = wp_insert_term( $name, 'wprm_ingredient' );
		}

		if ( is_wp_error( $term ) ) {
			if ( isset( $term->error_data['term_exists'] ) ) {
				$term_id = $term->error_data['term_exists'];
			} else {
				$term_id = 0;
			}
		} else {
			$term_id = $term['term_id'];
		}

		return $term_id;
	}
}

WPRM_Tools_WPURP_Ingredients::init();
