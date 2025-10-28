<?php
/**
 * Class Codepopular_WMUFS
 */
class MaxUploader_Admin {
    static function init() {

        if ( is_admin() ) {
            add_action('admin_enqueue_scripts', array( __CLASS__, 'wmufs_style_and_script' ));
            add_action('admin_menu', array( __CLASS__, 'upload_max_file_size_add_pages' ));
            add_filter('plugin_action_links_' . WMUFS_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ));
            add_filter('plugin_row_meta', array( __CLASS__, 'plugin_meta_links' ), 10, 2);
            add_filter('admin_footer_text', array( __CLASS__, 'admin_footer_text' ));

            // Handle form submission
            add_action('admin_init', array( __CLASS__, 'easy_media_form_submission' ));

            add_action('admin_head', array( __CLASS__, 'show_admin_notice' ));

            // AJAX handlers
            add_action('wp_ajax_wmufs_restore_default_settings', array( __CLASS__, 'restore_default_settings_ajax' ));
        }

        // Set Upload Limit
        self::upload_max_increase_upload();
    }
    /**
     * Handle form submission for max uploader settings.
     * @return void
     */
    static function easy_media_form_submission() {

        if (
            ! isset($_POST['easy_media_set_size_limit']) ||
            ! wp_verify_nonce(sanitize_text_field($_POST['easy_media_set_size_limit']), 'easy_media_set_size_action')
        ) {
            return;
        }

        $settings = get_option('wmufs_settings', []);
        
        // Ensure settings is always an array
        if (!is_array($settings)) {
            $settings = [];
        }

        // Save Type of Limit
        if (isset($_POST['type'])) {
            $settings['limit_type'] = sanitize_text_field($_POST['type']);
        }

        // 🧩 Base limit for all users
        if (isset($_POST['max_file_size_field'])) {
            $limit = (int) sanitize_text_field($_POST['max_file_size_field']) * 1024 * 1024;
            if (!isset($settings['max_limits'])) {
                $settings['max_limits'] = [];
            }
            $settings['max_limits']['all'] = $limit;
        }

        // 🧩 Per-role upload limits (optional)
        if (isset($_POST['role_limits']) && is_array($_POST['role_limits'])) {
            if (!isset($settings['max_limits'])) {
                $settings['max_limits'] = [];
            }
            foreach ($_POST['role_limits'] as $role => $size) {
                $settings['max_limits'][$role] = (int) sanitize_text_field($size) * 1024 * 1024;
            }
        }

        // ⏱ Execution time
        if (isset($_POST['max_execution_time_field'])) {
            $settings['max_execution_time'] = (int) sanitize_text_field($_POST['max_execution_time_field']);
        }

        // 💾 Memory limit
        if (isset($_POST['max_memory_limit_field'])) {
            $settings['max_memory_limit'] = (int) sanitize_text_field($_POST['max_memory_limit_field']) * 1024 * 1024;
        }

        update_option('wmufs_settings', $settings);

        set_transient('wmufs_settings_updated', 'Settings saved successfully.', 30);
        wp_safe_redirect(admin_url('admin.php?page=easy_media'));
        exit;
    }

    /**
     * AJAX handler for restoring default settings
     */
    static function restore_default_settings_ajax() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wmufs_restore_defaults')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wp-maximum-upload-file-size')));
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wp-maximum-upload-file-size')));
        }

        try {
            // Delete main plugin settings (this is the primary setting)
            delete_option('wmufs_settings');
            
            // Delete legacy settings only if they exist (for backward compatibility)
            if (get_option('wmufs_maximum_execution_time') !== false) {
                delete_option('wmufs_maximum_execution_time');
            }
            if (get_option('wmufs_memory_limit') !== false) {
                delete_option('wmufs_memory_limit');
            }
            if (get_option('wmufs_notice_disable_time') !== false) {
                delete_option('wmufs_notice_disable_time');
            }

            // Clear any transients (safe to call even if they don't exist)
            delete_transient('wmufs_settings_updated');
            delete_transient('codepopular_promo_data');
            delete_transient('codepopular_blog_posts');

            // Also clear any Appsero tracking settings if they exist
            $appsero_options = array(
                'wp_maximum_upload_file_size_allow_tracking',
                'wp_maximum_upload_file_size_tracking_notice',
                'wp_maximum_upload_file_size_tracking_last_send',
                'wp_maximum_upload_file_size_tracking_skipped'
            );
            
            foreach ($appsero_options as $option) {
                if (get_option($option) !== false) {
                    delete_option($option);
                }
            }

            wp_send_json_success(array(
                'message' => __('Settings have been restored to default values successfully.', 'wp-maximum-upload-file-size')
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => __('An error occurred while restoring settings: ', 'wp-maximum-upload-file-size') . $e->getMessage()
            ));
        }
    }

    static function show_admin_notice() {
        if ( $message = get_transient('wmufs_settings_updated') ) {
            echo '<div class="notice notice-success is-dismissible wmufs-notice"><p>' . esc_html($message) . '</p></div>';
            delete_transient('wmufs_settings_updated');
        }
    }


    static function wmufs_style_and_script() {
        wp_enqueue_style('wmufs-admin-style', WMUFS_PLUGIN_URL . 'assets/css/wmufs.css', array(), WMUFS_PLUGIN_VERSION);

        // Ensure jQuery is loaded
        wp_enqueue_script('jquery');

        // Enqueue your script with explicit dependency on jQuery
        wp_enqueue_script('wmufs-admin', WMUFS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), WMUFS_PLUGIN_VERSION, true);

        wp_localize_script(
            'wmufs-admin',
            'wmufs_admin_notice_ajax_object',
            array(
                'wmufs_admin_notice_ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wmufs_notice_status'),
                'plugin_url' => WMUFS_PLUGIN_URL,
                'active_tab' => isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general',
            )
        );

        // Add ajaxurl for inline scripts
        wp_add_inline_script('wmufs-admin', 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";', 'before');
    }

    static function get_plugin_version() {
        $plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');
        return $plugin_data['version'];
    }
    static function is_plugin_page() {
        $current_screen = get_current_screen();
        return ($current_screen->id === 'media_page_easy_media');
    }

    /**
     * Add plugin action links (Settings + Upgrade to Pro).
     *
     * @param array $links Existing plugin action links.
     * @return array Modified plugin action links.
     */
    public static function plugin_action_links( $links ) {
        // Settings link (always show).
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'admin.php?page=easy_media' ) ),
            esc_html__( 'Settings', 'easy-media' )
        );

        // Add the Settings link first.
        array_unshift( $links, $settings_link );

        // Only show "Upgrade to Pro" if premium is NOT active.
        if ( ! WMUFS_Helper::is_premium_active() ) {
            $upgrade_link = sprintf(
                '<a href="%s" target="_blank" style="color:#ff6600;font-weight:bold;">%s</a>',
                esc_url( 'https://codepopular.com/product/easymedia/?utm_source=upgrade-pro-button' ),
                esc_html__( 'Upgrade to Pro', 'easy-media' )
            );
            array_unshift( $links, $upgrade_link );
        }

        return $links;
    }



    /**
     * Add plugin meta links (Support, etc.) under plugin name in Plugins list.
     *
     * @param array  $links Existing plugin meta links.
     * @param string $file  Plugin file path.
     * @return array Modified plugin meta links.
     */
    public static function plugin_meta_links( $links, $file ) {
        if ( $file === plugin_basename( __FILE__ ) ) {
            $links[] = sprintf(
                '<a target="_blank" href="%s">%s</a>',
                esc_url( 'https://wordpress.org/support/plugin/wp-maximum-upload-file-size/' ),
                esc_html__( 'Support', 'easy-media' )
            );
        }

        return $links;
    }


    static function admin_footer_text( $text ) {
        if ( ! self::is_plugin_page() ) {
            return $text;
        }
        return '<span id="footer-thankyou">If you like <strong><ins>EasyMedia</ins></strong> please leave us a <a target="_blank" style="color:#f9b918" href="https://wordpress.org/support/view/plugin-reviews/wp-maximum-upload-file-size?rate=5#postform">★★★★★</a> rating. A huge thank you in advance!</span>';
    }

    static function upload_max_file_size_add_pages() {
        add_submenu_page(
            'upload.php', // Parent Slug.
            'EasyMedia - Increase Max Upload File Size',
            'EasyMedia',
            'manage_options',
            'easy_media',
            array( __CLASS__, 'upload_max_file_size_dash' )
        );
    }

    static function upload_max_file_size_dash() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

        $tabs = array(
            'general' => __('General', 'wp-maximum-upload-file-size'),
            'system_status' => __('System Status', 'wp-maximum-upload-file-size')
        );

        if (!WMUFS_Helper::is_premium_active()) {
            $tabs['upload_logs'] = __('Pro <span class="easymedia-pro-badge">PRO</span>', 'wp-maximum-upload-file-size');
        }

        $tabs = apply_filters('wmufs_admin_tabs', $tabs);

        ?>
        <div class="wmufs-wrap">
            <h2 class="nav-tab-wrapper">
                <?php foreach ($tabs as $tab_key => $tab_label): ?>
                    <a href="#" data-tab="<?php echo esc_attr($tab_key); ?>" class="nav-tab max-uploader-tab-link <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                        <?php if ($tab_key === 'general'): ?>
                            <span class="dashicons dashicons-admin-generic"></span>
                        <?php elseif ($tab_key === 'system_status'): ?>
                            <span class="dashicons dashicons-chart-bar"></span>
                        <?php elseif ($tab_key === 'upload_logs'): ?>
                            <span class="dashicons dashicons-list-view"></span>
                        <?php elseif ($tab_key === 'user_limits'): ?>
                            <span class="dashicons dashicons-groups"></span>
                        <?php elseif ($tab_key === 'statistics'): ?>
                            <span class="dashicons dashicons-chart-area"></span>
                        <?php elseif ($tab_key === 'media_manager'): ?>
                            <span class="dashicons dashicons-category"></span>
                        <?php elseif ($tab_key === 'license'): ?>
                            <span class="dashicons dashicons-admin-network"></span>
                        <?php endif; ?>
                        <?php echo wp_kses_post($tab_label); ?>
                    </a>
                <?php endforeach; ?>
            </h2>
            <div id="max-uploader-tab-content">
                <?php include_once WMUFS_PLUGIN_PATH . 'inc/MaxUploaderSystemStatus.php'; ?>

                <?php foreach ($tabs as $tab_key => $tab_label): ?>
                    <div id="max-uploader-tab-<?php echo esc_attr($tab_key); ?>" class="max-uploader-tab-content" <?php echo $active_tab !== $tab_key ? 'style="display:none;"' : ''; ?>>
                        <?php
                        if ($tab_key === 'general') {
                            include WMUFS_PLUGIN_PATH . 'admin/templates/MaxUploaderForm.php';
                        } elseif ($tab_key === 'system_status') {
                            include WMUFS_PLUGIN_PATH . 'admin/templates/ClassSystemHealth.php';
                        }elseif (in_array($tab_key, ['upload_logs', 'user_limits', 'statistics']) && !WMUFS_Helper::is_premium_active()) {
                            include WMUFS_PLUGIN_PATH . 'admin/templates/UpgradePro.php';
                        } else {
                            do_action('wmufs_admin_tab_content', $tab_key);
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        add_action('admin_head', [ __CLASS__, 'wmufs_remove_admin_action' ]);
    }

    static function wmufs_remove_admin_action() {
        remove_all_actions('user_admin_notices');
        remove_all_actions('admin_notices');
    }

    /**
     * @return void
     */
    static function upload_max_increase_upload() {
        // Get plugin settings
        $settings = get_option('wmufs_settings', []);
        
        // Only proceed if settings exist
        if (empty($settings)) {
            return;
        }

        // Get limit type (global or role_based)
        $limit_type = isset($settings['limit_type']) ? $settings['limit_type'] : 'global';
        $max_limits = isset($settings['max_limits']) ? $settings['max_limits'] : [];

        // Apply execution time setting
        $max_execution_time = (int) (isset($settings['max_execution_time']) ? $settings['max_execution_time'] : get_option('wmufs_maximum_execution_time'));
        if ($max_execution_time > 0 && function_exists('set_time_limit')) {
            @set_time_limit($max_execution_time);
        }

        // Apply memory limit setting
        $memory_limit = (int) (isset($settings['max_memory_limit']) ? $settings['max_memory_limit'] : get_option('wmufs_memory_limit'));
        if ($memory_limit > 0) {
            $memory_limit_mb = round($memory_limit / 1048576);
            @ini_set('memory_limit', $memory_limit_mb . 'M');
        }

        // Apply upload size limits based on a limit type
        if ($limit_type === 'global') {
            // Global limit for all users
            $global_limit = (int) (isset($max_limits['all']) ? $max_limits['all'] : 0);
            
            if ($global_limit > 0) {
                add_filter('upload_size_limit', function ($size) use ($global_limit) {
                    return $global_limit;
                });
            }
        } elseif ($limit_type === 'role_based' && WMUFS_Helper::is_premium_active()) {
            // Role-based limits (Pro feature only)
            $role_limits = $max_limits;
            
            add_filter('upload_size_limit', function ($size) use ($role_limits) {
                if (is_user_logged_in()) {
                    $user = wp_get_current_user();
                    foreach ($user->roles as $role) {
                        if (isset($role_limits[$role]) && $role_limits[$role] > 0) {
                            return (int) $role_limits[$role];
                        }
                    }
                }
                return $size;
            });
        }
        

        // Add validation of upload limit in pre-upload phase
        if ($limit_type === 'global') {
            $global_limit = (int) (isset($max_limits['all']) ? $max_limits['all'] : 0);
            
            if ($global_limit > 0) {
                add_filter('wp_handle_upload_prefilter', function ($file) use ($global_limit) {
                    if (isset($file['size']) && $file['size'] > $global_limit) {
                        $file['error'] = sprintf(
                            __('Upload exceeds the maximum allowed size of %s.', 'wp-maximum-upload-file-size'),
                            size_format($global_limit)
                        );
                    }
                    return $file;
                });
            }
        } elseif ($limit_type === 'role_based' && WMUFS_Helper::is_premium_active()) {
            $role_limits = $max_limits;
            
            add_filter('wp_handle_upload_prefilter', function ($file) use ($role_limits) {

                error_log("Checking role-based upload limit...");

                $max_size = 0;
                
                if (is_user_logged_in()) {
                    $user = wp_get_current_user();
                    foreach ($user->roles as $role) {
                        if (isset($role_limits[$role]) && $role_limits[$role] > 0) {
                            $max_size = (int) $role_limits[$role];
                            break;
                        }
                    }
                }
                
                if ($max_size > 0 && isset($file['size']) && $file['size'] > $max_size) {
                    $file['error'] = sprintf(
                        __('Upload exceeds the maximum allowed size of %s.', 'wp-maximum-upload-file-size'),
                        size_format($max_size)
                    );
                }
                return $file;
            });
        }

    }

}

add_action('init', array( 'MaxUploader_Admin', 'init' ));
