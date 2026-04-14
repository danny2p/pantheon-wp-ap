<?php
/**
 * Template for sync recipe term language page.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/tools
 */
?>

<div class="wrap wprm-tools">
	<h2><?php esc_html_e( 'Sync Recipe Term Language', 'wp-recipe-maker' ); ?></h2>
	<?php
	// translators: %d: number of recipes left to search through.
	printf( esc_html( _n( 'Syncing %d recipe', 'Syncing %d recipes', count( $posts ), 'wp-recipe-maker' ) ), count( $posts ) );
	?>.
	<?php
	$progress_bar_type = 'tools';
	include WPRM_DIR . 'templates/admin/progress-bar.php';
	?>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_manage' ) ); ?>" id="wprm-tools-finished"><?php esc_html_e( 'Finished succesfully. Click here to continue.', 'wp-recipe-maker' ); ?></a>
</div>
