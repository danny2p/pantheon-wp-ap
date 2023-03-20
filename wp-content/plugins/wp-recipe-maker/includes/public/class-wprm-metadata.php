<?php
/**
 * Handle the recipe metadata.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the recipe metadata.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Metadata {
	/**
	 * List of recipes we've already outputted the metadata for.
	 *
	 * @since    5.3.0
	 * @access   private
	 * @var      mixed $outputted_metadata_for List of recipes we've already outputted the metadata for.
	 */
	private static $outputted_metadata_for = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'metadata_in_head' ), 1 );
		add_action( 'after_setup_theme', array( __CLASS__, 'metadata_image_sizes' ) );

		add_filter( 'wpseo_schema_graph_pieces', array( __CLASS__, 'wpseo_schema_graph_pieces' ), 1, 2 );
	}

	/**
	 * Confirm recipe as being outputted in the metadata.
	 *
	 * @since	5.3.0
	 * @param 	int $recipe_id Recipe we've outputted the metadata for.
	 */
	public static function outputted_metadata_for( $recipe_id ) {
		self::$outputted_metadata_for[] = intval( $recipe_id );
	}

	/**
	 * Check if recipe metadata has been outputted.
	 *
	 * @since	5.6.0
	 * @param 	int $recipe_id Optional recipe to check for.
	 */
	public static function has_outputted_metadata( $recipe_id = false ) {
		if ( false === $recipe_id ) {
			return 0 < count( self::$outputted_metadata_for );
		} else {
			return in_array( intval( $recipe_id ), self::$outputted_metadata_for );
		}
	}

	/**
	 * Check if we should output the metadata for a recipe.
	 *
	 * @since	5.3.0
	 * @param 	int $recipe_id Recipe to check.
	 */
	public static function should_output_metadata_for( $recipe_id ) {
		// Don't output metadata twice.
		if ( self::has_outputted_metadata( $recipe_id ) ) {
			// Disabled in version 5.4.3 to prevent issues with metadata not showing up in certain cases.
			// return false;
		}

		// Only output metadata for first recipe on page.
		if ( WPRM_Settings::get( 'metadata_only_show_for_first_recipe' ) && 0 < count( self::$outputted_metadata_for ) && $recipe_id !== self::$outputted_metadata_for[0] ) {
			return false;
		}

		return true;
	}

	/**
	 * Output metadata in the HTML head.
	 *
	 * @since    1.25.0
	 */
	public static function metadata_in_head() {
		if ( WPRM_Settings::get( 'metadata_pinterest_optout' ) ) {
			// Only opt out if there are recipes on this page.
			if ( WPRM_Recipe_Manager::get_recipe_ids_from_post() ) {
				echo '<meta name="pinterest-rich-pin" content="false" />';
			}
		}

		if ( is_singular() && 'head' === WPRM_Settings::get( 'metadata_location' ) && ! self::use_yoast_seo_integration() && ! self::use_rank_math_integration() ) {
			$recipe_ids_to_output_metadata_for = self::get_recipe_ids_to_output();
			
			foreach ( $recipe_ids_to_output_metadata_for as $recipe_id ) {
				if ( self::should_output_metadata_for( $recipe_id ) ) {
					$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );
					$output = self::get_metadata_output( $recipe );

					if ( $output ) {
						self::outputted_metadata_for( $recipe_id );
						echo $output;
					}
				}
			}
		}
	}

	/**
	 * Get recipe IDs to output metadata for.
	 *
	 * @since	5.1.0
	 */
	public static function get_recipe_ids_to_output() {
		$recipe_ids_to_output_metadata_for = array();

		if ( is_singular() ) {
			$recipe_ids = WPRM_Recipe_Manager::get_recipe_ids_from_post();

			if ( $recipe_ids ) {
				if ( ! WPRM_Settings::get( 'metadata_only_show_for_first_recipe' ) ) {
					// Output metadata for all recipes.
					$recipe_ids_to_output_metadata_for = $recipe_ids;
				} else {
					// Only add metadata for first food recipe on page.
					foreach ( $recipe_ids as $recipe_id ) {
						$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

						if ( $recipe && 'other' !== $recipe->type() ) {
							$recipe_ids_to_output_metadata_for = array( $recipe_id );
							break;
						}
					}
				}
			}
		}

		return $recipe_ids_to_output_metadata_for;
	}

	/**
	 * Wether or not to use Rank Math integration.
	 *
	 * @since	8.7.0
	 */
	public static function use_rank_math_integration() {
		return WPRM_Settings::get( 'rank_math_integration' ) && class_exists( '\RankMath\Schema\JsonLD' );
	}

	/**
	 * Wether or not to use Yoast SEO 11 integration.
	 *
	 * @since	5.1.0
	 */
	public static function use_yoast_seo_integration() {
		return WPRM_Settings::get( 'yoast_seo_integration' ) && class_exists( '\Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece' );
	}

	/**
	 * Yoast SEO 11 Schema integration.
	 *
	 * @since	5.1.0
	 * @param 	array $pieces  Yoast schema pieces.
	 * @param 	mixed $context Yoast schema context.
	 */
	public static function wpseo_schema_graph_pieces( $pieces, $context ) {
		if ( self::use_yoast_seo_integration() ) {
			require_once( WPRM_DIR . 'includes/public/class-wprm-metadata-yoast-seo.php' );
			$recipe_piece = new WPRM_Metadata_Yoast_Seo( $context );
			$pieces[] = $recipe_piece;

			if ( $recipe_piece->is_needed() ) {
				// Make sure Yoast loads the Person schema for the recipe author.
				add_filter( 'wpseo_schema_needs_person', '__return_true' );
				add_filter( 'wpseo_schema_person_user_id', function( $person_user_id ) use( $recipe_piece ) {
					$person = $recipe_piece->get_person();

					if ( $person && $person['id'] ) {
						return $person['id'];
					}

					return $person_user_id;
				} );
			}
		}
	
		return $pieces;
	}

	/**
	 * Register image sizes for the recipe metadata.
	 *
	 * @since    1.25.0
	 */
	public static function metadata_image_sizes() {
		if ( function_exists( 'fly_add_image_size' ) ) {
			fly_add_image_size( 'wprm-metadata-1_1', 500, 500, true );
			fly_add_image_size( 'wprm-metadata-4_3', 500, 375, true );
			fly_add_image_size( 'wprm-metadata-16_9', 480, 270, true );
		} else {
			add_image_size( 'wprm-metadata-1_1', 500, 500, true );
			add_image_size( 'wprm-metadata-4_3', 500, 375, true );
			add_image_size( 'wprm-metadata-16_9', 480, 270, true );
		}
	}

	/**
	 * Get the metadata to output for a recipe.
	 *
	 * @since    1.0.0
	 * @param		 object $recipe Recipe to get the metadata for.
	 */
	public static function get_metadata_output( $recipe ) {
		$output = '';

		$metadata = self::sanitize_metadata( self::get_metadata( $recipe ) );
		if ( $metadata ) {
			$output = '<script type="application/ld+json">' . wp_json_encode( $metadata ) . '</script>';
		}

		return $output;
	}

	/**
	 * Santize metadata before outputting.
	 *
	 * @since    1.5.0
	 * @param		 mixed $metadata Metadata to sanitize.
	 */
	public static function sanitize_metadata( $metadata ) {
		$sanitized = array();
		if ( is_array( $metadata ) ) {
			foreach ( $metadata as $key => $value ) {
				$sanitized[ $key ] = self::sanitize_metadata( $value );
			}
		} else {
			$sanitized = strip_shortcodes( wp_strip_all_tags( do_shortcode( $metadata ) ) );
		}
		return $sanitized;
	}

	/**
	 * Get the metadata for a recipe.
	 *
	 * @since    1.0.0
	 * @param		 object $recipe Recipe to get the metadata for.
	 */
	public static function get_metadata( $recipe ) {
		if ( ! $recipe ) {
			return false;
		}

		// Prevent Jetpack Photon from replacing image URLs in metadata.
		// Source: https://git.ethitter.com/snippets/1
		$photon_removed = false;
		if ( class_exists( 'Jetpack_Photon' ) && Jetpack::is_module_active( 'photon' ) ) {
			$photon_removed = remove_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ) );
		}

		// Get the correct metadata for each recipe type.
		if ( 'food' === $recipe->type() || 'howto' === $recipe->type() ) {
			$metadata = self::get_metadata_details( $recipe );
		} else {
			$metadata = array();
		}

		// Restore Jetpack Photon if we removed it.
		if ( $photon_removed ) {
			add_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ), 10, 3 );
		}

		// Allow external filtering of metadata.
		return apply_filters( 'wprm_recipe_metadata', $metadata, $recipe );
	}

	/**
	 * Get the metadata details.
	 *
	 * @since	5.11.0
	 * @param	object $recipe Recipe to get the metadata for.
	 */
	public static function get_metadata_details( $recipe ) {
		// Essentials.
		$metadata = array(
			'@context' => 'http://schema.org/',
			'@type' => 'food' === $recipe->type() ? 'Recipe' : 'HowTo',
			'name' => $recipe->name(),
			'author' => array(
				'@type' => 'Person',
				'name' => $recipe->author_meta(),
			),
			'description' => wp_strip_all_tags( $recipe->summary() ),
		);

		// Dates.
		$date_published = date( 'c', strtotime( $recipe->date() ) );
		$metadata['datePublished'] = $date_published;

		$date_modified = date( 'c', strtotime( $recipe->date_modified() ) );
		if ( $date_modified !== $date_published ) {
			// Removed again on 2018-11-16 to see if this was causing rich snippet problems.
			// $metadata['dateModified'] = $date_modified;
		}

		// Recipe image.
		if ( $recipe->image_id() ) {
			if ( 'food' === $recipe->type() ) {
				$image_sizes = array(
					$recipe->image_url( 'full' ),
					$recipe->image_url( 'wprm-metadata-1_1' ),
					$recipe->image_url( 'wprm-metadata-4_3' ),
					$recipe->image_url( 'wprm-metadata-16_9' ),
				);
	
				$metadata['image'] = array_values( array_unique( $image_sizes ) );
			} else {
				$metadata['image'] = $recipe->image_url( 'full' );
			}
		}

		// Recipe video.
		$check_video_parts = false;
		if ( $recipe->video_metadata() ) {
			$metadata['video'] = $recipe->video_metadata();
			$metadata['video']['@type'] = 'VideoObject';
			$check_video_parts = true;
		}

		// Yield.
		if ( $recipe->servings() ) {
			if ( 'food' === $recipe->type() ) {
				$yield = array(
					$recipe->servings(),
				);

				if ( $recipe->servings_unit() ) {
					$yield[] = $recipe->servings() . ' ' . $recipe->servings_unit();
				}

				$metadata['recipeYield'] = $yield;
			} else {
				$metadata['yield'] = $recipe->servings() . ' ' . $recipe->servings_unit();
			}
		}

		// Cost.
		if ( 'howto' === $recipe->type() ) {
			if ( $recipe->cost() ) {
				$metadata['estimatedCost'] = $recipe->cost();
			}
		}

		// Times.
		if ( 'food' === $recipe->type() ) {
			if ( $recipe->prep_time() ) {
				$metadata['prepTime'] = 'PT' . $recipe->prep_time() . 'M';
			}
			if ( $recipe->cook_time() ) {
				$metadata['cookTime'] = 'PT' . $recipe->cook_time() . 'M';
			}
		}
		if ( $recipe->total_time() ) {
			$metadata['totalTime'] = 'PT' . $recipe->total_time() . 'M';
		}

		// Equipment.
		if ( 'howto' === $recipe->type() ) {
			$equipment = $recipe->equipment();
			if ( count( $equipment ) > 0 ) {
				$metadata_equipment = array();

				foreach ( $equipment as $equipment_item ) {
					$name = $equipment_item['name'];
					if ( $name ) {
						if ( isset( $equipment_item['amount'] ) && $equipment_item['amount'] ) {
							$name = $equipment_item['amount'] . ' ' . $name;
						}
						if ( isset( $equipment_item['notes'] ) && $equipment_item['notes'] ) {
							$name = $name . ' ' . $equipment_item['notes'];
						}
						$metadata_equipment[] = array(
							'@type' => 'HowToTool',
							'name' => $name,
						);
					}
				}

				$metadata['tool'] = $metadata_equipment;
			}
		}

		// Ingredients/Materials.
		$ingredients = $recipe->ingredients_without_groups();
		if ( $ingredients && count( $ingredients ) > 0 ) {
			$metadata_ingredients = array();

			foreach ( $ingredients as $ingredient ) {
				if ( 'food' === $recipe->type() ) {
					$metadata_ingredient = $ingredient['amount'] . ' ' . $ingredient['unit'] . ' ' . $ingredient['name'];
					if ( WPRM_Settings::get( 'metadata_include_ingredient_notes' ) && trim( $ingredient['notes'] ) !== '' ) {
						$ingredient_notes = ' (' . $ingredient['notes'] . ')';

						// Only add notes if it doesn't put ingredient over the 135 limit.
						if ( WPRM_Settings::get( 'metadata_restrict_ingredient_length' ) ) {
							if ( 135 >= strlen( $metadata_ingredient . $ingredient_notes ) ) { 
								$metadata_ingredient .= $ingredient_notes;
							}
						} else {
							$metadata_ingredient .= $ingredient_notes;
						}
					}

					$metadata_ingredients[] = $metadata_ingredient;
				} else {
					$metadata_material = array(
						'@type' => 'HowToSupply',
					);
	
					$quantity = trim( $ingredient['amount'] . ' ' . $ingredient['unit'] );
					if ( $quantity ) {
						$metadata_material['requiredQuantity'] = $quantity;
					}
					
					$name = $ingredient['name'];
					if ( WPRM_Settings::get( 'metadata_include_ingredient_notes' ) && trim( $ingredient['notes'] ) !== '' ) {
						$name .= ' (' . $ingredient['notes'] . ')';
					}
					$metadata_material['name'] = $name;

					if ( $name ) {
						$metadata_ingredients[] = $metadata_material;
					}
				}
			}

			if ( 'food' === $recipe->type() ) {
				$metadata['recipeIngredient'] = $metadata_ingredients;
			} else {
				$metadata['supply'] = $metadata_ingredients;
			}
		}

		// Instructions.
		$videos_metadata = $recipe->videos_metadata();
		$instruction_video_parts = array();
		$url = $recipe->permalink();

		$step_id = '#wprm-recipe-' . $recipe->id() . '-step';
		if ( $url ) {
			$url .= $step_id;
		}

		$instruction_groups = $recipe->instructions();
		if ( count( $instruction_groups ) > 0 ) {
			$metadata_instruction_groups = array();
			$metadata_all_instructions = array();
			$has_unnamed_group = false;

			foreach ( $instruction_groups as $group_index => $instruction_group ) {
				$metadata_instructions = array();

				foreach ( $instruction_group['instructions'] as $index => $instruction ) {
					$metadata_instruction = array(
						'@type' => 'HowToStep',
						'text' => wp_strip_all_tags( $instruction['text'] ),
					);

					// Handle instruction step name field.
					if ( 'ignore' !== WPRM_Settings::get( 'metadata_instruction_name' ) ) {
						$metadata_instruction['name'] = isset( $instruction['name'] ) ? wp_strip_all_tags( $instruction['name'] ) : '';

						if ( ! $metadata_instruction['name']  && 'reuse' === WPRM_Settings::get( 'metadata_instruction_name' ) ) {
							$metadata_instruction['name'] = $metadata_instruction['text'];
						}
					}

					// Link to instruction directly if parent post is set.
					if ( $url ) {
						$metadata_instruction['url'] = $url . '-' . $group_index . '-' . $index;
					}

					// Add instruction image.
					if ( $instruction['image'] ) {
						$thumb = wp_get_attachment_image_src( $instruction['image'], 'full' );

						if ( $thumb && isset( $thumb[0] ) ) {
							$metadata_instruction['image'] = $thumb[0];
						}
					}

					// Check video type for this instructions.
					$video_type = isset( $instruction['video'] ) && isset( $instruction['video']['type'] ) ? $instruction['video']['type'] : 'part'; // Default to part for backward compatibility.

					// Add video if no image is set.
					if ( ! isset( $metadata_instruction['image'] ) ) {
						$video_metadata = false;

						if ( isset( $videos_metadata['instructions'] ) && isset( $videos_metadata['instructions'][ $group_index ] ) && isset( $videos_metadata['instructions'][ $group_index ][ $index ] ) ) {
							$video_metadata = $videos_metadata['instructions'][ $group_index ][ $index ];
						}

						if ( $video_metadata ) {
							$metadata_instruction['video'] = $video_metadata;
							$metadata_instruction['video']['@type'] = 'VideoObject';
						}
					}

					// Maybe add video clip as part of main video.
					if ( $check_video_parts && isset( $instruction['video'] ) && 'part' === $video_type ) {
						$start = self::video_time_to_seconds( $instruction['video']['start'] );
						$end = self::video_time_to_seconds( $instruction['video']['end'] );

						if ( $end > $start ) {
							$video_step_id = $step_id . '-' . $group_index . '-' . $index;
							$clip_id = $video_step_id;

							$video_part_metadata = array(
								'@type' => 'Clip',
								'@id' => $clip_id,
								'name' => $instruction['video']['name'],
								'startOffset' => $start,
								'endOffset' => $end,
							);

							$video_part_url = self::video_get_url_to_time( $recipe, $metadata['video']['contentUrl'], $start );
							if ( $video_part_url ) {
								$video_part_metadata['url'] = $video_part_url;
							}

							$instruction_video_parts[] = $video_part_metadata;

							$metadata_instruction['video'] = array(
								'@id' => $video_step_id,
							);
						}
					}

					$metadata_instructions[] = $metadata_instruction;
				}

				if ( count( $metadata_instructions ) > 0 ) {
					if ( $instruction_group['name'] ) {
						$metadata_instruction_groups[] = array(
							'@type' => 'HowToSection',
							'name' => wp_strip_all_tags( $instruction_group['name'] ),
							'itemListElement' => $metadata_instructions,
						);
					} else {
						$has_unnamed_group = true;
						$metadata_instruction_groups = array_merge( $metadata_instruction_groups, $metadata_instructions );
					}

					$metadata_all_instructions = array_merge( $metadata_all_instructions, $metadata_instructions );
				}
			}

			if ( count( $metadata_instruction_groups ) > 0 ) {
				if ( 'food' === $recipe->type() ) {
					$metadata['recipeInstructions'] = $metadata_instruction_groups;
				} else {
					if ( $has_unnamed_group ) {
						// Google complains when mixing HowToStep and HowToSection for step metadata.
						$metadata['step'] = $metadata_all_instructions;
					} else {
						$metadata['step'] = $metadata_instruction_groups;
					}
				}
			}
		}

		// Video clips.
		if ( 0 < count( $instruction_video_parts ) ) {
			$metadata['video']['hasPart'] = $instruction_video_parts;
		}

		// Rating.
		$rating = $recipe->rating();
		if ( $rating['count'] > 0 ) {
			$metadata['aggregateRating'] = array(
				'@type' => 'AggregateRating',
				'ratingValue' => $rating['average'],
				'ratingCount' => $rating['count'],
			);
		}

		// Food Recipe only metadata.
		if ( 'food' === $recipe->type() ) {
			// Category & Cuisine.
			$courses = $recipe->tags( 'course' );
			if ( count( $courses ) > 0 ) {
				$metadata['recipeCategory'] = wp_list_pluck( $courses, 'name' );
			}
			$cuisines = $recipe->tags( 'cuisine' );
			if ( count( $cuisines ) > 0 ) {
				$metadata['recipeCuisine'] = wp_list_pluck( $cuisines, 'name' );
			}

			// Diets.
			$diets = $recipe->tags( 'suitablefordiet' );
			if ( count( $diets ) > 0 ) {
				$metadata['suitableForDiet'] = array_map( function( $diet ) {
					return 'https://schema.org/' . $diet;
				}, wp_list_pluck( $diets, 'name' ) );
			}

			// Keywords.
			$keywords = $recipe->tags( 'keyword' );
			if ( count( $keywords ) > 0 ) {
				$keyword_names = wp_list_pluck( $keywords, 'name' );
				$metadata['keywords'] = implode( ', ', $keyword_names );
			}

			// Nutrition.
			$nutrition_mapping = array(
				'serving_size' => 'servingSize',
				'calories' => 'calories',
				'fat' => 'fatContent',
				'saturated_fat' => 'saturatedFatContent',
				'unsaturated_fat' => 'unsaturatedFatContent',
				'trans_fat' => 'transFatContent',
				'carbohydrates' => 'carbohydrateContent',
				'sugar' => 'sugarContent',
				'fiber' => 'fiberContent',
				'protein' => 'proteinContent',
				'cholesterol' => 'cholesterolContent',
				'sodium' => 'sodiumContent',
			);
			$nutrition_metadata = array();
			$nutrition = $recipe->nutrition();

			// Calculate unsaturated fat.
			if ( isset( $nutrition['polyunsaturated_fat'] ) && isset( $nutrition['monounsaturated_fat'] ) ) {
				$nutrition['unsaturated_fat'] = $nutrition['polyunsaturated_fat'] + $nutrition['monounsaturated_fat'];
			} elseif ( isset( $nutrition['polyunsaturated_fat'] ) ) {
				$nutrition['unsaturated_fat'] = $nutrition['polyunsaturated_fat'];
			} elseif ( isset( $nutrition['monounsaturated_fat'] ) ) {
				$nutrition['unsaturated_fat'] = $nutrition['monounsaturated_fat'];
			}

			foreach ( $nutrition as $field => $value ) {
				if ( $value && array_key_exists( $field, $nutrition_mapping ) ) {
					$unit = 'g';

					if ( 'serving_size' === $field ) {
						if ( isset( $nutrition['serving_unit'] ) && $nutrition['serving_unit'] ) {
							$unit = $nutrition['serving_unit'];
						} else {
							$unit = WPRM_Settings::get( 'nutrition_default_serving_unit' );
						}
					} elseif ( 'calories' === $field ) {
						$unit = esc_html__( 'kcal', 'wp-recipe-maker' );
					} elseif ( 'cholesterol' === $field || 'sodium' === $field ) {
						$unit = esc_html__( 'mg', 'wp-recipe-maker' );
					}

					$nutrition_metadata[ $nutrition_mapping[ $field ] ] = trim( $value . ' ' . $unit );
				}
			}

			if ( count( $nutrition_metadata ) > 0 ) {
				if ( ! isset( $nutrition_metadata['servingSize'] ) ) {
					$nutrition_metadata['servingSize'] = esc_html__( '1 serving', 'wp-recipe-maker' );
				}

				$metadata['nutrition'] = array_merge( array(
					'@type' => 'NutritionInformation',
				), $nutrition_metadata );
			}
		}

		return $metadata;
	}

	/**
	 * Get the metadata for a food recipe.
	 *
	 * @since	5.2.0
	 * @param	object $recipe Recipe to get the metadata for.
	 */
	public static function get_food_metadata( $recipe ) {
		return self::get_metadata_details( $recipe );
	}

	/**
	 * Get the metadata for a how-to recipe.
	 *
	 * @since	5.2.0
	 * @param	object $recipe Recipe to get the metadata for.
	 */
	public static function get_howto_metadata( $recipe ) {
		return self::get_metadata_details( $recipe );
	}

	/**
	 * Get seconds from video time string.
	 *
	 * @since	5.7.0
	 * @param	mixed $time Time to convert.
	 */
	public static function video_time_to_seconds( $time ) {
		if ( ! $time ) {
			return 0;
		}

		$time_parts = explode( ':', $time, 2 );

		if ( 2 === count( $time_parts ) ) {
			$seconds = 60 * intval( $time_parts[0] ) + intval( $time_parts[1] );
		} else {
			$seconds = intval( $time_parts[0] );
		}

		return $seconds;
	}

	/**
	 * Get direct URL to video start time.
	 *
	 * @since	5.7.0
	 * @param	mixed $recipe	Recipe we're getting the video for.
	 * @param	mixed $url		Video contentUrl.
	 * @param	mixed $time		Time to get the URL for.
	 */
	public static function video_get_url_to_time( $recipe, $url, $time ) {
		if ( $url ) {
			if ( stripos( $url, 'youtube.com' ) || stripos( $url, 'youtu.be' ) ) {
				if ( false !== strpos( $url, '?' ) ) {
					return $url . '&t=' . $time;
				} else {
					return $url . '?t=' . $time;
				}
			}
			if ( stripos( $url, 'vimeo.com' ) ) {
				return $url . '#t=' . $time;
			}
			if ( stripos( $url, 'mediavine' ) ) {
				$permalink = $recipe->permalink();

				if ( $permalink ) {
					if ( false !== strpos( $permalink, '?' ) ) {
						return $permalink . '&mvs=' . $time . '#mv-first-video';
					} else {
						return $permalink . '?mvs=' . $time . '#mv-first-video';
					}
				}
			}
		}

		return false;
	}
}

WPRM_Metadata::init();
