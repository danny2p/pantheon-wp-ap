<?php
/**
 * Template to be used for the rating in comments.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/public
 */

?>
<?php
if ( WPRM_Settings::get( 'performance_use_combined_stars' ) ) :
	$svg = WPRM_URL . 'assets/icons/rating/stars-' . $rating . '.svg';
	$alt = sprintf( _n( '%s star', '%s stars', $rating, 'wp-recipe-maker' ), $rating );

	$svg = apply_filters( 'wprm_rating_stars_svg_url', $svg, $rating );

	if ( function_exists( 'get_rocket_option' ) && get_rocket_option( 'lazyload' ) && ! ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) ) :
?>
<img class="wprm-comment-rating" src="data:image/gif;base64,R0lGODdhAQABAPAAAP///wAAACwAAAAAAQABAEACAkQBADs=" data-lazy-src="<?php echo $svg; ?>" alt="<?php echo esc_attr( $alt ); ?>" width="80" height="16" />
<?php else : ?>
<img class="wprm-comment-rating" src="<?php echo $svg; ?>" alt="<?php echo esc_attr( $alt ); ?>" width="80" height="16" />
<?php endif; // WP Rocket lazy loading. ?>
<?php else : ?>
<div class="wprm-comment-rating">
	<span class="wprm-rating-stars"><?php
		for ( $i = 1; $i <= 5; $i++ ) {
			echo '<span class="wprm-rating-star">';
			if ( $i <= $rating ) {
					ob_start();
					include( WPRM_DIR . 'assets/icons/star-full.svg' );
					$star_icon = ob_get_contents();
					ob_end_clean();

					echo apply_filters( 'wprm_comment_rating_star_full_icon', $star_icon );
			} else {
					ob_start();
					include( WPRM_DIR . 'assets/icons/star-empty.svg' );
					$star_icon = ob_get_contents();
					ob_end_clean();

					echo apply_filters( 'wprm_comment_rating_star_icon', $star_icon );
			}
			echo '</span>';
		}
	?></span>
</div>
<?php endif; ?>