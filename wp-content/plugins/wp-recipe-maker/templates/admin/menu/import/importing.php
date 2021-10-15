<?php
/**
 * Template for recipe importing page.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.18.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/import
 */

?>

<div class="wrap wprm-import">
	<h2><?php echo __( 'Import', 'wp-recipe-maker' ) . ' - ' . esc_html( $importer->get_name() ); ?></h2>
	<?php printf( esc_html( _n( 'Importing %d recipe', 'Importing %d recipes', count( $recipes ), 'wp-recipe-maker' ) ), count( $recipes ) ); ?>.
	<div id="wprm-import-progress-container">
		<div id="wprm-import-progress-bar"></div>
	</div>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_import_overview' ) ); ?>" id="wprm-import-finished"><?php esc_html_e( 'Import finished succesfully. Click here to continue.', 'wp-recipe-maker' ); ?></a>
</div>
