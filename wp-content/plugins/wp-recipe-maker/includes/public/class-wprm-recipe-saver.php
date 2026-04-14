<?php
/**
 * Responsible for saving recipes.
 *
 * @link       https://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for saving recipes.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Recipe_Saver {

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'save_post', array( __CLASS__, 'update_post' ), 10, 2 );
		add_action( 'admin_init', array( __CLASS__, 'update_recipes_check' ) );

		add_filter( 'wp_insert_post_data', array( __CLASS__, 'post_type_switcher_fix' ), 20, 2 );
	}

	/**
	 * Create a new recipe.
	 *
	 * @since    1.0.0
	 * @param		 array $recipe Recipe fields to save.
	 */
	public static function create_recipe( $recipe ) {
		$post = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => 'draft',
		);

		$recipe_id = wp_insert_post( $post );
		WPRM_Recipe_Saver::update_recipe( $recipe_id, $recipe, true );

		WPRM_Changelog::log( 'recipe_created', $recipe_id );

		return $recipe_id;
	}

	/**
	 * Save recipe fields.
	 *
	 * @since	1.0.0
	 * @param	int		$id Post ID of the recipe.
	 * @param	array	$recipe Recipe fields to save.
	 * @param	boolean $ignore_log Whether this edit should be ignored for the changelog.
	 */
	public static function update_recipe( $id, $recipe, $ignore_log = false ) {
		$meta = array();

		// Featured Image.
		if ( isset( $recipe['image_id'] ) ) {
			if ( $recipe['image_id'] ) {
				set_post_thumbnail( $id, $recipe['image_id'] );
			} else {
				delete_post_thumbnail( $id );
			}
		}

		// Make sure language is set first, before setting taxonomies.
		$post_type_structure = WPRM_Settings::get( 'post_type_structure' );
		$language_is_set = array_key_exists( 'language', $recipe );

		if ( 'public' === $post_type_structure ) {
			if ( $language_is_set ) {
				WPRM_Compatibility::set_language_for( $id, $recipe['language'] );
			}
		} else {
			if ( $language_is_set ) {
				WPRM_Compatibility::set_language_for( $id, $recipe['language'] );
			} else {
				// Not manually setting the language, so default to the current admin language.
				$admin_language = WPRM_Compatibility::get_current_admin_language();
				if ( $admin_language ) {
					WPRM_Compatibility::set_language_for( $id, $admin_language );
				}
			}
		}

		// Recipe Taxonomies.
		if ( isset( $recipe['tags'] ) ) {
			$taxonomies = WPRM_Taxonomies::get_taxonomies();

			$term_language = false;
			if ( isset( $recipe['language'] ) && $recipe['language'] ) {
				$term_language = $recipe['language'];
			} else {
				$term_language = WPRM_Compatibility::get_language_for( $id );
			}

			if ( $term_language ) {
				WPRM_Compatibility::set_new_term_language_context( $term_language );
			}

			foreach ( $taxonomies as $taxonomy => $options ) {
				$key = substr( $taxonomy, 5 ); // Get rid of wprm_.
				$terms = isset( $recipe['tags'][ $key ] ) ? $recipe['tags'][ $key ] : array();
				$terms = is_array( $terms ) ? $terms : array( $terms );
				$terms = array_map( array( 'WPRM_Recipe_Sanitizer', 'sanitize_tags' ), $terms );

				wp_set_object_terms( $id, $terms, $taxonomy, false );
			}

			WPRM_Compatibility::clear_new_term_language_context();
		}

		// Recipe Equipment.
		if ( isset( $recipe['equipment'] ) ) {
			$equipment_ids = array();
			foreach ( $recipe['equipment'] as $equipment ) {
				$equipment_ids[] = intval( $equipment['id'] );
			}
			$equipment_ids = array_unique( $equipment_ids );

			$meta['wprm_equipment'] = $recipe['equipment'];
			wp_set_object_terms( $id, $equipment_ids, 'wprm_equipment', false );
		}

		// Recipe Ingredients.
		if ( isset( $recipe['ingredients'] ) ) {
			$ingredient_ids = array();
			$unit_ids = array();

			foreach ( $recipe['ingredients'] as $ingredient_group ) {
				foreach ( $ingredient_group['ingredients'] as $ingredient ) {
					$ingredient_ids[] = intval( $ingredient['id'] );
					
					// Unit ID from regular ingredient.
					if ( isset( $ingredient['unit_id'] ) && $ingredient['unit_id'] ) {
						$unit_ids[] = intval( $ingredient['unit_id'] );
					}

					// Unit ID from converted ingredients.
					if ( isset( $ingredient['converted'] ) ) {
						foreach ( $ingredient['converted'] as $system => $conversion ) {
							if ( isset( $ingredient['converted'][ $system ]['unit_id'] ) && $ingredient['converted'][ $system ]['unit_id'] ) {
								$unit_ids[] = intval( $ingredient['converted'][ $system ]['unit_id'] );
							}
						}
					}
				}
			}
			$ingredient_ids = array_unique( $ingredient_ids );
			$unit_ids = array_unique( $unit_ids );

			$meta['wprm_ingredients'] = $recipe['ingredients'];
			wp_set_object_terms( $id, $ingredient_ids, 'wprm_ingredient', false );
			wp_set_object_terms( $id, $unit_ids, 'wprm_ingredient_unit', false );
		}

		// Video fields (always clear metadata).
		$meta['wprm_video_metadata'] = '';
		if ( isset( $recipe['video_id'] ) )	{
			$meta['wprm_video_id'] = $recipe['video_id'];
		}
		if ( isset( $recipe['video_embed'] ) ) {
			$meta['wprm_video_embed'] = $recipe['video_embed'];
		}

		// Nutrition fields.
		if ( isset( $recipe['nutrition'] ) ) {
			foreach ( $recipe['nutrition'] as $nutrient => $value ) {
				$meta[ 'wprm_nutrition_' . $nutrient ] = $value;
			}
		}

		// Meta Fields.
		if ( isset( $recipe['type'] ) )							{ $meta['wprm_type'] = $recipe['type']; }
		if ( isset( $recipe['pin_image_id'] ) )					{ $meta['wprm_pin_image_id'] = $recipe['pin_image_id']; }
		if ( isset( $recipe['pin_image_repin_id'] ) )			{ $meta['wprm_pin_image_repin_id'] = $recipe['pin_image_repin_id']; }
		if ( isset( $recipe['author_display'] ) )				{ $meta['wprm_author_display'] = $recipe['author_display']; }
		if ( isset( $recipe['author_name'] ) )					{ $meta['wprm_author_name'] = $recipe['author_name']; }
		if ( isset( $recipe['author_link'] ) )					{ $meta['wprm_author_link'] = $recipe['author_link']; }
		if ( isset( $recipe['author_bio'] ) )					{ $meta['wprm_author_bio'] = $recipe['author_bio']; }
		if ( isset( $recipe['servings'] ) )						{ $meta['wprm_servings'] = $recipe['servings']; }
		if ( isset( $recipe['servings_unit'] ) )				{ $meta['wprm_servings_unit'] = $recipe['servings_unit']; }
		if ( isset( $recipe['servings_advanced_enabled'] ) )	{ $meta['wprm_servings_advanced_enabled'] = $recipe['servings_advanced_enabled']; }
		if ( isset( $recipe['servings_advanced'] ) )			{ $meta['wprm_servings_advanced'] = $recipe['servings_advanced']; }
		if ( isset( $recipe['cost'] ) )							{ $meta['wprm_cost'] = $recipe['cost']; }
		if ( isset( $recipe['prep_time'] ) )					{ $meta['wprm_prep_time'] = $recipe['prep_time']; }
		if ( isset( $recipe['prep_time_zero'] ) )				{ $meta['wprm_prep_time_zero'] = $recipe['prep_time_zero']; }
		if ( isset( $recipe['cook_time'] ) )					{ $meta['wprm_cook_time'] = $recipe['cook_time']; }
		if ( isset( $recipe['cook_time_zero'] ) )				{ $meta['wprm_cook_time_zero'] = $recipe['cook_time_zero']; }
		if ( isset( $recipe['total_time'] ) )					{ $meta['wprm_total_time'] = $recipe['total_time']; }
		if ( isset( $recipe['custom_time'] ) )					{ $meta['wprm_custom_time'] = $recipe['custom_time']; }
		if ( isset( $recipe['custom_time_zero'] ) )				{ $meta['wprm_custom_time_zero'] = $recipe['custom_time_zero']; }
		if ( isset( $recipe['custom_time_label'] ) )			{ $meta['wprm_custom_time_label'] = $recipe['custom_time_label']; }
		if ( isset( $recipe['instructions'] ) )					{ $meta['wprm_instructions'] = $recipe['instructions']; }
		if ( isset( $recipe['notes'] ) )						{ $meta['wprm_notes'] = $recipe['notes']; }
		if ( isset( $recipe['ingredient_links_type'] ) )		{ $meta['wprm_ingredient_links_type'] = $recipe['ingredient_links_type']; }
		if ( isset( $recipe['unit_system'] ) )					{ $meta['wprm_unit_system'] = $recipe['unit_system']; }
		if ( isset( $recipe['import_source'] ) ) 				{ $meta['wprm_import_source'] = $recipe['import_source']; }
		if ( isset( $recipe['import_backup'] ) ) 				{ $meta['wprm_import_backup'] = $recipe['import_backup']; }

		$meta = apply_filters( 'wprm_recipe_save_meta', $meta, $id, $recipe );

		// Store version number.
		$meta['wprm_version'] = WPRM_Version::convert_to_number();

		// Post Fields.
		$post = array(
			'ID' => $id,
			'meta_input' => $meta,
		);

		if ( isset( $recipe['name'] ) ) {
			$post['post_title'] = $recipe['name'];

			$post_name_prefix = 'public' === WPRM_Settings::get( 'post_type_structure' ) ? '' : 'wprm-';
			$post['post_name'] = $post_name_prefix . sanitize_title( $recipe['name'] );
		}

		if ( isset( $recipe['summary'] ) ) {
			$post['post_content'] = wp_slash( $recipe['summary'] );
		}

		// Only for "public" recipe type or when manually setting author.
		if ( 'public' === WPRM_Settings::get( 'post_type_structure' ) || 'manual' === WPRM_Settings::get( 'recipe_use_author' ) ) {
			if ( isset( $recipe['post_author'] ) && $recipe['post_author'] ) {
				$post['post_author'] = $recipe['post_author'];
			}
		}

		// Only for "public" recipe type.
		if ( 'public' === WPRM_Settings::get( 'post_type_structure' ) ) {
			if ( isset( $recipe['slug'] ) && $recipe['slug'] ) {
				$post['post_name'] = $recipe['slug'];
			}

			if ( isset( $recipe['date'] ) && $recipe['date'] ) {
				$post['post_date'] = $recipe['date'];
				$post['post_date_gmt'] = get_gmt_from_date( $recipe['date'] );

				// If date is in the future, post status should be future.
				$post_date_timestamp = strtotime( $post['post_date'] );
				if ( $post_date_timestamp && time() < $post_date_timestamp ) {
					$recipe['post_status'] = 'future';
				}
			}
	
			if ( isset( $recipe['post_status'] ) && $recipe['post_status'] ) {
				$post['post_status'] = $recipe['post_status'];

				// Need to update post date if publishing and post date is still set to the future (scheduled).
				if ( 'publish' === $post['post_status'] ) {
					$post_date_timestamp = isset( $post['post_date'] ) ? strtotime( $post['post_date'] ) : get_the_date( 'U', $id );
					$current_timestamp = time();

					if ( $post_date_timestamp && $current_timestamp < $post_date_timestamp ) {
						$post['post_date'] = date( 'Y-m-d H:i:s',  $current_timestamp );
						$post['post_date_gmt'] = gmdate( 'Y-m-d H:i:s',  $current_timestamp );
					}
				}
			} else {
				if ( isset( $recipe['name'] ) ) {
					// Don't use wprm- in front by default for "public" recipe type.
					$post['post_name'] = sanitize_title( $recipe['name'] );
				}
			}

			if ( isset( $recipe['post_password'] ) ) {
				$post['post_password'] = $recipe['post_password'];
			}
		}

		// Always update post to make sure revision gets made.
		WPRM_Recipe_Manager::invalidate_recipe( $id );
		wp_update_post( $post );

		// Keep translated taxonomy terms in sync with the recipe language.
		$term_language = $language_is_set && isset( $recipe['language'] ) ? $recipe['language'] : WPRM_Compatibility::get_language_for( $id );
		self::sync_recipe_term_languages( $id, $term_language );

		if ( ! $ignore_log ) {
			WPRM_Changelog::log( 'recipe_edited', $id );
		}

		// Update recipe SEO values afterwards.
		WPRM_Seo_Checker::update_seo_for( $id );

		// Remove cached Instacart data.
		delete_post_meta( $id, 'wprm_instacart_combinations' );
	}

	/**
	 * Sync recipe taxonomy terms to translated terms for a specific language.
	 *
	 * Non-destructive: when no translated term exists, the original term remains assigned.
	 *
	 * @since	10.4.0
	 * @param	int          $recipe_id Recipe ID to sync terms for.
	 * @param	false|string $language  Language code to sync to.
	 */
	public static function sync_recipe_term_languages( $recipe_id, $language = false ) {
		$recipe_id = intval( $recipe_id );

		if ( ! $recipe_id ) {
			return;
		}

		if ( ! $language ) {
			$language = WPRM_Compatibility::get_language_for( $recipe_id );
		}

		if ( ! $language ) {
			return;
		}

		$taxonomies = WPRM_Taxonomies::get_taxonomies();

		foreach ( array_keys( $taxonomies ) as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$term_ids = self::get_assigned_term_ids_from_database( $recipe_id, $taxonomy );

			if ( empty( $term_ids ) ) {
				continue;
			}

			$current_term_ids = $term_ids;
			$new_term_ids = array();
			$translated_term_replacements = array();

			foreach ( $term_ids as $term_id ) {
				$term_id = intval( $term_id );
				$translated_term_id = WPRM_Compatibility::get_translated_term_for_language( $term_id, $taxonomy, $language );

				if ( $translated_term_id && $translated_term_id !== $term_id ) {
					$new_term_ids[] = $translated_term_id;
					$translated_term_replacements[ $term_id ] = $translated_term_id;
				} else {
					$new_term_ids[] = $term_id;
				}
			}

			$new_term_ids = array_values( array_unique( $new_term_ids ) );
			sort( $current_term_ids );
			sort( $new_term_ids );

			if ( $current_term_ids !== $new_term_ids && ! empty( $translated_term_replacements ) ) {
				self::replace_translated_term_relationships( $recipe_id, $taxonomy, $translated_term_replacements );
			}
		}
	}

	/**
	 * Get assigned term IDs directly from the database.
	 *
	 * @since	10.8.1
	 * @param	int    $recipe_id Recipe ID.
	 * @param	string $taxonomy  Taxonomy to inspect.
	 */
	private static function get_assigned_term_ids_from_database( $recipe_id, $taxonomy ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT DISTINCT tt.term_id
			FROM {$wpdb->term_relationships} tr
			INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE tr.object_id = %d AND tt.taxonomy = %s
			ORDER BY tt.term_id ASC",
			$recipe_id,
			$taxonomy
		);

		$term_ids = $wpdb->get_col( $query );

		return is_array( $term_ids ) ? array_values( array_map( 'intval', $term_ids ) ) : array();
	}

	/**
	 * Replace translated term relationships without clearing unrelated assignments.
	 *
	 * This uses direct relationship updates because multilingual plugins can hide
	 * some term links from the standard replacement flow, which risks leaving old
	 * translated terms behind or wiping untranslated fallback terms.
	 *
	 * @since	10.8.1
	 * @param	int    $recipe_id    Recipe ID.
	 * @param	string $taxonomy     Taxonomy to update.
	 * @param	array  $replacements Map of source term IDs to translated term IDs.
	 */
	private static function replace_translated_term_relationships( $recipe_id, $taxonomy, $replacements ) {
		global $wpdb;

		if ( ! $recipe_id || ! $taxonomy || ! is_array( $replacements ) || empty( $replacements ) ) {
			return;
		}

		$current_relationships = self::get_assigned_term_taxonomy_ids_from_database( $recipe_id, $taxonomy );
		if ( empty( $current_relationships ) ) {
			return;
		}

		$translated_term_ids = array_values( array_unique( array_map( 'intval', $replacements ) ) );
		$translated_term_taxonomy_ids = self::get_term_taxonomy_ids_from_database( $translated_term_ids, $taxonomy );

		$term_taxonomy_ids_to_remove = array();
		$term_taxonomy_ids_to_add = array();

		foreach ( $replacements as $source_term_id => $translated_term_id ) {
			$source_term_id = intval( $source_term_id );
			$translated_term_id = intval( $translated_term_id );

			if ( ! isset( $current_relationships[ $source_term_id ] ) || ! isset( $translated_term_taxonomy_ids[ $translated_term_id ] ) ) {
				continue;
			}

			$term_taxonomy_ids_to_remove[] = intval( $current_relationships[ $source_term_id ] );
			$term_taxonomy_ids_to_add[] = intval( $translated_term_taxonomy_ids[ $translated_term_id ] );
		}

		$term_taxonomy_ids_to_remove = array_values( array_unique( array_filter( $term_taxonomy_ids_to_remove ) ) );
		$term_taxonomy_ids_to_add = array_values( array_unique( array_filter( $term_taxonomy_ids_to_add ) ) );

		if ( empty( $term_taxonomy_ids_to_remove ) && empty( $term_taxonomy_ids_to_add ) ) {
			return;
		}

		if ( ! empty( $term_taxonomy_ids_to_remove ) ) {
			$delete_placeholders = implode( ', ', array_fill( 0, count( $term_taxonomy_ids_to_remove ), '%d' ) );
			$delete_arguments = array_merge(
				array( $recipe_id ),
				$term_taxonomy_ids_to_remove
			);

			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->term_relationships}
					WHERE object_id = %d AND term_taxonomy_id IN ({$delete_placeholders})",
					$delete_arguments
				)
			);
		}

		if ( ! empty( $term_taxonomy_ids_to_add ) ) {
			foreach ( $term_taxonomy_ids_to_add as $term_taxonomy_id ) {
				$wpdb->replace(
					$wpdb->term_relationships,
					array(
						'object_id'        => $recipe_id,
						'term_taxonomy_id' => $term_taxonomy_id,
					),
					array(
						'%d',
						'%d',
					)
				);
			}
		}

		$affected_term_taxonomy_ids = array_values( array_unique( array_merge( $term_taxonomy_ids_to_remove, $term_taxonomy_ids_to_add ) ) );
		if ( ! empty( $affected_term_taxonomy_ids ) ) {
			wp_update_term_count( $affected_term_taxonomy_ids, $taxonomy );
		}

		clean_object_term_cache( $recipe_id, WPRM_POST_TYPE );
	}

	/**
	 * Get assigned term taxonomy IDs directly from the database.
	 *
	 * @since	10.8.1
	 * @param	int    $recipe_id Recipe ID.
	 * @param	string $taxonomy  Taxonomy to inspect.
	 */
	private static function get_assigned_term_taxonomy_ids_from_database( $recipe_id, $taxonomy ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT DISTINCT tt.term_id, tt.term_taxonomy_id
			FROM {$wpdb->term_relationships} tr
			INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE tr.object_id = %d AND tt.taxonomy = %s",
			$recipe_id,
			$taxonomy
		);

		$rows = $wpdb->get_results( $query );
		$relationships = array();

		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$relationships[ intval( $row->term_id ) ] = intval( $row->term_taxonomy_id );
			}
		}

		return $relationships;
	}

	/**
	 * Get term taxonomy IDs for specific term IDs directly from the database.
	 *
	 * @since	10.8.1
	 * @param	array  $term_ids  Term IDs to look up.
	 * @param	string $taxonomy  Taxonomy to inspect.
	 */
	private static function get_term_taxonomy_ids_from_database( $term_ids, $taxonomy ) {
		global $wpdb;

		$term_ids = array_values( array_unique( array_filter( array_map( 'intval', $term_ids ) ) ) );
		if ( empty( $term_ids ) || ! $taxonomy ) {
			return array();
		}

		$placeholders = implode( ', ', array_fill( 0, count( $term_ids ), '%d' ) );
		$arguments = array_merge( array( $taxonomy ), $term_ids );
		$query = $wpdb->prepare(
			"SELECT term_id, term_taxonomy_id
			FROM {$wpdb->term_taxonomy}
			WHERE taxonomy = %s AND term_id IN ({$placeholders})",
			$arguments
		);

		$rows = $wpdb->get_results( $query );
		$term_taxonomy_ids = array();

		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$term_taxonomy_ids[ intval( $row->term_id ) ] = intval( $row->term_taxonomy_id );
			}
		}

		return $term_taxonomy_ids;
	}

	/**
	 * Check if post being saved contains recipes we need to update.
	 *
	 * @since    1.0.0
	 * @param		 int    $id Post ID being saved.
	 * @param		 object $post Post being saved.
	 */
	public static function update_post( $id, $post ) {
		// Use parent post if we're currently updating a revision.
		$revision_parent = wp_is_post_revision( $post );
		if ( $revision_parent ) {
			$post = get_post( $revision_parent );
		}

		$recipe_ids = WPRM_Recipe_Manager::get_recipe_ids_from_post( $post->ID, true );
		$recipe_ids = false === $recipe_ids ? array() : $recipe_ids;

		// Make sure post itself is not included.
		if ( in_array( $post->ID, $recipe_ids ) ) {
			$recipe_ids = array_diff( $recipe_ids, array( $post->ID ) );
		}

		if ( count( $recipe_ids ) > 0 ) {
			// Immediately update when importing, otherwise do on next load to prevent issues with other plugins.
			if ( isset( $_POST['importer_uid'] ) || ( isset( $_POST['action'] ) && 'wprm_finding_parents' === $_POST['action'] ) ) { // Input var okay.
				self::update_recipes_in_post( $post->ID, $recipe_ids );
			} else {
				$post_recipes_to_update = get_option( 'wprm_post_recipes_to_update', array() );
				$post_recipes_to_update[ $post->ID ] = $recipe_ids;
				update_option( 'wprm_post_recipes_to_update', $post_recipes_to_update );
			}
		}

		if ( WPRM_POST_TYPE === $post->post_type ) {
			// Maybe clear cache of parent post.
			$parent_post_id = get_post_meta( $id, 'wprm_parent_post_id', true );
			$parent_post_id = $parent_post_id ? intval( $parent_post_id ) : false;

			if ( $parent_post_id && $parent_post_id !== $post->ID ) {
				WPRM_Cache::clear( $parent_post_id, false );
			}
		}

		// Fix recipes that have this post as a parent post when they aren't actually inside anymore.
		$args = array(
			'post_type' => WPRM_POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key'     => 'wprm_parent_post_id',
					'compare' => '=',
					'value' => $id,
				),
			),
			'fields' => 'ids',
		);

		$query = new WP_Query( $args );
		
		if ( $query->have_posts() ) {
			$ids = $query->posts;

			foreach ( $ids as $id ) {
				if ( ! in_array( $id, $recipe_ids ) ) {
					delete_post_meta( $id, 'wprm_parent_post_id' );
				}
			}
		}
	}

	/**
	 * Check if post being saved contains recipes we need to update.
	 *
	 * @since    1.19.0
	 */
	public static function update_recipes_check() {
		if ( ! isset( $_POST['action'] ) ) {
			$post_recipes_to_update = get_option( 'wprm_post_recipes_to_update', array() );

			if ( ! empty( $post_recipes_to_update ) ) {
				$i = 0;
				while ( $i < 10 && ! empty( $post_recipes_to_update ) ) {
					// Get first post to update the recipes for.
					$recipe_ids = reset( $post_recipes_to_update );
					$post_id = key( $post_recipes_to_update );

					self::update_recipes_in_post( $post_id, $recipe_ids );

					// Update remaing post/recipes to update.
					unset( $post_recipes_to_update[ $post_id ] );
					$i++;
				}

				update_option( 'wprm_post_recipes_to_update', $post_recipes_to_update );
			}
		}
	}

	/**
	 * Update recipes with post data.
	 *
	 * @since    1.20.0
	 * @param	 mixed $post_id    Post to use the data from.
	 * @param	 array $recipe_ids Recipes to update.
	 */
	public static function update_recipes_in_post( $post_id, $recipe_ids ) {
		$post = get_post( $post_id );

		// Can happen when revision was scheduled and already removed.
		if ( ! $post ) {
			return;
		}

		// Skip Revisionize revisions.
		$revisionize = get_post_meta( $post_id, '_post_revision_of', true );
		if ( $revisionize && is_plugin_active( 'revisionize/revisionize.php' ) && get_post_status( $revisionize ) && intval( $revisionize ) !== $post_id ) {
			return;
		}

		// Skip Revision Manager TMC revisions.
		$rm_tmc = get_post_meta( $post_id, 'linked_post_id', true );
		if ( $rm_tmc && is_plugin_active( 'revision-manager-tmc/revision-manager-tmc.php' ) && get_post_status( $rm_tmc ) && intval( $rm_tmc ) !== $post_id ) {
			return;
		}

		// Skip Revisionary / PublishPress revisions.
		$revisionary = get_post_meta( $post_id, '_rvy_base_post_id', true );
		if ( $revisionary && is_plugin_active( 'revisionary/revisionary.php' ) && get_post_status( $revisionary ) && intval( $revisionary ) !== $post_id ) {
			return;
		}

		// Skip Yoast Duplicate Posts Rewrite.
		$yoast_dp = get_post_meta( $post_id, '_dp_is_rewrite_republish_copy', true );
		if ( $yoast_dp && is_plugin_active( 'duplicate-post/duplicate-post.php' ) ) {
			return;
		}

		if ( 'trash' !== $post->post_status ) {
			$categories = get_the_terms( $post, 'category' );
			$cat_ids = ! $categories || is_wp_error( $categories ) ? array() : wp_list_pluck( $categories, 'term_id' );

			// Don't use pending for recipe as we use that for Recipe Submissions.
			$recipe_post_status = 'pending' === $post->post_status ? 'draft' : $post->post_status;

			// Prevent recipes from taking over custom post statusses (and being excluded from the manage page).
			$allowed_post_statusses = array( 'publish', 'future', 'draft', 'private' );
			
			if ( ! in_array( $recipe_post_status, $allowed_post_statusses ) ) {
				$recipe_post_status = 'draft';
			}

			// Get language for this post.
			$parent_language = WPRM_Compatibility::get_language_for( $post_id );

			// Update recipes.
			foreach ( $recipe_ids as $recipe_id ) {
				// Prevent infinite loop.
				if ( $recipe_id === $post_id ) {
					continue;
				}

				$recipe = array(
					'ID'          	=> $recipe_id,
					'post_status' 	=> $recipe_post_status,
					'post_date' 	=> $post->post_date,
					'post_date_gmt' => $post->post_date_gmt,
					'post_modified' => $post->post_modified,
					'edit_date'		=> true, // Required when going from draft to future.
				);

				if ( 'parent' === WPRM_Settings::get( 'recipe_use_author' ) ) {
					$recipe['post_author'] = $post->post_author;
				}

				// Check if parent post for this recipe should be updated.
				wp_update_post( $recipe );

				$should_update_parent_post = true;
				if ( WPRM_Settings::get( 'parent_post_autolock' ) ) {
					$current_parent_id = get_post_meta( $recipe_id, 'wprm_parent_post_id', true );

					if ( $current_parent_id && false !== get_post_type( $current_parent_id ) ) {
						// A current parent exists and is still a valid post, so do not update.
						$should_update_parent_post = false;
					}
				}

				if ( $should_update_parent_post ) {
					update_post_meta( $recipe_id, 'wprm_parent_post_id', $post_id );
				}

				// Make sure recipe language matches parent language, unless manually setting the language for public post type.
				if ( 'public' !== WPRM_Settings::get( 'post_type_structure' ) ) {
					if ( false !== $parent_language ) {
						WPRM_Compatibility::set_language_for( $recipe_id, $parent_language );
						self::sync_recipe_term_languages( $recipe_id, $parent_language );
					}
				}

				// Optionally associate categories with recipes.
				if ( is_object_in_taxonomy( WPRM_POST_TYPE, 'category' ) ) {
					wp_set_post_categories( $recipe_id, $cat_ids );
				}
			}
		} else {
			// Parent got deleted, set as draft and remove parent post relation.
			foreach ( $recipe_ids as $recipe_id ) {
				$recipe = array(
					'ID'          => $recipe_id,
					'post_status' => 'draft',
				);
				wp_update_post( $recipe );

				delete_post_meta( $recipe_id, 'wprm_parent_post_id' );
			}
		}
	}

	/**
	 * Prevent post type switcher bug from changing our recipe's post type.
	 *
	 * @since    1.4.0
	 * @param		 array $data    Data that might have been modified by Post Type Switcher.
	 * @param	   array $postarr Unmodified post data.
	 */
	public static function post_type_switcher_fix( $data, $postarr ) {
		if ( WPRM_POST_TYPE === $postarr['post_type'] ) {
			$data['post_type'] = WPRM_POST_TYPE;
		}
		return $data;
	}
}

WPRM_Recipe_Saver::init();
