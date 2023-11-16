<?php
/**
 * Handle caching plugins.
 *
 * @link      	https://bootstrapped.ventures
 * @since      	8.10.3
 *
 * @package    	WP_Recipe_Maker
 * @subpackage 	WP_Recipe_Maker/includes/public
 */

/**
 * Handle caching plugins.
 *
 * @since		8.10.3
 * @package    	WP_Recipe_Maker
 * @subpackage 	WP_Recipe_Maker/includes/public
 * @author     	Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Cache {
	/**
	 * Try to clear cache for a specific post ID.
	 *
	 * @since	8.10.3
	 */
	public static function clear( $post_id = false, $clear_all = true ) {
		// Clear cache for specific post ID.
		if ( $post_id ) {
			// WP Rocket.
			if ( function_exists( 'rocket_clean_post' ) ) {
				rocket_clean_post( $post_id );
			}

			// W3 Total Cache.
			if ( function_exists( 'w3tc_flush_post' ) ) {
				w3tc_flush_post( $post_id );
			}

			// WP Super Cache.
			if ( function_exists( 'wp_cache_post_change' ) ) {
				wp_cache_post_change( $post_id );
			}

			// WP Fastest Cache.
			if ( function_exists( 'wpfc_clear_post_cache_by_id' ) ) {
				wpfc_clear_post_cache_by_id( $post_id );
			}

			// LiteSpeed Cache.
			do_action( 'litespeed_purge_post', $post_id );

			// WP Optimize.
			if ( class_exists( 'WPO_Page_Cache' ) && method_exists( 'WPO_Page_Cache', 'delete_single_post_cache' ) ) {
				WPO_Page_Cache::delete_single_post_cache( $post_id );
			}

			// KeyCDN Cache Enabler.
			if ( class_exists( 'Cache_Enabler' ) && method_exists( 'Cache_Enabler', 'clear_post_cache' ) ) {
				Cache_Enabler::clear_post_cache( $post_id );
			}

			if ( class_exists( 'FlyingPress\Purge' ) ) {
				$post_link = get_permalink( $post_id );

				if ( $post_link ) {
					if ( is_callable( 'FlyingPress\Purge::purge_url' ) ) {
						FlyingPress\Purge::purge_url( $post_link );
					} elseif ( is_callable( 'FlyingPress\Purge::purge_by_urls' ) ) {
						FlyingPress\Purge::purge_by_urls( array( $post_link ) );
					}
				}
			}
		}

		// Allow other plugins to hook in.
		do_action( 'wprm_clear_cache', $post_id, $clear_all );
	
		// Caches below require us to clear everything.
		if ( $clear_all ) {
			// WP Engine Cache.
			if ( class_exists( 'WpeCommon' ) ) {
				if ( method_exists('WpeCommon', 'purge_memcached' ) ) {
					\WpeCommon::purge_memcached();
				}
			
				if ( method_exists('WpeCommon', 'clear_maxcdn_cache' ) ) {
					\WpeCommon::clear_maxcdn_cache();
				}
			
				if ( method_exists('WpeCommon', 'purge_varnish_cache' ) ) {
					\WpeCommon::purge_varnish_cache();
				}
			}

			// Kinsta Cache.
			global $kinsta_cache;
			if ( class_exists( '\Kinsta\Cache' ) && ! empty( $kinsta_cache ) ) {
				$kinsta_cache->kinsta_cache_purge->purge_complete_caches();
			}

			// GoDaddy Cache.
			if ( function_exists( 'ccfm_godaddy_purge' ) ) {
				ccfm_godaddy_purge();
			}

			// SiteGround Optimizer Cache.
			if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
				sg_cachepress_purge_cache();
			}

			// Breeze Cache.
			do_action('breeze_clear_all_cache');

			// Autoptimize Cache.
			if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) {
				autoptimizeCache::clearall();
			}

			// Clear WP Object Cache.
			global $wp_object_cache;
			if ( $wp_object_cache && is_object( $wp_object_cache ) ) {
				try {
					wp_cache_flush();
				} catch ( Exception $e ) {
					// Do nothing.
				}
			}
		}
	}
}
