<?php

include WMUFS_PLUGIN_PATH . 'inc/MaxUploaderWordPressStatus.php';
$wordpress_status = (new MaxUploaderWordPressStatus())->get_info();

include WMUFS_PLUGIN_PATH . 'inc/MaxUploaderDatabaseStatus.php';
$database_info = (new Max_Uploader_Database_Status())->get_info();

include WMUFS_PLUGIN_PATH . 'inc/MaxUploaderServerStatus.php';
$server_status = (new MaxUploaderServerStatus())->get_info();

$system_status = array(
    [
        'group' => 'WordPress Status',
        'status' => $wordpress_status,
    ],

	[
		'group' => 'Server Status And PHP Status',
		'status' => $server_status,
	],
//
//    [
//        'group' => 'Database Status',
//        'status' => $database_info
//    ],
);
