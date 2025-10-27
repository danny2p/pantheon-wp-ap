<?php
$max_uploader_settings = get_option('wmufs_settings', []);
$max_size = isset($max_uploader_settings['max_limits']['all']) ? $max_uploader_settings['max_limits']['all'] : '';
if (!$max_size) {
    $max_size = wp_max_upload_size();
}
$max_size = $max_size / 1024 / 1024;

// Get WordPress default upload limit
$wp_default_limit = wp_max_upload_size() / 1024 / 1024; // Convert to MB

// Unified size options (16 MB to 10 GB)
$size_options = array(
    '2' => '2 MB',
    '4' => '4 MB',
    '8' => '8 MB',
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

// Add WordPress default limit if it's not already in the list
if (!isset($size_options[(string)$wp_default_limit]) && $wp_default_limit > 0) {
    $size_options[(string)$wp_default_limit] = 'Server Default (' . round($wp_default_limit) . ' MB)';
    ksort($size_options, SORT_NUMERIC);
}

// Execution time
$wpufs_max_execution_time = isset($max_uploader_settings['max_execution_time']) ? $max_uploader_settings['max_execution_time'] : '';
$wpufs_max_execution_time = $wpufs_max_execution_time ?: ini_get('max_execution_time');

// Get memory limit
$max_uploader_customize_memory_limit = isset($max_uploader_settings['max_memory_limit']) ? $max_uploader_settings['max_memory_limit'] : '';
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

// Make sure $memory_limit_mb is always defined
if (!isset($memory_limit_mb)) {
    $memory_limit_mb = 0;
}

// Check if pro/premium is active
$pro_active = WMUFS_Helper::is_premium_active();

// Get Limit Type (global or role-based)
$wmufs_limit_type = isset($max_uploader_settings['limit_type']) ? $max_uploader_settings['limit_type'] : 'global';

?>

<div class="wrap wmufs_mb_50">
    <h1><span class="dashicons dashicons-admin-settings" style="font-size: inherit; line-height: unset;"></span>
        <?php esc_html_e('Control Upload Limits', 'wp-maximum-upload-file-size'); ?>
    </h1><br>

    <div class="wmufs_admin_deashboard">
        <div class="wmufs_row" id="poststuff">

            <!-- Start Content Area -->
            <div class="wmufs_admin_left wmufs_card wmufs-col-8 wmufs_form_centered">
                <div class="wmufs_inner_form_box">
                    <div class="wmufs-card wmufs-toggle-card">
                        <h3 class="wmufs-card-title">Select Upload Limit Mode</h3>

                        <div class="wmufs-toggle-buttons">
                            <button type="button" class="wmufs-toggle-btn <?php esc_html($wmufs_limit_type === 'global' ? 'active' : '')?>" data-target="#all-users-section">Global Limit</button>
                            <button type="button" class="wmufs-toggle-btn <?php esc_html($wmufs_limit_type === 'role_based' ? 'active' : '')?>" data-target="#role-based-section">Role-Based Limit <?php if(!$pro_active){?><span class="easymedia-pro-badge">PRO</span><?php } ?></button>
                        </div>

                        <div id="all-users-section" class="wmufs-toggle-section">
                            <!-- All Users Form -->
                            <form method="post" action="options.php">
                                <input type="hidden" name="type" value="global">
                                <h2><?php esc_html_e('Apply Upload Limit for All Users', 'wp-maximum-upload-file-size'); ?></h2>
                                <table class="form-table">
                                    <tbody>
                                    <tr>
                                        <th scope="row"><label for="max_file_size_field"><?php esc_html_e('Site Global Limit', 'wp-maximum-upload-file-size'); ?></label></th>
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
                                        <th scope="row"><label for="max_execution_time_field"><?php esc_html_e('Execution Time', 'wp-maximum-upload-file-size'); ?></label></th>
                                        <td>
                                            <input id="max_execution_time_field" name="max_execution_time_field" type="number" value="<?php echo esc_attr($wpufs_max_execution_time); ?>">
                                            <br><small><?php esc_html_e('Example: 300, 600, 1800, 3600', 'wp-maximum-upload-file-size'); ?></small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="max_memory_limit_field"><?php esc_html_e('Memory Limit', 'wp-maximum-upload-file-size'); ?></label></th>
                                        <td>
                                            <select id="max_memory_limit_field" name="max_memory_limit_field">
                                                <?php
                                                foreach ($size_options as $key => $label) {
                                                    echo '<option value="' . esc_attr($key) . '" ' . selected($key, $memory_limit_mb, false) . '>' . esc_html($label) . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <?php if ((int)$memory_limit_mb > 4096): ?>
                                                <p style="color: red; font-weight: bold;">⚠️ <?php esc_html_e('Warning: Setting the memory limit above 4 GB may cause server instability on shared hosting environments.', 'wp-maximum-upload-file-size'); ?></p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

                                <?php wp_nonce_field('easy_media_set_size_action', 'easy_media_set_size_limit'); ?>
                                <?php submit_button(); ?>
                            </form>
                        </div>

                        <div id="role-based-section" class="wmufs-toggle-section" style="display:none;">
                            <form id="role-limits-form" method="post" action="options.php">
                                <?php
                                $roles = WMUFS_Helper::get_available_roles();
                                $role_limits = WMUFS_Helper::get_role_limits();
                                ?>
                                <h2><?php esc_html_e('Role-Based Upload Limits', 'wp-maximum-upload-file-size'); ?></h2>
                                <?php if (!$pro_active) : ?>
                                    <p><?php esc_html_e('Upgrade to Pro to edit role-based upload limits.', 'wp-maximum-upload-file-size'); ?> <a href="<?php echo esc_url(WMUFS_Helper::get_upgrade_url()); ?>" target="_blank"><?php _e('Learn More', 'wp-maximum-upload-file-size'); ?></a></p>
                                <?php endif; ?>
                                <table class="wp-list-table widefat fixed striped">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e('Role', 'wp-maximum-upload-file-size'); ?></th>
                                        <th><?php esc_html_e('Display Name', 'wp-maximum-upload-file-size'); ?></th>
                                        <th><?php esc_html_e('Upload Limit (MB)', 'wp-maximum-upload-file-size'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($roles as $role_key => $role_data):
                                        // Default to WordPress default limit if no custom role limit is set
                                        $current_limit = isset($role_limits[$role_key]) ? $role_limits[$role_key] / (1024 * 1024) : $wp_default_limit;
                                        ?>
                                        <tr>
                                            <td><?php echo esc_html($role_key); ?></td>
                                            <td><?php echo esc_html($role_data['name']); ?></td>
                                            <td>
                                                <?php if ($pro_active) : ?>
                                                    <select name="role_limits[<?php echo esc_attr($role_key); ?>]">
                                                        <?php foreach ($size_options as $key => $label): ?>
                                                            <option value="<?php echo esc_attr($key); ?>" <?php selected($current_limit, $key); ?>><?php echo esc_html($label); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                <?php else : ?>
                                                    <span><?php _e('Upgrade Pro', 'wp-maximum-upload-file-size')?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php if ($pro_active) : ?>
                                    <input type="hidden" name="type" value="role_based">
                                    <?php wp_nonce_field('easy_media_set_size_action', 'easy_media_set_size_limit'); ?>
                                    <?php submit_button(); ?>

                                <?php else : ?>
                                    <p class="submit">
                                        <button type="button" class="button button-primary"><?php _e('Upgrade Pro', 'wp-maximum-upload-file-size'); ?><span class="easymedia-pro-badge">PRO</span></button>
                                    </p>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Restore Default Settings Section -->
                    <div class="wmufs-card wmufs-toggle-card" style="margin-top: 20px;">
                        <h3 class="wmufs-card-title"><?php esc_html_e('Reset Settings', 'wp-maximum-upload-file-size'); ?></h3>
                        <p><?php esc_html_e('Reset all plugin settings to their default values. This will clear all custom upload limits, execution time, and memory limit settings.', 'wp-maximum-upload-file-size'); ?></p>
                        <p class="submit">
                            <button type="button" id="restore-default-settings" class="button button-secondary" style="background-color: #dc3232; color: white; border-color: #dc3232;">
                                <span class="dashicons dashicons-undo" style="vertical-align: middle; margin-right: 5px;"></span>
                                <?php esc_html_e('Restore Default Settings', 'wp-maximum-upload-file-size'); ?>
                            </button>
                        </p>
                    </div>

                    <!-- Premium Features List -->
                    <?php if(WMUFS_Helper::get_upgrade_url()){ ?>
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

                    <?php } ?>
                </div>
            </div>
            <!-- End Content Area -->

            <div class="wmufs_admin_right_sidebar wmufs_card wmufs-col-4">
                <?php include WMUFS_PLUGIN_PATH . 'admin/templates/class-wmufs-sidebar.php'; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleButtons = document.querySelectorAll('.wmufs-toggle-btn');
        const toggleSections = document.querySelectorAll('.wmufs-toggle-section');

        // Get a limit type from PHP
        const activeType = "<?php echo esc_js($wmufs_limit_type); ?>";
        const activeTarget = activeType === 'role_based' ? '#role-based-section' : '#all-users-section';

        // Initially activate the correct button + section
        toggleButtons.forEach(button => {
            const target = button.getAttribute('data-target');
            if (target === activeTarget) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });

        toggleSections.forEach(section => {
            if ('#' + section.id === activeTarget) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });

        // Handle button clicks
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                toggleButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                toggleSections.forEach(section => section.style.display = 'none');
                const targetSection = document.querySelector(this.dataset.target);
                if (targetSection) {
                    targetSection.style.display = 'block';
                }
            });
        });

        // Handle restore default settings button
        const restoreButton = document.getElementById('restore-default-settings');
        if (restoreButton) {
            restoreButton.addEventListener('click', function() {
                if (confirm('<?php esc_html_e('Are you sure you want to restore default settings? This will reset all your custom upload limits, execution time, and memory limit settings.', 'wp-maximum-upload-file-size'); ?>')) {
                    // Show loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<span class="dashicons dashicons-update" style="vertical-align: middle; margin-right: 5px; animation: spin 1s linear infinite;"></span><?php esc_html_e('Restoring...', 'wp-maximum-upload-file-size'); ?>';
                    this.disabled = true;

                    // Make AJAX request - use jQuery for better compatibility
                    const ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '<?php echo admin_url('admin-ajax.php'); ?>';
                    
                    // Use jQuery AJAX for better compatibility
                    jQuery.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'wmufs_restore_default_settings',
                            nonce: '<?php echo wp_create_nonce('wmufs_restore_defaults'); ?>'
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.success) {
                                // Show success message
                                const notice = document.createElement('div');
                                notice.className = 'notice notice-success is-dismissible';
                                notice.innerHTML = '<p>' + data.data.message + '</p>';
                                document.querySelector('.wrap').insertBefore(notice, document.querySelector('.wmufs_admin_deashboard'));
                                
                                // Reload page after 2 seconds
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                // Show error message
                                const notice = document.createElement('div');
                                notice.className = 'notice notice-error is-dismissible';
                                notice.innerHTML = '<p>' + (data.data.message || '<?php esc_html_e('An error occurred while restoring settings.', 'wp-maximum-upload-file-size'); ?>') + '</p>';
                                document.querySelector('.wrap').insertBefore(notice, document.querySelector('.wmufs_admin_deashboard'));
                                
                                // Restore button state
                                restoreButton.innerHTML = originalText;
                                restoreButton.disabled = false;
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error);
                            // Show error message
                            const notice = document.createElement('div');
                            notice.className = 'notice notice-error is-dismissible';
                            notice.innerHTML = '<p><?php esc_html_e('An error occurred while restoring settings.', 'wp-maximum-upload-file-size'); ?></p>';
                            document.querySelector('.wrap').insertBefore(notice, document.querySelector('.wmufs_admin_deashboard'));
                            
                            // Restore button state
                            restoreButton.innerHTML = originalText;
                            restoreButton.disabled = false;
                        }
                    });
                }
            });
        }
    });
</script>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>