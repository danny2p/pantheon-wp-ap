<?php
/*
 * Plugin Name:WP Upload Size
 * Description: Increase upload media file size in wordpress
 * Author:Husain Ahmed
 * Version: 2.0.2
 * Author URI: https://husain25.wordpress.com/
 * Author Email: husain.ahmed25@gmail.com
 * License: HAQV1
 */
 
	if ( !function_exists( 'wpusFileSizeMenu' ) ) {
		function wpusFileSizeMenu() {
			add_submenu_page('tools.php', 'WP Upload Size',	'WP Upload Size', 'manage_options',	'wpusUploadSize', 'wpusUploadSize' );
		}
	}
	add_action('admin_menu', 'wpusFileSizeMenu');

	function wpusUploadSize(){
		
	    
		
		if (!isset($_POST['wpusFileSizeField']) || !wp_verify_nonce($_POST['wpusFileSizeField'], 'wpusFileSizeAction')) {
			
		}else {
			$totalSize = sanitize_text_field($_POST['file_size']);
			update_option('wpus_file_size', $totalSize);
		}
		echo '<form method="post">';
		settings_fields("header_section");
		do_settings_sections("manage_options"); 
		wp_nonce_field('wpusFileSizeAction', 'wpusFileSizeField');
		submit_button();
		echo '</form>';
	}
	
	function wpusGetFileSize() {
		return get_option('wpus_file_size');
	}
	add_filter('upload_size_limit', 'wpusGetFileSize');
	
	function wpusDisplayOptions() {
		add_settings_section("header_section", "<br>Increase Upload Size", "wpusDisplayHeaderOptions", "manage_options");
		add_settings_field("header_logo", "Enter Numeric Value", "wpusDisplayLogoElement", "manage_options", "header_section");
		register_setting("header_section", "file_size");
	}

	function wpusDisplayHeaderOptions(){}

	function wpusDisplayLogoElement() {
		printf(
				'<input type="text" name="file_size" value="%s" />',
				(null!==get_option('wpus_file_size') ) ? esc_attr( get_option('wpus_file_size')) : ''
			);
		printf('(Enter the numeric value in bytes. Ex. for 64MB you would be put 67108864) ','');
	}
	add_action("admin_init", "wpusDisplayOptions");
