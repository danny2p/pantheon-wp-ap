<?php
/**
 * Template for reports page.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin
 */

?>

<div class="wrap wprm-reports wprm-admin-page-cards">
	<div class="wprm-tools-hero">
		<div>
			<h1><?php esc_html_e( 'WP Recipe Maker Reports', 'wp-recipe-maker' ); ?></h1>
			<p><?php esc_html_e( 'Generate reports to review recipe interactions and other recipe data collected by WP Recipe Maker.', 'wp-recipe-maker' ); ?></p>
		</div>
	</div>

	<h3 class="wprm-tools-section-title"><?php esc_html_e( 'Available Reports', 'wp-recipe-maker' ); ?></h3>
	<div class="wprm-tools-grid">
		<div class="wprm-tools-panel">
			<span class="wprm-tools-tag"><?php esc_html_e( 'Report', 'wp-recipe-maker' ); ?></span>
			<h2><?php esc_html_e( 'Recipe Interactions', 'wp-recipe-maker' ); ?></h2>
			<p><?php esc_html_e( 'Review recipe interaction data collected through Analytics. Make sure Analytics are enabled on the WP Recipe Maker > Settings > Analytics page to use this report.', 'wp-recipe-maker' ); ?></p>
			<div class="wprm-tools-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_report_recipe_interactions' ) ); ?>" class="button button-secondary button-compact" id="report_recipe_interactions"><?php esc_html_e( 'Generate Recipe Interactions Report', 'wp-recipe-maker' ); ?></a>
			</div>
		</div>
		<?php do_action( 'wprm_reports' ); ?>
	</div>
</div>
