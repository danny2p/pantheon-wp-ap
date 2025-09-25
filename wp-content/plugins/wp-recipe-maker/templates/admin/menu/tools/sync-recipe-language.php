<?php
/**
 * Template for sync recipe language page.
 *
 * @link       http://bootstrapped.ventures
 * @since      10.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/tools
 */
?>

<div class="wrap wprm-tools">
	<h2><?php esc_html_e( 'Sync Recipe Language', 'wp-recipe-maker' ); ?></h2>
	<?php
	// translators: %d: number of recipes left to search through.
	printf( esc_html( _n( 'Syncing %d recipe', 'Syncing %d recipes', count( $posts ), 'wp-recipe-maker' ) ), count( $posts ) );
	?>.
	<div id="wprm-tools-progress-container">
		<div id="wprm-tools-progress-bar"></div>
	</div>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_manage' ) ); ?>" id="wprm-tools-finished"><?php esc_html_e( 'Finished succesfully. Click here to continue.', 'wp-recipe-maker' ); ?></a>
</div>
