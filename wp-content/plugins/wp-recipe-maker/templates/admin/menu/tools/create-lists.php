<?php
/**
 * Template for import MV Create lists page.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/tools
 */

?>

<div class="wrap wprm-tools">
	<h2><?php esc_html_e( 'Create Lists', 'wp-recipe-maker' ); ?></h2>
	<?php printf( esc_html( _n( 'Searching %d Create List', 'Searching %d Create Lists', count( $mv_lists ), 'wp-recipe-maker' ) ), count( $mv_lists ) ); ?>.
	<div id="wprm-tools-progress-container">
		<div id="wprm-tools-progress-bar"></div>
	</div>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_manage#lists' ) ); ?>" id="wprm-tools-finished"><?php esc_html_e( 'Finished succesfully. Click here to continue.', 'wp-recipe-maker' ); ?></a>
</div>
