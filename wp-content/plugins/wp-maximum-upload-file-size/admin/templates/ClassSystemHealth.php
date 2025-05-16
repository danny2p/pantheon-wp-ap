<div class="wrap wmufs_mb_50">
    <h1>
        <span class="dashicons dashicons-database-view system-status-icon"></span>
		<?php esc_html_e( 'System Status', 'wp-maximum-upload-file-size' ); ?>
    </h1>
    <br>

    <div class="wmufs_admin_deashboard">
        <div class="wmufs_row" id="poststuff">

            <!-- Start Content Area -->
            <div class="wmufs_admin_left wmufs_card wmufs-col-8">
				<?php foreach ( $system_status as $group ) : ?>
                    <h2><?php echo esc_html( $group['group'] ); ?></h2>

                    <table class="wmufs-system-status">
                        <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Title','wp-maximum-upload-file-size');?></th>
                            <th scope="col"><?php esc_html_e('Status', 'wp-maximum-upload-file-size');?></th>
                            <th scope="col"><?php esc_html_e('Message', 'wp-maximum-upload-file-size');?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ( $group['status'] as $item ) : ?>
                            <tr>
                                <td><?php echo esc_html( $item['title'] ); ?></td>
                                <td>
									<?php if ( 1 == $item['status'] ) : ?>
                                        <span class="dashicons dashicons-yes"></span>
									<?php else : ?>
                                        <span class="dashicons dashicons-warning"></span>
									<?php endif; ?>
                                </td>
                                <td>
									<?php if ( 1 == $item['status'] ) : ?>
                                        <p class="wpifw_status_message">
											<?php echo esc_html( $item['version'] ); ?>
											<?php echo $item['success_message']; //phpcs:ignore ?>
                                        </p>
									<?php else : ?>
										<?php echo esc_html( $item['version'] ); ?>
                                        <p class="wpifw_status_message"><?php echo $item['error_message']; //phpcs:ignore ?></p>
									<?php endif; ?>
                                </td>
                            </tr>
						<?php endforeach; ?>
                        </tbody>
                    </table>
				<?php endforeach; ?>
            </div>

            <!-- Start Sidebar Area -->
            <div class="wmufs_admin_right_sidebar wmufs_card wmufs-col-4">
				<?php include WMUFS_PLUGIN_PATH . 'admin/templates/class-wmufs-sidebar.php'; ?>
            </div>

        </div>
    </div>
</div>
