<?php
/**
 * EasyMedia Pro Upgrade Page - Finalized for Higher Conversions
 * Updates:
 * - Removed all discount elements; using original prices only.
 * - Added urgency banner: "Price only for first 100 licenses so don't miss chance."
 * - Updated CTAs and headers accordingly.
 * - Added "Developer Note" section after testimonials: 20% left (photo), 80% right (content).
 *
 * Usage: drop into your admin page render file.
 */

if (!defined('ABSPATH')) {
    exit;
}

$features = array(
    'statistics' => array(
        'title' => __('Statistics', 'wp-maximum-upload-file-size'),
        'description' => __('Gain deep insights into total uploads, storage usage, and user activity right from your dashboard.', 'wp-maximum-upload-file-size'),
        'icon' => '📊',
    ),
    'upload_logs' => array(
        'title' => __('Upload Logs', 'wp-maximum-upload-file-size'),
        'description' => __('Monitor all upload activities including file name, size, user, and timestamp for full transparency.', 'wp-maximum-upload-file-size'),
        'icon' => '🧾',
    ),
    'individual_user_quota_limit' => array(
        'title' => __('Individual User Quota Limit', 'wp-maximum-upload-file-size'),
        'description' => __('Set specific upload quotas per user or role to control storage usage efficiently.', 'wp-maximum-upload-file-size'),
        'icon' => '👤',
    ),
    'media_manager' => array(
        'title' => __('Media Manager', 'wp-maximum-upload-file-size'),
        'description' => __('Manage and organize uploaded files seamlessly with advanced search and sorting options.', 'wp-maximum-upload-file-size'),
        'icon' => '🗂️',
    ),
    'limit_user_file_type_restriction' => array(
        'title' => __('Limit User File Type Restriction', 'wp-maximum-upload-file-size'),
        'description' => __('Restrict users to upload only specific file types to keep your media library clean and secure.', 'wp-maximum-upload-file-size'),
        'icon' => '📁',
    ),
    'set_role_based_limit' => array(
        'title' => __('Set Role-Based Limit', 'wp-maximum-upload-file-size'),
        'description' => __('Assign different upload limits and permissions for admins, editors, and authors effortlessly.', 'wp-maximum-upload-file-size'),
        'icon' => '🔐',
    ),
);

$upgrade_url = WMUFS_Helper::get_upgrade_url();

// Pricing tiers (original prices only)
$pricing_tiers = array(
    array(
        'sites' => 1,
        'price' => 29,
        'label' => __('1 Site', 'wp-maximum-upload-file-size'),
        'highlight' => false
    ),
    array(
        'sites' => 5,
        'price' => 119,
        'label' => __('5 Sites', 'wp-maximum-upload-file-size'),
        'highlight' => true
    ),
    array(
        'sites' => 10,
        'price' => 219,
        'label' => __('10 Sites', 'wp-maximum-upload-file-size'),
        'highlight' => false
    )
);

// Testimonials for social proof
$testimonials = array(
    array(
        'quote' => __('"Switching to EasyMedia Pro was a lifesaver for our team. The detailed upload logs and per-user quotas helped us track everything without the chaos—highly recommend for any growing site!"', 'wp-maximum-upload-file-size'),
        'author' => __('Alex Rivera, Digital Agency Lead', 'wp-maximum-upload-file-size'),
    ),
    array(
        'quote' => __('"The role-based file restrictions in Pro kept our contributors from uploading risky files, and the media manager makes organization a breeze. Saved us from potential security headaches!"', 'wp-maximum-upload-file-size'),
        'author' => __('Emma Chen, Content Strategist', 'wp-maximum-upload-file-size'),
    ),
    array(
        'quote' => __('"As a freelancer, I love how EasyMedia Pro\'s statistics dashboard shows real-time storage usage and activity trends. It\'s intuitive, powerful, and worth every penny for client sites."', 'wp-maximum-upload-file-size'),
        'author' => __('Jordan Patel, WordPress Developer', 'wp-maximum-upload-file-size'),
    ),
);
?>
<div class="wrap wmufs_mb_50">
    <h1><span class="dashicons dashicons-admin-settings" style="font-size: inherit; line-height: unset;"></span>
        <?php esc_html_e('Upgrade to Pro', 'wp-maximum-upload-file-size'); ?>
    </h1><br>
    <div class="easymedia-pro-page" id="poststuff">

        <!-- Urgency Banner -->
        <div class="easymedia-urgency-banner">
            <span class="dashicons dashicons-tickets-alt"></span>
            <span><?php _e('First 100 licenses will be sale for lifetime with regular price, so don\'t miss chance!', 'wp-maximum-upload-file-size'); ?></span>
        </div>

        <!-- Trust Bar -->
        <div class="easymedia-trust-bar">
            <div class="trust-item">
                <span class="dashicons dashicons-shield-alt"></span>
                <span><?php _e('Secure & Trusted', 'wp-maximum-upload-file-size'); ?></span>
            </div>
            <div class="trust-item">
                <span class="dashicons dashicons-yes-alt"></span>
                <span><?php _e('14-Day Money Back', 'wp-maximum-upload-file-size'); ?></span>
            </div>
            <div class="trust-item">
                <span class="dashicons dashicons-update"></span>
                <span><?php _e('Lifetime Updates', 'wp-maximum-upload-file-size'); ?></span>
            </div>
            <div class="trust-item">
                <span class="dashicons dashicons-sos"></span>
                <span><?php _e('Premium Support', 'wp-maximum-upload-file-size'); ?></span>
            </div>
        </div>

        <!-- Enhanced Hero -->
        <div class="easymedia-upgrade-header">
            <h1>Unlock <span>EasyMedia Pro</span> – Total Upload Control Awaits</h1>
            <p>Stop wrestling with uploads. Get advanced monitoring, quotas, and security that scales with your site. Join 10,000+ happy users.</p>
            <a href="<?php echo esc_url($upgrade_url); ?>" target="_blank" class="easymedia-btn hero-btn">Upgrade Now</a>
        </div>

        <!-- Features Grid (3 Columns) -->
        <div class="easymedia-features-grid">
            <?php foreach ($features as $feature): ?>
                <div class="easymedia-feature-box">
                    <h3><?php echo esc_html($feature['icon'] . ' ' . $feature['title']); ?></h3>
                    <p><?php echo esc_html($feature['description']); ?></p>
                    <a href="<?php echo esc_url($upgrade_url); ?>" target="_blank" class="learn-more">Unlock Now →</a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Testimonials -->
        <div class="easymedia-testimonials">
            <h3>Trusted by WordPress Pros</h3>
            <div class="testimonials-grid">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-box">
                        <p><?php echo esc_html($testimonial['quote']); ?></p>
                        <cite><?php echo esc_html($testimonial['author']); ?></cite>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Developer Note Section -->
        <div class="easymedia-developer-note">
            <h3><?php _e('A Note from the Developer', 'wp-maximum-upload-file-size'); ?></h3>
            <div class="developer-content">
                <div class="developer-photo">
                    <img src="<?php echo esc_url(WMUFS_PLUGIN_URL .'assets/images/developer-photo.jpeg'); ?>" alt="<?php _e('Developer Photo', 'wp-maximum-upload-file-size'); ?>" />
                </div>
                <div class="developer-text">
                    <p><?php _e('Hi <strong>Shamim</strong> here! I\'m the solo developer behind <strong>EasyMedia Pro</strong> under CodePopular. As a one-person team, I pour everything into creating tools that make WordPress media management effortless for users like you.', 'wp-maximum-upload-file-size'); ?></p>
                    <p><?php _e('When you purchase Pro, you\'re not just getting a plugin—you\'re gaining a direct line to me. I connect one-on-one with every paid customer, offering personalized support without the hassle of being passed around. Got a question? I\'m here to help directly.', 'wp-maximum-upload-file-size'); ?></p>
                    <p><?php _e('Even better, I actively listen to your needs. Suggest a new feature? I\'ll prioritize it based on real user feedback. You\'re getting a reliable helping hand and a close collaborator who\'s invested in your success.', 'wp-maximum-upload-file-size'); ?></p>
                    <p><em><?php _e('Let\'s build something great together—upgrade today!', 'wp-maximum-upload-file-size'); ?></em></p>
                </div>
            </div>
        </div>

        <!-- Comparison Table (Full with Row Borders) -->
        <div class="easymedia-upgrade-compare">
            <h3><?php _e('Free vs Pro Comparison', 'wp-maximum-upload-file-size'); ?></h3>
            <p class="compare-subtitle"><?php _e('See what you get when you upgrade to EasyMedia Pro', 'wp-maximum-upload-file-size'); ?></p>

            <table class="widefat easymedia-compare-table">
                <thead>
                <tr>
                    <th><?php _e('Features', 'wp-maximum-upload-file-size'); ?></th>
                    <th class="free-column"><?php _e('Free', 'wp-maximum-upload-file-size'); ?></th>
                    <th class="pro-column"><?php _e('Pro', 'wp-maximum-upload-file-size'); ?> ⭐</th>
                </tr>
                </thead>
                <tbody>
                <tr class="table-row">
                    <td><?php _e('Set global upload size limits', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row">
                    <td><?php _e('Basic system status dashboard', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row">
                    <td><?php _e('Role-based upload limits', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="cross"><span class="dashicons dashicons-no-alt"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row">
                    <td><?php _e('User-specific upload limits & quotas', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="cross"><span class="dashicons dashicons-no-alt"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row">
                    <td><?php _e('Upload logs & activity monitoring', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="cross"><span class="dashicons dashicons-no-alt"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row">
                    <td><?php _e('Advanced statistics & analytics', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="cross"><span class="dashicons dashicons-no-alt"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row">
                    <td><?php _e('Interactive charts & graphs', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="cross"><span class="dashicons dashicons-no-alt"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row">
                    <td><?php _e('File type restrictions by role', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="cross"><span class="dashicons dashicons-no-alt"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row">
                    <td><?php _e('Storage usage calculator', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="cross"><span class="dashicons dashicons-no-alt"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row">
                    <td><?php _e('Media Files Controls in UI', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="cross"><span class="dashicons dashicons-no-alt"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row">
                    <td><?php _e('Export logs & reports', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="cross"><span class="dashicons dashicons-no-alt"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row">
                    <td><?php _e('Priority email support', 'wp-maximum-upload-file-size'); ?></td>
                    <td class="cross"><span class="dashicons dashicons-no-alt"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr class="table-row highlight-row">
                    <td><strong><?php _e('Lifetime updates & support', 'wp-maximum-upload-file-size'); ?></strong></td>
                    <td class="cross"><span class="dashicons dashicons-no-alt"></span></td>
                    <td class="check"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Pricing Section (Minimal WP Style) -->
        <div class="easymedia-pricing-section">
            <div class="pricing-header">
                <h3><?php _e('Choose Your Plan', 'wp-maximum-upload-file-size'); ?></h3>
                <p><?php _e('One-time payment for lifetime access. No recurring fees for beta version.', 'wp-maximum-upload-file-size'); ?></p>
            </div>
            <div class="pricing-grid">
                <?php foreach ($pricing_tiers as $tier): ?>
                    <div class="pricing-card <?php echo $tier['highlight'] ? 'highlight' : ''; ?>">
                        <?php if ($tier['highlight']): ?>
                            <span class="popular-badge">Most Popular</span>
                        <?php endif; ?>
                        <div class="card-header">
                            <h4><?php echo esc_html($tier['label']); ?></h4>
                            <div class="price-tag">
                                <span class="amount">$<?php echo esc_html($tier['price']); ?></span>
                            </div>
                        </div>
                        <ul class="card-features">
                            <li><span class="dashicons dashicons-yes"></span> <?php printf(__('Unlimited uploads on %d site(s)', 'wp-maximum-upload-file-size'), $tier['sites']); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <?php _e('All Pro features included', 'wp-maximum-upload-file-size'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <?php _e('Lifetime updates', 'wp-maximum-upload-file-size'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <?php _e('Priority support', 'wp-maximum-upload-file-size'); ?></li>
                            <?php if ($tier['highlight']): ?>
                                <li class="popular"><span class="dashicons dashicons-heart"></span> <?php _e('Best Value', 'wp-maximum-upload-file-size'); ?></li>
                            <?php endif; ?>
                        </ul>
                        <a href="<?php echo esc_url($upgrade_url); ?>" target="_blank" class="button button-primary pricing-btn">
                            <?php _e('Buy Now', 'wp-maximum-upload-file-size'); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CTA Footer -->
        <div class="easymedia-upgrade-footer">
            <a href="<?php echo esc_url($upgrade_url); ?>" target="_blank" class="easymedia-btn cta-btn">Upgrade to Pro</a>
            <p class="note"><?php _e('Upgrade once — enjoy lifetime access with priority support and regular updates.', 'wp-maximum-upload-file-size'); ?></p>
        </div>

        <!-- Guarantee Bar -->
        <div class="easymedia-upgrade-guarantee">
            <span class="dashicons dashicons-shield-alt"></span>
            <strong><?php _e('14-Day Money-Back Guarantee', 'wp-maximum-upload-file-size'); ?></strong>
            <span class="separator">•</span>
            <?php _e('Secure Checkout', 'wp-maximum-upload-file-size'); ?>
            <span class="separator">•</span>
            <?php _e('Instant Access', 'wp-maximum-upload-file-size'); ?>
        </div>
    </div>
</div>

<style>
    .easymedia-pro-page {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    }

    /* Urgency Banner */
    .easymedia-urgency-banner {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        text-align: center;
        padding: 15px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 20px;
        margin-top:-10px
    }

    /* Trust Bar */
    .easymedia-trust-bar {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 30px;
        padding: 15px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e5e7eb;
        flex-wrap: wrap;
    }

    .trust-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #475569;
        font-weight: 500;
    }

    .trust-item .dashicons {
        color: #10b981;
        font-size: 16px;
        margin-top:6px;
    }

    /* Hero/Header */
    .easymedia-upgrade-header {
        text-align: center;
        padding: 50px 40px;
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
        color: white;
    }

    .easymedia-upgrade-header h1 {
        font-size: 2.5rem;
        margin-bottom: 15px;
        color:#fff;
    }

    .easymedia-upgrade-header h1 span {
        color: #ffcc00;
    }

    .easymedia-upgrade-header p {
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto 25px;
        opacity: 0.95;
    }

    .hero-btn {
        background: #ffcc00 !important;
        color: #000 !important;
        font-size: 1.1rem;
        padding: 15px 35px;
        border-radius: 8px;
        font-weight: 700;
        transition: transform 0.2s;
        text-decoration: none;
    }

    .hero-btn:hover {
        transform: scale(1.05);
    }

    /* Features Grid (Strict 3 Columns) */
    .easymedia-features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        padding: 50px 40px;
        background: #f9fafc;
    }

    .easymedia-feature-box {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        text-align: left;
        transition: transform 0.2s, box-shadow 0.3s;
    }

    .easymedia-feature-box:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .easymedia-feature-box h3 {
        font-size: 1.3rem;
        color: #222;
        margin-bottom: 15px;
    }

    .easymedia-feature-box p {
        color: #666;
        font-size: 0.95rem;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .learn-more {
        font-weight: 600;
        color: #3b82f6;
        text-decoration: none;
        font-size: 0.95rem;
        transition: color 0.2s;
    }

    .learn-more:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    /* Testimonials */
    .easymedia-testimonials {
        padding: 50px 40px;
        background: white;
        text-align: center;
    }

    .easymedia-testimonials h3 {
        margin-bottom: 30px;
        color: #111827;
        font-size: 1.8rem;
    }

    .testimonials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin: 0 auto;
    }

    .testimonial-box {
        background: #f8fafc;
        padding: 25px;
        border-radius: 10px;
        border-left: 4px solid #3b82f6;
    }

    .testimonial-box p {
        font-style: italic;
        margin-bottom: 15px;
        color: #374151;
        font-size: 16px;
    }

    .testimonial-box cite {
        font-weight: 600;
        color: #6b7280;
        font-size: 0.9rem;
    }

    /* Developer Note Section */
    .easymedia-developer-note {
        padding: 50px 40px;
        background: #f9fafc;
        text-align: left;
    }

    .easymedia-developer-note h3 {
        text-align: center;
        font-size: 28px;
        margin-bottom: 30px;
        color: #111827;
        font-weight: 700;
    }

    .developer-content {
        display: grid;
        grid-template-columns: 20% 80%;
        gap: 30px;
        max-width: 900px;
        margin: 0 auto;
        align-items: start;
    }

    .developer-photo {
        text-align: center;
    }

    .developer-photo img {
        width: 100%;
        max-width: 150px;
        height: auto;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        object-fit: cover;
    }

    .developer-text p {
        margin-bottom: 18px;
        font-size: 16px;
        line-height: 1.6;
        color: #374151;
    }

    .developer-text em {
        color: #3b82f6;
        font-style: italic;
    }

    /* Comparison Table with Row Borders */
    .easymedia-upgrade-compare {
        padding: 50px 40px;
        background: #f9fafc;
    }

    .easymedia-upgrade-compare h3 {
        text-align: center;
        font-size: 28px;
        margin-bottom: 10px;
        font-weight: 700;
        color: #111827;
    }

    .compare-subtitle {
        text-align: center;
        color: #6b7280;
        margin-bottom: 40px;
        font-size: 16px;
    }

    .easymedia-compare-table {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        width: 100%;
    }

    .easymedia-compare-table th {
        text-align: center;
        background: #f8fafc;
        font-weight: 700;
        padding: 18px 20px;
        font-size: 15px;
        color: #1f2937;
        border-bottom: 2px solid #e5e7eb;
    }

    .easymedia-compare-table th:first-child {
        text-align: left;
    }

    .easymedia-compare-table .pro-column {
        background: linear-gradient(135deg, #eff6ff 0%, #f5f3ff 100%);
        color: #3b82f6;
        font-weight: 600;
    }

    .easymedia-compare-table td {
        text-align: center;
        font-size: 14px;
        padding: 16px 20px;
        vertical-align: middle;
    }

    .easymedia-compare-table td:first-child {
        text-align: left;
        font-weight: 500;
        color: #374151;
    }

    .easymedia-compare-table .check .dashicons {
        color: #10b981;
        font-size: 20px;
    }

    .easymedia-compare-table .cross .dashicons {
        color: #ef4444;
        font-size: 20px;
        opacity: 0.5;
    }

    .easymedia-compare-table .highlight-row {
        background: #fef3c7;
    }

    .easymedia-compare-table .highlight-row td {
        font-weight: 600;
    }

    .table-row {
        border-bottom: 1px solid #e5e7eb;
    }

    .table-row:last-child {
        border-bottom: none;
    }

    /* Pricing (Minimal WP Style) */
    .easymedia-pricing-section {
        padding: 50px 40px;
        background: white;
        text-align: center;
    }

    .pricing-header h3 {
        font-size: 24px;
        margin-bottom: 10px;
        color: #23282d;
    }

    .pricing-header p {
        color: #646970;
        font-size: 14px;
        margin: 0 0 30px 0;
    }

    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        max-width: 900px;
        margin: 0 auto;
    }

    .pricing-card {
        background: #fff;
        border: 1px solid #c3c4c7;
        border-radius: 4px;
        padding: 20px;
        text-align: center;
        transition: border-color 0.2s;
        position: relative; /* Added for badge positioning */
    }

    .pricing-card.highlight {
        border-color: #0073aa;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .popular-badge {
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        background: #0073aa;
        color: white;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        z-index: 1;
    }

    .card-header {
        margin-top: 20px; /* Space for badge */
    }

    .card-header h4 {
        font-size: 16px;
        color: #23282d;
        margin: 0 0 10px 0;
        font-weight: 600;
    }

    .price-tag {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }

    .amount {
        font-size: 2rem;
        font-weight: 700;
        color: #0073aa;
    }

    .card-features {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
        text-align: left;
    }

    .card-features li {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        font-size: 14px;
        color: #50575e;
    }

    .card-features .dashicons {
        color: #00a32a;
        margin-right: 8px;
        font-size: 16px;
    }

    .popular {
        color: #0073aa;
        font-weight: 600;
        justify-content: center;
        margin-top: 5px;
    }

    .pricing-btn {
        width: 100%;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        background: #0073aa;
        border: 1px solid #0073aa;
        border-radius: 4px;
        color: white;
        text-decoration: none;
        transition: background 0.2s;
    }

    .pricing-btn:hover {
        background: #005a87;
        border-color: #005a87;
    }

    /* Footer CTA */
    .easymedia-upgrade-footer {
        text-align: center;
        padding: 40px;
        background: #f9fafc;
    }

    .cta-btn {
        background: #0073aa !important;
        color: #fff !important;
        font-size: 1.1rem;
        padding: 15px 35px;
        border-radius: 4px;
        font-weight: 700;
        margin-bottom: 15px;
        display: inline-block;
        transition: background 0.2s;
        border: 1px solid #0073aa;
        text-decoration: none;
    }

    .cta-btn:hover {
        background: #005a87 !important;
        border-color: #005a87;
    }

    .note {
        color: #646970;
        font-size: 0.9rem;
    }

    /* Guarantee */
    .easymedia-upgrade-guarantee {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
        padding: 20px;
        background: #f0f9ff;
        color: #50575e;
        font-size: 14px;
        border-top: 1px solid #ededed;
    }

    .easymedia-upgrade-guarantee .dashicons {
        color: #00a32a;
        font-size: 18px;
    }

    .easymedia-upgrade-guarantee strong {
        color: #23282d;
    }

    .separator {
        color: #c3c4c7;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .easymedia-features-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .easymedia-features-grid {
            grid-template-columns: 1fr;
            padding: 30px 20px;
        }

        .easymedia-upgrade-header, .easymedia-upgrade-compare, .easymedia-pricing-section, .easymedia-testimonials, .easymedia-developer-note {
            padding-left: 20px;
            padding-right: 20px;
        }

        .pricing-grid {
            grid-template-columns: 1fr;
        }

        .testimonials-grid {
            grid-template-columns: 1fr;
        }

        .price-tag {
            flex-direction: column;
            gap: 5px;
        }

        .amount {
            font-size: 1.5rem;
        }

        .popular-badge {
            top: 5px;
            font-size: 10px;
            padding: 3px 10px;
        }

        .developer-content {
            grid-template-columns: 1fr;
            gap: 20px;
            text-align: center;
        }

        .developer-photo {
            order: 2;
        }

        .developer-text {
            order: 1;
        }
    }
</style>