<?php
/**
 * Template for the comment moderation meta box.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin
 */

?>
<div class="wprm-comment-moderation-container">
	<div class="wprm-comment-moderation-input-container">
		<div class="wprm-comment-moderation-input">
			<textarea id="wprm-comment-moderation-note" name="wprm-comment-moderation-note" placeholder="<?php echo esc_attr( __( 'Optional private note about this comment', 'wp-recipe-maker' ) ); ?>"></textarea>
		</div>
		<div class="wprm-comment-moderation-input">
			<label for="wprm-comment-moderation-flag">
				<input type="checkbox" id="wprm-comment-moderation-flag" name="wprm-comment-moderation-flag" value="1" <?php checked( get_comment_meta( $comment->comment_ID, 'wprm_comment_moderation_flag', true ), 1 ); ?> />
				<?php _e( 'Flag this comment', 'wp-recipe-maker' ); ?>
			</label>
		</div>
		<?php
		$admin_email = self::get_admin_email();

		if ( $admin_email && ! empty( $admin_email ) ) :
		?>
		<div class="wprm-comment-moderation-input">
			<label for="wprm-comment-moderation-email">
				<input type="checkbox" id="wprm-comment-moderation-email" name="wprm-comment-moderation-email" value="1" />
				<?php _e( 'Send email warning to:', 'wp-recipe-maker' ); ?> <span class="wprm-comment-moderation-admin-email"><?php echo implode( ', ', $admin_email ); ?></span>
			</label>
			
		</div>
		<?php endif; ?>
	</div>
	<?php
	$log = get_comment_meta( $comment->comment_ID, 'wprm_comment_moderation_log', true );

	if ( $log ) {
		echo '<table class="wprm-comment-moderation-log">';
		echo '<tr>';
		echo '<th>' . __( 'Date', 'wp-recipe-maker' ) . '</th>';
		echo '<th>' . __( 'User', 'wp-recipe-maker' ) . '</th>';
		echo '<th>&nbsp;</th>';
		echo '<th>' . __( 'Description', 'wp-recipe-maker' ) . '</th>';
		echo '</tr>';

		$default_text = array(
			'flag' => __( 'Added flag for moderation', 'wp-recipe-maker' ),
			'unflag' => __( 'Removed flag for moderation', 'wp-recipe-maker' ),
			'stars-added' => __( 'Star rating added', 'wp-recipe-maker' ) . ': ',
			'stars-changed' => __( 'Star rating changed', 'wp-recipe-maker' ) . ': ',
			'stars-removed' => __( 'Star rating removed', 'wp-recipe-maker' ) . ': ',
		);

		foreach ( $log as $line ) {
			echo '<tr>';
			echo '<td>' . $line['datetime'] . '</td>';

			// Display user.
			$user = get_user_by( 'id', $line['user'] );
			$user_name = $user ? $user->display_name : __( 'Unknown', 'wp-recipe-maker' );
			$user_display = '#' . $line['user'] . ' - ' . $user_name;
			$user_edit_link = $user ? get_edit_user_link( $user->ID ) : false;

			if ( $user_edit_link ) {
				$user_display = '<a href="' . $user_edit_link . '">' . $user_display . '</a>';
			}
			echo '<td>' . $user_display . '</td>';

			// Display type icon.
			echo '<td>';
			$type = sanitize_key( $line['type'] );
			if ( $type && file_exists( WPRM_DIR . 'assets/icons/comment-moderation/' . $type . '.svg' ) ) {
				echo '<span class="wprm-comment-moderation-icon">';
				echo '<img src="' . WPRM_URL . 'assets/icons/comment-moderation/' . $type . '.svg" alt="" />';
				echo '</span>';
			}

			echo '</td>';

			// Display description.
			$text = isset( $default_text[ $type ] ) ? $default_text[ $type ] : '';
			$text .= isset( $line['text'] ) ? $line['text'] : '';

			echo '<td>' . esc_html( $text ) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	?>
</div>
