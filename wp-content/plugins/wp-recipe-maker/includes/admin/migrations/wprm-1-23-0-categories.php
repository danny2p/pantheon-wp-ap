<?php
/**
 * Migration for removing the categories associated with recipes again.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.23.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

// Associate categories with recipes during migration.
register_taxonomy_for_object_type( 'category', WPRM_POST_TYPE );

// Loop through all recipes.
$limit = 100;
$offset = 0;

while ( true ) {
	$args = array(
		'post_type' => WPRM_POST_TYPE,
		'post_status' => 'any',
		'orderby' => 'date',
		'order' => 'DESC',
		'fields' => 'ids',
		'posts_per_page' => $limit,
		'offset' => $offset,
	);

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		break;
	}

	$posts = $query->posts;

	foreach ( $posts as $post ) {
		wp_delete_object_term_relationships( $post, 'category' );
	}

	$offset += $limit;
}
