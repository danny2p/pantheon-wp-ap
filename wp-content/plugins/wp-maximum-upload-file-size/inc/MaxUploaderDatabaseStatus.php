<?php

class Max_Uploader_Database_Status {

    /**
     * WordPress DB instance
     *
     * @var wpdb
     */
    protected wpdb $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Get database info in structured format
     *
     * @return array
     */
    public function get_info(): array
    {
        return array(
            array(
                'title'           => esc_html__( 'Database Name', 'wp-maximum-upload-file-size' ),
                'version'         => DB_NAME,
                'status'          => 1,
                'success_message' => esc_html__( '- ok', 'wp-maximum-upload-file-size' ),
                'error_message'   => '',
            ),
            array(
                'title'           => esc_html__( 'Database Host', 'wp-maximum-upload-file-size' ),
                'version'         => DB_HOST,
                'status'          => 1,
                'success_message' => esc_html__( '- ok', 'wp-maximum-upload-file-size' ),
                'error_message'   => '',
            ),
            array(
                'title'           => esc_html__( 'Database User', 'wp-maximum-upload-file-size' ),
                'version'         => DB_USER,
                'status'          => 1,
                'success_message' => esc_html__( '- ok', 'wp-maximum-upload-file-size' ),
                'error_message'   => '',
            ),
            array(
                'title'           => esc_html__( 'Database Version', 'wp-maximum-upload-file-size' ),
                'version'         => $this->wpdb->db_version(),
                'status'          => 1,
                'success_message' => esc_html__( '- ok', 'wp-maximum-upload-file-size' ),
                'error_message'   => '',
            ),
            array(
                'title'           => esc_html__( 'Table Prefix', 'wp-maximum-upload-file-size' ),
                'version'         => $this->wpdb->prefix,
                'status'          => 1,
                'success_message' => esc_html__( '- ok', 'wp-maximum-upload-file-size' ),
                'error_message'   => '',
            ),
        );
    }
}

