<?php
/**
 * Register the recipe taxonomies.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Register the recipe taxonomies.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Taxonomies {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 2 );
		add_action( 'pre_get_posts', array( __CLASS__, 'taxonomies_archive' ), 1 );

		add_filter( 'the_content', array( __CLASS__, 'archive_content' ) );

		add_filter( 'wprm_recipe_tag_shortcode_link', array( __CLASS__, 'archive_link' ), 11, 2 );
		add_filter( 'wprm_recipe_equipment_shortcode_link', array( __CLASS__, 'archive_link' ), 11, 2 );
		add_filter( 'wprm_recipe_ingredients_shortcode_link', array( __CLASS__, 'archive_link' ), 11, 2 );
	}

	/**
	 * Register the recipe taxonomies.
	 *
	 * @since    1.0.0
	 */
	public static function register_taxonomies() {
		$taxonomies = WPRM_Taxonomies::get_taxonomies_to_register();

		foreach ( $taxonomies as $taxonomy => $options ) {
			$args = array(
				'labels'            => array(
					'name'          => $options['name'],
					'singular_name' => $options['singular_name'],
				),
				'hierarchical'      => true,
				'public'            => false,
				'show_ui' 			=> false,
				'query_var'         => false,
				'rewrite'           => false,
				'show_in_rest'      => true,
			);

			if ( $options['archive'] ) {
				$args['public'] = true;
				$args['publicly_queryable'] = true;
				$args['show_in_nav_menus'] = true;
				$args['query_var'] = $taxonomy;
				$args['rewrite'] = array(
					'slug' => $options['slug'],
				);

				if ( WPRM_Settings::get( 'taxonomies_show_default_ui' ) ) {
					$args['show_ui'] = true;
				}
			}

			register_taxonomy( $taxonomy, WPRM_POST_TYPE, $args );
			register_taxonomy_for_object_type( $taxonomy, WPRM_POST_TYPE );

			if ( 'wprm_suitablefordiet' === $taxonomy ) {
				self::check_diet_taxonomy_terms();
			}
		}

		// Check if flush is needed.
		$flush_needed = get_transient( 'wprm_custom_taxonomies_flush_needed' );

		if ( $flush_needed ) {
			delete_transient( 'wprm_custom_taxonomies_flush_needed' );
			flush_rewrite_rules();
		}
	}

	/**
	 * Get the recipe taxonomies to register.
	 *
	 * @since    1.0.0
	 */
	public static function get_taxonomies_to_register() {
		$default_taxonomies = array(
			'wprm_course' => array(
				'name'               => _x( 'Courses', 'taxonomy general name', 'wp-recipe-maker' ),
				'singular_name'      => _x( 'Course', 'taxonomy singular name', 'wp-recipe-maker' ),
			),
			'wprm_cuisine' => array(
				'name'               => _x( 'Cuisines', 'taxonomy general name', 'wp-recipe-maker' ),
				'singular_name'      => _x( 'Cuisine', 'taxonomy singular name', 'wp-recipe-maker' ),
			),
			'wprm_suitablefordiet' => array(
				'name'               => _x( 'Diets', 'taxonomy general name', 'wp-recipe-maker' ),
				'singular_name'      => _x( 'Diet', 'taxonomy singular name', 'wp-recipe-maker' ),
			),
			'wprm_keyword' => array(
				'name'               => _x( 'Keywords', 'taxonomy general name', 'wp-recipe-maker' ),
				'singular_name'      => _x( 'Keyword', 'taxonomy singular name', 'wp-recipe-maker' ),
			),
			'wprm_ingredient' => array(
				'name'               => _x( 'Ingredients', 'taxonomy general name', 'wp-recipe-maker' ),
				'singular_name'      => _x( 'Ingredient', 'taxonomy singular name', 'wp-recipe-maker' ),
			),
			'wprm_ingredient_unit' => array(
				'name'               => _x( 'Ingredient Units', 'taxonomy general name', 'wp-recipe-maker' ),
				'singular_name'      => _x( 'Ingredient Unit', 'taxonomy singular name', 'wp-recipe-maker' ),
			),
			'wprm_equipment' => array(
				'name'               => _x( 'Equipment', 'taxonomy general name', 'wp-recipe-maker' ),
				'singular_name'      => _x( 'Equipment', 'taxonomy singular name', 'wp-recipe-maker' ),
			),
		);
		$taxonomies = $default_taxonomies;

		// Check for any adjusted values.
		$custom_taxonomies = get_option( 'wprm_custom_taxonomies', array() );
		foreach ( $custom_taxonomies as $key => $options ) {
			if ( array_key_exists( $key, $taxonomies ) ) {
				$taxonomies[ $key ] = array_merge( $taxonomies[ $key ], $custom_taxonomies[ $key ] );
			}
		}

		// Allow custom taxonomies to get added.
		$taxonomies = apply_filters( 'wprm_recipe_taxonomies', $taxonomies );

		// Remove suitablefordiet if disabled in the settings.
		if ( false === WPRM_Settings::get( 'metadata_suitablefordiet' ) ) {
			unset( $taxonomies['wprm_suitablefordiet'] );
		}

		// Set default values for options.
		foreach ( $taxonomies as $key => $options ) {
			$taxonomies[ $key ]['default'] = array_key_exists( $key, $default_taxonomies );
			$taxonomies[ $key ]['key'] = substr( $key, 5 );
			$taxonomies[ $key ]['slug'] = isset( $options['slug'] ) && $options['slug'] ? $options['slug'] : $taxonomies[ $key ]['key'];
			$taxonomies[ $key ]['archive'] = isset( $options['archive'] ) ? $options['archive'] : false;
		}

		return $taxonomies;
	}

	/**
	 * Get the recipe taxonomies.
	 *
	 * @since    1.10.0
	 * @param    boolean $include_internal Wether to include taxonomies used internally.
	 */
	public static function get_taxonomies( $include_internal = false ) {
		$taxonomies = self::get_taxonomies_to_register();
		if ( ! $include_internal ) {
			unset( $taxonomies['wprm_ingredient'] );
			unset( $taxonomies['wprm_ingredient_unit'] );
			unset( $taxonomies['wprm_equipment'] );
		}

		return $taxonomies;
	}

	/**
	 * Check if a recipe taxonomy exists.
	 *
	 * @since    1.13.0
	 * @param    mixed $taxonomy Name of the taxonomy to check.
	 */
	public static function exists( $taxonomy ) {
		$taxonomies = self::get_taxonomies_to_register();
		return array_key_exists( $taxonomy, $taxonomies );
	}

	/**
	 * Check if a recipe taxonomy has archive pages.
	 *
	 * @since    7.1.0
	 * @param    mixed $taxonomy Name of the taxonomy to check.
	 */
	public static function has_archive_pages( $taxonomy ) {
		$taxonomies = self::get_taxonomies_to_register();

		if ( isset( $taxonomies[ $taxonomy ] ) && isset( $taxonomies[ $taxonomy ]['archive'] ) && $taxonomies[ $taxonomy ]['archive'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Insert default terms for recipe taxonomies.
	 *
	 * @since    1.0.0
	 */
	public static function insert_default_taxonomy_terms() {
		if ( taxonomy_exists( 'wprm_course' ) ) {
			wp_insert_term( __( 'Breakfast',    'wp-recipe-maker' ), 'wprm_course' );
			wp_insert_term( __( 'Appetizer',    'wp-recipe-maker' ), 'wprm_course' );
			wp_insert_term( __( 'Soup',         'wp-recipe-maker' ), 'wprm_course' );
			wp_insert_term( __( 'Main Course',  'wp-recipe-maker' ), 'wprm_course' );
			wp_insert_term( __( 'Side Dish',    'wp-recipe-maker' ), 'wprm_course' );
			wp_insert_term( __( 'Salad',        'wp-recipe-maker' ), 'wprm_course' );
			wp_insert_term( __( 'Dessert',      'wp-recipe-maker' ), 'wprm_course' );
			wp_insert_term( __( 'Snack',        'wp-recipe-maker' ), 'wprm_course' );
			wp_insert_term( __( 'Drinks',       'wp-recipe-maker' ), 'wprm_course' );
		}

		if ( taxonomy_exists( 'wprm_cuisine' ) ) {
			wp_insert_term( __( 'French',           'wp-recipe-maker' ), 'wprm_cuisine' );
			wp_insert_term( __( 'Italian',          'wp-recipe-maker' ), 'wprm_cuisine' );
			wp_insert_term( __( 'Mediterranean',    'wp-recipe-maker' ), 'wprm_cuisine' );
			wp_insert_term( __( 'Indian',           'wp-recipe-maker' ), 'wprm_cuisine' );
			wp_insert_term( __( 'Chinese',          'wp-recipe-maker' ), 'wprm_cuisine' );
			wp_insert_term( __( 'Japanese',         'wp-recipe-maker' ), 'wprm_cuisine' );
			wp_insert_term( __( 'American',         'wp-recipe-maker' ), 'wprm_cuisine' );
			wp_insert_term( __( 'Mexican',          'wp-recipe-maker' ), 'wprm_cuisine' );
		}

		self::check_diet_taxonomy_terms();
	}

	/**
	 * Check diet taxonomy terms.
	 *
	 * @since    5.9.0
	 */
	public static function check_diet_taxonomy_terms() {
		if ( taxonomy_exists( 'wprm_suitablefordiet' ) ) {
			$terms = array(
				'DiabeticDiet' 		=> __( 'Diabetic', 'wp-recipe-maker' ),
				'GlutenFreeDiet'	=> __( 'Gluten Free', 'wp-recipe-maker' ),
				'HalalDiet'			=> __( 'Halal', 'wp-recipe-maker' ),
				'HinduDiet'			=> __( 'Hindu', 'wp-recipe-maker' ),
				'KosherDiet'		=> __( 'Kosher', 'wp-recipe-maker' ),
				'LowCalorieDiet'	=> __( 'Low Calorie', 'wp-recipe-maker' ),
				'LowFatDiet'		=> __( 'Low Fat', 'wp-recipe-maker' ),
				'LowLactoseDiet'	=> __( 'Low Lactose', 'wp-recipe-maker' ),
				'LowSaltDiet'		=> __( 'Low Salt', 'wp-recipe-maker' ),
				'VeganDiet'			=> __( 'Vegan', 'wp-recipe-maker' ),
				'VegetarianDiet'	=> __( 'Vegetarian', 'wp-recipe-maker' ),
			);

			if ( count( array_keys( $terms ) ) !== wp_count_terms( 'wprm_suitablefordiet', array( 'hide_empty' => false ) ) ) {
				foreach ( $terms as $term => $label ) {
					$existing_term = term_exists( $term, 'wprm_suitablefordiet' );
					if ( $existing_term ) {
						$existing_term_id = $existing_term['term_id'];

						if ( $existing_term_id ) {
							$existing_term_label = get_term_meta( $existing_term_id, 'wprm_term_label', true );

							if ( ! $existing_term_label ) {
								update_term_meta( $existing_term_id, 'wprm_term_label', $label );
							}
						}
					} else {
					  	wp_insert_term( $term, 'wprm_suitablefordiet' );
					}
				}
			}
		}
	}

	/**
	 * Make taxonomies publically accessible.
	 *
	 * @since    7.1.0
	 * @param    WP_Query $query Current query.
	 */
	public static function taxonomies_archive( $query ) {
		if ( ! is_admin() && $query->is_main_query() ) {
			if ( is_tax() ) {
				$taxonomies = array_keys( $query->tax_query->queried_terms );

				if ( 1 === count( $taxonomies ) ) {
					$taxonomy = $taxonomies[0];

					if ( 'wprm_' === substr( $taxonomy, 0, 5 ) ) {
						if ( ! isset( $query->query_vars->post_type ) ) {
							$query->set( 'post_type', array( WPRM_POST_TYPE ) );
						}
					}
				}
			}
		}
	}

	/**
	 * Change content that gets output on archive pages.
	 *
	 * @since    7.1.0
	 * @param	mixed $content Current content output.
	 */
	public static function archive_content( $content ) {
		if ( WPRM_POST_TYPE === get_post_type() ) {		
			// Singular recipe page.
			if ( is_singular( WPRM_POST_TYPE ) ) {
				if ( post_password_required() ) {
					return get_the_password_form();
				} else {
					return do_shortcode( '<p>[wprm-recipe id="' . esc_attr( get_the_ID() ). '"]</p>' );
				}
			}

			// Archive page.
			if ( is_archive() && WPRM_Settings::get( 'post_type_archive_handle_output' ) ) {
				$template = WPRM_Settings::get( 'post_type_archive_output_template' );
				return do_shortcode( '<p>[wprm-recipe id="' . esc_attr( get_the_ID() ). '" template="' . esc_attr( $template ) . '"]</p>' );
			}
		}
		return $content;
	}

	/**
	 * Add Archive page links for terms.
	 *
	 * @since	7.1.0
	 * @param	mixed $output Current output.
	 * @param	array $term	  Term we're outputting.
	 */
	public static function archive_link( $output, $term ) {
		$term_id = false;

		if ( is_object( $term ) && isset( $term->term_id ) ) {
			$term_id = intval( $term->term_id );
		} elseif ( is_array( $term ) && isset( $term['id'] ) ) {
			$term_id = intval( $term['id'] );
			$term = get_term( $term_id );
		}

		if ( $term_id && is_object( $term ) && ! is_wp_error( $term ) ) {
			$taxonomy = $term->taxonomy;

			$should_add_link = false;

			// Check settings if we should add a link.
			switch ( $taxonomy ) {
				case 'wprm_ingredient';
					$should_add_link = WPRM_Settings::get( 'ingredient_links_archive_pages' );
					break;
				case 'wprm_equipment';
					$should_add_link = WPRM_Settings::get( 'equipment_links_archive_pages' );
					break;
				default:
					$should_add_link = WPRM_Settings::get( 'term_links_archive_pages' );
			}
			
			// Don't link if there already is one.
			if ( false !== stripos( $output, '<a href' ) ) {
				$should_add_link = false;
			}

			// Check if this taxonomy has archive pages.
			if ( $should_add_link && self::has_archive_pages( $taxonomy ) ) {
				$link = get_term_link( $term, $taxonomy );
				 return '<a href="' . $link . '">' . $output . '</a>';
			}
		}

		return $output;
	}
}

WPRM_Taxonomies::init();
