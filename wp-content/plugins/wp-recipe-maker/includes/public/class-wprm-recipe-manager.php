<?php
/**
 * Responsible for returning recipes.
 *
 * @link       https://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for returning recipes.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Recipe_Manager {

	/**
	 * Recipes that have already been requested for easy subsequent access.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $recipes    Array containing recipes that have already been requested for easy access.
	 */
	private static $recipes = array();

	/**
	 * Array of posts with the recipes in them.
	 *
	 * @since    4.2.0
	 * @access   private
	 * @var      array    $posts    Array containing posts with recipes in them.
	 */
	private static $posts = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'wp_ajax_wprm_get_recipe', array( __CLASS__, 'ajax_get_recipe' ) );
		add_action( 'wp_ajax_wprm_search_recipes', array( __CLASS__, 'ajax_search_recipes' ) );
		add_action( 'wp_ajax_wprm_search_posts', array( __CLASS__, 'ajax_search_posts' ) );
		add_action( 'wp_ajax_wprm_create_post_for_recipe', array( __CLASS__, 'ajax_create_post_for_recipe' ) );
		add_action( 'wp_ajax_wprm_add_recipe_to_post', array( __CLASS__, 'ajax_add_recipe_to_post' ) );

		add_action( 'wp_footer', array( __CLASS__, 'recipe_data_in_footer' ) );
	}

	/**
	 * Get all recipes. Should generally not be used.
	 *
	 * @since    1.2.0
	 */
	public static function get_recipes() {
		$recipes = array();

		$limit = 200;
		$offset = 0;

		while ( true ) {
			$args = array(
					'post_type' => WPRM_POST_TYPE,
					'post_status' => 'any',
					'orderby' => 'date',
					'order' => 'DESC',
					'posts_per_page' => $limit,
					'offset' => $offset,
					'suppress_filters' => true,
					'lang' => '',
			);

			$query = new WP_Query( $args );

			if ( ! $query->have_posts() ) {
				break;
			}

			$posts = $query->posts;

			foreach ( $posts as $post ) {
				// Only include recipes the user can read/edit to respect WordPress access controls.
				if ( ! current_user_can( 'read_post', $post->ID ) ) {
					continue;
				}

				$recipes[ $post->ID ] = array(
					'name' => $post->post_title,
				);

				wp_cache_delete( $post->ID, 'posts' );
				wp_cache_delete( $post->ID, 'post_meta' );
			}

			$offset += $limit;
			wp_cache_flush();
		}

		return $recipes;
	}

	/**
	 * Get the x latest recipes.
	 *
	 * @since	4.0.0
	 * @param	int $limit Number of recipes to get, defaults to 10.
	 * @param	mixed $display How to display the recipes.
	 */
	public static function get_latest_recipes( $limit = 10, $display = 'name' ) {
		$recipes = array();

		$args = array(
				'post_type' => WPRM_POST_TYPE,
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
				// Only include recipes the user can read/edit to respect WordPress access controls.
				if ( ! current_user_can( 'read_post', $post->ID ) ) {
					continue;
				}

				// Special case.
				if ( 'manage' === $display ) {
					$recipe = self::get_recipe( $post );

					if ( $recipe ) {
						$recipes[] = $recipe->get_data_manage();
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

				$recipes[] = array(
					'id' =>  $post->ID,
					'text' => $text,
				);
			}
		}

		return $recipes;
	}

	/**
	 * Get latest posts.
	 *
	 * @since    9.0.0
	 * @param    int    $limit   Number of posts to get.
	 * @param    string $display Display format ('name' or 'id').
	 * @return   array  Array of posts with 'id' and 'text' keys.
	 */
	public static function get_latest_posts( $limit = 10, $display = 'name' ) {
		$posts = array();

		// Get allowed post types (exclude recipes and attachments).
		$ignore_post_types = array(
			WPRM_POST_TYPE,
			'attachment',
		);
		$public_post_types = get_post_types( array( 'public' => true ), 'names' );
		$allowed_post_types = array_diff( $public_post_types, $ignore_post_types );

		// If no allowed post types, return empty array.
		if ( empty( $allowed_post_types ) ) {
			return array();
		}

		$args = array(
			'post_type' => $allowed_post_types,
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
			$query_posts = $query->posts;
			$post_type_cache = array(); // Cache post type objects.

			foreach ( $query_posts as $post ) {
				// Only include posts the user can read/edit to respect WordPress access controls.
				if ( ! current_user_can( 'read_post', $post->ID ) ) {
					continue;
				}

				// Get post type name (cached).
				if ( ! isset( $post_type_cache[ $post->post_type ] ) ) {
					$post_type_object = get_post_type_object( $post->post_type );
					$post_type_cache[ $post->post_type ] = $post_type_object ? $post_type_object->labels->singular_name : $post->post_type;
				}
				$post_type_label = $post_type_cache[ $post->post_type ];

				switch ( $display ) {
					case 'id':
						$text = $post_type_label . ' - ' . $post->ID . ' - ' . $post->post_title;
						break;
					default:
						$text = $post->post_title;
				}

				$posts[] = array(
					'id' => $post->ID,
					'text' => $text,
				);
			}
		}

		return $posts;
	}

	/**
	 * Search for recipes by keyword.
	 *
	 * @since    1.8.0
	 */
	public static function ajax_search_recipes() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			// Require edit_posts capability to prevent unauthorized access.
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error( array(
					'message' => __( 'You do not have permission to perform this action.', 'wp-recipe-maker' ),
				) );
				wp_die();
			}

			$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // Input var okay.

			$recipes = array();
			$recipes_with_id = array();

			$args = array(
				'post_type' => WPRM_POST_TYPE,
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
						'post_type' => WPRM_POST_TYPE,
						'post_status' => 'any',
						'posts_per_page' => 100,
						'post__in' => array( $id ),
					);
	
					$query = new WP_Query( $args );
	
					$posts = array_merge( $query->posts, $posts );
				}
			}

			foreach ( $posts as $post ) {
				// Only include recipes the user can read/edit to respect WordPress access controls.
				if ( ! current_user_can( 'read_post', $post->ID ) ) {
					continue;
				}

				$recipes[] = array(
					'id' => $post->ID,
					'text' => $post->post_title,
				);

				$recipes_with_id[] = array(
					'id' => $post->ID,
					'text' => $post->ID . ' - ' . $post->post_title,
				);
			}

			wp_send_json_success( array(
				'recipes' => $recipes,
				'recipes_with_id' => $recipes_with_id,
			) );
		}

		wp_die();
	}

	/**
	 * Search for posts by keyword.
	 *
	 * @since    9.0.0
	 */
	public static function ajax_search_posts() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			// Require edit_posts capability to prevent unauthorized access.
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error( array(
					'message' => __( 'You do not have permission to perform this action.', 'wp-recipe-maker' ),
				) );
				wp_die();
			}

			$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // Input var okay.

			$found_posts = array();
			$found_posts_with_id = array();
			$seen_post_ids = array(); // Track post IDs to prevent duplicates.

			// Get allowed post types (exclude recipes and attachments).
			$ignore_post_types = array(
				WPRM_POST_TYPE,
				'attachment',
			);
			$public_post_types = get_post_types( array( 'public' => true ), 'names' );
			$allowed_post_types = array_diff( $public_post_types, $ignore_post_types );

			// If no allowed post types, return empty results.
			if ( empty( $allowed_post_types ) ) {
				wp_send_json_success( array(
					'posts' => array(),
					'posts_with_id' => array(),
				) );
				wp_die();
			}

			$args = array(
				'post_type' => $allowed_post_types,
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
						'post_type' => $allowed_post_types,
						'post_status' => 'any',
						'posts_per_page' => 100,
						'post__in' => array( $id ),
					);
	
					$query = new WP_Query( $args );
	
					// Merge and deduplicate by post ID.
					$id_posts = array();
					foreach ( $query->posts as $id_post ) {
						$id_posts[ $id_post->ID ] = $id_post;
					}
					foreach ( $posts as $post ) {
						if ( ! isset( $id_posts[ $post->ID ] ) ) {
							$id_posts[ $post->ID ] = $post;
						}
					}
					$posts = array_values( $id_posts );
				}
			}

			foreach ( $posts as $post ) {
				// Skip if we've already processed this post ID.
				if ( isset( $seen_post_ids[ $post->ID ] ) ) {
					continue;
				}

				// Only include posts the user can read/edit to respect WordPress access controls.
				if ( ! current_user_can( 'read_post', $post->ID ) ) {
					continue;
				}

				// Mark this post ID as seen.
				$seen_post_ids[ $post->ID ] = true;

				$found_posts[] = array(
					'id' => $post->ID,
					'text' => $post->post_title,
				);

				// Get post type name.
				$post_type_object = get_post_type_object( $post->post_type );
				$post_type_label = $post_type_object ? $post_type_object->labels->singular_name : $post->post_type;

				$found_posts_with_id[] = array(
					'id' => $post->ID,
					'text' => $post_type_label . ' - ' . $post->ID . ' - ' . $post->post_title,
				);
			}

			wp_send_json_success( array(
				'posts' => $found_posts,
				'posts_with_id' => $found_posts_with_id,
			) );
		}

		wp_die();
	}

	/**
	 * Get recipe data by ID through AJAX.
	 *
	 * @since    1.0.0
	 */
	public static function ajax_get_recipe() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			// Require edit_posts capability to prevent unauthorized access.
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error( array(
					'message' => __( 'You do not have permission to perform this action.', 'wp-recipe-maker' ),
				) );
				wp_die();
			}

			$recipe_id = isset( $_POST['recipe_id'] ) ? intval( $_POST['recipe_id'] ) : 0; // Input var okay.

			if ( $recipe_id > 0 ) {
				// Only return recipe data if the user can read/edit the recipe.
				if ( ! current_user_can( 'read_post', $recipe_id ) ) {
					wp_send_json_error( array(
						'message' => __( 'You do not have permission to access this recipe.', 'wp-recipe-maker' ),
					) );
					wp_die();
				}
			}

			$recipe = self::get_recipe( $recipe_id );
			$recipe_data = $recipe ? $recipe->get_data() : array();

			wp_send_json_success( array(
				'recipe' => $recipe_data,
			) );
		}

		wp_die();
	}

	/**
	 * Get user's editor preference (block or classic).
	 *
	 * @since    1.0.0
	 * @return   bool True if user prefers block editor, false for classic editor.
	 */
	private static function get_user_editor_preference() {
		$use_block_editor = true;
		
		if ( class_exists( 'Classic_Editor' ) ) {
			$user_id = get_current_user_id();
			$user_editor = get_user_meta( $user_id, 'editor', true );
			$use_block_editor = ( 'classic' !== $user_editor );
		}
		
		return $use_block_editor;
	}

	/**
	 * Determine if a post uses block editor.
	 *
	 * @since    1.0.0
	 * @param    WP_Post|int $post Post object or post ID.
	 * @return   bool True if post uses block editor, false otherwise.
	 */
	private static function post_uses_block_editor( $post ) {
		if ( function_exists( 'use_block_editor_for_post' ) ) {
			return use_block_editor_for_post( $post );
		}
		
		// Fallback: check meta if Classic Editor plugin is active
		if ( class_exists( 'Classic_Editor' ) ) {
			$post_id = is_object( $post ) ? $post->ID : absint( $post );
			$classic_remember = get_post_meta( $post_id, 'classic-editor-remember', true );
			return ( 'classic-editor' !== $classic_remember );
		}
		
		// No Classic Editor plugin, assume block editor
		return true;
	}

	/**
	 * Set editor preference meta for a post.
	 *
	 * @since    1.0.0
	 * @param    int  $post_id Post ID.
	 * @param    bool $use_block_editor Whether to use block editor.
	 * @return   string Edit link with appropriate query args.
	 */
	private static function set_post_editor_preference( $post_id, $use_block_editor ) {
		$edit_link = admin_url( add_query_arg( array(
			'post' => $post_id,
			'action' => 'edit',
		), 'post.php' ) );
		
		// Remove any existing editor preference
		delete_post_meta( $post_id, 'classic-editor-remember' );
		delete_post_meta( $post_id, '_wp_use_block_editor_for_post' );
		
		if ( class_exists( 'Classic_Editor' ) ) {
			if ( ! $use_block_editor ) {
				// User prefers classic editor
				update_post_meta( $post_id, 'classic-editor-remember', 'classic-editor' );
			} else {
				// User prefers block editor (or no preference, default to block)
				update_post_meta( $post_id, '_wp_use_block_editor_for_post', true );
				$edit_link = add_query_arg( 'classic-editor__forget', '', $edit_link );
			}
		} else {
			// Classic Editor plugin not active, ensure block editor
			update_post_meta( $post_id, '_wp_use_block_editor_for_post', true );
		}
		
		return $edit_link;
	}

	/**
	 * Format recipe content for insertion into post.
	 *
	 * @since    1.0.0
	 * @param    int  $recipe_id Recipe ID.
	 * @param    bool $use_block_editor Whether to use block format.
	 * @return   string Formatted recipe content.
	 */
	private static function format_recipe_content( $recipe_id, $use_block_editor ) {
		if ( $use_block_editor ) {
			// Use block format for block editor
			return '<!-- wp:wp-recipe-maker/recipe {"id":' . absint( $recipe_id ) . '} /-->';
		} else {
			// Use shortcode for classic editor
			return '[wprm-recipe id="' . absint( $recipe_id ) . '"]';
		}
	}

	/**
	 * Create a new post for a recipe.
	 *
	 * @since    1.0.0
	 */
	public static function ajax_create_post_for_recipe() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			// Require edit_posts capability to prevent unauthorized access.
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error( array(
					'message' => __( 'You do not have permission to perform this action.', 'wp-recipe-maker' ),
				) );
				wp_die();
			}

			$recipe_id = isset( $_POST['recipe_id'] ) ? absint( $_POST['recipe_id'] ) : 0; // Input var okay.

			if ( $recipe_id > 0 ) {
				// Only create post if the user can read/edit the recipe.
				if ( ! current_user_can( 'read_post', $recipe_id ) ) {
					wp_send_json_error( array(
						'message' => __( 'You do not have permission to access this recipe.', 'wp-recipe-maker' ),
					) );
					wp_die();
				}

				$recipe = self::get_recipe( $recipe_id );

				if ( $recipe ) {
					// Validate post type capabilities
					$post_type = 'post';
					$post_type_object = get_post_type_object( $post_type );
					
					if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->create_posts ) ) {
						wp_send_json_error( array(
							'message' => __( 'You do not have permission to create posts.', 'wp-recipe-maker' ),
						) );
						wp_die();
					}

					// Determine if post will use block editor
					$use_block_editor = self::get_user_editor_preference();

					// Format content based on editor type
					$content = self::format_recipe_content( $recipe_id, $use_block_editor );

					$post = array(
						'post_type' => $post_type,
						'post_status' => 'draft',
						'post_title' => sanitize_text_field( $recipe->name() ),
						'post_content' => $content,
					);

					$post_id = wp_insert_post( $post );

					if ( is_wp_error( $post_id ) ) {
						wp_send_json_error( array(
							'message' => __( 'Failed to create post: ', 'wp-recipe-maker' ) . $post_id->get_error_message(),
						) );
						wp_die();
					}

					if ( $post_id ) {
						// Set editor preference and get edit link
						$edit_link = self::set_post_editor_preference( $post_id, $use_block_editor );

						wp_send_json_success( array(
							'editLink' => $edit_link,
						) );
					} else {
						wp_send_json_error( array(
							'message' => __( 'Failed to create post.', 'wp-recipe-maker' ),
						) );
					}
				} else {
					wp_send_json_error( array(
						'message' => __( 'Recipe not found.', 'wp-recipe-maker' ),
					) );
				}
			} else {
				wp_send_json_error( array(
					'message' => __( 'Invalid recipe ID.', 'wp-recipe-maker' ),
				) );
			}
		}

		wp_die();
	}

	/**
	 * Add recipe shortcode to an existing post.
	 *
	 * @since    1.0.0
	 */
	public static function ajax_add_recipe_to_post() {
		if ( check_ajax_referer( 'wprm', 'security', false ) ) {
			// Require edit_posts capability to prevent unauthorized access.
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error( array(
					'message' => __( 'You do not have permission to perform this action.', 'wp-recipe-maker' ),
				) );
				wp_die();
			}

			$recipe_id = isset( $_POST['recipe_id'] ) ? absint( $_POST['recipe_id'] ) : 0; // Input var okay.
			$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0; // Input var okay.

			if ( $recipe_id > 0 && $post_id > 0 ) {
				// Only add recipe if the user can read/edit both the recipe and the post.
				if ( ! current_user_can( 'read_post', $recipe_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
					wp_send_json_error( array(
						'message' => __( 'You do not have permission to perform this action.', 'wp-recipe-maker' ),
					) );
					wp_die();
				}

				$recipe = self::get_recipe( $recipe_id );
				$post = get_post( $post_id );

				if ( $recipe && $post ) {
					// Determine if post uses block editor (consistent with new post creation)
					$use_block_editor = self::post_uses_block_editor( $post );

					// Format content based on editor type
					$recipe_block = "\n\n" . self::format_recipe_content( $recipe_id, $use_block_editor );

					// Append recipe to existing content
					$content = $post->post_content . $recipe_block;

					$updated = wp_update_post( array(
						'ID' => $post_id,
						'post_content' => $content,
					) );

					if ( is_wp_error( $updated ) ) {
						wp_send_json_error( array(
							'message' => __( 'Failed to update post: ', 'wp-recipe-maker' ) . $updated->get_error_message(),
						) );
						wp_die();
					}

					if ( $updated ) {
						// Don't change editor preference for existing posts - use get_edit_post_link
						// which respects the post's current editor preference
						$edit_link = get_edit_post_link( $post_id, '' );

						wp_send_json_success( array(
							'editLink' => $edit_link,
						) );
					} else {
						wp_send_json_error( array(
							'message' => __( 'Failed to update post.', 'wp-recipe-maker' ),
						) );
					}
				} else {
					wp_send_json_error( array(
						'message' => __( 'Recipe or post not found.', 'wp-recipe-maker' ),
					) );
				}
			} else {
				wp_send_json_error( array(
					'message' => __( 'Invalid recipe ID or post ID.', 'wp-recipe-maker' ),
				) );
			}
		}

		wp_die();
	}

	/**
	 * Get recipe object by ID.
	 *
	 * @since    1.0.0
	 * @param		 mixed $post_or_recipe_id ID or Post Object for the recipe we want.
	 */
	public static function get_recipe( $post_or_recipe_id ) {
		if ( 'demo' === $post_or_recipe_id ) {
			return self::get_demo_recipe();
		} elseif ( 'feature-explorer' === $post_or_recipe_id ) {
			return self::get_feature_explorer_demo_recipe();
		} else {
			$recipe_id = is_object( $post_or_recipe_id ) && $post_or_recipe_id instanceof WP_Post ? $post_or_recipe_id->ID : intval( $post_or_recipe_id );
		}

		// Only get new recipe object if it hasn't been retrieved before.
		if ( ! array_key_exists( $recipe_id, self::$recipes ) ) {
			$post = is_object( $post_or_recipe_id ) && $post_or_recipe_id instanceof WP_Post ? $post_or_recipe_id : get_post( intval( $post_or_recipe_id ) );

			if ( $post instanceof WP_Post && WPRM_POST_TYPE === $post->post_type ) {
				$recipe = new WPRM_Recipe( $post );
			} else {
				$recipe = false;
			}

			self::$recipes[ $recipe_id ] = $recipe;
		}

		return self::$recipes[ $recipe_id ];
	}

	/**
	 * Get demo recipe.
	 *
	 * @since	5.8.0
	 */
	public static function get_demo_recipe() {
		ob_start();
		include( WPRM_DIR . 'templates/admin/demo-recipe.json' );
		$json = ob_get_contents();
		ob_end_clean();

		$json_recipe = json_decode( $json, true );
		$json_recipe = apply_filters( 'wprm_demo_recipe', $json_recipe );

		$sanitized_recipe = WPRM_Recipe_Sanitizer::sanitize( $json_recipe );

		// Fix technical fields.
		$sanitized_recipe['id'] = 'demo';
		$sanitized_recipe['parent_url'] = '#';
		$sanitized_recipe['post_author'] = $json_recipe['post_author'];
		$sanitized_recipe['ingredients_flat'] = $json_recipe['ingredients_flat'];
		$sanitized_recipe['instructions_flat'] = $json_recipe['instructions_flat'];

		// Set some additional fields.
		$sanitized_recipe['image_url'] = WPRM_URL . 'assets/images/demo-recipe.jpg';
		$sanitized_recipe['pin_image_url'] = WPRM_URL . 'assets/images/demo-recipe.jpg';
		$sanitized_recipe['rating'] = array(
			'count' => 8,
			'total' => 30,
			'average' => 3.75,
		);
		$sanitized_recipe['permalink'] = home_url() . '/demo-recipe/';

		$demo_recipe = new WPRM_Recipe_Shell( $sanitized_recipe );
		WPRM_Template_Shortcodes::set_current_recipe_shell( $demo_recipe );

		return $demo_recipe;
	}

	/**
	 * Get Feature Explorer demo recipe.
	 *
	 * @since	10.5.0
	 */
	public static function get_feature_explorer_demo_recipe() {
		ob_start();
		include( WPRM_DIR . 'templates/admin/feature-explorer-demo-recipe.json' );
		$json = ob_get_contents();
		ob_end_clean();

		$json_recipe = json_decode( $json, true );
		$json_recipe = apply_filters( 'wprm_feature_explorer_demo_recipe', $json_recipe );

		$sanitized_recipe = WPRM_Recipe_Sanitizer::sanitize( $json_recipe );

		// Fix technical fields.
		$sanitized_recipe['id'] = 'feature-explorer';
		$sanitized_recipe['parent_url'] = '#';
		$sanitized_recipe['post_author'] = $json_recipe['post_author'];
		$sanitized_recipe['ingredients_flat'] = $json_recipe['ingredients_flat'];
		$sanitized_recipe['instructions_flat'] = $json_recipe['instructions_flat'];

		// Feature Explorer specific taxonomy icons (inline SVG) for tag image rendering.
		$sanitized_recipe['feature_explorer_taxonomy_icons'] = self::get_feature_explorer_taxonomy_icons();

		// Set some additional fields.
		$sanitized_recipe['image_url'] = WPRM_URL . 'assets/images/demo-recipe.jpg';
		$sanitized_recipe['pin_image_url'] = WPRM_URL . 'assets/images/demo-recipe.jpg';
		$sanitized_recipe['rating'] = array(
			'count' => 8,
			'total' => 30,
			'average' => 3.75,
		);
		$sanitized_recipe['permalink'] = home_url() . '/feature-explorer-demo-recipe/';

		$demo_recipe = new WPRM_Recipe_Shell( $sanitized_recipe );
		WPRM_Template_Shortcodes::set_current_recipe_shell( $demo_recipe );

		return $demo_recipe;
	}

	/**
	 * Get sanitized Feature Explorer taxonomy icon map.
	 *
	 * @since	10.5.0
	 */
	public static function get_feature_explorer_taxonomy_icons() {
		static $sanitized_icons = null;

		if ( null !== $sanitized_icons ) {
			return $sanitized_icons;
		}

		ob_start();
		include( WPRM_DIR . 'templates/admin/feature-explorer-demo-recipe.json' );
		$json = ob_get_contents();
		ob_end_clean();

		$json_recipe = json_decode( $json, true );
		$json_recipe = apply_filters( 'wprm_feature_explorer_demo_recipe', $json_recipe );

		$sanitized_icons = self::sanitize_feature_explorer_taxonomy_icons(
			isset( $json_recipe['feature_explorer_taxonomy_icons'] ) ? $json_recipe['feature_explorer_taxonomy_icons'] : array()
		);

		return $sanitized_icons;
	}

	/**
	 * Sanitize Feature Explorer taxonomy icon map.
	 *
	 * @since	10.5.0
	 * @param	mixed $icon_map Raw icon map from JSON.
	 */
	private static function sanitize_feature_explorer_taxonomy_icons( $icon_map ) {
		$sanitized = array();

		if ( ! is_array( $icon_map ) ) {
			return $sanitized;
		}

		foreach ( $icon_map as $taxonomy_key => $terms ) {
			$taxonomy_key = sanitize_key( $taxonomy_key );
			if ( ! $taxonomy_key || ! is_array( $terms ) ) {
				continue;
			}

			$sanitized[ $taxonomy_key ] = array();

			foreach ( $terms as $term_slug => $svg ) {
				$term_slug = sanitize_key( $term_slug );
				$svg = self::sanitize_feature_explorer_inline_svg( $svg );

				if ( $term_slug && $svg ) {
					$sanitized[ $taxonomy_key ][ $term_slug ] = $svg;
				}
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize inline SVG markup for Feature Explorer demo icons.
	 *
	 * @since	10.5.0
	 * @param	mixed $svg Raw SVG markup.
	 */
	private static function sanitize_feature_explorer_inline_svg( $svg ) {
		if ( ! is_string( $svg ) ) {
			return '';
		}

		$allowed_svg_tags = array(
			'svg' => array(
				'xmlns' => true,
				'viewbox' => true,
				'viewBox' => true,
				'width' => true,
				'height' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'aria-hidden' => true,
				'role' => true,
				'focusable' => true,
				'class' => true,
			),
			'g' => array(
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'transform' => true,
				'class' => true,
			),
			'path' => array(
				'd' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'fill-rule' => true,
				'clip-rule' => true,
				'transform' => true,
				'class' => true,
			),
			'circle' => array(
				'cx' => true,
				'cy' => true,
				'r' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'class' => true,
			),
			'rect' => array(
				'x' => true,
				'y' => true,
				'width' => true,
				'height' => true,
				'rx' => true,
				'ry' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'class' => true,
			),
			'line' => array(
				'x1' => true,
				'y1' => true,
				'x2' => true,
				'y2' => true,
				'stroke' => true,
				'stroke-width' => true,
				'stroke-linecap' => true,
				'class' => true,
			),
			'polygon' => array(
				'points' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'class' => true,
			),
			'polyline' => array(
				'points' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'class' => true,
			),
			'title' => array(),
			'desc' => array(),
		);

		$svg = trim( $svg );
		$svg = wp_kses( $svg, $allowed_svg_tags );

		if ( false === stripos( $svg, '<svg' ) ) {
			return '';
		}

		return $svg;
	}

	/**
	 * Get an array of recipe IDs that are in a specific post.
	 *
	 * @since	4.2.0
	 * @param	mixed	$post_id Optional post ID. Uses current post if not set.
	 * @param	boolean	$ignore_cache Whether the cache should be ignored.
	 */
	public static function get_recipe_ids_from_post( $post_id = false, $ignore_cache = false ) {
		// Default to current post ID and sanitize.
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$post_id = intval( $post_id );

		// Search through post content if not in cache only.
		if ( $ignore_cache || ! isset( self::$posts[ $post_id ] ) ) {
			$post = get_post( $post_id );

			if ( $post ) {
				if ( WPRM_POST_TYPE === $post->post_type ) {
					self::$posts[ $post_id ] = array( $post_id );
				} else {
					$recipe_ids = self::get_recipe_ids_from_content( $post->post_content );

					// Thrive Architect compatibility.
					if ( function_exists( 'tve_get_post_meta' ) ) {
						$content = tve_get_post_meta( get_the_ID(), 'tve_updated_post', true );
						$thrive_recipe_ids = self::get_recipe_ids_from_content( $content );

						$recipe_ids = array_unique( $recipe_ids + $thrive_recipe_ids );
					}

					// Themify Builder compatibility.
					if ( '<!-- wp:themify-builder/canvas /-->' === substr( $post->post_content, 0, 38 ) ) {
						$ThemifyBuilder = isset( $GLOBALS['ThemifyBuilder'] ) ? $GLOBALS['ThemifyBuilder'] : false;

						if ( $ThemifyBuilder ) {
							$content = $ThemifyBuilder->get_builder_output( $post->ID );
						
							if ( $content ) {
								preg_match_all( '/id="wprm-recipe-container-(\d+)"/m', $content, $matches );
								$recipe_ids = array_unique( $recipe_ids + $matches[1] );
							}
						}
					}

					// Bricks Builder compatibility.
					if (class_exists('\Bricks\Frontend') && method_exists('\Bricks\Frontend', 'render_content')) {
						$bricks_builder_used = get_post_meta( $post_id, '_bricks_editor_mode', true );

						if ( $bricks_builder_used && 'bricks' === $bricks_builder_used ) {
							$bricks_data = get_post_meta( $post_id, '_bricks_page_content_2', true );
	
							if ( $bricks_data ) {
								// Set a flag to prevent infinite recursion
								static $processing_bricks = false;

								if ( ! $processing_bricks ) {
									$processing_bricks = true;
									
									ob_start();
									\Bricks\Frontend::render_content( $bricks_data );
									$bricks_output = ob_get_contents();
									ob_end_clean();
		
									$bricks_recipe_ids = self::get_recipe_ids_from_content( $bricks_output );
									$recipe_ids = array_unique( $recipe_ids + $bricks_recipe_ids );
									
									$processing_bricks = false;
								}
							}
						}
					}

					// Allow for filtering of recipe IDs found in the post.
					$recipe_ids = apply_filters( 'wprm_get_recipe_ids_from_post', $recipe_ids, $post_id );
					
					self::$posts[ $post_id ] = $recipe_ids;
				}
			} else {
				// Fail now and give another chance to find ids later.
				return false;
			}
		}

		return self::$posts[ $post_id ];
	}

	/**
	 * Get an array of recipe IDs that are in the content.
	 *
	 * @since    1.0.0
	 * @param		 mixed $content Content we want to check for recipes.
	 */
	public static function get_recipe_ids_from_content( $content ) {
		// Gutenberg.
		$gutenberg_matches = array();
		$gutenberg_patern = '/<!--\s+wp:(wp\-recipe\-maker\/recipe)(\s+(\{.*?\}))?\s+(\/)?-->/';
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
		preg_match_all( WPRM_Fallback_Recipe::get_fallback_regex(), $content, $matches );
		$classic_matches = isset( $matches[1] ) ? array_map( 'intval', $matches[1] ) : array();

		// Site Origin Page Builder Compatibility.
		$content = str_ireplace( '\&quot;', '"', $content );

		// Match shortcodes (need for Site Origin Page Builder, for example).
		$shortcode_pattern = '/\[wprm-recipe\s.*?id=\"?\'?(\d+)\"?\'?.*?\]/mi';
		preg_match_all( $shortcode_pattern, $content, $matches );
		$shortcode_matches = isset( $matches[1] ) ? array_map( 'intval', $matches[1] ) : array();

		// Divi Builder.
		$divi_matches = array();
		if ( function_exists( 'et_core_is_builder_used_on_current_request' ) ) {
			$pattern = get_shortcode_regex( array( 'divi_wprm_recipe' ) );

			if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) && array_key_exists( 2, $matches ) ) {
				foreach ( $matches[2] as $key => $value ) {
					if ( 'divi_wprm_recipe' === $value ) {
						$divi_atts = shortcode_parse_atts( stripslashes( $matches[3][ $key ] ) );

						if ( isset( $divi_atts['recipe_id'] ) ) {
							$divi_matches[] = intval( $divi_atts['recipe_id'] );
						}
					}
				}
			}
		}

		$divi5_matches = self::get_recipe_ids_from_divi5_blocks( $content );

		$recipe_ids = $gutenberg_matches + $classic_matches + $shortcode_matches + $divi_matches + $divi5_matches;

		// Allow for filtering of recipe IDs found in the content.
		$recipe_ids = apply_filters( 'wprm_get_recipe_ids_from_content', $recipe_ids, $content );

		return $recipe_ids;
	}

	/**
	 * Extract recipe IDs from Divi 5 blocks.
	 *
	 * @since 10.0.0
	 * @param mixed $content Content we want to check for recipes.
	 */
	private static function get_recipe_ids_from_divi5_blocks( $content ) {
		if ( false === strpos( $content, 'wprm/recipe' ) ) {
			return array();
		}

		if ( ! function_exists( 'parse_blocks' ) ) {
			return array();
		}

		$blocks = parse_blocks( $content );

		if ( empty( $blocks ) ) {
			return array();
		}

		return self::extract_recipe_ids_from_divi5_blocks( $blocks );
	}

	/**
	 * Recursively loop through blocks to find Divi 5 recipe modules.
	 *
	 * @since 10.0.0
	 * @param array $blocks Parsed block list.
	 */
	private static function extract_recipe_ids_from_divi5_blocks( $blocks ) {
		$ids = array();

		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			if ( isset( $block['blockName'] ) && 'wprm/recipe' === $block['blockName'] ) {
				$maybe_id = self::get_recipe_id_from_divi5_attrs( isset( $block['attrs'] ) ? $block['attrs'] : array() );

				if ( $maybe_id ) {
					$ids[] = $maybe_id;
				}
			}

			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$ids = array_merge( $ids, self::extract_recipe_ids_from_divi5_blocks( $block['innerBlocks'] ) );
			}
		}

		return $ids;
	}

	/**
	 * Attempt to extract a numeric recipe ID from Divi 5 block attributes.
	 *
	 * @since 10.0.0
	 * @param array $attrs Block attributes.
	 */
	private static function get_recipe_id_from_divi5_attrs( $attrs ) {
		if ( empty( $attrs ) || ! is_array( $attrs ) ) {
			return false;
		}

		$candidates = array();

		// Native attribute structure when the recipe field is stored as an object.
		if ( isset( $attrs['recipe']['innerContent']['desktop']['value'] ) ) {
			$candidates[] = $attrs['recipe']['innerContent']['desktop']['value'];
		}

		if ( isset( $attrs['recipe']['innerContent']['value'] ) ) {
			$candidates[] = $attrs['recipe']['innerContent']['value'];
		}

		if ( isset( $attrs['recipe']['value'] ) ) {
			$candidates[] = $attrs['recipe']['value'];
		}

		// Some conversions flatten the property name to a dotted or underscored string.
		if ( isset( $attrs['recipe.innerContent'] ) ) {
			$candidates[] = $attrs['recipe.innerContent'];
		}

		if ( isset( $attrs['recipe_innerContent'] ) ) {
			$candidates[] = $attrs['recipe_innerContent'];
		}

		foreach ( $candidates as $candidate ) {
			$recipe_id = self::normalize_divi5_recipe_candidate( $candidate );

			if ( $recipe_id ) {
				return $recipe_id;
			}
		}

		// As a final fallback, search recursively for a numeric "value" key.
		foreach ( $attrs as $key => $attr ) {
			if ( ! is_string( $key ) || false === strpos( $key, 'recipe' ) || ! is_array( $attr ) ) {
				continue;
			}

			$maybe_id = self::normalize_divi5_recipe_candidate( $attr );

			if ( $maybe_id ) {
				return $maybe_id;
			}
		}

		return false;
	}

	/**
	 * Normalize data stored in a Divi 5 attribute to a recipe ID.
	 *
	 * @since 10.0.0
	 * @param mixed $candidate Candidate value to inspect.
	 */
	private static function normalize_divi5_recipe_candidate( $candidate ) {
		if ( is_array( $candidate ) ) {
			if ( isset( $candidate['value'] ) ) {
				return self::normalize_divi5_recipe_candidate( $candidate['value'] );
			}

			foreach ( $candidate as $value ) {
				$maybe = self::normalize_divi5_recipe_candidate( $value );

				if ( $maybe ) {
					return $maybe;
				}
			}

			return false;
		}

		if ( is_numeric( $candidate ) ) {
			$recipe_id = absint( $candidate );
			return $recipe_id > 0 ? $recipe_id : false;
		}

		if ( is_string( $candidate ) ) {
			if ( preg_match( '/id="?(\\d+)"?/', $candidate, $matches ) ) {
				return absint( $matches[1] );
			}

			if ( preg_match( '/\\d+/', $candidate, $matches ) ) {
				return absint( $matches[0] );
			}
		}

		return false;
	}

	/**
	 * Invalidate cached recipe.
	 *
	 * @since    1.0.0
	 * @param		 int $recipe_id ID of the recipe to invalidate.
	 */
	public static function invalidate_recipe( $recipe_id ) {
		if ( array_key_exists( $recipe_id, self::$recipes ) ) {
			unset( self::$recipes[ $recipe_id ] );
		}
	}

	/**
	 * Recipe data to pass along in footer.
	 *
	 * @since	8.10.0
	 */
	public static function recipe_data_in_footer( $recipe_ids = array() ) {
		// add_action will pass along empty string, so make sure we have an array.
		$recipe_ids = is_array( $recipe_ids ) ? $recipe_ids : array();

		$recipes = apply_filters( 'wprm_recipes_on_page', $recipe_ids );

		if ( $recipes ) {
			$recipe_data = array();
			$recipes = array_unique( $recipes );

			foreach( $recipes as $recipe_id ) {
				$recipe = self::get_recipe( $recipe_id );

				if ( $recipe ) {
					$recipe_data[ 'recipe-' . $recipe_id ] = $recipe->get_data_frontend();
				}
			}

			if ( $recipe_data ) {
				echo '<script>window.wprm_recipes = ' . wp_json_encode( $recipe_data ) . '</script>';
			}
		}
	}
}

WPRM_Recipe_Manager::init();
