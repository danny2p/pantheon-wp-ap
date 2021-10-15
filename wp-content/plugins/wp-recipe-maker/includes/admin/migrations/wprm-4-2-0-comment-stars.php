<?php
/**
 * Enable new comment ratings performance setting.
 *
 * @link       http://bootstrapped.ventures
 * @since      4.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$comment_stars_color = WPRM_Settings::get( 'template_color_comment_rating' );

// Disable new performance setting if people are not using the default stars color.
if ( '#343434' !== $comment_stars_color ) {
	WPRM_Settings::update_settings( array(
		'performance_use_combined_stars' => false,
	) );
}