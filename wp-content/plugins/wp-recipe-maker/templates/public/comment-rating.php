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
$stars_style = WPRM_Settings::get( 'rating_stars_style' );

if ( WPRM_Settings::get( 'performance_use_combined_stars' ) ) :
	$star_icon = 'pointy' === $stars_style ? 'stars-' : 'stars-alt-';

	$svg = WPRM_URL . 'assets/icons/rating/' . $star_icon . $rating . '.svg';
	// translators: %s: number of stars for this recipe.
	$alt = sprintf( _n( '%s star', '%s stars', $rating, 'wp-recipe-maker' ), $rating );

	$svg = apply_filters( 'wprm_rating_stars_svg_url', $svg, $rating );

	if ( function_exists( 'get_rocket_option' ) && get_rocket_option( 'lazyload' ) && ! ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) ) :
?>
<img class="wprm-comment-rating" src="data:image/gif;base64,R0lGODdhAQABAPAAAP///wAAACwAAAAAAQABAEACAkQBADs=" data-lazy-src="<?php echo esc_attr( $svg ); ?>" alt="<?php echo esc_attr( $alt ); ?>" width="80" height="16" />
<?php else : ?>
<img class="wprm-comment-rating" src="<?php echo esc_attr( $svg ); ?>" alt="<?php echo esc_attr( $alt ); ?>" width="80" height="16" />
<?php endif; // WP Rocket lazy loading. ?>
<?php else :

// Use padding from settings but allow overriding if already set.
if ( ! isset( $padding ) ) {
	$padding = intval( WPRM_Settings::get( 'comment_rating_star_padding' ) );
}
$padding = 0 < $padding ? $padding : 0;	

$container_style = $padding ? ' style="margin: 0 -' . $padding . 'px;"' : '';
?>
<div class="wprm-comment-rating">
	<span class="wprm-rating-stars"<?php echo wp_kses_post( $container_style ); ?>><?php
		$star_icon_name = 'pointy' === $stars_style ? 'star-' : 'star-alt-';
	
		for ( $i = 1; $i <= 5; $i++ ) {
			$style = $padding ? ' style="padding: 0 ' . $padding . 'px;"' : '';
			$star_type = $i <= $rating ? 'full' : 'empty';

			echo '<span class="wprm-rating-star wprm-rating-star-' . esc_attr( $star_type ) . '"' . wp_kses_post( $style ) . '>';
			if ( $i <= $rating ) {
					ob_start();
					include( WPRM_DIR . 'assets/icons/' . $star_icon_name . 'full.svg' );
					$star_icon = ob_get_contents();
					ob_end_clean();

					echo apply_filters( 'wprm_comment_rating_star_full_icon', $star_icon );
			} else {
					ob_start();
					include( WPRM_DIR . 'assets/icons/' . $star_icon_name . 'empty.svg' );
					$star_icon = ob_get_contents();
					ob_end_clean();

					echo apply_filters( 'wprm_comment_rating_star_icon', $star_icon );
			}
			echo '</span>';
		}
	?></span>
</div>
<?php endif; ?>