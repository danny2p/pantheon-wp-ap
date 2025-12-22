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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-type="<?php echo esc_attr( $type ); ?>"<?php if ( isset( $modal['recipe_id'] ) ) : ?> data-recipe-id="<?php echo esc_attr( $modal['recipe_id'] ); ?>"<?php endif; ?> aria-hidden="true">
	<?php
		if ( $container ) {
			// Overtaking entire modal (for example to handle content in React).
			echo $container;
		} else {
	?>
	<div class="wprm-popup-modal__overlay" tabindex="-1">
		<div class="wprm-popup-modal__container" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
			<header class="wprm-popup-modal__header">
				<h2 class="wprm-popup-modal__title" id="<?php echo esc_attr( $id ); ?>-title">
					<?php echo esc_html( $title ); ?>
				</h2>

				<button class="wprm-popup-modal__close" aria-label="<?php esc_html_e( 'Close', 'wp-recipe-maker' ) ?>" data-micromodal-close></button>
			</header>

			<div class="wprm-popup-modal__content" id="<?php echo esc_attr( $id ); ?>-content">
				<?php echo $content; ?>
			</div>

			<?php
				if ( $footer || $buttons ) :
			?>
			<footer class="wprm-popup-modal__footer">
				<?php echo $footer; ?>
				<?php
				foreach ( $buttons as $button ) {
					$button_classes = array(
						'wprm-popup-modal__btn',
					);

					if ( isset( $button['primary'] ) && $button['primary'] ) { $button_classes[] = 'wprm-popup-modal__btn-primary'; }
					if ( isset( $button['class'] ) ) { $button_classes[] = $button['class']; }

					$button_attributes = array(
						'type' => 'button',
					);

					if ( isset( $button['attributes'] ) && is_array( $button['attributes'] ) ) {
						foreach ( $button['attributes'] as $attribute_key => $attribute_value ) {
							if ( '' !== $attribute_key ) {
								$button_attributes[ $attribute_key ] = $attribute_value;
							}
						}
					}

					$button_attributes_output = '';

					foreach ( $button_attributes as $attribute_key => $attribute_value ) {
						$button_attributes_output .= ' ' . esc_attr( $attribute_key );

						if ( null !== $attribute_value && '' !== $attribute_value ) {
							$button_attributes_output .= '="' . esc_attr( $attribute_value ) . '"';
						}
					}

					echo '<button class="' . esc_attr( implode( ' ', $button_classes ) ) . '"' . $button_attributes_output . '>' . esc_html( $button['text'] ) . '</button>';
				}
				?>
			</footer>
			<?php endif; ?>
		</div>
  	</div>
	<?php } ?>
</div>