<?php
/**
 * Shared progress bar template partial.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.8.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/admin
 *
 * @var string $progress_bar_type Progress bar type: 'tools', 'import', or 'reports'.
 */

$progress_bar_type = isset( $progress_bar_type ) ? $progress_bar_type : 'tools';
?>
<div id="wprm-<?php echo esc_attr( $progress_bar_type ); ?>-progress-container" class="wprm-progress-container">
	<div id="wprm-<?php echo esc_attr( $progress_bar_type ); ?>-progress-bar" class="wprm-progress-fill"></div>
	<span class="wprm-progress-percentage"><span class="wprm-admin-loader"></span></span>
</div>
