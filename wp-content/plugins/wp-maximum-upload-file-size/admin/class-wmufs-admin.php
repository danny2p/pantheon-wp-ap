<?php
/**
 * Class Codepopular_WMUFS
 */
class MaxUploader_Admin {
    static function init(): void {
        if ( is_admin() ) {
            add_action('admin_enqueue_scripts', array( __CLASS__, 'wmufs_style_and_script' ));
            add_action('admin_menu', array( __CLASS__, 'upload_max_file_size_add_pages' ));
            add_filter('plugin_action_links_' . WMUFS_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ));
            add_filter('plugin_row_meta', array( __CLASS__, 'plugin_meta_links' ), 10, 2);
            add_filter('admin_footer_text', array( __CLASS__, 'admin_footer_text' ));

            // Handle form submission
            add_action('admin_init', array( __CLASS__, 'max_uploader_form_submission' ));

            add_action('admin_head', array( __CLASS__, 'show_admin_notice' ));
        }

        // Set Upload Limit
        self::upload_max_increase_upload();
    }

    /**
     * Handle form submission for max uploader settings.
     * @return void
     */
	static function max_uploader_form_submission(): void {
		if (
			! isset($_POST['upload_max_file_size_nonce']) ||
			! wp_verify_nonce(sanitize_text_field($_POST['upload_max_file_size_nonce']), 'upload_max_file_size_action')
		) {
			return;
		}

		$settings = [];

		if ( isset($_POST['max_file_size_field']) ) {
			$limit = (int) sanitize_text_field($_POST['max_file_size_field']) * 1024 * 1024;
            $settings['max_limits'] = [
              'all' => $limit,
            ];
		}

		if ( isset($_POST['max_execution_time_field']) ) {
			$settings['max_execution_time'] = (int) sanitize_text_field($_POST['max_execution_time_field']);
		}

		if ( isset($_POST['max_memory_limit_field']) ) {
			$settings['max_memory_limit'] = (int) sanitize_text_field($_POST['max_memory_limit_field']) * 1024 * 1024;
		}

		// Save as JSON string or array. WordPress can handle arrays (auto-serialized).
		update_option('wmufs_settings', $settings);

		set_transient('wmufs_settings_updated', 'Settings saved successfully.', 30);
		wp_safe_redirect(admin_url('admin.php?page=max_uploader'));

		exit;
	}


    static function show_admin_notice(): void {
        if ( $message = get_transient('wmufs_settings_updated') ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
            delete_transient('wmufs_settings_updated');
        }
    }


    static function wmufs_style_and_script(): void {
        wp_enqueue_style('wmufs-admin-style', WMUFS_PLUGIN_URL . 'assets/css/wmufs.css', array(), WMUFS_PLUGIN_VERSION);

        // Ensure jQuery is loaded
        wp_enqueue_script('jquery');

        // Enqueue your script with explicit dependency on jQuery
        wp_enqueue_script('wmufs-admin', WMUFS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), time(), true);

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
    }

    static function get_plugin_version(): string {
        $plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');
        return $plugin_data['version'];
    }
    static function is_plugin_page(): bool {
        $current_screen = get_current_screen();
        return ($current_screen->id === 'media_page_max_uploader');
    }

    static function plugin_action_links( $links ) {
        $settings_link = '<a href="' . admin_url('admin.php?page=max_uploader') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    static function plugin_meta_links( $links, $file ) {
        if ( $file === plugin_basename(__FILE__) ) {
            $links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-maximum-upload-file-size/">Support</a>';
        }
        return $links;
    }

    static function admin_footer_text( $text ) {
        if ( ! self::is_plugin_page() ) {
            return $text;
        }
        return '<span id="footer-thankyou">If you like <strong><ins>WP Maximum Upload File Size</ins></strong> please leave us a <a target="_blank" style="color:#f9b918" href="https://wordpress.org/support/view/plugin-reviews/wp-maximum-upload-file-size?rate=5#postform">★★★★★</a> rating. A huge thank you in advance!</span>';
    }

    static function upload_max_file_size_add_pages() {
	    add_submenu_page(
	        'upload.php', // Parent Slug.
            'Increase Max Upload File Size',
            'MaxUploader',
            'manage_options',
            'max_uploader',
            [ __CLASS__, 'upload_max_file_size_dash' ],
        );
    }

    static function upload_max_file_size_dash() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        ?>
        <div class="wmufs-wrap">
            <h2 class="nav-tab-wrapper">
                <a href="#" data-tab="general" class="nav-tab max-uploader-tab-link <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-generic"></span> General
                </a>
                <a href="#" data-tab="system_status" class="nav-tab max-uploader-tab-link <?php echo $active_tab === 'system_status' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-chart-bar"></span> System Status
                </a>
                <a href="#" data-tab="pro" class="nav-tab max-uploader-tab-link <?php echo $active_tab === 'pro' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-star-filled"></span> Pro
                </a>
            </h2>
            <div id="max-uploader-tab-content">
                <?php include_once WMUFS_PLUGIN_PATH . 'inc/MaxUploaderSystemStatus.php'; ?>
                <div id="max-uploader-tab-general" class="max-uploader-tab-content" <?php echo $active_tab !== 'general' ? 'style="display:none;"' : ''; ?>>
                    <?php
                    include WMUFS_PLUGIN_PATH . 'admin/templates/MaxUploaderForm.php';
                    ?>
                </div>
                <div id="max-uploader-tab-system_status" class="max-uploader-tab-content" <?php echo $active_tab !== 'system_status' ? 'style="display:none;"' : ''; ?>>
                    <?php
                    include WMUFS_PLUGIN_PATH . 'admin/templates/ClassSystemHealth.php';
                    ?>
                </div>
                <div id="max-uploader-tab-pro" class="max-uploader-tab-content" <?php echo $active_tab !== 'pro' ? 'style="display:none;"' : ''; ?>>
                    <?php
                    include WMUFS_PLUGIN_PATH . 'admin/templates/FreeVsPro.php';
                    ?>
                </div>
            </div>
        </div>
        <?php
        add_action('admin_head', [ __CLASS__, 'wmufs_remove_admin_action' ]);
    }

    static function wmufs_remove_admin_action(): void {
        remove_all_actions('user_admin_notices');
        remove_all_actions('admin_notices');
    }

	/**
	 * @return void
	 */
	static function upload_max_increase_upload(): void {

        $settings = get_option('wmufs_settings') ?? [];
        $max_upload_size = (int) ($settings['max_limits']['all'] ?? get_option('max_file_size')); // bytes
        $max_execution_time = (int) ($settings['max_execution_time'] ??  get_option('wmufs_maximum_execution_time'));
        $memory_limit = (int) ($settings['max_memory_limit'] ?? get_option('wmufs_memory_limit'));

        // Set max upload size
		add_filter('upload_size_limit', function ($data) use ($max_upload_size) {
			return $max_upload_size > 0 ? $max_upload_size : $data;
		});

        // Set max execution time
		if ( !empty($max_execution_time) && $max_execution_time > 0) {
			// Only try to set a time limit if the function is available
			if (function_exists('set_time_limit')) {
				@set_time_limit( $max_execution_time ); // Suppress errors if the host restricts
			}
		}

        // Set a memory limit
		if ( !empty($memory_limit) && $memory_limit > 0) {
            $memory_limit_mb = round($memory_limit / 1048576); // convert to MB ex: 2048
			@ini_set('memory_limit', ((int) $memory_limit_mb) . 'M'); // e.g., 512M, 1024M
		}


	}

}

add_action('init', array( 'MaxUploader_Admin', 'init' ));
?>
