<?php

/**
 * Class Class_Max_Uploader_Loader
 */
class Class_Max_Uploader_Loader{
    // Autoload dependency.
    public function __construct(){
        $this->load_dependency();
    }

    /**
     * Load all Plugin FIle.
     */
    public function load_dependency(): void {

        include_once(WMUFS_PLUGIN_PATH. 'inc/class-wmufs-i18n.php');
        include_once(WMUFS_PLUGIN_PATH. 'inc/codepopular-plugin-suggest.php');
        include_once(WMUFS_PLUGIN_PATH. 'inc/hooks.php');
        include_once(WMUFS_PLUGIN_PATH. 'inc/codepopular-promotion.php');
        include_once(WMUFS_PLUGIN_PATH. 'admin/class-wmufs-admin.php');
        include_once(WMUFS_PLUGIN_PATH. 'inc/class-wmufs-chunk-files.php');

    }
}

/**
 * Initialize load class .
 */
function wmufs_run(): void {
    if ( class_exists( 'Class_Max_Uploader_Loader' ) ) {
        new Class_Max_Uploader_Loader();
    }
}

