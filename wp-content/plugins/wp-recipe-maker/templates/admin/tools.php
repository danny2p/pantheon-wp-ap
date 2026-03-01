<?php
/**
 * Template for tools page.
 *
 * @link       https://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin
 */

?>

<div class="wrap wprm-tools">
	<h1><?php esc_html_e( 'WP Recipe Maker Tools', 'wp-recipe-maker' ); ?></h1>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Find Parent Posts', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_finding_parents' ) ); ?>" class="button" id="tools_finding_parents"><?php esc_html_e( 'Find Parent Posts', 'wp-recipe-maker' ); ?></a>
					<p class="description" id="tagline-tools_finding_parents">
						<?php
							esc_html_e( 'Go through all posts and pages on your website to find and link recipes to their parent.', 'wp-recipe-maker' );
							if ( WPRM_Settings::get( 'parent_post_autolock' ) ) {
								echo '<br/><strong>' . esc_html( __( 'Important:', 'wp-recipe-maker' ) ) . '</strong> ';
								esc_html_e( 'Automatic locking of the parent post is enabled on the WP Recipe Maker > Settings > Post Type & Taxonomies page, so the parent post will only change for recipes that do not have a parent post set.', 'wp-recipe-maker' );
							}
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Find Recipe Ratings', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_finding_ratings' ) ); ?>" class="button" id="tools_finding_ratings"><?php esc_html_e( 'Find Recipe Ratings', 'wp-recipe-maker' ); ?></a>
					<p class="description" id="tagline-tools_finding_ratings">
						<?php esc_html_e( 'Go through all recipes on your website to find any missing ratings.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Fix Comment Ratings', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_fixing_comment_ratings' ) ); ?>" class="button" id="tools_fixing_comment_ratings"><?php esc_html_e( 'Fix Comment Ratings', 'wp-recipe-maker' ); ?></a>
					<p class="description" id="tagline-tools_fixing_comment_ratings">
						<?php esc_html_e( 'Go through all comment ratings and remove any rating where the associated comment does not exist anymore.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Refresh Video Metadata', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_refresh_video_metadata' ) ); ?>" class="button" id="tools_refresh_video_metadata"><?php esc_html_e( 'Refresh Video Metadata', 'wp-recipe-maker' ); ?></a>
					<p class="description" id="tagline-tools_refresh_video_metadata">
						<?php esc_html_e( 'Refresh the video metadata for all recipes on your website.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
			<?php if ( 'private' === WPRM_Settings::get( 'post_type_structure' ) && WPRM_Compatibility::multilingual() ) : ?>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Sync Recipe Language', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_sync_recipe_language' ) ); ?>" class="button" id="tools_sync_recipe_language"><?php esc_html_e( 'Sync Recipe Language', 'wp-recipe-maker' ); ?></a>
					<p class="description" id="tagline-tools_sync_recipe_language">
						<?php esc_html_e( 'Sync the recipe language to match the parent post language for all recipes on your website.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Find Ingredient Units', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_find_ingredient_units' ) ); ?>" class="button" id="tools_find_ingredient_units"><?php esc_html_e( 'Find Ingredient Units', 'wp-recipe-maker' ); ?></a>
					<p class="description" id="tagline-tools_find_ingredient_units">
						<?php esc_html_e( 'Use to make sure the WP Recipe Maker > Manage > Recipe Fields > Ingredient Units page is up to date.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Health Check', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_health_check' ) ); ?>" class="button" id="tools_health_check"><?php esc_html_e( 'Run Health Check', 'wp-recipe-maker' ); ?></a>
					<p class="description" id="tagline-tools_health_check">
						<?php esc_html_e( 'Perform a health check of the plugin and your recipes.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<h2><?php esc_html_e( 'Settings Utilities', 'wp-recipe-maker' ); ?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Export Settings', 'wp-recipe-maker' ); ?>
					</th>
					<td>
						<button type="button" class="button" id="tools_export_settings"><?php esc_html_e( 'Export Settings', 'wp-recipe-maker' ); ?></button>
						<p class="description" id="tagline-tools_export_settings">
							<?php esc_html_e( 'Download all WP Recipe Maker settings as a JSON file so you can restore them later or move them to another site.', 'wp-recipe-maker' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Import Settings', 'wp-recipe-maker' ); ?>
					</th>
					<td>
						<form id="wprm-import-settings-form" enctype="multipart/form-data">
							<input type="file" name="wprm_settings_file" accept=".json,application/json" required />
							<button type="submit" class="button"><?php esc_html_e( 'Import Settings', 'wp-recipe-maker' ); ?></button>
						</form>
						<p class="description">
							<?php esc_html_e( 'Upload a settings export file to import those values on this site. This will overwrite existing settings.', 'wp-recipe-maker' ); ?>
						</p>
						<p class="description" id="wprm-import-settings-result"></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Reset Settings', 'wp-recipe-maker' ); ?>
					</th>
					<td>
						<a href="#" class="button" id="tools_reset_settings"><?php esc_html_e( 'Reset Settings to Default', 'wp-recipe-maker' ); ?></a>
						<p class="description" id="tagline-tools_reset_settings">
							<?php esc_html_e( 'Try using this if the settings page is not working at all.', 'wp-recipe-maker' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<h2><?php esc_html_e( 'Template Utilities', 'wp-recipe-maker' ); ?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Export Templates', 'wp-recipe-maker' ); ?>
					</th>
					<td>
						<button type="button" class="button" id="tools_export_templates"><?php esc_html_e( 'Export Templates', 'wp-recipe-maker' ); ?></button>
						<p class="description">
							<?php esc_html_e( 'Download all user-created recipe templates so you can move them to another site or keep a backup.', 'wp-recipe-maker' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Import Templates', 'wp-recipe-maker' ); ?>
					</th>
					<td>
						<form id="wprm-import-templates-form" enctype="multipart/form-data">
							<input type="file" name="wprm_templates_file" accept=".json,application/json" required />
							<button type="submit" class="button"><?php esc_html_e( 'Import Templates', 'wp-recipe-maker' ); ?></button>
						</form>
						<p class="description">
							<?php esc_html_e( 'Upload a template export file to add those templates to this site. Existing templates with the same slug will be updated.', 'wp-recipe-maker' ); ?>
						</p>
						<p class="description" id="wprm-import-templates-result"></p>
					</td>
				</tr>
			</tbody>
		</table>
	<?php endif; ?>
<?php
	if ( class_exists( 'WPRMP_Amazon_api' ) ) :
		$partner_tag_set = WPRM_Settings::get( 'amazon_partner_tag' ) ? true : false;
		$api_credentials_set = class_exists( 'WPRMP_Amazon_Api_Factory' ) ? WPRMP_Amazon_Api_Factory::has_api_credentials() : ( class_exists( 'WPRMP_Amazon_Api' ) ? WPRMP_Amazon_Api::validate_api_credentials() : false );
?>
	<h2><?php esc_html_e( 'Amazon Affiliate Links Migration for Equipment', 'wp-recipe-maker' ); ?></h2>
	<?php if ( ! $partner_tag_set ) : ?>
		<p><?php esc_html_e( 'Set your Amazon Store ID on the WP Recipe Maker > Settings > Amazon Products page to enable these tools.', 'wp-recipe-maker' ); ?></p><br/>
	<?php else : ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Convert Affiliate HTML Code', 'wp-recipe-maker' ); ?>
					</th>
					<td>
						<?php if ( $api_credentials_set ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_amazon_html_products' ) ); ?>" class="button"><?php esc_html_e( 'Convert to Products', 'wp-recipe-maker' ); ?></a> <a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_amazon_html_links' ) ); ?>" class="button"><?php esc_html_e( 'Convert to Regular Links', 'wp-recipe-maker' ); ?></a>
						<?php else : ?>
							<a class="button button-disabled"><?php esc_html_e( 'Convert to Products', 'wp-recipe-maker' ); ?></a> <a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_amazon_html_links' ) ); ?>" class="button"><?php esc_html_e( 'Convert to Regular Links', 'wp-recipe-maker' ); ?></a>
						<?php endif; ?>
						<p class="description">
							<?php
								esc_html_e( 'Converts Amazon Affiliate HTML code to either products or regular links.', 'wp-recipe-maker' );
								if ( ! $api_credentials_set ) {
									echo ' ';
									esc_html_e( 'Converting to products is only possible when the Amazon API credentials are set on the WP Recipe Maker > Settings > Amazon Products page.', 'wp-recipe-maker' );
								}
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Convert Regular Links', 'wp-recipe-maker' ); ?>
					</th>
					<td>
						<?php if ( $api_credentials_set ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_amazon_links_to_products' ) ); ?>" class="button"><?php esc_html_e( 'Convert to Products', 'wp-recipe-maker' ); ?></a>
						<?php else : ?>
							<a class="button button-disabled"><?php esc_html_e( 'Convert to Products', 'wp-recipe-maker' ); ?></a>
						<?php endif; ?>
						<p class="description">
							<?php
								esc_html_e( 'Converts regular Amazon equipment links to products. Does not work for short links.', 'wp-recipe-maker' );
								if ( ! $api_credentials_set ) {
									echo ' ';
									esc_html_e( 'Converting to products is only possible when the Amazon API credentials are set on the WP Recipe Maker > Settings > Amazon Products page.', 'wp-recipe-maker' );
								}
							?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	<?php endif; // Partner tag set. ?>
<?php endif; // Amazon class exists. ?>
<?php if ( class_exists( 'WPUltimateRecipe' ) ) : ?>
	<h2><?php esc_html_e( 'WP Ultimate Recipe Migration', 'wp-recipe-maker' ); ?></h2>
	<p><?php esc_html_e( 'Use these buttons to migrate from our old WP Ultimate Recipe plugin.', 'wp-recipe-maker' ); ?></p>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Import Ingredient Links', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_wpurp_ingredients&field=link' ) ); ?>" class="button"><?php esc_html_e( 'Import Ingredient Links', 'wp-recipe-maker' ); ?></a>
					<p class="description">
						<?php esc_html_e( 'Import all ingredients that have ingredient links set.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Import Ingredient Plural', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_wpurp_ingredients&field=plural' ) ); ?>" class="button"><?php esc_html_e( 'Import Ingredient Plural', 'wp-recipe-maker' ); ?></a>
					<p class="description">
						<?php esc_html_e( 'Import all ingredients that have a plural set.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Import Shopping List Groups', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_wpurp_ingredients&field=group' ) ); ?>" class="button"><?php esc_html_e( 'Import Shopping List Groups', 'wp-recipe-maker' ); ?></a>
					<p class="description">
						<?php esc_html_e( 'Import all ingredients that have a shopping list group set for use in the Recipe Collections feature.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
			<?php if ( taxonomy_exists( 'wprm_nutrition_ingredient' ) ) : ?>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Import Nutrition Facts', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_wpurp_nutrition' ) ); ?>" class="button"><?php esc_html_e( 'Import Nutrition Facts', 'wp-recipe-maker' ); ?></a>
					<p class="description">
						<?php esc_html_e( 'Import all ingredients that have nutrition facts set. These will become Custom Nutrition Ingredients', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
			<?php endif; // Taxonomy exists. ?>
		</tbody>
	</table>
<?php endif; // WP Ultimate Recipe is active. ?>
<?php
	global $wpdb;
	$table = $wpdb->prefix . 'mv_creations';

	if ( $table === $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) ) :
?>
	<h2><?php esc_html_e( 'Mediavine Create Migration', 'wp-recipe-maker' ); ?></h2>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Convert Reviews', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_create_reviews' ) ); ?>" class="button"><?php esc_html_e( 'Convert Reviews to Comments', 'wp-recipe-maker' ); ?></a>
					<p class="description">
						<?php esc_html_e( 'Converts MV Create reviews to regular comments with a comment rating. Will only do this after recipes have been imported to WPRM. Comments will be created for the parent post of the WPRM recipe.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Create Lists', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_create_lists' ) ); ?>" class="button"><?php esc_html_e( 'Recreate Lists in WP Recipe Maker', 'wp-recipe-maker' ); ?></a>
					<p class="description">
						<?php esc_html_e( 'Will create a WPRM Roundup List for your Create Lists. Make sure to import the recipes themselves first. Will not automatically replace the Create List inside of the post content. Running again will replace the previously imported Create List.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
<?php endif; // MV Create Reviews exist. ?>
<?php if ( current_user_can( 'manage_options' ) ) : ?>
	<h2><?php esc_html_e( 'Debug Utilities', 'wp-recipe-maker' ); ?></h2>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Download Debug Information', 'wp-recipe-maker' ); ?>
				</th>
				<td>
					<button type="button" class="button" id="tools_download_debug_info"><?php esc_html_e( 'Download Debug Information', 'wp-recipe-maker' ); ?></button>
					<p class="description">
						<?php esc_html_e( 'Download a JSON file with diagnostic information to share with our support team.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
<?php endif; ?>
</div>
