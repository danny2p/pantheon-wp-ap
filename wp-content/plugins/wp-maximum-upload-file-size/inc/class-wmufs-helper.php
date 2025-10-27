<?php
/**
 * WMUFS Premium Helper Class
 *
 * Centralized helper class to manage premium version checks and avoid code duplication
 * This class provides a single method to check if the premium version is active
 * and can be used throughout both free and pro versions of the plugin.
 *
 * @package MaxUploader
 * @since 2.0.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WMUFS_Helper {

    /**
     * Check if premium version is active
     *
     * This method checks for both the PRO version constant and class existence
     * to determine if the premium version is active and available.
     *
     * @return bool True if premium version is active, false otherwise
     * @since 2.0.3
     */
    public static function is_premium_active() {
        return defined('WMUFS_PRO_VERSION') || class_exists('WMUFS_Pro_Loader');
    }

    /**
     * Get upgrade URL for a premium version
     *
     * Returns the URL where users can upgrade to premium
     *
     * @return string Upgrade URL
     * @since 2.0.3
     */
    public static function get_upgrade_url() {
        return 'https://codepopular.com/product/easymedia?utm_source=plugin&utm_medium=link&utm_campaign=wmufs_free_to_pro_upgrade';
    }

    /**
     * Get role limits
     * 
     * @return array Array of role limits or empty array if not set
     */
    public static function get_role_limits() {
        $settings = get_option('wmufs_settings', array());
        return is_array($settings) && isset($settings['max_limits']) ? $settings['max_limits'] : array();
    }

    /**
     * Get available WordPress roles
     */
    public static function get_available_roles()
    {
        global $wp_roles;
        return $wp_roles->roles;
    }

    public static function user_can_manage_options() {
        return current_user_can('manage_options');
    }

}
