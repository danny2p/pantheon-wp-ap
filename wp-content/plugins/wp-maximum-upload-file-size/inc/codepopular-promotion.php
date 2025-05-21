<?php

if ( ! function_exists( 'codepopular_add_dashboard_widgets' ) ) {
	/**
	 * Add a widget to the dashboard.
	 */
	function codepopular_add_dashboard_widgets() {
		add_meta_box(
			'codepopular_latest_news_dashboard_widget',
			__( 'CodePopular Latest News from Blog', 'wp-maximum-upload-file-size' ),
			'codepopular_dashboard_widget_render',
			'dashboard',
			'side',
			'high'
		);
	}
	add_action( 'wp_dashboard_setup', 'codepopular_add_dashboard_widgets', 1 );
}

if ( ! function_exists( 'codepopular_dashboard_widget_render' ) ) {
	/**
	 * Render the dashboard widget content with transient cache.
	 */
	function codepopular_dashboard_widget_render(): void {

		// ✅ PROMOTION (cached for 12 hours)
		$promo_data = get_transient( 'codepopular_promo_data' );

		if ( false === $promo_data ) {
			$promo_response = wp_remote_get( 'https://raw.githubusercontent.com/shamimbdpro/promotion/main/promotion.json', array( 'timeout' => 10 ) );
			if ( ! is_wp_error( $promo_response ) ) {
				$promo_data = json_decode( wp_remote_retrieve_body( $promo_response ), true );
				set_transient( 'codepopular_promo_data', $promo_data, 12 * HOUR_IN_SECONDS );
			}
		}

		// ✅ Show promo if active
		if ( isset( $promo_data['active'] ) && $promo_data['active'] === 'yes' ) {
			?>
            <div class="codepopular-pro-widget">
                <a href="<?php echo esc_url( $promo_data['link'] ); ?>?utm_source=maxuploader_dashboard_feed_banner" target="_blank">
                    <img src="<?php echo esc_url( $promo_data['square_banner'] ); ?>" alt="CodePopular" style="width: 100%;"/>
                </a>
            </div>
			<?php
		}

		// ✅ BLOG POSTS (cached for 6 hours)
		$posts_data = get_transient( 'codepopular_blog_posts' );

		if ( false === $posts_data ) {
			$post_response = wp_remote_get( 'https://codepopular.com/wp-json/wp/v2/posts?per_page=4&categories=19', array( 'timeout' => 10 ) );
			if ( ! is_wp_error( $post_response ) ) {
				$posts_data = json_decode( wp_remote_retrieve_body( $post_response ) );
				set_transient( 'codepopular_blog_posts', $posts_data, 6 * HOUR_IN_SECONDS );
			}
		}

		// ✅ Show blog posts
		if ( ! empty( $posts_data ) && is_array( $posts_data ) ) {
			foreach ( $posts_data as $post ) {
				$date = gmdate( 'M j, Y', strtotime( $post->modified ) );
				?>
                <p class="codepopular-blog-feeds">
                    <a style="text-decoration: none;font-weight: bold" href="<?php echo esc_url( $post->link ); ?>?utm_source=maxuploader-dashboard-feed" target="_blank">
						<?php echo esc_html( $post->title->rendered ); ?>
                    </a> - <?php echo esc_html( $date ); ?>
                </p>
                <span><?php echo wp_trim_words( wp_strip_all_tags( $post->content->rendered ), 25, '...' ); ?></span>
				<?php
			}
			?>
            <hr>
            <p>
                <a style="text-decoration: none;font-weight: bold" href="https://codepopular.com/blog/?utm_source=maxuploader-dashboard-feed" target="_blank">
					<?php echo esc_html__( 'Get more WordPress tips & news on our blog...', 'wp-maximum-upload-file-size' ); ?>
                </a>
            </p>
            <a class="button" href="https://codepopular.com/contact?utm_source=maxuploader-dashboard-feed" target="_blank">
				<?php echo esc_html( 'Talk with WordPress Expert' ); ?>
            </a>
			<?php
		}
	}
}

// ✅ ADMIN NOTICE: Load only if the notice is not hidden
if ( time() > get_option( 'wmufs_notice_disable_time' ) ) {
	add_action( 'load-index.php', function () {
		add_action( 'admin_notices', 'codepopular_wmufs_promotions' );
	} );
}

if ( ! function_exists( 'codepopular_wmufs_promotions' ) ) {
	/**
	 * Display admin notice for support/promotion.
	 */
	function codepopular_wmufs_promotions() { ?>
        <div class="notice notice-success is-dismissible hideWmufsNotice">
            <div class="codepopular_notice">
                <h4><?php esc_html_e( 'Thank you for using our Plugin to Increase Upload Size!', 'wp-maximum-upload-file-size' ); ?></h4>
                <p><?php esc_html_e( 'We are glad that you are using our plugin... Thank you to everyone.', 'wp-maximum-upload-file-size' ); ?></p>
                <div class="codepopular__buttons">
                    <a href="https://ko-fi.com/codepopular?utm_source=maxuploader-dashboard-feed" target="_blank" class="codepopular__button btn__green dashicons-heart">
						<?php esc_html_e( 'Buy me a coffee', 'wp-maximum-upload-file-size' ); ?>
                    </a>
                    <a href="https://wordpress.org/support/plugin/wp-maximum-upload-file-size/reviews/#new-post" target="_blank" class="codepopular__button btn__yellow dashicons-star-filled">
						<?php esc_html_e( 'Add a Plugin review', 'wp-maximum-upload-file-size' ); ?>
                    </a>
                    <a href="https://codepopular.com/contact?utm_source=maxuploader-dashboard-feed" target="_blank" class="codepopular__button btn__dark dashicons-email">
						<?php esc_html_e( 'Contact Us', 'wp-maximum-upload-file-size' ); ?>
                    </a>
                    <button type="button" id="hideWmufsNotice" class="codepopular__button btn__blue dashicons-no"><?php esc_html_e( 'Hide for 6 months', 'wp-maximum-upload-file-size' ); ?></button>
                </div>
            </div>
        </div>
	<?php }
}
