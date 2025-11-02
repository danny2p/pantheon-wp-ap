
<!-- Create Support Ticket -->
<div class="wmufs_card_mini wmufs_mb_20">
    <div class="support-ticket">
        <h2><?php echo esc_html__('Do you need any free help?', 'wp-maximum-upload-file-size'); ?></h2>
        <div class="support-buttons">
            <a target="_blank" class="button" href="<?php echo esc_url_raw('https://wordpress.org/support/plugin/wp-maximum-upload-file-size/');?>">
                <span class="dashicons dashicons-sos"></span>&nbsp;<?php echo esc_html__('Open Ticket', 'wp-maximum-upload-file-size'); ?>
            </a>
            <a target="_blank" class="button" href="<?php echo esc_url_raw('https://codepopular.com/contact/?utm_source=wp_dashboard&utm_medium=plugin&utm_campaign=contact_us_button');?>">
                <span class="dashicons dashicons-email"></span>&nbsp;<?php esc_html_e('Contact Us', 'wp-maximum-upload-file-size'); ?>
            </a>
            <a target="_blank" class="button button-primary" href="<?php echo esc_url_raw('https://ko-fi.com/codepopular');?>">
                <span class="dashicons dashicons-smiley"></span>&nbsp;<?php esc_html_e('Buy Me a Coffee', 'wp-maximum-upload-file-size'); ?>
            </a>
        </div>
    </div>
</div>

<!-- Pro feature list -->
<?php if(!WMUFS_Helper::is_premium_active()){?>
<div class="wmufs_card_mini wmufs_mb_20">
    <div class="support-ticket">
        <h2><?php echo esc_html__('✨ Update to Pro?', 'wp-maximum-upload-file-size'); ?></h2>
        <div class="wmufs-pro-badge">
            <span class="wmufs-badge-text"><?php esc_html_e('One-Time Payment', 'wp-maximum-upload-file-size'); ?></span>
            <span class="wmufs-badge-subtext"><?php esc_html_e('No Recurring Fees', 'wp-maximum-upload-file-size'); ?></span>
        </div>
        <div class="easymedia-pro-feature-list">
            <ul>
                <li>
                    <span class="dashicons dashicons-yes-alt"></span>
                    <span class="feature-text"><?php esc_html_e('Advance Media Logs', 'wp-maximum-upload-file-size'); ?></span>
                </li>
                <li>
                    <span class="dashicons dashicons-yes-alt"></span>
                    <span class="feature-text"><?php esc_html_e('Trace Media Source Points', 'wp-maximum-upload-file-size'); ?></span>
                </li>
                <li>
                    <span class="dashicons dashicons-yes-alt"></span>
                    <span class="feature-text"><?php esc_html_e('Per-User Upload Quota Limits', 'wp-maximum-upload-file-size'); ?></span>
                </li>
                <li>
                    <span class="dashicons dashicons-yes-alt"></span>
                    <span class="feature-text"><?php esc_html_e('Allow custom file upload', 'wp-maximum-upload-file-size'); ?></span>
                </li>
                <li>
                    <span class="dashicons dashicons-yes-alt"></span>
                    <span class="feature-text"><?php esc_html_e('Restrict Upload by File Types', 'wp-maximum-upload-file-size'); ?></span>
                </li>
                <li>
                    <span class="dashicons dashicons-yes-alt"></span>
                    <span class="feature-text"><?php esc_html_e('Advanced Statistics Dashboard', 'wp-maximum-upload-file-size'); ?></span>
                </li>
                <li>
                    <span class="dashicons dashicons-yes-alt"></span>
                    <span class="feature-text"><?php esc_html_e('Advanced Media Manager', 'wp-maximum-upload-file-size'); ?></span>
                </li>
                <li>
                    <span class="dashicons dashicons-yes-alt"></span>
                    <span class="feature-text"><?php esc_html_e('Priority Support', 'wp-maximum-upload-file-size'); ?></span>
                </li>
            </ul>
        </div>
        <div class="support-buttons">
            <a target="_blank" class="button button-primary wmufs-upgrade-btn" href="<?php echo esc_url_raw('https://codepopular.com/product/easymedia?utm_source=plugin&utm_medium=link&utm_campaign=wmufs_free_to_pro_upgrade');?>">
                <span class="dashicons dashicons-cart"></span>&nbsp;<?php esc_html_e('Upgrade to Pro Now', 'wp-maximum-upload-file-size'); ?>
            </a>
        </div>
    </div>
</div>
<?php } ?>
