<?php
/**
 * Template for recipe import search page.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.10.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/import
 */

?>

<div class="wrap wprm-import">
	<h4><?php esc_html_e( 'Search Results', 'wp-recipe-maker' ); ?></h4>
	<?php
	$uid = isset( $_GET['from'] ) ? sanitize_title( wp_unslash( $_GET['from'] ) ) : ''; // Input var okay.
	$page = isset( $_GET['p'] ) ? intval( wp_unslash( $_GET['p'] ) ) : 0; // Input var okay.
	$importer = self::get_importer( $uid );

	if ( ! $importer || ! $importer->requires_search() ) {
		esc_html_e( 'Something went wrong.', 'wp-recipe-maker' );
	} else {
		$search_result = $importer->search_recipes( $page );

		if ( $search_result['finished'] ) {
			esc_html_e( 'Search finished.', 'wp-recipe-maker' );
			echo '<br/>';
			printf( esc_html( _n( '%d recipe found', '%d recipes found', $search_result['recipes'], 'wp-recipe-maker' ) ), intval( $search_result['recipes'] ) );
			echo '<br/>';
			?>
			<a href="<?php echo esc_url( add_query_arg( array( 'from' => $uid, 'p' => 0 ), admin_url( 'admin.php?page=wprm_import' ) ) ); ?>"><?php esc_html_e( 'Explore import options', 'wp-recipe-maker' ); ?></a>
			<?php
		} else {
			esc_html_e( 'Still searching, keep this page open.', 'wp-recipe-maker' );
			echo '<br/>';
			printf( esc_html( _n( '%d recipe found', '%d recipes found', $search_result['recipes'], 'wp-recipe-maker' ) ), intval( $search_result['recipes'] ) );
			?>
			<script>
			window.location = '<?php echo add_query_arg( array( 'from' => $uid, 'p' => ($page + 1) ), admin_url( 'admin.php?page=wprm_import_search' ) ); ?>';
			</script>
			<?php
		}
	}?>
</div>
