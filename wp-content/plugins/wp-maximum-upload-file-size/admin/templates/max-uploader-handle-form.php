<?php

$max_size = get_option('max_file_size');
if ( ! $max_size ) {
    $max_size = wp_max_upload_size();
}
$max_size = $max_size / 1024 / 1024;
$upload_sizes = array(
	'16' => '16 MB',
	'32' => '32 MB',
	'40' => '40 MB',
	'64' => '64 MB',
	'128' => '128 MB',
	'256' => '256 MB',
	'512' => '512 MB',
	'1024' => '1 GB',
	'2048' => '2 GB',
	'3072' => '3 GB',
	'4096' => '4 GB',
	'5120' => '5 GB',
	'10240' => '10 GB',
);

// Add the current WP max upload size if not in predefined list
if ( ! array_key_exists( (string) $max_size, $upload_sizes ) ) {
	$upload_sizes[ (string) $max_size ] = $max_size . ' MB (Detected)';
	ksort($upload_sizes, SORT_NUMERIC);
}


$wpufs_max_execution_time = get_option('wmufs_maximum_execution_time') != '' ? get_option('wmufs_maximum_execution_time') : ini_get('max_execution_time');


?>

<div class="wrap wmufs_mb_50">
    <h1><span class="dashicons dashicons-admin-settings" style="font-size: inherit; line-height: unset;"></span><?php echo esc_html_e( ' Control Upload Limits', 'wp-maximum-upload-file-size' ); ?></h1><br>
    <div class="wmufs_admin_deashboard">
        <!-- Row -->
        <div class="wmufs_row" id="poststuff">

            <!-- Start Content Area -->
            <div class="wmufs_admin_left wmufs_card wmufs-col-8 wmufs_form_centered">
                <div class="wmufs_inner_form_box">
                    <form method="post">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th scope="row"><label for="upload_max_file_size_field">Choose Upload File Size</label></th>
                                <td>
                                    <select id="upload_max_file_size_field" name="upload_max_file_size_field"> <?php
                                        foreach ( $upload_sizes as $key => $size ) {
                                        echo '<option value="' . esc_attr($key) . '" ' . ($key == $max_size ? 'selected' : '') . '>' . esc_html($size) . '</option>';
                                        } ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="upload_max_file_size_field">Execution Time</label></th>
                                <td>
                                    <input name="wmufs_maximum_execution_time" type="number" value="<?php echo esc_html($wpufs_max_execution_time);?>">
                                    <br><small>Example: 300, 600, 1800, 3600</small>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                        <?php wp_nonce_field('upload_max_file_size_action', 'upload_max_file_size_nonce'); ?>
                        <?php submit_button(); ?>
                    </form>

                    <div class="wmufs_faq_section">
                        <h2>Frequently Asked Questions</h2>
                        <div class="wmufs_faq_item">
                            <strong>Q: What happens if I set a file size higher than my server allows?</strong>
                            <p>A: Your server configuration will override this setting. Please make sure to update your server's <code>php.ini</code>, <code>.htaccess</code>, or contact your hosting provider to allow larger uploads.</p>
                        </div>
                        <div class="wmufs_faq_item">
                            <strong>Q: What is the recommended maximum execution time?</strong>
                            <p>A: For large file uploads or slow internet connections, 300 to 600 seconds is recommended. However, your hosting provider may have limits.</p>
                        </div>
                        <div class="wmufs_faq_item">
                            <strong>Q: Why don’t changes take effect immediately?</strong>
                            <p>A: Sometimes, server caching or PHP-FPM may delay changes. Try clearing your server cache or restarting PHP services.</p>
                        </div>
                        <div class="wmufs_faq_item">
                            <strong>Q: Can I upload files larger than 2GB?</strong>
                            <p>A: It depends on your server configuration and PHP version. Many shared hosting providers do not allow files larger than 2GB.</p>
                        </div>
                        <div class="wmufs_faq_item">
                            <strong>Q: Where can I find my current server limits?</strong>
                            <p>A: Go to <code>Tools > Site Health > Info || System Status Tab</code> or ask your hosting provider.</p>
                        </div>
                    </div>

                </div>
             </div>
            <!-- End Content Area -->

            <!-- Start Sidebar Area -->
            <div class="wmufs_admin_right_sidebar wmufs_card wmufs-col-4">
                <?php include WMUFS_PLUGIN_PATH . 'admin/templates/class-wmufs-sidebar.php'; ?>
            </div>
            <!-- End Sidebar area-->

        </div> <!-- End Row--->
    </div>
</div> <!-- End Wrapper -->

