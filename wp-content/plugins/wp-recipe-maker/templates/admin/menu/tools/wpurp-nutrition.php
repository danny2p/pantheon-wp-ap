<?php
/**
 * Template for importing ingredient nutrition from WP Ultimate Recipe.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu/tools
 */
?>

<div class="wrap wprm-tools">
	<h2><?php esc_html_e( 'Importing WP Ultimate Recipe Ingredients', 'wp-recipe-maker' ); ?> - <?php esc_html_e( 'Nutrition Facts', 'wp-recipe-maker' ); ?></h2>
	<?php printf( esc_html( _n( 'Searching %d ingredient', 'Searching %d ingredients', count( $ingredients ), 'wp-recipe-maker' ) ), count( $ingredients ) ); ?>.
	<div id="wprm-tools-progress-container">
		<div id="wprm-tools-progress-bar"></div>
	</div>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprm_manage' ) ); ?>" id="wprm-tools-finished"><?php esc_html_e( 'Finished succesfully. Click here to continue.', 'wp-recipe-maker' ); ?></a>
</div>
