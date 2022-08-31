<?php
/**
 * Handle the recipe video metadata.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle the recipe video metadata.
 *
 * @since      3.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_MetadataVideo {

	/**
	 * Apis for looking up video metadata.
	 *
	 * @since    3.1.0
	 * @access   private
	 * @var      array	$apis	Apis for looking up video metadata.
	 */
	private static $apis = array(
		'youtube' => 'AIzaSyCb5vbLd6f6397ygyBd-yIU0omZagxTfDY',
	);

	/**
	 * Output metadata in the HTML head.
	 *
	 * @since   3.0.0
	 * @param	object $recipe Recipe to get the video metadata for.
	 */
	public static function get_video_metadata_for_recipe( $recipe ) {
		$metadata = array(
			'main' => false,
			'instructions' => array(),
		);

		// Get main video metadata.
		if ( $recipe->video_id() ) {
			$attachment = get_post( $recipe->video_id() );

			if ( $attachment ) {
				$metadata['main'] = self::get_video_metadata_for_upload( $recipe->video_id() );
			}
		} elseif ( $recipe->video_embed() ) {
			$metadata['main'] = self::get_video_metadata_for_embed( $recipe->video_embed(), $recipe );
		}

		// Get instruction videos metadata.
		$instruction_groups = $recipe->instructions();
		foreach ( $instruction_groups as $group_index => $instruction_group ) {
			$metadata['instructions'][ $group_index ] = array();

			foreach ( $instruction_group['instructions'] as $index => $instruction ) {
				$video_metadata = false;

				if ( isset( $instruction['video'] ) && isset( $instruction['video']['type'] ) ) {
					if ( 'upload' === $instruction['video']['type'] && $instruction['video']['id'] ) {
						$video_metadata = self::get_video_metadata_for_upload( $instruction['video']['id'] );
					} elseif ( 'embed' === $instruction['video']['type'] && $instruction['video']['embed'] ) {
						$video_metadata = self::get_video_metadata_for_embed( $instruction['video']['embed'] );
					}
				}

				$metadata['instructions'][ $group_index ][ $index ] = $video_metadata;
			}
		}

		return $metadata;
	}

	/**
	 * Get video metadata for uploaded video.
	 *
	 * @since   5.11.0
	 * @param	int $id ID of the video to get the metadata for.
	 */
	public static function get_video_metadata_for_upload( $id ) {
		$metadata = false;
		$attachment = get_post( $id );

		if ( $attachment ) {
			$video_data = wp_get_attachment_metadata( $id );
			$video_url = wp_get_attachment_url( $id );

			$image_id = get_post_thumbnail_id( $id );
			$thumb = wp_get_attachment_image_src( $image_id, 'full' );
			$thumbnail_url = $thumb && isset( $thumb[0] ) ? $thumb[0] : '';

			$metadata = array(
				'name' => $attachment->post_title,
				'description' => $attachment->post_content,
				'thumbnailUrl' => $thumbnail_url,
				'contentUrl' => $video_url,
				'uploadDate' => date( 'c', strtotime( $attachment->post_date ) ),
				'duration' => 'PT' . $video_data['length'] . 'S',
			);
		}

		return $metadata;
	}

	/**
	 * Get video metadata for embedded video.
	 *
	 * @since   5.11.0
	 * @param	string $embed_code  Video embed code to get the metadata for.
	 * @param	mixed  $recipe		Optional recipe to get the metadata for.
	 */
	public static function get_video_metadata_for_embed( $embed_code, $recipe = false ) {
		$metadata = false;
		$embed_code = trim( $embed_code );

		// Only check if there actually is some embed code.
		if ( $embed_code ) {
			$metadata = $metadata ? $metadata : self::check_for_youtube_embed( $embed_code );
			$metadata = $metadata ? $metadata : self::check_for_mediavine_embed( $embed_code, $recipe );
			$metadata = $metadata ? $metadata : self::check_for_adthrive_embed( $embed_code );
			$metadata = $metadata ? $metadata : self::check_for_wp_youtube_lyte_embed( $embed_code );
			$metadata = $metadata ? $metadata : self::check_for_brid_tv_embed( $embed_code );
	
			$metadata = $metadata ? $metadata : self::check_for_oembed( $embed_code );
			$metadata = $metadata ? $metadata : self::check_for_meta_html( $embed_code );
		}

		return $metadata;
	}

	/**
	 * Check the embed code for a Adthrive video.
	 *
	 * @since   3.0.0
	 * @param	string $embed_code Embed code to check.
	 */
	private static function check_for_adthrive_embed( $embed_code ) {
		$metadata = false;

		$pattern = get_shortcode_regex( array( 'adthrive-in-post-video-player' ) );

		// Prevent issues with - in shortcode.
		$embed_code = str_ireplace( 'video-id', 'video_id', $embed_code );
		$embed_code = str_ireplace( 'upload-date', 'upload_date', $embed_code );

		preg_match( '/' . $pattern . '/s', $embed_code, $matches );
		
		if ( $matches && isset( $matches[3] ) ) {
			$attributes_string = str_replace( '<', '&lt;', $matches[3] ); // Otherwise shortcode_parse_atts doesn't work.
			$attributes = shortcode_parse_atts( stripslashes( $attributes_string ) );

			$video_id = isset( $attributes['video_id'] ) ? $attributes['video_id'] : false;

			if ( $video_id ) {
				$upload_date = isset( $attributes['upload_date'] ) ? $attributes['upload_date'] : '';
				$name = isset( $attributes['name'] ) ? $attributes['name'] : '';
				$description = isset( $attributes['description'] ) ? $attributes['description'] : '';

				$metadata = array(
					'name' => $name,
					'description' => $description,
					'thumbnailUrl' => 'https://content.jwplatform.com/thumbs/' . $video_id . '-720.jpg',
					'contentUrl' => 'https://content.jwplatform.com/videos/' . $video_id . '.mp4',
					'uploadDate' => $upload_date,
				);
			}
		}

		return $metadata;
	}

	/**
	 * Check the embed code for a MediaVine video.
	 *
	 * @since   3.0.0
	 * @param	string $embed_code Embed code to check.
	 * @param	object $recipe Recipe to get the video metadata for.
	 */
	private static function check_for_mediavine_embed( $embed_code, $recipe = false ) {
		$metadata = false;

		// New embed code.
		preg_match( '/mv-video-id-(.*?)(\"|\s)/im', $embed_code, $match );

		// Look for old embed code if new one not found.
		if ( ! $match ) {
			preg_match( '/.mediavine.com\/videos\/(.*?)\.js/im', $embed_code, $match );			
		}

		if ( $match && isset( $match[1] ) ) {
			$mv_video_id = $match[1];
			$metadata = self::check_for_oembed( 'https://embed.mediavine.com/videos/' . $mv_video_id );

			// Make sure MV doesn't output metadata as well if we're already doing that.
			if ( $metadata && false === strpos( $embed_code, 'data-disable-jsonld' ) ) {
				$metadata_disabled_embed_code = str_ireplace( 'id="' . $mv_video_id . '"', 'id="' . $mv_video_id . '" data-disable-jsonld="true"', $embed_code );

				if ( $embed_code !== $metadata_disabled_embed_code ) {
					if ( $recipe ) {
						update_post_meta( $recipe->id(), 'wprm_video_embed', $metadata_disabled_embed_code );
					}
				}
			}
		}

		return $metadata;
	}

	/**
	 * Check the embed code for a YouTube video.
	 *
	 * @since   8.1.0
	 * @param	string $embed_code Embed code to check.
	 */
	private static function check_for_youtube_embed( $embed_code ) {
		$metadata = false;

		// oEmbed detects https://www.youtube.com/watch?v=123456789 videos by default, both not YouTube shorts or https://youtu.be URLs.
		$found_youtube_id = false;

		// Check for YT Shorts URL.
		preg_match( '/www\.youtube\.com\/shorts\/(.*)$/i', $embed_code, $matches );
		if ( $matches && isset( $matches[1] ) ) {
			$found_youtube_id = $matches[1];
		}

		// Check for shortened URL.
		preg_match( '/youtu\.be\/(.*)$/i', $embed_code, $matches );
		if ( $matches && isset( $matches[1] ) ) {
			$found_youtube_id = $matches[1];
		}

		// Try regular YT URL to get the metadata.
		if ( $found_youtube_id ) {
			$metadata = self::check_for_oembed( 'https://www.youtube.com/watch?v=' . $found_youtube_id );
		}

		return $metadata;
	}

	/**
	 * Check the embed code for a WP YouTube Lyte video.
	 *
	 * @since   3.1.0
	 * @param	string $embed_code Embed code to check.
	 */
	private static function check_for_wp_youtube_lyte_embed( $embed_code ) {
		$metadata = false;

		$pattern = get_shortcode_regex( array( 'lyte' ) );
		preg_match( '/' . $pattern . '/s', $embed_code, $matches );
		
		if ( $matches && isset( $matches[3] ) ) {

			$attributes = shortcode_parse_atts( stripslashes( $matches[3] ) );
			$video_id = isset( $attributes['id'] ) ? $attributes['id'] : false;

			if ( $video_id ) {
				$metadata = self::check_for_oembed( 'https://www.youtube.com/watch?v=' . $video_id );
			}
		}

		return $metadata;
	}

	/**
	 * Check the embed code for a Brid TV video.
	 *
	 * @since   5.7.0
	 * @param	string $embed_code Embed code to check.
	 */
	private static function check_for_brid_tv_embed( $embed_code ) {
		$metadata = false;

		$pattern = get_shortcode_regex( array( 'brid' ) );
		preg_match( '/' . $pattern . '/s', $embed_code, $matches );
		
		if ( $matches && isset( $matches[3] ) ) {
			$attributes = shortcode_parse_atts( stripslashes( $matches[3] ) );

			$video_id = isset( $attributes['video'] ) ? $attributes['video'] : false;

			if ( $video_id ) {
				$name = isset( $attributes['title'] ) ? $attributes['title'] : '';
				$description = isset( $attributes['description'] ) ? $attributes['description'] : '';
				$duration = isset( $attributes['duration'] ) ? 'PT' . intval( $attributes['duration'] ) . 'S' : '';
				$thumbnail_url = isset( $attributes['thumbnailurl'] ) ? $attributes['thumbnailurl'] : '';
				$upload_date = isset( $attributes['uploaddate'] ) ? date( 'c', strtotime( $attributes['uploaddate'] ) ) : '';

				$metadata = array(
					'name' => $name,
					'description' => $description,
					'thumbnailUrl' => $thumbnail_url,
					'uploadDate' => $upload_date,
					'duration' => $duration,
				);
			}
		}

		return $metadata;
	}

	/**
	 * Check the embed code for meta fields.
	 *
	 * @since   3.3.0
	 * @param	string $embed_code Embed code to check.
	 */
	private static function check_for_meta_html( $embed_code ) {
		$metadata = false;

		$dom = new DOMDocument;

		// Prevent errors from showing up.
		$internalErrors = libxml_use_internal_errors( true );
		$loaded_html = $dom->loadHTML( $embed_code );
		libxml_use_internal_errors( $internalErrors );

		if ( $loaded_html ) {
			$meta_tags = $dom->getElementsByTagName( 'meta' );

			if ( 0 < $meta_tags->length ) {
				$metadata = array();

				foreach ( $meta_tags as $meta_tag ) {
					if ( in_array( $meta_tag->getAttribute( 'itemprop' ),
							array(
								'uploadDate',
								'name',
								'description',
								'duration',
								'expires',
								'interactionCount',
								'thumbnailUrl',
								'contentUrl',
								'embedUrl',
							)
						) ) {
						$metadata[ $meta_tag->getAttribute( 'itemprop' ) ] = $meta_tag->getAttribute( 'content' );
					}
				}

				if ( ! $metadata ) {
					$metadata = false;
				}
			}
		}

		return $metadata;
	}

	/**
	 * Check the embed code for an oEmbed video.
	 *
	 * @since   3.0.0
	 * @param	string $embed_code Embed code to check.
	 */
	private static function check_for_oembed( $embed_code ) {
		$metadata = false;
		$url = false;

		// Check if it's a regular URL.
		$potential_url = filter_var( $embed_code, FILTER_SANITIZE_URL );

		if ( filter_var( $potential_url, FILTER_VALIDATE_URL ) ) {
			$url = $potential_url;
		}

		// No regular URL? Check embed code.
		if ( ! $url ) {
			$url = self::get_url_from_embed_code( $embed_code );
		}

		// If we've found a URL, try getting the metadata through oEmbed.
		if ( $url ) {
			// Get the WP oEmbed class.
			global $wp_oembed;
			if ( ! $wp_oembed ) {
				if ( file_exists( ABSPATH . WPINC . '/class-wp-oembed.php' ) ) {
					require_once( ABSPATH . WPINC . '/class-wp-oembed.php' );
				} else {
					// Backwards compatibility.
					require_once( ABSPATH . WPINC . '/class-oembed.php' );
				}
				$wp_oembed = new WP_oEmbed();
			}

			// Check if we can find a provider for this URL.
			$provider = $wp_oembed->get_provider( $url );

			if ( $provider ) {
				$oembed_data = $wp_oembed->fetch( $provider, $url );

				if ( $oembed_data ) {
					$name = isset( $oembed_data->title ) ? $oembed_data->title : '';
					$description = isset( $oembed_data->description ) ? $oembed_data->description : '';
					$duration = isset( $oembed_data->duration ) ? 'PT' . intval( $oembed_data->duration ) . 'S' : '';
					$thumbnail_url = isset( $oembed_data->thumbnail_url ) ? $oembed_data->thumbnail_url : '';
					$upload_date = isset( $oembed_data->upload_date ) ? date( 'c', strtotime( $oembed_data->upload_date ) ) : '';

					if ( ! $upload_date && isset( $oembed_data->uploadDate ) ) {
						$upload_date = date( 'c', strtotime( $oembed_data->uploadDate ) );
					}

					// Default to oEmbed URL.
					$content_url = isset( $oembed_data->content_url ) ? $oembed_data->content_url : '';

					if ( ! $content_url && isset( $oembed_data->contentUrl ) ) {
						$content_url = $oembed_data->contentUrl;
					}
					$content_url = $content_url ? $content_url : $url;

					// EmbedUrl.
					$embed_url = isset( $oembed_data->embed_url ) ? $oembed_data->embed_url : '';
					if ( ! $embed_url && isset( $oembed_data->embedUrl ) ) {
						$embed_url = $oembed_data->embedUrl;
					}
					if ( ! $embed_url && isset( $oembed_data->html ) ) {
						preg_match( '/src\s*=\s*"([^"]+)"/im', $oembed_data->html, $match );
						if ( $match && isset( $match[1] ) ) {
							$embed_url = $match[1];
						}
					}

					$metadata = array(
						'name' => $name,
						'description' => $description,
						'thumbnailUrl' => $thumbnail_url,
						'embedUrl' => $embed_url,
						'contentUrl' => $content_url,
						'uploadDate' => $upload_date,
						'duration' => $duration,
					);
				}

				// Extend Youtube metadata via API.
				if ( is_array( $metadata ) && false !== stripos( $provider, 'youtube' ) ) {
					$metadata = self::get_youtube_metadata( $url ) + $metadata;

					// Don't set YouTube metadata if uploadDate is missing.
					if ( ! $metadata['uploadDate'] ) {
						$metadata = false;
					}
				}
			}
		}

		return $metadata;
	}

	/**
	 * Check the embed code for a URL.
	 *
	 * @since   3.0.0
	 * @param	string $embed_code Embed code to get the URL from.
	 */
	private static function get_url_from_embed_code( $embed_code ) {
		// Check for YouTube embed code.
		preg_match("/youtube(-nocookie)?.com\/embed\/(.*?)[\"\?]/im", $embed_code, $match );
		if ( $match && isset( $match[2] ) ) {
			return 'https://www.youtube.com/watch?v=' . $match[2];
		}

		// Check for src="" in the embed code.
		preg_match( '/src\s*=\s*"([^"]+)"/im', $embed_code, $match );
		if ( $match && isset( $match[1] ) ) {
			return $match[1];
		}		

		return false;
	}

	/**
	 * Get the Youtube API key.
	 *
	 * @since   6.0.0
	 */
	private static function get_youtube_api_key() {
		$personal_key = trim( WPRM_Settings::get( 'metadata_youtube_api_key' ) );
		return $personal_key ? $personal_key : self::$apis['youtube'];
	}

	/**
	 * Get Youtube metadata video the API.
	 *
	 * @since   3.1.0
	 * @param	string $url Youtube video URL.
	 */
	private static function get_youtube_metadata( $url ) {
		$metadata = array();

		// Get video ID.
		preg_match( "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $video_parts );
		if( isset( $video_parts[1] ) ) {
			$video_id = $video_parts[1];
		}

		if ( $video_id ) {
			$api_key = self::get_youtube_api_key();
			$api_url = 'https://youtube.googleapis.com/youtube/v3/videos?part=snippet&part=contentDetails&id=' . urlencode( $video_id ) . '&key=' . urlencode( $api_key );

			$response = wp_remote_get( $api_url );
			$body = ! is_wp_error( $response ) && isset( $response['body'] ) ? json_decode( $response['body'] ) : false;

			if ( $body ) {
				$item = isset( $body->items[0] ) ? $body->items[0] : false;

				if ( $item ) {
					$snippet = $item->snippet;
					$contentDetails = $item->contentDetails;
					$metadata = array();

					// Don't set empty values.
					if ( isset ( $snippet->title ) && $snippet->title ) { $metadata['name'] = $snippet->title; }
					if ( isset ( $snippet->description ) && $snippet->description ) { $metadata['description'] = $snippet->description; }
					if ( isset ( $snippet->publishedAt ) && $snippet->publishedAt ) { $metadata['uploadDate'] = date( 'c', strtotime( $snippet->publishedAt ) ); }
					if ( isset ( $contentDetails->duration ) && $contentDetails->duration ) { $metadata['duration'] = $contentDetails->duration; }
				}
			}
		}

		return $metadata;
	}
}
