<?php
/**
 * Template for finding parents page.
 *
 * @link       http://bootstrapped.ventures
 * @since      2.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/tools
 */

?>

<div class="wrap wprm-tools">
	<h2><?php esc_html_e( 'Find Parent Posts', 'wp-recipe-maker' ); ?></h2>
	<?php printf( esc_html( _n( 'Searching %d post', 'Searching %d posts', count( $posts ), 'wp-recipe-maker' ) ), count( $posts ) ); ?>.
	<div id="wprm-tools-progress-container">
		<div id="wprm-tools-progress-bar"></div>
	</div>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_manage' ) ); ?>" id="wprm-tools-finished"><?php esc_html_e( 'Finished succesfully. Click here to continue.', 'wp-recipe-maker' ); ?></a>
</div>
