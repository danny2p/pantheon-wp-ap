<?php
/**
 * Migration for fixing the ingredient IDs.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.7.1
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

// Loop through all recipes.
$limit = 100;
$offset = 0;

while ( true ) {
	$args = array(
		'post_type' => WPRM_POST_TYPE,
		'post_status' => 'any',
		'orderby' => 'date',
		'order' => 'DESC',
		'posts_per_page' => $limit,
		'offset' => $offset,
	);

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		break;
	}

	$posts = $query->posts;

	foreach ( $posts as $post ) {
		$recipe = WPRM_Recipe_Manager::get_recipe( $post );

		$ingredient_ids = array();
		foreach ( $recipe->ingredients() as $ingredient_group ) {
			foreach ( $ingredient_group['ingredients'] as $ingredient ) {
				$ingredient_ids[] = intval( $ingredient['id'] );
			}
		}
		$ingredient_ids = array_unique( $ingredient_ids );

		wp_set_object_terms( $recipe->id(), $ingredient_ids, 'wprm_ingredient', false );

		wp_cache_delete( $post->ID, 'posts' );
		wp_cache_delete( $post->ID, 'post_meta' );
	}

	$offset += $limit;
	wp_cache_flush();
}
