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

<div class="wrap wprm-tools">
	<h1><?php esc_html_e( 'WP Recipe Maker Reports', 'wp-recipe-maker' ); ?></h1>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Recipe Interactions', 'wp-recipe-maker' ); ?>
				</th>
				<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_report_recipe_interactions' ) ); ?>" class="button" id="report_recipe_interactions"><?php esc_html_e( 'Generate Recipe Interactions Report', 'wp-recipe-maker' ); ?></a>
					<p class="description" id="tagline-report_recipe_interactions">
						<?php esc_html_e( 'Make sure Analytics are enabled on the WP Recipe Maker > Settings > Analytics page to use its recipe interactions data.', 'wp-recipe-maker' ); ?>
					</p>
				</td>
			</tr>
			<?php do_action( 'wprm_reports' ); ?>
		</tbody>
	</table>
</div>