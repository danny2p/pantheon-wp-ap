<?php
/**
 * Handle the recipe metadata integration with Rank Math.
 *
 * @link       https://bootstrapped.ventures
 * @since      8.7.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the recipe metadata integration with Rank Math.
 *
 * @since      8.7.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Metadata_Rank_Math {

	/**
	 * Recipe to output schema for.
	 *
	 * @var mixed
	 */
	public static $recipe = false;

	/**
	 * Register actions and filters.
	 *
	 * @since    8.7.0
	 */
	public static function init() {
		add_filter( 'rank_math/json_ld', array( __CLASS__, 'rank_math_json_ld' ), 99, 2 );
	}

	/**
	 * Alter the data Rank Math uses for their JSON-LD output.
	 *
	 * @since   8.7.0
	 * @param	array  $data	An array of data to output in json-ld.
	 * @param	JsonLD $jsonld	JsonLD instance.
	 */
	public static function rank_math_json_ld( $data, $jsonld ) {
		if ( WPRM_Settings::get( 'rank_math_integration' ) ) {
			$recipe = self::get_recipe();
			// var_dump( $recipe );

			if ( $recipe ) {
				$metadata = self::get_recipe_metadata( $data );

				if ( $metadata ) {
					WPRM_Metadata::outputted_metadata_for( $recipe->id() );
					$data['recipe'] = $metadata;
				}
			}
			// echo '<pre>' . var_export( $data, true ) . '</pre>';
			// echo '<pre>' . var_export( $jsonld, true ) . '</pre>';
			// die();
		}

		return $data;
	}

	/**
	 * Get recipe to output the metadata for.
	 *
	 * @since   8.7.0
	 */
	public static function get_recipe() {
		if ( self::$recipe ) {
			return self::$recipe;
		}

		if ( is_singular() ) {
			$recipe_ids = WPRM_Metadata::get_recipe_ids_to_output();

			if ( $recipe_ids ) {
				foreach ( $recipe_ids as $recipe_id ) {
					$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

					if ( $recipe && 'other' !== $recipe->type() && WPRM_Metadata::should_output_metadata_for( $recipe->id() ) ) {
						self::$recipe = $recipe;
						return $recipe;
					}
				}
			}
		}

		self::$recipe = false;
		return false;
	}

	/**
	 * Get recipe to output the metadata for.
	 *
	 * @since   8.7.0
	 */
	public static function get_recipe_metadata( $data ) {
		$recipe = self::$recipe;
		$metadata = false;

		if ( $recipe ) {
			$metadata = WPRM_Metadata::sanitize_metadata( WPRM_Metadata::get_metadata( $recipe ) );

			if ( $metadata ) {
				// Context is already set by Rank Math.
				unset( $metadata['@context'] );

				$webpage = false;
				$article = false;
				$blog_posting = false;
				
				foreach ( $data as $key => $schema ) {
					if ( isset( $schema['@type'] ) ) {
						if ( 'WebPage' === $schema['@type'] ) {
							$webpage = $key;
						} elseif ( 'Article' === $schema['@type'] ) {
							$article = $key;
						} elseif ( 'BlogPosting' === $schema['@type'] ) {
							$article = $key;
						}
					}
				}

				// Set ID for recipe.
				if ( $webpage && isset( $data[ $webpage ]['url'] ) ) {
					$metadata['@id'] = $data[ $webpage ]['url'] . '#recipe';
					$metadata['mainEntityOfPage'] = $data[ $webpage ]['url'];
				}

				// Set recipe as main entity of the WebPage.
				if ( $webpage && isset( $data[ $webpage ]['@id'] ) ) {
					$metadata['mainEntityOfPage'] = $data[ $webpage ]['@id'];
				}

				// Check what the parent should be, default to WebPage.
				$parent = $webpage;
				if ( $article ) { $parent = $article; }
				if ( $blog_posting) { $parent = $blog_posting; }

				if ( $parent && isset( $data[ $webpage ]['url'] ) ) {
					$parent_id = isset( $data[ $parent ]['@id'] ) ? $data[ $parent ]['@id'] : $data[ $webpage ]['url'] . '#' . $parent;
					$metadata['isPartOf'] = array( '@id' => $parent_id );
				}
			}
		}

		return $metadata;
	}
}

WPRM_Metadata_Rank_Math::init();