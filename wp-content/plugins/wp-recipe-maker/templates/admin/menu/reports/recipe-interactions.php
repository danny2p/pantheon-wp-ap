<?php
/**
 * Template for recipe interactions report.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/reports
 */
?>

<div class="wrap wprm-reports">
	<h2><?php esc_html_e( 'Recipe Interactions Report', 'wp-recipe-maker' ); ?></h2>
	<?php
		if ( ! $report_finished ) :
	?>
	<?php
	// translators: %d: number of recipes left to search through.
	printf( esc_html( _n( 'Searching %d recipe', 'Searching %d recipes', count( $posts ), 'wp-recipe-maker' ) ), count( $posts ) );
	?>.
	<?php
	$progress_bar_type = 'reports';
	include WPRM_DIR . 'templates/admin/progress-bar.php';
	?>
	<?php
		else :
	?>
	<div id="wprm-reports-recipe-interactions">Loading...</div>
	<?php endif; ?>
</div>
