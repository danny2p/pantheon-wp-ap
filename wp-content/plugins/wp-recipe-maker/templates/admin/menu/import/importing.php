<?php
/**
 * Template for recipe importing page.
 *
 * @link       https://bootstrapped.ventures
 * @since      1.18.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/import
 */

?>

<div class="wrap wprm-import">
	<h2><?php echo esc_html( __( 'Import', 'wp-recipe-maker' ) . ' - ' . $importer->get_name() ); ?></h2>
	<?php
	// translators: %d: number of recipes left to import.
	printf( esc_html( _n( 'Importing %d recipe', 'Importing %d recipes', count( $recipes ), 'wp-recipe-maker' ) ), count( $recipes ) );
	?>.
	<?php
	$progress_bar_type = 'import';
	include WPRM_DIR . 'templates/admin/progress-bar.php';
	?>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_import_overview' ) ); ?>" id="wprm-import-finished"><?php esc_html_e( 'Import finished succesfully. Click here to continue.', 'wp-recipe-maker' ); ?></a>
</div>
