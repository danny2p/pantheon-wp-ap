<?php
/**
 * Template to be used for the popup modal.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/public
 */
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-type="<?php echo esc_attr( $type ); ?>" aria-hidden="true">
	<?php
		if ( $container ) {
			// Overtaking entire modal (for example to handle content in React).
			echo $container;
		} else {
	?>
	<div class="wprm-popup-modal__overlay" tabindex="-1" data-micromodal-close>
		<div class="wprm-popup-modal__container" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
			<header class="wprm-popup-modal__header">
				<h2 class="wprm-popup-modal__title" id="<?php echo esc_attr( $id ); ?>-title">
					<?php echo $title; ?>
				</h2>

				<button class="wprm-popup-modal__close" aria-label="<?php _e( 'Close', 'wp-recipe-maker' ) ?>" data-micromodal-close></button>
			</header>

			<div class="wprm-popup-modal__content" id="<?php echo esc_attr( $id ); ?>-content">
				<?php echo $content; ?>
			</div>

			<?php
				if ( $buttons ) :
			?>
			<footer class="wprm-popup-modal__footer">
				<?php
				foreach ( $buttons as $button ) {
					$button_classes = array(
						'wprm-popup-modal__btn',
					);

					if ( isset( $button['primary'] ) && $button['primary'] ) { $button_classes[] = 'wprm-popup-modal__btn-primary'; }
					if ( isset( $button['class'] ) ) { $button_classes[] = $button['class']; }

					echo '<button class="' . esc_attr( implode( ' ', $button_classes ) ) . '">' . $button['text'] . '</button>';
				}
				?>
			</footer>
			<?php endif; ?>
		</div>
  	</div>
	<?php } ?>
</div>