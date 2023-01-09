<?php
/**
 * Handle the recipe metadata integration with Yoast SEO Schema (version 11+)
 *
 * @link       http://bootstrapped.ventures
 * @since      5.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the recipe metadata integration with Yoast SEO Schema (version 11+)
 *
 * @since      5.1.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Metadata_Yoast_Seo implements WPSEO_Graph_Piece {
	/**
	 * A value object with context variables.
	 *
	 * @var WPSEO_Schema_Context
	 */
	private $context;

	/**
	 * Recipe to output schema for.
	 *
	 * @var WPRM_Recipe
	 */
	private $recipe;

	/**
	 * Wether or not an article is used.
	 *
	 * @var boolean
	 */
	private $using_article;

	/**
	 * WPRM_Metadata_Yoast_Seo constructor.
	 *
	 * @param WPSEO_Schema_Context $context A value object with context variables.
	 */
	public function __construct( WPSEO_Schema_Context $context ) {
		$this->context = $context;

		$this->using_article = false;
		add_filter( 'wpseo_schema_article', array( $this, 'wpseo_schema_article' ) );
	}

	/**
	 * Alter the article metadata.
	 * 
	 * @param array $data Article schema data.
	 */
	public function wpseo_schema_article( $data ) {
		$this->using_article = true;

		if ( $this->is_needed() ) {
			// Our recipe is the main entity.
			unset( $data['mainEntityOfPage'] );
		}

		return $data;
	}

	/**
	 * Determine whether we should return Recipe schema.
	 *
	 * @return bool
	 */
	public function is_needed() {
		if ( is_singular() ) {
			$recipe_ids = WPRM_Metadata::get_recipe_ids_to_output();

			if ( $recipe_ids ) {
				foreach ( $recipe_ids as $recipe_id ) {
					$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

					if ( $recipe && 'other' !== $recipe->type() && WPRM_Metadata::should_output_metadata_for( $recipe->id() ) ) {
						$this->recipe = $recipe;
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Add recipe piece of the graph.
	 *
	 * @return array|bool $graph A graph piece on success, false on failure.
	 */
	public function generate() {
		$metadata = WPRM_Metadata::sanitize_metadata( WPRM_Metadata::get_metadata( $this->recipe ) );

		if ( $metadata ) {
			WPRM_Metadata::outputted_metadata_for( $this->recipe->id() );
			// Context is already set by Yoast.
			unset( $metadata['@context'] );

			// Give an ID to the recipe part.
			$metadata['@id'] = $this->context->canonical . '#recipe';

			// Recipe isPartOf an article of webpage, with the recipe the mainEntityOfPage.
			$parent = $this->using_article ? $this->context->canonical . WPSEO_Schema_IDs::ARTICLE_HASH : $this->context->canonical . WPSEO_Schema_IDs::WEBPAGE_HASH;

			$metadata['isPartOf'] = array( '@id' => $parent );
			$metadata['mainEntityOfPage'] = $this->context->canonical . WPSEO_Schema_IDs::WEBPAGE_HASH;

			// Unless author is custom (and actually set), use the author that Yoast already defines.
			if ( 'custom' !== $this->recipe->author_display() || '' === $this->recipe->custom_author_name() ) {
				// Only if Yoast's author is the same as the recipe author.
				if ( $this->context->post->post_author === $this->recipe->post_author() ) {
					$user_schema_id = YoastSEO()->helpers->schema->id->get_user_schema_id( $this->recipe->post_author(), $this->context );

					if ( $user_schema_id ) {
						$metadata['author'] = array( '@id' => $user_schema_id );
					}
				}
			}

			return $metadata;
		}

		return false;
	}
}
