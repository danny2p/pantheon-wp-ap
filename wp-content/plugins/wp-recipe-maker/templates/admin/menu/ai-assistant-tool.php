<?php
/**
 * Template for an AI Assistant tool sub-page.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.5.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu
 */

$back_url = admin_url( 'admin.php?page=wprm_ai_assistant' );
?>

<div class="wrap wprm-ai-assistant-page">
	<div style="margin-top: 20px;">
		<a href="<?php echo esc_url( $back_url ); ?>" class="wprm-ai-assistant-back-link">&larr; <?php esc_html_e( 'Back to AI Assistant', 'wp-recipe-maker' ); ?></a>
	</div>
	<div id="wprm-ai-assistant-tool" data-tool="<?php echo esc_attr( $tool ); ?>"></div>
</div>
