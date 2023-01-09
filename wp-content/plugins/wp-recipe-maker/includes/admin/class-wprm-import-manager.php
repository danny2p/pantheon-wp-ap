<?php
/**
 * Responsible for handling the import of recipes from other sources.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Responsible for handling the import of recipes from other sources.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Import_Manager {

	/**
	 * Only to be enabled when debugging the import.
	 *
	 * @since    1.25.0
	 * @access   private
	 * @var      boolean    $debugging    Wether or not we are debugging the import.
	 */
	private static $debugging = false;

	/**
	 * Importers that can be used to import recipes from other sources.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $importers    Array containing all available importers.
	 */
	private static $importers = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		self::load_importers();
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ), 16 );
		add_action( 'admin_post_wprm_check_imported_recipes', array( __CLASS__, 'form_check_imported_recipes' ) );

		add_action( 'wp_ajax_wprm_import_recipes', array( __CLASS__, 'ajax_import_recipes' ) );
	}

	/**
	 * Add the import submenu to the WPRM menu.
	 *
	 * @since    1.0.0
	 */
	public static function add_submenu_page() {
		add_submenu_page( 'wprecipemaker', __( 'Import Recipes', 'wp-recipe-maker' ), __( 'Import Recipes', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_import_access' ), 'wprm_import_overview', array( __CLASS__, 'overview_page_template' ) );
		add_submenu_page( '', __( 'Import Recipes', 'wp-recipe-maker' ), __( 'Import Recipes', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_import_access' ), 'wprm_import', array( __CLASS__, 'import_page_template' ) );
		add_submenu_page( '', __( 'Search Recipes', 'wp-recipe-maker' ), __( 'Search Recipes', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_import_access' ), 'wprm_import_search', array( __CLASS__, 'import_search_page_template' ) );
		add_submenu_page( '', __( 'Importing Recipes', 'wp-recipe-maker' ), __( 'Importing Recipes', 'wp-recipe-maker' ), WPRM_Settings::get( 'features_import_access' ), 'wprm_importing', array( __CLASS__, 'importing_recipes' ) );
	}

	/**
	 * Get the template for the import overview page.
	 *
	 * @since    1.0.0
	 */
	public static function overview_page_template() {
		require_once( WPRM_DIR . 'templates/admin/menu/import/import-overview.php' );
	}

	/**
	 * Get the template for the import page.
	 *
	 * @since    1.0.0
	 */
	public static function import_page_template() {
		require_once( WPRM_DIR . 'templates/admin/menu/import/import-recipes.php' );
	}

	/**
	 * Get the template for the import search page.
	 *
	 * @since    1.10.0
	 */
	public static function import_search_page_template() {
		require_once( WPRM_DIR . 'templates/admin/menu/import/import-search.php' );
	}

	/**
	 * Get the template for the importing page.
	 *
	 * @since    1.18.0
	 */
	public static function importing_recipes() {
		if ( isset( $_POST['wprm_import_recipes'] ) && wp_verify_nonce( sanitize_key( $_POST['wprm_import_recipes'] ), 'wprm_import_recipes' ) ) { // Input var okay.
			$importer_uid = isset( $_POST['importer'] ) ? sanitize_title( wp_unslash( $_POST['importer'] ) ) : ''; // Input var okay.
			$recipes = isset( $_POST['recipes'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['recipes'] ) ) : array(); // Input var okay.

			$importer = self::get_importer( $importer_uid );

			if ( $importer && count( $recipes ) > 0 ) {
				// Only when debugging.
				if ( self::$debugging ) {
					$result = self::import_recipes( $importer, $recipes, $_POST ); // Input var okay.
					var_dump( $result );
					die();
				}

				// Import recipes via AJAX.
				wp_localize_script( 'wprm-admin', 'wprm_import', array(
					'importer_uid' => $importer_uid,
					'post_data' => $_POST,
					'recipes' => $recipes,
				));

				require_once( WPRM_DIR . 'templates/admin/menu/import/importing.php' );
			} else {
				esc_html_e( 'Something went wrong.', 'wp-recipe-maker' );
			}
		} else {
			esc_html_e( 'Something went wrong.', 'wp-recipe-maker' );
		}
	}

	/**
	 * Parse ingredients submitted through AJAX.
	 *
	 * @since    1.7.0
	 */
	public static function ajax_import_recipes() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			$importer_uid = isset( $_POST['importer_uid'] ) ? sanitize_title( wp_unslash( $_POST['importer_uid'] ) ) : ''; // Input var okay.
			$post_data = isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : array(); // Input var okay.
			$recipes = isset( $_POST['recipes'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['recipes'] ) ) : array(); // Input var okay.

			$importer = self::get_importer( $importer_uid );

			$recipes_left = array();
			$recipes_imported = array();

			if ( $importer && count( $recipes ) > 0 ) {
				$recipes_left = $recipes;
				$recipes_imported = array_splice( $recipes_left, 0, 3 );

				$result = self::import_recipes( $importer, $recipes_imported, $post_data ); // Input var okay.

				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array(
						'redirect' => add_query_arg( array( 'from' => $importer_uid, 'error' => rawurlencode( $result->get_error_message() ) ), admin_url( 'admin.php?page=wprm_import' ) ),
					) );
				}
			}

			wp_send_json_success( array(
				'post_data' => $post_data,
				'recipes_left' => $recipes_left,
				'recipes_imported' => $recipes_imported,
			) );
		}

		wp_die();
	}

	/**
	 * Mark the recipes selected in the form as checked.
	 *
	 * @since    1.0.0
	 */
	public static function form_check_imported_recipes() {
		if ( isset( $_POST['wprm_check_imported_recipes'] ) && wp_verify_nonce( sanitize_key( $_POST['wprm_check_imported_recipes'] ), 'wprm_check_imported_recipes' ) ) { // Input var okay.
			$importer_uid = isset( $_POST['importer'] ) ? sanitize_title( wp_unslash( $_POST['importer'] ) ) : ''; // Input var okay.
			$recipes = isset( $_POST['recipes'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['recipes'] ) ) : array(); // Input var okay.

			foreach ( $recipes as $recipe_id ) {
				$uid = get_post_meta( $recipe_id, 'wprm_import_source', true );

				if ( $uid === $importer_uid ) {
					update_post_meta( $recipe_id, 'wprm_import_source', $uid . '-checked' );
				}
			}
		}
		wp_safe_redirect( admin_url( 'admin.php?page=wprm_import_overview' ) );
		exit();
	}

	/**
	 * Import recipes using the specified importer.
	 *
	 * @since    1.0.0
	 * @param		 object $importer Importer to use for importing.
	 * @param		 array  $recipes IDs of recipes to import.
	 * @param		 array  $post_data POST data passed along when submitting the form.
	 */
	public static function import_recipes( $importer, $recipes, $post_data ) {
		// Reverse sort by ID to make sure multiple recipes in the same post are handled correctly.
		arsort( $recipes );

		foreach ( $recipes as $import_recipe_id ) {
			$imported_recipe = $importer->get_recipe( $import_recipe_id, $post_data );
			$imported_recipe = apply_filters( 'wprm_import_recipe_' . $importer->get_uid(), $imported_recipe, $import_recipe_id, $post_data );

			if ( is_wp_error( $imported_recipe ) ) {
				return $imported_recipe;
			}

			if ( $imported_recipe ) {
				$imported_recipe['import_source'] = $importer->get_uid();

				$recipe_id = isset( $imported_recipe['import_id'] ) ? intval( $imported_recipe['import_id'] ) : 0;
				$recipe = WPRM_Recipe_Sanitizer::sanitize( $imported_recipe );

				if ( $recipe_id && false !== get_post_type( $recipe_id ) ) {
					if ( WPRM_POST_TYPE !== get_post_type( $recipe_id ) ) {
						set_post_type( $recipe_id, WPRM_POST_TYPE );
					}
					WPRM_Recipe_Saver::update_recipe( $recipe_id, $recipe );
				} else {
					$recipe_id = WPRM_Recipe_Saver::create_recipe( $recipe );
				}

				$result = $importer->replace_recipe( $import_recipe_id, $recipe_id, $post_data );

				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}
		}
	}

	/**
	 * Get importer by UID.
	 *
	 * @since    1.0.0
	 * @param		 int $uid UID of the importer.
	 */
	public static function get_importer( $uid ) {
		$importer = false;
		foreach ( self::$importers as $possible_importer ) {
			if ( sanitize_title( $possible_importer->get_uid() ) === $uid ) {
				$importer = $possible_importer;
			}
		}

		return $importer;
	}

	/**
	 * Get recipes that were imported by a specific importer.
	 *
	 * @since    1.0.0
	 * @param		 int     $uid UID of the importer.
	 * @param		 boolean $exclude_checked Wether to exclude recipes that have already been checked.
	 */
	public static function get_imported_recipes( $uid, $exclude_checked = false ) {
		$args = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => 'any',
			'orderby' => 'date',
			'order' => 'DESC',
			'meta_key' => 'wprm_import_source',
			'nopaging' => true,
		);

		if ( $exclude_checked ) {
			$args['meta_value'] = $uid;
		} else {
			$args['meta_value'] = array( $uid, $uid . '-checked' );
			$args['meta_compare'] = 'IN';
		}

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			return $query->posts;
		} else {
			return array();
		}
	}

	/**
	 * Load all available importers from the /includes/admin/import directory.
	 *
	 * @since    1.0.0
	 */
	private static function load_importers() {
		$directories = apply_filters( 'wprm_importer_directories', array( WPRM_DIR . 'includes/admin/import' ) );
		$importers = array();
    
		foreach( $directories as $dir ) {
			if ( $handle = opendir( $dir ) ) {
				while ( false !== ( $file = readdir( $handle ) ) ) {
					preg_match( '/^class-wprm-import-(.*?).php/', $file, $match );
					if ( isset( $match[1] ) ) {
						require_once( $dir . '/' . $match[0] );
						$class_name = 'WPRM_Import_' . ucfirst( strtolower( $match[1] ) );
						$importers[] = new $class_name();
					}
				}
			}
		}
		self::$importers = $importers;
	}
}

WPRM_Import_Manager::init();
