<?php
/**
 * Template for the AI Assistant page.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.1
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin/menu
 */

$documentation_url = 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/ai-assistant/';
$learn_more_label = __( 'Learn More', 'wp-recipe-maker' );
?>

<div class="wrap wprm-ai-assistant-page wprm-admin-page-cards">
	<div class="wprm-ai-assistant-hero">
		<span class="wprm-ai-assistant-icon" aria-hidden="true"></span>
		<div>
			<h1><?php esc_html_e( 'AI Assistant', 'wp-recipe-maker' ); ?></h1>
			<p><?php esc_html_e( 'The new AI Assistant is designed to help with repetitive recipe work, reduce manual cleanup, and make managing recipe content more efficient. It is currently in beta and will keep expanding from this page.', 'wp-recipe-maker' ); ?></p>
			<p><?php esc_html_e( 'You can already learn more about what is planned, how it works, and how the feature will evolve in the documentation.', 'wp-recipe-maker' ); ?></p>
			<div class="wprm-ai-assistant-actions">
				<a class="button button-primary button-compact" href="<?php echo esc_url( $documentation_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View Documentation', 'wp-recipe-maker' ); ?></a>
			</div>
		</div>
	</div>

	<div class="wprm-import-warning">
		<span class="dashicons dashicons-warning"></span>
		<div>
			<strong><?php esc_html_e( 'Beta Notice', 'wp-recipe-maker' ); ?></strong>
			<p><?php esc_html_e( 'These AI Assistant features are still in beta while we figure out how to handle pricing and which features make the most sense and will actually get used.', 'wp-recipe-maker' ); ?></p>
			<p><?php esc_html_e( 'That means features could stop working or change functionality altogether as things evolve.', 'wp-recipe-maker' ); ?></p>
			<p>
				<?php
				printf(
					wp_kses(
						/* translators: %s: feedback email link. */
						__( 'Feedback at %s is appreciated, especially around potential improvements and new AI integration ideas.', 'wp-recipe-maker' ),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					),
					'<a href="' . esc_url( 'mailto:support@bootstrapped.ventures' ) . '">support@bootstrapped.ventures</a>'
				);
				?>
			</p>
		</div>
	</div>

	<div class="wprm-ai-assistant-grid">
		<?php foreach ( $tools as $tool ) : ?>
			<div class="wprm-ai-assistant-panel">
				<?php if ( ! empty( $tool['documentation_url'] ) ) : ?>
					<a href="<?php echo esc_url( $tool['documentation_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="wprm-ai-assistant-docs-link wprm-admin-tippy" aria-label="<?php echo esc_attr( $learn_more_label ); ?>" data-wprm-tooltip="<?php echo esc_attr( $learn_more_label ); ?>">
						<span class="dashicons dashicons-editor-help" aria-hidden="true"></span>
					</a>
				<?php endif; ?>
				<span class="wprm-ai-assistant-tag"><?php esc_html_e( 'AI Tool', 'wp-recipe-maker' ); ?></span>
				<h2><?php echo esc_html( $tool['name'] ); ?></h2>
				<p><?php echo esc_html( $tool['description'] ); ?></p>
				<div class="wprm-ai-assistant-tool-links">
					<?php
					$button = isset( $tool['button'] ) && is_array( $tool['button'] ) ? $tool['button'] : array();
					$button_label = isset( $button['label'] ) ? $button['label'] : '';
					$button_url = isset( $button['url'] ) ? $button['url'] : '';
						$button_tooltip = isset( $button['tooltip'] ) ? $button['tooltip'] : '';
						$button_class = isset( $button['class'] ) ? $button['class'] : 'button button-compact';
						$button_html = isset( $button['html'] ) ? $button['html'] : '';
						$button_callback = isset( $button['callback'] ) ? $button['callback'] : false;
						$button_new_tab = isset( $button['new_tab'] ) ? (bool) $button['new_tab'] : true;
						?>
					<?php if ( is_callable( $button_callback ) ) : ?>
						<?php
						$button_output = call_user_func( $button_callback, $tool, $button );
						if ( $button_output ) {
							echo $button_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					<?php elseif ( $button_html ) : ?>
						<?php echo $button_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php elseif ( $button_label && $button_url ) : ?>
						<span class="<?php echo $button_tooltip ? 'wprm-ai-assistant-tippy' : ''; ?>" data-wprm-tooltip="<?php echo esc_attr( $button_tooltip ); ?>">
							<a class="<?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_url( $button_url ); ?>"<?php echo $button_new_tab ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> aria-disabled="<?php echo $button_tooltip ? 'true' : 'false'; ?>">
								<span class="wprm-ai-assistant-icon" aria-hidden="true"></span>
								<span><?php echo esc_html( $button_label ); ?></span>
							</a>
						</span>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
