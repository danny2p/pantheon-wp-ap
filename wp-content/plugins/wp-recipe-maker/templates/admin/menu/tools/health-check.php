<?php
/**
 * Template for health check page.
 *
 * @link       https://bootstrapped.ventures
 * @since      8.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/tools
 */

?>

<div class="wrap wprm-tools">
	<h2><?php esc_html_e( 'Running Health Check', 'wp-recipe-maker' ); ?></h2>
	<?php
	// translators: %d: number of posts left to process.
	printf( esc_html( _n( 'Processing %d post', 'Processing %d posts', count( $posts ), 'wp-recipe-maker' ) ), count( $posts ) );
	?>.
	<?php
	$progress_bar_type = 'tools';
	include WPRM_DIR . 'templates/admin/progress-bar.php';
	?>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprecipemaker' ) ); ?>" id="wprm-tools-finished"><?php esc_html_e( 'Finished succesfully. Click here to continue.', 'wp-recipe-maker' ); ?></a>
</div>
