<?php
/**
 * Template for recipe import overview page.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/import
 */

?>

<div class="wrap wprm-import">
	<h2><?php esc_html_e( 'Import Recipes', 'wp-recipe-maker' ); ?></h2>
	<h3><?php esc_html_e( 'Considerations Before Importing', 'wp-recipe-maker' ); ?></h3>
	<p>
		<?php esc_html_e( "Importing recipes will convert them to our format and they won't be available in the old plugin anymore. We recommend backing up before starting the process and trying to import 1 single recipe first to make sure everything converts properly.", 'wp-recipe-maker' ); ?>
	</p>
	<p>
		<?php esc_html_e( 'If your current plugin uses custom post types and has different permalinks than regular posts you might want to use a redirection plugin to set up 301 redirects. Contact us if you need help!', 'wp-recipe-maker' ); ?>
	</p>

	<h3><?php esc_html_e( 'Import recipes', 'wp-recipe-maker' ); ?></h3>
	<p>
		<?php
		if ( ! WPRM_Addons::is_active( 'premium' ) ) {
			echo __( 'These importers are only available in', 'wp-recipe-maker' ) . ' <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium</a>:<br/>';

			echo '<br/>';
			echo 'Import recipes from JSON<br/>';
			echo 'Import taxonomy terms from JSON<br/>';
			echo '<br/>';
			echo 'Import recipes from Paprika';
		} else {
			echo '<a href="' . admin_url( 'admin.php?page=wprm_import_json' ) . '">Import recipes from JSON</a><br/>';
			echo '<a href="' . admin_url( 'admin.php?page=wprm_import_taxonomies' ) . '">Import taxonomy terms from JSON</a><br/>';
			echo '<br/>';
			echo '<a href="' . admin_url( 'admin.php?page=wprm_import_paprika' ) . '">Import recipes from Paprika</a><br/>';
		}
		?>
	</p>

	<h3><?php esc_html_e( 'Import recipes from other plugins', 'wp-recipe-maker' ); ?></h3>
	<?php
	$recipes_to_import = array();
	foreach ( self::$importers as $importer ) {
		$recipe_count = $importer->get_recipe_count();

		if ( intval( $recipe_count ) > 0 || $importer->requires_search() ) {
			$recipes_to_import[ $importer->get_uid() ] = array(
				'name' => $importer->get_name(),
				'requires_search' => $importer->requires_search(),
				'count' => $recipe_count,
			);
		}
	}

	if ( 0 === count( $recipes_to_import ) ) :
		echo '<p>' . esc_html__( 'No recipes found.', 'wp-recipe-maker' ) . '</p>';
	else :
	?>
		<?php foreach ( $recipes_to_import as $uid => $importer ) : ?>
			<h4 style="margin-bottom: 0"><?php echo esc_html( $importer['name'] ); ?></h4>
			<?php if ( $importer['requires_search'] ) : ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'from' => $uid ), admin_url( 'admin.php?page=wprm_import_search' ) ) ); ?>"><?php esc_html_e( 'Search for recipes', 'wp-recipe-maker' ); ?></a><br/?>
			<?php endif; // Requires search. ?>
			<?php if ( intval( $importer['count'] ) > 0 ) : ?>
			<?php
			if ( is_int( $importer['count'] ) ) {
				printf( esc_html( _n( '%d recipe found', '%d recipes found', $importer['count'], 'wp-recipe-maker' ) ), intval( $importer['count'] ) );
			} else {
				echo esc_html( $importer['count'] ) . ' ' . esc_html__( ' recipes found' );
			}
			?><br />
			<a href="<?php echo esc_url( add_query_arg( array( 'from' => $uid, 'p' => 0 ), admin_url( 'admin.php?page=wprm_import' ) ) ); ?>"><?php esc_html_e( 'Explore import options', 'wp-recipe-maker' ); ?></a>
			<?php endif; // Recipe count. ?>
		<?php endforeach; // Each importer. ?>
	<?php endif; // Recipes to import. ?>

	<h3><?php esc_html_e( 'After Importing', 'wp-recipe-maker' ); ?></h3>
	<?php
	$imported_recipes = array();
	foreach ( self::$importers as $importer ) {
		$recipes = self::get_imported_recipes( $importer->get_uid(), true );

		if ( count( $recipes ) > 0 ) {
			$imported_recipes[ $importer->get_uid() ] = array(
				'name' => $importer->get_name(),
				'recipes' => $recipes,
			);
		}
	}

	if ( 0 === count( $imported_recipes ) ) :
		echo '<p>' . esc_html__( 'No imported recipes found.', 'wp-recipe-maker' ) . '</p>';
	else :
	?>
	<p>
		<?php esc_html_e( 'After importing your recipes, run a Health Check on the WP Recipe Maker > Dashboard page.', 'wp-recipe-maker' ); ?>
	</p>
	<p>
		<?php esc_html_e( 'We recommend going through all of these recipes to make sure the import process was successful. Pay attention to the different ingredient parts to be able to make use of all of our features.', 'wp-recipe-maker' ); ?> <?php esc_html_e( 'After doing so you can mark a recipe as checked to keep track of the recipes you still have to go through.', 'wp-recipe-maker' ); ?>
	</p>
	<p>
		<?php esc_html_e( 'Getting a lot of recipes without parent post or are you missing any ratings?', 'wp-recipe-maker' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_tools' ) ); ?>"><?php esc_html_e( 'Use our Find Parents tool first and Find Ratings afterwards', 'wp-recipe-maker' ); ?></a>.
	</p>
		<?php foreach ( $imported_recipes as $uid => $importer ) : ?>
		<h4 style="margin-bottom: 0"><?php echo esc_html( $importer['name'] ); ?></h4>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="wprm_check_imported_recipes">
			<input type="hidden" name="importer" value="<?php echo esc_attr( $uid ); ?>">
			<?php wp_nonce_field( 'wprm_check_imported_recipes', 'wprm_check_imported_recipes', false ); ?>
			<table class="wprm-import-recipes">
				<tbody>
					<?php foreach ( $importer['recipes'] as $post ) :
						$recipe = WPRM_Recipe_Manager::get_recipe( $post ); ?>
						<tr>
							<td>
								<input type="checkbox" name="recipes[]" value="<?php echo esc_attr( $recipe->id() ); ?>" />
							</td>
							<td>
								<a href="#" class="wprm-import-recipes-actions-edit" data-id="<?php echo esc_attr( $recipe->id() ); ?>"><span class="dashicons dashicons-edit"></span></a> <?php echo esc_html( $recipe->name() ); ?>
							</td>
							<td>
								<?php if ( $recipe->parent_post_id() > 0 ) : ?>
								<a href="<?php echo esc_url( get_edit_post_link( $recipe->parent_post_id() ) ); ?>" target="_blank"><span class="dashicons dashicons-edit"></span></a> <a href="<?php echo esc_url( get_permalink( $recipe->parent_post_id() ) ); ?>" target="_blank"><span class="dashicons dashicons-visibility"></span></a> <?php echo get_the_title( $recipe->parent_post_id() ); ?>
								<?php else : ?>
								<?php esc_html_e( 'no parent post found', 'wp-recipe-maker' ); ?>
								<?php endif; // Parent Post ID. ?>
							</td>
						</tr>
					<?php endforeach; // Each recipe. ?>
				</tbody>
			</table>
			<?php submit_button( __( 'Mark Selected Recipes as Checked', 'wp-recipe-maker' ) ); ?>
		</form>
		<?php endforeach; // Each importer. ?>
	<?php endif; // Recipes to import. ?>
</div>
