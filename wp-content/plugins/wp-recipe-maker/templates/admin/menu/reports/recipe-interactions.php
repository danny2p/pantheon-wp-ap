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
	<?php printf( esc_html( _n( 'Searching %d recipe', 'Searching %d recipes', count( $posts ), 'wp-recipe-maker' ) ), count( $posts ) ); ?>.
	<div id="wprm-reports-progress-container">
		<div id="wprm-reports-progress-bar"></div>
	</div>
	<?php
		else :
	?>
	<div id="wprm-reports-recipe-interactions">Loading...</div>
	<?php endif; ?>
</div>
