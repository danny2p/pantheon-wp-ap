<?php
/**
 * Template for fixing comment ratings page.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.9.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/tools
 */
?>

<div class="wrap wprm-tools">
	<h2><?php esc_html_e( 'Fix Comment Ratings', 'wp-recipe-maker' ); ?></h2>
	<?php printf( esc_html( _n( 'Searching %d rating', 'Searching %d ratings', count( $ratings ), 'wp-recipe-maker' ) ), count( $ratings ) ); ?>.
	<div id="wprm-tools-progress-container">
		<div id="wprm-tools-progress-bar"></div>
	</div>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_finding_ratings' ) ); ?>" id="wprm-tools-finished"><?php esc_html_e( 'Finished succesfully. Use the "Find Ratings" tool to clear any rating caches.', 'wp-recipe-maker' ); ?></a>
</div>
