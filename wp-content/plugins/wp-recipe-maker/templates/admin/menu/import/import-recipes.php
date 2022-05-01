<?php
/**
 * Template for recipe import page.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/import
 */

?>

<div class="wrap wprm-import">
	<?php
	$uid = isset( $_GET['from'] ) ? sanitize_title( wp_unslash( $_GET['from'] ) ) : ''; // Input var okay.
	$page = isset( $_GET['p'] ) ? intval( wp_unslash( $_GET['p'] ) ) : 0; // Input var okay.
	$importer = self::get_importer( $uid );

	if ( ! $importer ) :
		esc_html_e( 'Something went wrong.', 'wp-recipe-maker' );
	else :
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=wprm_importing' ) ); ?>">
		<input type="hidden" name="action" value="wprm_import_recipes">
		<input type="hidden" name="importer" value="<?php echo esc_attr( $importer->get_uid() ); ?>">
		<?php wp_nonce_field( 'wprm_import_recipes', 'wprm_import_recipes', false ); ?>
		<h2><?php echo __( 'Import', 'wp-recipe-maker' ) . ' - ' . esc_html( $importer->get_name() ); ?></h2>
		<?php
		$error = isset( $_GET['error'] ) ? sanitize_text_field( wp_unslash( $_GET['error'] ) ) : ''; // Input var okay.
		if ( $error ) :
		?>
		<div class="wprm-import-error">
			<?php echo esc_html( $error ); ?>
		</div>
		<?php endif; // Error. ?>
		<?php
		$settings = apply_filters( 'wprm_import_settings_' . $uid, $importer->get_settings_html() );

		if ( $settings ) :
		?>
		<h3><?php esc_html_e( 'Import Settings', 'wp-recipe-maker' ); ?></h3>
		<?php echo $settings; ?>
		<?php endif; // Settings. ?>
		<h3><?php esc_html_e( 'Recipes to Import', 'wp-recipe-maker' ); ?></h3>
		<?php
		$has_search = method_exists( $importer, 'get_recipes_search' );
		
		if ( $has_search ) :
			$search = isset( $_GET['s'] ) ? sanitize_text_field( urldecode( $_GET['s'] ) ) : '';
		?>
		<p><a href="<?php echo esc_url( add_query_arg( array( 'from' => $uid ), admin_url( 'admin.php?page=wprm_import' ) ) ); ?>" id="wprm-import-recipes-search"><?php _e( 'Click to search the recipes to import', 'wp-recipe-maker' ); ?></a></p>
		<?php
			if ( $search ) :
			?>
			<p><?php echo __( 'Searching', 'wp-recipe-maker' ) . ': ' . $search; ?> <a href="<?php echo esc_url( add_query_arg( array( 'from' => $uid ), admin_url( 'admin.php?page=wprm_import' ) ) ); ?>" id="wprm-import-recipes-search-stop">(<?php _e( 'cancel', 'wp-recipe-maker' ); ?>)</a></p>
			<?php endif;
		endif; ?>
		<p><em><?php esc_html_e( 'Use SHIFT-click to (un)check multiple recipes at once.', 'wp-recipe-maker' ); ?></em></p>
		<?php esc_html_e( 'Select', 'wp-recipe-maker' ); ?>: <a href="#" class="wprm-import-recipes-select-all"><?php esc_html_e( 'all', 'wp-recipe-maker' ); ?></a>, <a href="#" class="wprm-import-recipes-select-none"><?php esc_html_e( 'none', 'wp-recipe-maker' ); ?></a>
		<table class="wprm-import-recipes">
			<tbody>
				<?php
				if ( $has_search && $search ) {
					$searching = true;
					$recipes = $importer->get_recipes_search( $search );
				} else {
					$searching = false;
					$recipes = $importer->get_recipes( $page );					
				}

				foreach ( $recipes as $id => $recipe ) :
				?>
				<tr>
					<td>
						<input type="checkbox" name="recipes[]" value="<?php echo esc_attr( $id ); ?>" />
					</td>
					<td>
						<?php if ( $recipe['url'] ) : ?>
						<a href="<?php echo esc_url( $recipe['url'] ); ?>" target="_blank"><?php echo esc_html( $recipe['name'] ); ?></a>
						<?php else : ?>
						<?php echo esc_html( $recipe['name'] . ' (' . __( 'no parent post found', 'wp-recipe-maker' ) . ')' ); ?>
						<?php endif; // Recipe edit URL. ?>
					</td>
				</tr>
				<?php endforeach; // Recipes to import. ?>
			</tbody>
		</table>
		<?php
		if ( ! $searching && $importer->get_recipe_count() > count( $recipes ) ) {
			$recipes_left = $importer->get_recipe_count() - count( $recipes );
			echo '<em>';
			printf( esc_html( _n( '%d more recipe', '%d more recipes', $recipes_left, 'wp-recipe-maker' ) ), intval( $recipes_left ) );

			if ( 0 === count( $recipes ) ) {
				echo '<br/>';
				echo '<a href="#" class="wprm-import-reset-page">' . esc_html__( 'Back to start.', 'wp-recipe-maker' ) . '</a>';
			} else {
				echo '<br/>';
				echo '<a href="#" class="wprm-import-next-page">' . esc_html__( 'Go to the next page.', 'wp-recipe-maker' ) . '</a>';
			}
			echo '</em>';
		}
		?>
		<?php submit_button( __( 'Import Selected Recipes', 'wp-recipe-maker' ) ); ?>
	</form>
	<?php endif; // Recipe Importer. ?>
</div>
