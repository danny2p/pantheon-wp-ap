<?php
/**
 * Notice about removing the inline metadata.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.25.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$notice = 'We removed inline metadata from our default templates and only use JSON-LD now.<br/>';
$notice .= 'If you are using a custom template you should consider removing the inline metadata there as well.';

self::$notices[] = $notice;
