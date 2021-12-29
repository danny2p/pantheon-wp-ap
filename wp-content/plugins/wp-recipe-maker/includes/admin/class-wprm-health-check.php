<?php
/**
 * Health check for the dashboard page.
 *
 * @link       https://bootstrapped.ventures
 * @since      8.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Health check for the dashboard page.
 *
 * @since      8.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Health_Check {

	/**
	 * Plugin version number when the health check was updated last.
	 *
	 * @since	8.0.0
	 * @access  private
	 * @var     string $health_check_version Plugin version number.
	 */
	private static $health_check_version = '8.0.0';

	/**
	 * Get the Health Check data to display on the dashboard page.
	 *
	 * @since	8.0.0
	 */
	public static function get_data() {
		$health_check = get_option( 'wprm_health_check', false );

		if ( ! $health_check ) {
			$health_check = array(
				'items' => array(),
				'date' => false,
				'version' => '0.0.0',
			);
		}

		// Format date and get urgency.
		$health_check['date_formatted'] = __( 'Never', 'wp-recipe-maker' );
		$health_check['urgency'] = 'never';

		if ( $health_check['date'] ) {
			$datetime = new DateTime();
			$datetime->setTimestamp( $health_check['date'] );
			$health_check['date_formatted'] = $datetime->format( 'M j, Y' );
			$health_check['date_formatted_full'] = $datetime->format( 'Y-m-d H:i:s' );

			if ( strtotime( '-1 week' ) < $health_check['date'] ) {
				$health_check['urgency'] = 'ok';
			} elseif ( strtotime( '-4 weeks' ) < $health_check['date'] ) {
				$health_check['urgency'] = 'fair';
			} elseif ( strtotime( '-2 months' ) < $health_check['date'] ) {
				$health_check['urgency'] = 'bad';
			} else {
				$health_check['urgency'] = 'asap';
			}
		}

		// Check if the health check has been updated since last run.
		$health_check['updated'] = false;
		if ( '0.0.0' !== $health_check['version'] && version_compare( $health_check['version'], self::$health_check_version ) < 0 ) {
			$health_check['updated'] = true;
			$health_check['urgency'] = 'asap';
		}

		// General stuff.
		$health_check['tool'] = admin_url( 'admin.php?page=wprm_health_check' );

		return $health_check;
	}

	/**
	 * Save the health check results.
	 *
	 * @since	8.0.0
	 * @param	mixed $results Health check results to save
	 */
	public static function save_results( $results ) {
		$health_check = array(
			'items' => $results,
			'date' => time(),
			'version' => self::$health_check_version,
		);

		update_option( 'wprm_health_check', $health_check, false );
	}

	/**
	 * Starting a new health check.
	 *
	 * @since	8.0.0
	 */
	public static function start() {
		delete_transient( 'wprm_health_check_data' );
		set_transient( 'wprm_health_check_data', array(), HOUR_IN_SECONDS );
	}

	/**
	 * Save temporary health check data per post ID.
	 *
	 * @since	8.0.0
	 * @param	mixed	$id			Post ID to save the data for.
	 * @param	mixed	$post_data	Data to save for this post.
	 */
	public static function save( $id, $post_data ) {
		$data = get_transient( 'wprm_health_check_data' );

		if ( false !== $data ) {
			$data[ $id ] = $post_data;
			set_transient( 'wprm_health_check_data', $data, HOUR_IN_SECONDS );
		}
	}

	/**
	 * Finished a health check.
	 *
	 * @since	8.0.0
	 */
	public static function stop() {
		$data = get_transient( 'wprm_health_check_data' );
		delete_transient( 'wprm_health_check_data' );

		if ( false !== $data ) {
			$results = array();

			// Initial values.
			$results['seo_types'] = array(
				'bad' 		=> 0,
				'warning' 	=> 0,
				'rating' 	=> 0,
				'good' 		=> 0,
				'other' 	=> 0,
			);
			$results['missing_thumbnails'] = array();

			// Temporary storage.
			$recipes_to_posts = array();
			$post_details = array();
			$recipe_details = array();
			$recipe_names = array();

			// Loop over all the data first to restructure.
			foreach ( $data as $post_id => $result ) {
				if ( isset( $result['post_name'] ) ) {
					$post_details[ $post_id ] = array(
						'name' => $result['post_name'],
						'edit_url' => $result['post_edit_url'],
					);
				}
				if ( isset( $result['recipe_name'] ) ) {
					$recipe_details[ $post_id ] = array(
						'name' => $result['recipe_name'],
						'parent_post_id' => $result['recipe_parent_post_id'],
					);

					if ( $result['recipe_name'] ) {
						if ( ! isset( $recipe_names[ $result['recipe_name'] ] ) ) {
							$recipe_names[ $result['recipe_name'] ] = array();
						}
						$recipe_names[ $result['recipe_name'] ][] = $post_id;
					}
				}

				if ( isset( $result['recipe_ids'] ) ) {
					foreach ( $result['recipe_ids'] as $recipe_id ) {
						if ( ! isset( $recipes_to_posts[ $recipe_id ] ) ) {
							$recipes_to_posts[ $recipe_id ] = array();
						}

						$recipes_to_posts[ $recipe_id ][] = $post_id;
					}
				}

				if ( isset( $result['missing_thumbnail'] ) && $result['missing_thumbnail'] ) {
					$results['missing_thumbnails'][ $post_id ] = isset( $recipe_details[ $post_id ] ) ? $recipe_details[ $post_id ]['name'] : '';
				}

				if ( isset( $result['seo_type'] ) && isset( $results['seo_types'][ $result['seo_type'] ] ) ) {
					$results['seo_types'][ $result['seo_type'] ]++;
				}
			}

			// Check for recipes used in multiple posts.
			$results['multiple_parents'] = array();

			foreach ( $recipes_to_posts as $recipe => $posts ) {
				$unique_posts = array_unique( $posts );

				if ( 1 < count( $unique_posts ) ) {
					$results['multiple_parents'][ $recipe ] = array(
						'name' => isset( $recipe_details[ $recipe ] ) ? $recipe_details[ $recipe ]['name'] : '',
						'parent_post_id' => isset( $recipe_details[ $recipe ] ) ? $recipe_details[ $recipe ]['parent_post_id'] : 0,
						'posts' => array(),
					);

					foreach ( $unique_posts as $post ) {
						$results['multiple_parents'][ $recipe ]['posts'][] = array(
							'id' => $post,
							'name' => isset( $post_details[ $post ] ) ? $post_details[ $post ]['name'] : '',
							'edit_url' => isset( $post_details[ $post ] ) ? $post_details[ $post ]['edit_url'] : '',
						);
					}
				}
			}

			// Check for recipes with the same name.
			$results['duplicate_names'] = array();

			foreach ( $recipe_names as $name => $recipes ) {
				if ( 1 < count( $recipes ) ) {
					$results['duplicate_names'][] = array(
						'name' => $name,
						'recipes' => $recipes,
					);
				}
			}

			// Check for known compatibility problems.
			$compatibility = array();

			if ( is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) {
				$cache_rest_enabled = get_option( 'litespeed.conf.cache-rest', false );

				if ( $cache_rest_enabled ) {
					$compatibility[] = 'litespeed-cache';
				}
			}

			if ( $compatibility ) {
				$results['compatibility'] = $compatibility;
			}

			self::save_results( $results );
		}
	}

	/**
	 * Perform health check on a specific post ID.
	 *
	 * @since	8.0.0
	 * @param	int	$post_id Post ID to check.
	 */
	public static function check( $post_id ) {
		$post = get_post( $post_id );

		if ( $post ) {
			if ( WPRM_POST_TYPE === $post->post_type ) {
				$recipe = WPRM_Recipe_Manager::get_recipe( $post );

				if ( $recipe ) {
					self::check_recipe( $recipe );
				}
			} else {
				self::check_other( $post );
			}
		}
	}

	/**
	 * Perform health check on a specific post.
	 *
	 * @since	8.0.0
	 * @param	mixed	$post Post to check
	 */
	public static function check_other( $post ) {
		$data = array();

		// Check for any recipes inside of post content.
		if ( isset( $post->post_content ) && $post->post_content ) {
			$data['recipe_ids'] = WPRM_Recipe_Manager::get_recipe_ids_from_content( $post->post_content );
		}

		// General data to use.
		if ( isset( $post->post_title ) ) {
			$data['post_name'] = $post->post_title;
		}
		$data['post_edit_url'] = get_edit_post_link( $post->ID );

		// Store data.
		self::save( $post->ID, $data );
	}

	/**
	 * Perform health check on a specific recipe.
	 *
	 * @since	8.0.0
	 * @param	mixed	$recipe Recipe to check
	 */
	public static function check_recipe( $recipe ) {
		$data = array();

		// Check if all thumbnail sizes are set (if there actually is a recipe image).
		if ( 'food' === $recipe->type() && $recipe->image_id() ) {
			$image_sizes = array(
				$recipe->image_url( 'full' ),
				$recipe->image_url( 'wprm-metadata-1_1' ),
				$recipe->image_url( 'wprm-metadata-4_3' ),
				$recipe->image_url( 'wprm-metadata-16_9' ),
			);

			$nbr_unique_image_sizes = count( array_values( array_unique( $image_sizes ) ) );

			if ( count( $image_sizes ) !== $nbr_unique_image_sizes ) {
				$data['missing_thumbnail'] = true;
			}
		}

		// Update SEO for recipe (prevents missing) and get results.
		$seo = WPRM_Seo_Checker::update_seo_for( $recipe->id() );
		if ( $seo ) {
			$data['seo_type'] = $seo['type'];
		}

		// General data to use.
		$data['recipe_name'] = $recipe->name();
		$data['recipe_parent_post_id'] = $recipe->parent_post_id();

		// Store data.
		self::save( $recipe->id(), $data );
	}
}