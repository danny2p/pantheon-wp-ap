<?php


add_action('wp_ajax_wmufs_admin_notice_ajax_object_save', 'wmufs_admin_notice_ajax_object_callback');

    /**
     * Save option after clicking hide button in WP dashboard.
     *
     * @return void
     */
     function wmufs_admin_notice_ajax_object_callback() {

        $data = isset($_POST['data']) ? sanitize_text_field(wp_unslash($_POST['data'])) : array();

        if ( $data ) {

            // Check valid request form user.
            check_ajax_referer('wmufs_notice_status');

            update_option('wmufs_notice_disable_time', strtotime("+6 Months"));

            $response['message'] = 'success';
            wp_send_json_success($response);
        }

        wp_die();
    }


add_action('admin_footer', 'custom_button_inline_after_upload_limit_by_class');

function custom_button_inline_after_upload_limit_by_class(): void {
	$screen = get_current_screen();

	if ($screen->base === 'media') {
		$custom_link = admin_url('admin.php?page=max_uploader');
		?>
		<script type="text/javascript">
            jQuery(document).ready(function($) {
                // Target the p tag with the max-upload-size class
                var uploadNotice = $('p.max-upload-size');

                // Append inline link after the message
                uploadNotice.append(
                    ' <a href="<?php echo esc_url($custom_link); ?>" style="margin-left: 5px;">Change with - MaxUploader</a>'
                );
            });
		</script>
		<?php
	}
}
