<?php
/**
 * Responsible for handling the import WP Ultimate Recipe ingredient nutrition tool.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the import WP Ultimate Recipe ingredient nutrition tool.
 *
 * @since      5.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Tools_WPURP_Nutrition {

	/**
	 * Register actions and filters.
	 *
	 * @since	5.6.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 20 );
		add_action( 'wp_ajax_wprm_wpurp_nutrition', array( __CLASS__, 'ajax_wpurp_nutrition' ) );
	}

	/**
	 * Add the tools submenu to the WPRM menu.
	 *
	 * @since	5.6.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( '', __( 'Importing Nutrition', 'wp-recipe-maker' ), __( 'Importing Nutrition', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_tools_access' ), 'wprm_wpurp_nutrition', array( __CLASS__, 'wpurp_nutrition' ) );
	}

	/**
	 * Get the template for the import ingredient nutrition from WP Ultimate Recipe page.
	 *
	 * @since	5.6.0
	 */
	public static function wpurp_nutrition() {
		$nutrition = get_option( 'wpurp_nutritional_information', array() );
		$ingredients = array_keys( $nutrition );

		// Only when debugging.
		if ( WPRM_Tools_Manager::$debugging ) {
			$result = self::import_nutrition( $ingredients ); // Input var okay.
			var_dump( $result );
			die();
		}

		// Handle via AJAX.
		wp_localize_script( 'wprm-admin', 'wprm_tools', array(
			'action' => 'wpurp_nutrition',
			'posts' => $ingredients,
			'args' => array(),
		));

		require_once( WPRM_DIR . 'templates/admin/menu/tools/wpurp-nutrition.php' );
	}

	/**
	 * Import WP Ultimate Recipe ingredient nutrition through AJAX.
	 *
	 * @since    2.1.0
	 */
	public static function ajax_wpurp_nutrition() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$posts = isset( $_POST['posts'] ) ? json_decode( wp_unslash( $_POST['posts'] ) ) : array(); // Input var okay.

			$posts_left = array();
			$posts_processed = array();

			if ( count( $posts ) > 0 ) {
				$posts_left = $posts;
				$posts_processed = array_map( 'intval', array_splice( $posts_left, 0, 10 ) );

				$result = self::import_nutrition( $posts_processed );

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
	 */
	public static function import_nutrition( $ingredients ) {
		$wpurp_nutrition = get_option( 'wpurp_nutritional_information', array() );

		foreach ( $ingredients as $ingredient_id ) {
			$ingredient = get_term( $ingredient_id, 'ingredient' );

			$nutrition = isset( $wpurp_nutrition[ $ingredient_id ] ) ? $wpurp_nutrition[ $ingredient_id ] : false;

			if ( $nutrition ) {
				$wprm_nutrition = array(
					'amount' => isset( $nutrition['_meta']['serving_quantity'] ) ? sanitize_text_field( $nutrition['_meta']['serving_quantity'] ) : '',
					'unit' => isset( $nutrition['_meta']['serving_unit'] ) ? sanitize_text_field( $nutrition['_meta']['serving_unit'] ) : '',
					'nutrients' => array(),
				);

				// Uses reference serving instead if requested.
				$import_reference_servings = false;
				
				if ( $import_reference_servings ) {
					$reference_servings = sanitize_text_field( $nutrition['_meta']['serving'] );

					$match = preg_match( '/^\s*[\d\.\/-]+/', $reference_servings, $servings_array );
					if ( 1 === $match ) {
						$amount = str_replace( ' ','', $servings_array[0] );
						$amount = WPRM_Recipe_Parser::format_quantity( WPRM_Recipe_Parser::parse_quantity( $amount ) );
					} else {
						$amount = '';
					}

					$unit = preg_replace( '/^\s*[\d\.\/-]+\s*/', '', $reference_servings );

					$wprm_nutrition['amount'] = $amount;
					$wprm_nutrition['unit'] = $unit;
				}

				$nutrition_mapping = array(
					'calories'              => 'calories',
					'carbohydrate'          => 'carbohydrates',
					'protein'               => 'protein',
					'fat'                   => 'fat',
					'saturated_fat'         => 'saturated_fat',
					'polyunsaturated_fat'   => 'polyunsaturated_fat',
					'monounsaturated_fat'   => 'monounsaturated_fat',
					'trans_fat'             => 'trans_fat',
					'cholesterol'           => 'cholesterol',
					'sodium'                => 'sodium',
					'potassium'             => 'potassium',
					'fiber'                 => 'fiber',
					'sugar'                 => 'sugar',
					'vitamin_a'             => 'vitamin_a',
					'vitamin_c'             => 'vitamin_c',
					'calcium'               => 'calcium',
					'iron'                  => 'iron',
				);
		
				$migrate_values = array(
					'vitamin_a' => 5000,
					'vitamin_c' => 82.5,
					'calcium' => 1000,
					'iron' => 18,
				);
		
				foreach ( $nutrition_mapping as $wpurp_field => $wprm_field ) {
					$wprm_nutrition['nutrients'][ $wprm_field ] = isset( $nutrition[ $wpurp_field ] ) ? $nutrition[ $wpurp_field ] : '';
		
					if ( array_key_exists( $wprm_field, $migrate_values ) && $wprm_nutrition['nutrients'][ $wprm_field ] ) {
						// Daily needs * currently saved as percentage, round to 1 decimal.
						$wprm_nutrition['nutrients'][ $wprm_field ] = round( $migrate_values[ $wprm_field ] * ( $wprm_nutrition['nutrients'][ $wprm_field ] / 100 ), 1 );
					}
				}

				$term_id = self::get_or_create_ingredient( $ingredient->name );
				update_term_meta( $term_id, 'wprpn_nutrition', $wprm_nutrition );
			}
		}
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
		$term = term_exists( $name, 'wprm_nutrition_ingredient' );

		if ( 0 === $term || null === $term ) {
			$term = wp_insert_term( $name, 'wprm_nutrition_ingredient' );
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

WPRM_Tools_WPURP_Nutrition::init();
