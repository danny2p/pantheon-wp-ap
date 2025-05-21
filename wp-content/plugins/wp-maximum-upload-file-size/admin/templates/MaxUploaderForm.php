<?php

$max_uploader_settings = get_option('wmufs_settings', []);
$max_size = $max_uploader_settings['max_limits']['all'] ?? '';
if (!$max_size) {
	$max_size = wp_max_upload_size();
}
$max_size = $max_size / 1024 / 1024;

// Unified size options (16 MB to 10 GB)
$size_options = array(
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

// Add custom upload size if needed
if (!isset($size_options[(string)$max_size])) {
	$size_options[(string)$max_size] = $max_size . ' MB';
	ksort($size_options, SORT_NUMERIC);
}

// Execution time
$wpufs_max_execution_time =	$max_uploader_settings['max_execution_time'] ?? '';
$wpufs_max_execution_time = $wpufs_max_execution_time ?: ini_get('max_execution_time');

// Get memory limit
$max_uploader_customize_memory_limit = $max_uploader_settings['max_memory_limit'] ?? '';
if ($max_uploader_customize_memory_limit) {
    $memory_limit_mb = $max_uploader_customize_memory_limit / 1024 / 1024;
} else {
    $memory_limit_mb = ini_get('memory_limit');
}

if ($memory_limit_mb && preg_match('/(\d+)([KMG]?)/i', $memory_limit_mb, $matches)) {
	$value = (int)$matches[1];
	$unit = strtoupper($matches[2]);
	switch ($unit) {
		case 'G':
			$memory_limit_mb = $value * 1024;
			break;
		case 'K':
			$memory_limit_mb = (int)($value / 1024);
			break;
		default:
			$memory_limit_mb = $value;
			break;
	}
}


// Add detected memory limit if not present
if (!isset($size_options[(string)$memory_limit_mb])) {
	$label = ($memory_limit_mb >= 1024) ? ($memory_limit_mb / 1024) . ' GB' : $memory_limit_mb . ' MB';
	$size_options[(string)$memory_limit_mb] = $label;
	ksort($size_options, SORT_NUMERIC);
}

?>

<div class="wrap wmufs_mb_50">
    <h1><span class="dashicons dashicons-admin-settings" style="font-size: inherit; line-height: unset;"></span>
		<?php esc_html_e(' Control Upload Limits', 'wp-maximum-upload-file-size'); ?>
    </h1><br>

    <div class="wmufs_admin_deashboard">
        <div class="wmufs_row" id="poststuff">

            <!-- Start Content Area -->
            <div class="wmufs_admin_left wmufs_card wmufs-col-8 wmufs_form_centered">
                <div class="wmufs_inner_form_box">
                    <form method="post">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th scope="row"><label for="max_file_size_field">Choose Upload File Size</label></th>
                                <td>
                                    <select id="max_file_size_field" name="max_file_size_field">
										<?php
										foreach ($size_options as $key => $size) {
											echo '<option value="' . esc_attr($key) . '" ' . selected($key, $max_size, false) . '>' . esc_html($size) . '</option>';
										}
										?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="max_execution_time_field">Execution Time</label></th>
                                <td>
                                    <input id="max_execution_time_field" name="max_execution_time_field" type="number" value="<?php echo esc_attr($wpufs_max_execution_time); ?>">
                                    <br><small>Example: 300, 600, 1800, 3600</small>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="max_memory_limit_field">Memory Limit</label></th>
                                <td>
                                    <select id="max_memory_limit_field" name="max_memory_limit_field">
										<?php
										foreach ($size_options as $key => $label) {
											echo '<option value="' . esc_attr($key) . '" ' . selected($key, $memory_limit_mb, false) . '>' . esc_html($label) . '</option>';
										}
										?>
                                    </select>
									<?php if ((int)$memory_limit_mb > 4096): ?>
                                        <p style="color: red; font-weight: bold;">⚠️ Warning: Setting the memory limit above 4 GB may cause server instability on shared hosting environments.</p>
									<?php endif; ?>
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
                            <p>A: Your server configuration will override this setting. Please update your <code>php.ini</code>, <code>.htaccess</code>, or contact your host.</p>
                        </div>
                        <div class="wmufs_faq_item">
                            <strong>Q: What is the recommended maximum execution time?</strong>
                            <p>A: For large uploads or slow connections, 300 to 600 seconds is recommended. Confirm limits with your host.</p>
                        </div>
                        <div class="wmufs_faq_item">
                            <strong>Q: Why don’t changes take effect immediately?</strong>
                            <p>A: Server caching or PHP-FPM may delay changes. Clear server cache or restart PHP services.</p>
                        </div>
                        <div class="wmufs_faq_item">
                            <strong>Q: Can I upload files larger than 2GB?</strong>
                            <p>A: It depends on your PHP/server configuration. Many shared hosts do not allow uploads > 2GB.</p>
                        </div>
                        <div class="wmufs_faq_item">
                            <strong>Q: Where can I find my current server limits?</strong>
                            <p>A: Go to <code>Tools > Site Health > Info || System Status Tab</code> or ask your host.</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Content Area -->

            <div class="wmufs_admin_right_sidebar wmufs_card wmufs-col-4">
				<?php include WMUFS_PLUGIN_PATH . 'admin/templates/class-wmufs-sidebar.php'; ?>
            </div>
        </div>
    </div>
</div>
