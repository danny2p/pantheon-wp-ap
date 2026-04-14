<?php
/**
 * Template for recipe import overview page.
 *
 * @link       https://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/import
 */

?>

<div class="wrap wprm-import wprm-tools-page wprm-admin-page-cards">
	<?php
	$has_elite_ai_import = defined( 'WPRMP_BUNDLE' ) && 'Elite' === WPRMP_BUNDLE;
	$ai_import_url = $has_elite_ai_import ? admin_url( 'admin.php?page=wprm_ai_assistant&tool=import_with_ai' ) : admin_url( 'admin.php?page=wprm_ai_assistant' );
	$learn_more_label = __( 'Learn More', 'wp-recipe-maker' );
	$render_docs_link = static function( $url ) use ( $learn_more_label ) {
		?>
		<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" class="wprm-tools-docs-link wprm-admin-tippy" aria-label="<?php echo esc_attr( $learn_more_label ); ?>" data-wprm-tooltip="<?php echo esc_attr( $learn_more_label ); ?>">
			<span class="dashicons dashicons-editor-help" aria-hidden="true"></span>
		</a>
		<?php
	};
	?>

	<div class="wprm-tools-hero">
		<div>
			<h1><?php esc_html_e( 'Import Recipes', 'wp-recipe-maker' ); ?></h1>
			<p><?php esc_html_e( 'Import recipes from other plugins, JSON files, Paprika, or convert text to recipes using the AI Assistant.', 'wp-recipe-maker' ); ?></p>
		</div>
	</div>

	<h3 class="wprm-tools-section-title"><?php esc_html_e( 'Import recipes', 'wp-recipe-maker' ); ?></h3>
	<div class="wprm-tools-grid">
		<?php if ( WPRM_Addons::is_active( 'premium' ) ) : ?>
		<div class="wprm-tools-panel">
			<?php $render_docs_link( 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-recipes-from-json/' ); ?>
			<span class="wprm-tools-tag"><?php esc_html_e( 'Premium', 'wp-recipe-maker' ); ?></span>
			<h2><?php esc_html_e( 'Import Recipes from JSON', 'wp-recipe-maker' ); ?></h2>
			<p><?php esc_html_e( 'Import recipes by uploading a JSON file.', 'wp-recipe-maker' ); ?></p>
			<div class="wprm-tools-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_import_json' ) ); ?>" class="button button-secondary button-compact"><?php esc_html_e( 'Import from JSON', 'wp-recipe-maker' ); ?></a>
			</div>
		</div>

		<div class="wprm-tools-panel">
			<?php $render_docs_link( 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/json-import-and-export-for-taxonomy-terms/' ); ?>
			<span class="wprm-tools-tag"><?php esc_html_e( 'Premium', 'wp-recipe-maker' ); ?></span>
			<h2><?php esc_html_e( 'Import Taxonomy Terms from JSON', 'wp-recipe-maker' ); ?></h2>
			<p><?php esc_html_e( 'Import taxonomy terms by uploading a JSON file.', 'wp-recipe-maker' ); ?></p>
			<div class="wprm-tools-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_import_taxonomies' ) ); ?>" class="button button-secondary button-compact"><?php esc_html_e( 'Import Taxonomy Terms', 'wp-recipe-maker' ); ?></a>
			</div>
		</div>

		<div class="wprm-tools-panel">
			<?php $render_docs_link( 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-recipes-from-the-paprika-recipe-manager-app/' ); ?>
			<span class="wprm-tools-tag"><?php esc_html_e( 'Premium', 'wp-recipe-maker' ); ?></span>
			<h2><?php esc_html_e( 'Import Recipes from Paprika', 'wp-recipe-maker' ); ?></h2>
			<p><?php esc_html_e( 'Import recipes exported from the Paprika recipe manager app.', 'wp-recipe-maker' ); ?></p>
			<div class="wprm-tools-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_import_paprika' ) ); ?>" class="button button-secondary button-compact"><?php esc_html_e( 'Import from Paprika', 'wp-recipe-maker' ); ?></a>
			</div>
		</div>
		<?php else : ?>
		<div class="wprm-tools-panel">
			<?php $render_docs_link( 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-recipes-from-json/' ); ?>
			<span class="wprm-tools-tag"><?php esc_html_e( 'Premium', 'wp-recipe-maker' ); ?></span>
			<h2><?php esc_html_e( 'JSON & Paprika Import', 'wp-recipe-maker' ); ?></h2>
			<p><?php esc_html_e( 'Import recipes from JSON files, taxonomy terms from JSON, or recipes from Paprika.', 'wp-recipe-maker' ); ?></p>
			<div class="wprm-tools-actions">
				<a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank" class="button button-secondary button-compact"><?php esc_html_e( 'Get WP Recipe Maker Premium', 'wp-recipe-maker' ); ?></a>
			</div>
		</div>
		<?php endif; ?>

		<div class="wprm-tools-panel">
			<?php $render_docs_link( 'https://help.bootstrapped.ventures/import-text-with-ai/' ); ?>
			<span class="wprm-tools-tag"><?php esc_html_e( 'AI', 'wp-recipe-maker' ); ?></span>
			<h2><?php esc_html_e( 'Import Recipe from Text with AI', 'wp-recipe-maker' ); ?></h2>
			<p><?php esc_html_e( 'Use the AI Assistant to convert text into a structured recipe.', 'wp-recipe-maker' ); ?></p>
			<div class="wprm-tools-actions">
				<a href="<?php echo esc_url( $ai_import_url ); ?>" class="button button-secondary button-compact"><?php esc_html_e( 'Open AI Assistant', 'wp-recipe-maker' ); ?></a>
			</div>
		</div>
	</div>

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

	$importer_docs_urls = array(
		'bigoven'            => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-bigoven/',
		'cookbook'           => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-cookbook/',
		'cooked'             => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-cooked/',
		'create'             => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-create-by-mediavine/',
		'easyrecipe'         => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-easyrecipe/',
		'foodiepress'        => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-foodiepress/',
		// 'json-ld'            => '',
		'mealplannerpro'     => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-meal-planner-pro/',
		'purr'               => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-purr-recipe-plugin/',
		'recipecard'         => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-recipe-card/',
		'recipes-generator'  => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-recipes-generator/',
		'simmer'             => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-recipes-by-simmer/',
		'simplerecipepro'    => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-simple-recipe-pro/',
		'simplerecipepro-new'=> 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-simple-recipe-pro/',
		'tasty'              => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-tasty-recipes/',
		'wordpress'          => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-wordpress-com-shortcode/',
		'wpdelicious'        => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-wp-delicious/',
		'wpultimaterecipe'   => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-wp-ultimate-recipe/',
		'wpzoom'             => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-recipe-card-blocks-by-wpzoom/',
		'wpzoom_cpt'         => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-recipe-card-blocks-by-wpzoom/',
		'yummly'             => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-yummly/',
		'ziplist'            => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-ziplist/',
	);

	if ( count( $recipes_to_import ) > 0 ) :
	?>
	<h3 class="wprm-tools-section-title"><?php esc_html_e( 'Import from Other Plugins', 'wp-recipe-maker' ); ?></h3>
	<div class="wprm-import-warning">
		<span class="dashicons dashicons-warning"></span>
		<div>
			<strong><?php esc_html_e( 'Before you start', 'wp-recipe-maker' ); ?></strong>
			<p>
				<?php esc_html_e( "Importing recipes will convert them to our format and they won't be available in the old plugin anymore. We recommend backing up before starting the process and trying to import 1 single recipe first to make sure everything converts properly.", 'wp-recipe-maker' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'If your current plugin uses custom post types and has different permalinks than regular posts you might want to use a redirection plugin to set up 301 redirects.', 'wp-recipe-maker' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'This section may show recipes found for multiple plugins. Make sure to only use the importer that matches the plugin you were actually using, as some importers can incorrectly detect recipes from other sources.', 'wp-recipe-maker' ); ?>
			</p>
			<a href="mailto:support@bootstrapped.ventures" class="button button-primary button-compact"><?php esc_html_e( 'Contact Us If You Have Any Questions', 'wp-recipe-maker' ); ?></a>
		</div>
	</div>
	<div class="wprm-tools-grid">
		<?php foreach ( $recipes_to_import as $uid => $importer ) : ?>
		<div class="wprm-tools-panel">
			<span class="wprm-tools-tag is-migration"><?php esc_html_e( 'Migration', 'wp-recipe-maker' ); ?></span>
			<h2><?php echo esc_html( $importer['name'] ); ?></h2>
			<p>
				<?php
				if ( intval( $importer['count'] ) > 0 ) {
					if ( is_int( $importer['count'] ) ) {
						// translators: %d: number of recipes found to import.
						printf( esc_html( _n( '%d recipe found', '%d recipes found', $importer['count'], 'wp-recipe-maker' ) ), intval( $importer['count'] ) );
					} else {
						echo esc_html( $importer['count'] ) . ' ' . esc_html__( ' recipes found', 'wp-recipe-maker' );
					}
				}
				?>
			</p>
			<div class="wprm-tools-actions">
				<?php if ( $importer['requires_search'] ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'from' => $uid ), admin_url( 'admin.php?page=wprm_import_search' ) ) ); ?>" class="button button-secondary button-compact"><?php esc_html_e( 'Search for Recipes', 'wp-recipe-maker' ); ?></a>
				<?php endif; ?>
				<?php if ( intval( $importer['count'] ) > 0 ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'from' => $uid, 'p' => 0 ), admin_url( 'admin.php?page=wprm_import' ) ) ); ?>" class="button button-secondary button-compact"><?php esc_html_e( 'Explore Import Options', 'wp-recipe-maker' ); ?></a>
				<?php endif; ?>
				<?php if ( isset( $importer_docs_urls[ $uid ] ) ) : ?>
				<?php $render_docs_link( $importer_docs_urls[ $uid ] ); ?>
				<?php endif; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

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

	if ( count( $imported_recipes ) > 0 ) :
	?>
	<h3 class="wprm-tools-section-title"><?php esc_html_e( 'After Importing', 'wp-recipe-maker' ); ?></h3>
	<div class="wprm-tools-grid">
		<div class="wprm-tools-panel" style="grid-column: 1 / -1;">
			<span class="wprm-tools-tag"><?php esc_html_e( 'Info', 'wp-recipe-maker' ); ?></span>
			<h2><?php esc_html_e( 'Review Imported Recipes', 'wp-recipe-maker' ); ?></h2>
			<p>
				<?php esc_html_e( 'After importing your recipes, run a Health Check on the WP Recipe Maker > Dashboard page.', 'wp-recipe-maker' ); ?>
				<?php esc_html_e( 'We recommend going through all of these recipes to make sure the import process was successful. Pay attention to the different ingredient parts to be able to make use of all of our features.', 'wp-recipe-maker' ); ?>
				<?php esc_html_e( 'After doing so you can mark a recipe as checked to keep track of the recipes you still have to go through.', 'wp-recipe-maker' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Getting a lot of recipes without parent post or are you missing any ratings?', 'wp-recipe-maker' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_tools' ) ); ?>"><?php esc_html_e( 'Use our Find Parents tool first and Find Ratings afterwards', 'wp-recipe-maker' ); ?></a>.
			</p>

			<?php foreach ( $imported_recipes as $uid => $importer ) : ?>
			<h4 style="margin: 16px 0 8px 0;"><?php echo esc_html( $importer['name'] ); ?></h4>
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
									<a href="<?php echo esc_url( get_edit_post_link( $recipe->parent_post_id() ) ); ?>" target="_blank"><span class="dashicons dashicons-edit"></span></a> <a href="<?php echo esc_url( get_permalink( $recipe->parent_post_id() ) ); ?>" target="_blank"><span class="dashicons dashicons-visibility"></span></a> <?php echo esc_html( get_the_title( $recipe->parent_post_id() ) ); ?>
									<?php else : ?>
									<?php esc_html_e( 'no parent post found', 'wp-recipe-maker' ); ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button( __( 'Mark Selected Recipes as Checked', 'wp-recipe-maker' ), 'button button-primary button-compact' ); ?>
			</form>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</div>
