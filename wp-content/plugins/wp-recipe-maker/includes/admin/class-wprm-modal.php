<?php
/**
 * Handle the recipe modal.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/modal
 */

/**
 * Handle the recipe modal.
 *
 * @since      5.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/modal
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Modal {

	/**
	 * Register actions and filters.
	 *
	 * @since    5.0.0
	 */
	public static function init() {
		add_action( 'admin_footer', array( __CLASS__, 'add_modal_content' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Check if modal assets should get loaded.
	 *
	 * @since    6.7.0
	 */
	public static function should_load_modal_assets() {
		if ( ! WPRM_Assets::should_load_admin_assets() ) {
			return false;
		} else {
			$screen = get_current_screen();

			if ( 'toplevel_page_et_bloom_options' === $screen->id
				|| 'et_theme_builder' === substr( $screen->id, -16 )
				|| 'bulletproof-security' === substr( $screen->id, 0, 20 )
				|| 'hustle' === $screen->parent_base
				|| 'admin_page_newsletter' === substr( $screen->id, 0, 21 )
				|| 'bookly-menu' === $screen->parent_base
				|| 'WishListMember' === $screen->parent_base ) {
				return false;
			}

			// Visual composer.
			if ( isset( $_GET['vcv-action'] ) && 'frontend' === $_GET['vcv-action'] ) {
				return false;
			}

			// Flatsome UX Builder.
			if ( isset( $_GET['app'] ) && 'uxbuilder' === $_GET['app'] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Add modal template to edit screen.
	 *
	 * @since    5.0.0
	 */
	public static function add_modal_content() {
		if ( ! self::should_load_modal_assets() ) {
			return;
		}

		echo '<div id="wprm-admin-modal"></div>';
		echo '<div id="wprm-admin-modal-notes-placeholder" style="display: none">';
		wp_editor( '', 'wprm-admin-modal-notes-editor' );
		echo '</div>';
	}


	/**
	 * Enqueue stylesheets and scripts.
	 *
	 * @since    5.0.0
	 */
	public static function enqueue() {
		if ( ! self::should_load_modal_assets() ) {
			return;
		}

		// Load WordPress JS requirements.
		wp_enqueue_media();
		// WordPress < 4.8 compatibility.
		if ( function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();
		}
		
		wp_enqueue_style( 'wprm-admin-modal', WPRM_URL . 'dist/admin-modal.css', array(), WPRM_VERSION, 'all' );
		wp_enqueue_script( 'wprm-admin-modal', WPRM_URL . 'dist/admin-modal.js', array(), WPRM_VERSION, true );

		// Starting recipe for modal.
		$empty_recipe = new WPRM_Recipe_Shell(array(
			'equipment' => array(
				array(
					'uid' => 0,
					'name' => '',
				),
			),
			'ingredients_flat' => array(
				array(
					'uid' => 0,
					'type' => 'ingredient',
					'amount' => '',
					'unit' => '',
					'name' => '',
					'notes' => '',
				),
			),
			'instructions_flat' => array(
				array(
					'uid' => 0,
					'type' => 'instruction',
					'text' => '',
					'image' => 0,
					'image_url' => '',
				),
			),
		));

		$localize_data = apply_filters( 'wprm_admin_modal_localize', array(
			'recipe' => $empty_recipe->get_data(),
			'editor_uid' => 0,
			'options' => array(
				'post_status' => self::get_post_status_options(),
				'post_author' => self::get_post_author_options(),
				'author' => self::get_author_options(),
				'equipment_link_nofollow' => self::get_equipment_link_nofollow_options(),
				'ingredient_link_nofollow' => self::get_ingredient_link_nofollow_options(),
				'term_link_nofollow' => self::get_term_link_nofollow_options(),
			),
			'settings' => array(
				'import_instructions_split' => WPRM_Settings::get( 'import_instructions_split' ),
			),
			'multilingual' => WPRM_Compatibility::multilingual(),
			'categories' => self::get_categories(),
			'custom_fields' => false,
			'nutrition' => WPRM_Nutrition::get_fields(),
			'unit_conversion' => false,
			'notices' => WPRM_Notices::get_notices(),
			'integrations' => array(
				'my_emissions_checkbox' => WPRM_Compatibility_My_Emissions::show_checkbox_in_modal(),
			),
			'images' => array(
				'video' => includes_url( 'images/media/video.png' ),
			),
		) );

		wp_localize_script( 'wprm-shared', 'wprm_admin_modal', $localize_data );
	}

	/**
	 * Get all category options.
	 *
	 * @since    5.0.0
	 */
	public static function get_categories() {
		$categories = array();
		$wprm_taxonomies = WPRM_Taxonomies::get_taxonomies();

		foreach ( $wprm_taxonomies as $wprm_taxonomy => $options ) {
			$wprm_key = substr( $wprm_taxonomy, 5 );

			$terms = get_terms( array(
				'taxonomy' => $wprm_taxonomy,
				'hide_empty' => false,
				'count' => true,
			) );

			if ( is_wp_error( $terms ) ) {
				continue;
			}

			$categories[ $wprm_key ] = array(
				'label' => $options['name'],
				'terms' => array_values( (array) $terms ),
				'creatable' => true,
			);

			// Add optional help text.
			switch( $wprm_key ) {
				case 'course':
					$categories[ $wprm_key ]['help'] = __( 'Used in the recipe metadata. The type of meal or course for this recipe. Examples: dinner, entree, snack', 'wp-recipe-maker' );
					break;
				case 'cuisine':
					$categories[ $wprm_key ]['help'] = __( 'Used in the recipe metadata. The region associated with your recipe. Examples: French, Mediterranean, American', 'wp-recipe-maker' );
					break;
				case 'suitablefordiet':
					$categories[ $wprm_key ]['help'] = __( 'Used in the recipe metadata. This is a restricted list of diets whose label can be changed on the Manage > Recipe Fields page.', 'wp-recipe-maker' );
					$categories[ $wprm_key ]['creatable'] = false;
					break;
				case 'keyword':
					$categories[ $wprm_key ]['help'] = __( 'Used in the recipe metadata. Should describe the recipe, but not fit in "Courses" or "Cuisines". Examples: summer, quick, nutmeg crust', 'wp-recipe-maker' );
					break;
			}
		}

		return $categories;
	}

	/**
	 * Get all post status options.
	 *
	 * @since    7.1.0
	 */
	public static function get_post_status_options() {
		$post_statuses = get_post_statuses();

		$options = array();
		foreach ( $post_statuses as $value => $label ) {
			$options[] = array(
				'value' => $value,
				'label' => $label,
			);
		}

		return $options;
	}

	/**
	 * Get all post author options.
	 *
	 * @since    8.0.0
	 */
	public static function get_post_author_options() {
		$options = array();

		// Only needed when the recipe post type is public or manually setting post author.
		if ( 'public' === WPRM_Settings::get( 'post_type_structure' ) || 'manual' === WPRM_Settings::get( 'recipe_use_author' ) ) {
			$args = array();

			// Prevent deprecation warning.
			if ( version_compare( $GLOBALS['wp_version'], '5.9', '<' ) ) {
				$args['who'] = 'authors';
			} else {
				$args['capability'] = array( 'edit_posts' );
			}

			$authors = get_users( $args );

			foreach ( $authors as $author ) {
				$label = $author->ID;

				if ( $author->data->display_name ) {
					$label .= ' - ' . $author->data->display_name;
				}

				$options[] = array(
					'value' => $author->ID,
					'label' => $label,
				);
			}
		}

		return $options;
	}

	/**
	 * Get all author options.
	 *
	 * @since    5.0.0
	 */
	public static function get_author_options() {
		$labels = array(
			'disabled' => __( "Don't show", 'wp-recipe-maker' ),
			'post_author' => __( 'Name of post author', 'wp-recipe-maker' ),
			'custom' => __( 'Custom author name', 'wp-recipe-maker' ),
			'same' => __( 'Same author for every recipe', 'wp-recipe-maker' ),
		);

		$default = WPRM_Settings::get( 'recipe_author_display_default' );

		$options = array(
			array(
				'value' => 'default',
				'label' => __( 'Use Default', 'wp-recipe-maker' ) . ' (' . $labels[ $default ] . ')',
				'actual' => $default,
			),
		);

		foreach ( $labels as $value => $label ) {
			$options[] = array(
				'value' => $value,
				'label' => $label,
				'actual' => $value,
			);
		}

		return $options;
	}

	/**
	 * Get all equipment link nofollow options.
	 *
	 * @since    5.0.0
	 */
	public static function get_equipment_link_nofollow_options() {
		$labels = array(
			'follow' => __( "Don't Use Nofollow", 'wp-recipe-maker' ),
			'nofollow' => __( 'Use Nofollow', 'wp-recipe-maker' ),
			'sponsored' => __( 'Use Sponsored', 'wp-recipe-maker' ),
		);

		$default = WPRM_Settings::get( 'equipment_links_nofollow' );

		$options = array(
			array(
				'value' => 'default',
				'label' => __( 'Use Default', 'wp-recipe-maker' ) . ' (' . $labels[ $default ] . ')',
				'actual' => $default,
			),
		);

		foreach ( $labels as $value => $label ) {
			$options[] = array(
				'value' => $value,
				'label' => $label,
				'actual' => $value,
			);
		}

		return $options;
	}

	/**
	 * Get all ingredient link nofollow options.
	 *
	 * @since    5.0.0
	 */
	public static function get_ingredient_link_nofollow_options() {
		$labels = array(
			'follow' => __( "Don't Use Nofollow", 'wp-recipe-maker' ),
			'nofollow' => __( 'Use Nofollow', 'wp-recipe-maker' ),
			'sponsored' => __( 'Use Sponsored', 'wp-recipe-maker' ),
		);

		$default = WPRM_Settings::get( 'ingredient_links_nofollow' );

		$options = array(
			array(
				'value' => 'default',
				'label' => __( 'Use Default', 'wp-recipe-maker' ) . ' (' . $labels[ $default ] . ')',
				'actual' => $default,
			),
		);

		foreach ( $labels as $value => $label ) {
			$options[] = array(
				'value' => $value,
				'label' => $label,
				'actual' => $value,
			);
		}

		return $options;
	}

	/**
	 * Get all term link nofollow options.
	 *
	 * @since    5.0.0
	 */
	public static function get_term_link_nofollow_options() {
		$labels = array(
			'follow' => __( "Don't Use Nofollow", 'wp-recipe-maker' ),
			'nofollow' => __( 'Use Nofollow', 'wp-recipe-maker' ),
			'sponsored' => __( 'Use Sponsored', 'wp-recipe-maker' ),
		);

		$default = WPRM_Settings::get( 'term_links_nofollow' );

		$options = array(
			array(
				'value' => 'default',
				'label' => __( 'Use Default', 'wp-recipe-maker' ) . ' (' . $labels[ $default ] . ')',
				'actual' => $default,
			),
		);

		foreach ( $labels as $value => $label ) {
			$options[] = array(
				'value' => $value,
				'label' => $label,
				'actual' => $value,
			);
		}

		return $options;
	}
}

WPRM_Modal::init();
