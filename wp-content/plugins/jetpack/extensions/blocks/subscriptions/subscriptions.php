<?php
/**
 * Subscriptions Block.
 *
 * @package automattic/jetpack
 */

namespace Automattic\Jetpack\Extensions\Subscriptions;

use Automattic\Jetpack\Blocks;
use Automattic\Jetpack\Extensions\Premium_Content\Subscription_Service\Abstract_Token_Subscription_Service;
use Automattic\Jetpack\Extensions\Premium_Content\Subscription_Service\Jetpack_Token_Subscription_Service;
use Automattic\Jetpack\Modules;
use Automattic\Jetpack\Status\Host;
use Automattic\Jetpack\Status\Request;
use Jetpack_Gutenberg;
use Jetpack_Memberships;
use Jetpack_Subscriptions_Widget;

require_once __DIR__ . '/class-jetpack-subscription-site.php';
require_once __DIR__ . '/constants.php';
require_once JETPACK__PLUGIN_DIR . 'extensions/blocks/premium-content/_inc/subscription-service/include.php';

/**
 * These block defaults should match ./constants.js
 */
const DEFAULT_BORDER_RADIUS_VALUE = 0;
const DEFAULT_BORDER_WEIGHT_VALUE = 1;
const DEFAULT_FONTSIZE_VALUE      = '16px';
const DEFAULT_PADDING_VALUE       = 15;
const DEFAULT_SPACING_VALUE       = 10;
const DEFAULT_BUTTON_WIDTH        = 'auto';

/**
 * Registers the block for use in Gutenberg
 * This is done via an action so that we can disable
 * registration if we need to.
 */
function register_block() {
	/*
	 * Disable the feature on P2 blogs
	 */
	if ( function_exists( '\WPForTeams\is_wpforteams_site' ) &&
		\WPForTeams\is_wpforteams_site( get_current_blog_id() ) ) {
		return;
	}

	/*
	 * Do not proceed if the newsletter feature (Subscriptions module) is not enabled
	 */
	if ( ! ( new Modules() )->is_active( 'subscriptions' ) ) {
		return;
	}

	require_once JETPACK__PLUGIN_DIR . '/modules/memberships/class-jetpack-memberships.php';

	if ( \Jetpack_Memberships::should_enable_monetize_blocks_in_editor() ) {
		Blocks::jetpack_register_block(
			__DIR__,
			array(
				'render_callback' => __NAMESPACE__ . '\render_block',
				'supports'        => array(
					'spacing' => array(
						'margin'  => true,
						'padding' => true,
					),
					'align'   => array( 'wide', 'full' ),
				),
			)
		);
	}

	register_post_meta(
		'post',
		META_NAME_FOR_POST_LEVEL_ACCESS_SETTINGS,
		array(
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'auth_callback' => function () {
				return wp_get_current_user()->has_cap( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'post',
		META_NAME_FOR_POST_DONT_EMAIL_TO_SUBS,
		array(
			'default'       => false,
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'boolean',
			'auth_callback' => function () {
				return wp_get_current_user()->has_cap( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'post',
		META_NAME_FOR_POST_TIER_ID_SETTINGS,
		array(
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'integer',
			'auth_callback' => function () {
				return wp_get_current_user()->has_cap( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'post',
		META_NAME_CONTAINS_PAYWALLED_CONTENT,
		array(
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'boolean',
			'auth_callback' => function () {
				return wp_get_current_user()->has_cap( 'edit_posts' );
			},
		)
	);

	// This ensures Jetpack will sync this post meta to WPCOM.
	add_filter(
		'jetpack_sync_post_meta_whitelist',
		function ( $allowed_meta ) {
			return array_merge(
				$allowed_meta,
				array(
					META_NAME_FOR_POST_LEVEL_ACCESS_SETTINGS,
					META_NAME_FOR_POST_DONT_EMAIL_TO_SUBS,
					META_NAME_CONTAINS_PAYWALLED_CONTENT,
				)
			);
		}
	);

	// Hide the content – Priority 8 makes it run before do_blocks gets called for the content
	add_filter( 'the_content', __NAMESPACE__ . '\add_paywall', 8 );

	// Close comments on the front-end
	add_filter( 'comments_open', __NAMESPACE__ . '\maybe_close_comments', 10, 2 );
	add_filter( 'pings_open', __NAMESPACE__ . '\maybe_close_comments', 10, 2 );

	// Hide existing comments
	add_filter( 'get_comment', __NAMESPACE__ . '\maybe_gate_existing_comments' );

	// Gate the excerpt for a post
	add_filter( 'get_the_excerpt', __NAMESPACE__ . '\jetpack_filter_excerpt_for_newsletter', 10, 2 );

	// Add a 'Newsletter' column to the Edit posts page
	// We only display the "Newsletter" column if we have configured the paid newsletter plan
	if ( defined( 'WP_ADMIN' ) && WP_ADMIN && Jetpack_Memberships::has_configured_plans_jetpack_recurring_payments( 'newsletter' ) ) {
		add_action( 'manage_post_posts_columns', __NAMESPACE__ . '\register_newsletter_access_column' );
		add_action( 'manage_post_posts_custom_column', __NAMESPACE__ . '\render_newsletter_access_rows', 10, 2 );
		add_action( 'admin_head', __NAMESPACE__ . '\newsletter_access_column_styles' );
	}

	add_action( 'init', __NAMESPACE__ . '\maybe_prevent_super_cache_caching' );

	add_action( 'wp_after_insert_post', __NAMESPACE__ . '\add_paywalled_content_post_meta', 99, 2 );

	add_filter(
		'jetpack_options_whitelist',
		function ( $options ) {
			$options[] = 'jetpack_subscriptions_subscribe_post_end_enabled';
			$options[] = 'jetpack_subscriptions_subscribe_navigation_enabled';

			return $options;
		}
	);

	// If called via REST API, we need to register later in the lifecycle
	if ( ( new Host() )->is_wpcom_platform() && ! Request::is_frontend() ) {
		add_action(
			'restapi_theme_init',
			function () {
				Jetpack_Subscription_Site::init()->handle_subscribe_block_placements();
			}
		);
	} else {
		Jetpack_Subscription_Site::init()->handle_subscribe_block_placements();
	}
}
add_action( 'init', __NAMESPACE__ . '\register_block', 9 );

/**
 * Returns true when in a WP.com environment.
 *
 * @return boolean
 */
function is_wpcom() {
	return defined( 'IS_WPCOM' ) && IS_WPCOM;
}

/**
 * Adds a 'Newsletter' column after the 'Title' column in the post list
 *
 * @param array $columns An array of column names.
 * @return array An array of column names.
 */
function register_newsletter_access_column( $columns ) {
	$position   = array_search( 'title', array_keys( $columns ), true );
	$new_column = array( NEWSLETTER_COLUMN_ID => __( 'Newsletter', 'jetpack' ) );
	return array_merge(
		array_slice( $columns, 0, $position + 1, true ),
		$new_column,
		array_slice( $columns, $position, null, true )
	);
}

/**
 * Add a meta to prevent publication on firehose, ES AI or Reader
 *
 * @param int      $post_id Post id being saved.
 * @param \WP_Post $post Post being saved.
 * @return void
 */
function add_paywalled_content_post_meta( int $post_id, \WP_Post $post ) {
	if ( $post->post_type !== 'post' ) {
		return;
	}

	$access_level = get_post_meta( $post_id, META_NAME_FOR_POST_LEVEL_ACCESS_SETTINGS, true );

	$is_paywalled = false;
	switch ( $access_level ) {
		case Abstract_Token_Subscription_Service::POST_ACCESS_LEVEL_PAID_SUBSCRIBERS_ALL_TIERS:
		case Abstract_Token_Subscription_Service::POST_ACCESS_LEVEL_PAID_SUBSCRIBERS:
		case Abstract_Token_Subscription_Service::POST_ACCESS_LEVEL_SUBSCRIBERS:
			$is_paywalled = true;
	}
	if ( $is_paywalled ) {
		update_post_meta( $post_id, META_NAME_CONTAINS_PAYWALLED_CONTENT, $is_paywalled );
	}
	if ( ! $is_paywalled ) {
		delete_post_meta( $post_id, META_NAME_CONTAINS_PAYWALLED_CONTENT );
	}
}

/**
 * Displays the newsletter access level.
 *
 * @param string $column_id The ID of the column to display.
 * @param int    $post_id The current post ID.
 */
function render_newsletter_access_rows( $column_id, $post_id ) {
	if ( NEWSLETTER_COLUMN_ID !== $column_id ) {
		return;
	}

	$access_level = get_post_meta( $post_id, META_NAME_FOR_POST_LEVEL_ACCESS_SETTINGS, true );

	switch ( $access_level ) {
		case Abstract_Token_Subscription_Service::POST_ACCESS_LEVEL_PAID_SUBSCRIBERS_ALL_TIERS:
			echo esc_html__( 'Paid Subscribers (all plans)', 'jetpack' );
			break;
		case Abstract_Token_Subscription_Service::POST_ACCESS_LEVEL_PAID_SUBSCRIBERS:
			echo esc_html__( 'Paid Subscribers', 'jetpack' );
			break;
		case Abstract_Token_Subscription_Service::POST_ACCESS_LEVEL_SUBSCRIBERS:
			echo esc_html__( 'Subscribers', 'jetpack' );
			break;
		case Abstract_Token_Subscription_Service::POST_ACCESS_LEVEL_EVERYBODY:
			echo esc_html__( 'Everybody', 'jetpack' );
			break;
		default:
			echo '';
	}
}

/**
 * Adds the Newsletter column styles
 */
function newsletter_access_column_styles() {
	echo '<style id="jetpack-newsletter-newsletter-access-column"> table.fixed .column-newsletter_access { width: 10%; } </style>';
}

/**
 * Determine the amount of folks currently subscribed to the blog, splitted out in total_subscribers, email_subscribers, social_followers & paid_subscribers.
 *
 * @return array containing ['value' => ['total_subscribers' => 0, 'email_subscribers' => 0, 'paid_subscribers' => 0, 'social_followers' => 0]]
 */
function fetch_subscriber_counts() {
	$subs_count = 0;
	if ( is_wpcom() ) {
		$subs_count = array(
			'value' => \wpcom_fetch_subs_counts( true ),
		);
	} else {
		$cache_key  = 'wpcom_subscribers_totals';
		$subs_count = get_transient( $cache_key );
		if ( false === $subs_count || 'failed' === $subs_count['status'] ) {
			$xml = new \Jetpack_IXR_Client();
			$xml->query( 'jetpack.fetchSubscriberCounts' );

			if ( $xml->isError() ) { // If we get an error from .com, set the status to failed so that we will try again next time the data is requested.
				$subs_count = array(
					'status'  => 'failed',
					'code'    => $xml->getErrorCode(),
					'message' => $xml->getErrorMessage(),
					'value'   => ( isset( $subs_count['value'] ) ) ? $subs_count['value'] : array(
						'total_subscribers' => 0,
						'email_subscribers' => 0,
						'social_followers'  => 0,
						'paid_subscribers'  => 0,
					),
				);
			} else {
				$subs_count = array(
					'status' => 'success',
					'value'  => $xml->getResponse(),
				);
			}
			set_transient( $cache_key, $subs_count, 3600 ); // Try to cache the result for at least 1 hour.
		}
	}
	return $subs_count;
}

/**
 * Returns subscriber count based on include_social_followers attribute
 *
 * @param bool $include_social_followers Whether to include social followers in the count.
 * @return int
 */
function get_subscriber_count( $include_social_followers ) {
	$counts = fetch_subscriber_counts();

	if ( $include_social_followers ) {
		$subscriber_count = $counts['value']['total_subscribers'] + $counts['value']['social_followers'];
	} else {
		$subscriber_count = $counts['value']['total_subscribers'];
	}
	return $subscriber_count;
}

/**
 * Returns true if the block attributes contain a value for the given key.
 *
 * @param array  $attributes Array containing the block attributes.
 * @param string $key        Block attribute key.
 *
 * @return boolean
 */
function has_attribute( $attributes, $key ) {
	return isset( $attributes[ $key ] ) && $attributes[ $key ] !== 'undefined';
}

/**
 * Returns the value for the given attribute key, with the option of providing a default fallback value.
 *
 * @param array  $attributes Array containing the block attributes.
 * @param string $key        Block attribute key.
 * @param mixed  $default    Optional fallback value in case the key doesn't exist.
 *
 * @return mixed
 */
function get_attribute( $attributes, $key, $default = null ) {
	return has_attribute( $attributes, $key ) ? $attributes[ $key ] : $default;
}

/**
 * Mimics getColorClassName, getFontSizeClass and getGradientClass from @wordpress/block-editor js package.
 *
 * @param string $setting Setting name.
 * @param string $value   Setting value.
 *
 * @return string
 */
function get_setting_class_name( $setting, $value ) {
	if ( ! $setting || ! $value ) {
		return '';
	}

	return sprintf( 'has-%s-%s', $value, $setting );
}

/**
 * Uses block attributes to generate an array containing the classes for various block elements.
 * Based on Jetpack_Subscriptions_Widget::do_subscription_form() which the block was originally using.
 *
 * @param array $attributes Array containing the block attributes.
 *
 * @return array
 */
function get_element_class_names_from_attributes( $attributes ) {
	$text_color_class = get_setting_class_name( 'color', get_attribute( $attributes, 'textColor' ) );
	$font_size_class  = get_setting_class_name( 'font-size', get_attribute( $attributes, 'fontSize' ) );
	$border_class     = get_setting_class_name( 'border-color', get_attribute( $attributes, 'borderColor' ) );

	$button_background_class = get_setting_class_name( 'background-color', get_attribute( $attributes, 'buttonBackgroundColor' ) );
	$button_gradient_class   = get_setting_class_name( 'gradient-background', get_attribute( $attributes, 'buttonGradient' ) );

	$email_field_background_class = get_setting_class_name( 'background-color', get_attribute( $attributes, 'emailFieldBackgroundColor' ) );
	$email_field_gradient_class   = get_setting_class_name( 'gradient-background', get_attribute( $attributes, 'emailFieldGradient' ) );

	$submit_button_classes = array_filter(
		array(
			'wp-block-button__link'  => true,
			'no-border-radius'       => 0 === get_attribute( $attributes, 'borderRadius', 0 ),
			$font_size_class         => true,
			$border_class            => true,
			'has-text-color'         => ! empty( $text_color_class ),
			$text_color_class        => true,
			'has-background'         => ! empty( $button_background_class ) || ! empty( $button_gradient_class ),
			$button_background_class => ! empty( $button_background_class ),
			$button_gradient_class   => ! empty( $button_gradient_class ),
		)
	);

	$email_field_classes = array_filter(
		array(
			'no-border-radius'            => 0 === get_attribute( $attributes, 'borderRadius', 0 ),
			$font_size_class              => true,
			$border_class                 => true,
			$email_field_background_class => true,
			$email_field_gradient_class   => true,
		)
	);

	$block_wrapper_classes = array_filter(
		array(
			'wp-block-jetpack-subscriptions__supports-newline' => true,
			'wp-block-jetpack-subscriptions__use-newline' => (bool) get_attribute( $attributes, 'buttonOnNewLine' ),
			'wp-block-jetpack-subscriptions__show-subs'   => (bool) get_attribute( $attributes, 'showSubscribersTotal' ),
		)
	);

	return array(
		'block_wrapper' => implode( ' ', array_keys( $block_wrapper_classes ) ),
		'email_field'   => implode( ' ', array_keys( $email_field_classes ) ),
		'submit_button' => implode( ' ', array_keys( $submit_button_classes ) ),
	);
}

/**
 * Checks if block style is "button only"
 *
 * @param string $class_name Block attribute className; multiple names are spearated by space.
 *
 * @return bool
 */
function is_button_only_style( $class_name ) {
	if ( empty( $class_name ) ) {
		return false;
	}

	$class_names = explode( ' ', $class_name );
	return in_array( 'is-style-button', $class_names, true );
}

/**
 * Uses block attributes to generate an array containing the styles for various block elements.
 * Based on Jetpack_Subscriptions_Widget::do_subscription_form() which the block was originally using.
 *
 * @param array $attributes Array containing the block attributes.
 *
 * @return array
 */
function get_element_styles_from_attributes( $attributes ) {
	$is_button_only_style = is_button_only_style( get_attribute( $attributes, 'className', '' ) );

	$button_background_style = ! has_attribute( $attributes, 'buttonBackgroundColor' ) && has_attribute( $attributes, 'customButtonGradient' )
		? get_attribute( $attributes, 'customButtonGradient' )
		: get_attribute( $attributes, 'customButtonBackgroundColor' );

	$email_field_styles           = '';
	$submit_button_wrapper_styles = '';
	$submit_button_styles         = '';

	if ( ! empty( $button_background_style ) ) {
		$submit_button_styles .= sprintf( 'background: %s;', $button_background_style );
	}

	if ( has_attribute( $attributes, 'customTextColor' ) ) {
		$submit_button_styles .= sprintf( 'color: %s;', get_attribute( $attributes, 'customTextColor' ) );
	}

	if ( has_attribute( $attributes, 'buttonWidth' ) ) {
		$submit_button_wrapper_styles .= sprintf( 'width: %s;', get_attribute( $attributes, 'buttonWidth', DEFAULT_BUTTON_WIDTH ) );
		$submit_button_wrapper_styles .= 'max-width: 100%;';

		// Account for custom margins on inline forms.
		$submit_button_styles .= true === get_attribute( $attributes, 'buttonOnNewLine' )
			? 'width: 100%;'
			: sprintf( 'width: calc(100%% - %dpx);', get_attribute( $attributes, 'spacing', DEFAULT_SPACING_VALUE ) );
	}

	$font_size = get_attribute( $attributes, 'customFontSize', DEFAULT_FONTSIZE_VALUE );
	$style     = sprintf( 'font-size: %s%s;', $font_size, is_numeric( $font_size ) ? 'px' : '' );

	$submit_button_styles .= $style;
	$email_field_styles   .= $style;

	$padding = get_attribute( $attributes, 'padding', DEFAULT_PADDING_VALUE );
	$style   = sprintf( 'padding: %1$dpx %2$dpx %1$dpx %2$dpx;', $padding, round( $padding * 1.5 ) );

	$submit_button_styles .= $style;
	$email_field_styles   .= $style;

	if ( ! $is_button_only_style ) {
		$button_spacing = get_attribute( $attributes, 'spacing', DEFAULT_SPACING_VALUE );
		if ( true === get_attribute( $attributes, 'buttonOnNewLine' ) ) {
			$submit_button_styles .= sprintf( 'margin-top: %dpx;', $button_spacing );
		} else {
			$submit_button_styles .= 'margin: 0; '; // Reset Safari's 2px default margin for buttons affecting input and button union
			$submit_button_styles .= sprintf( 'margin-left: %dpx;', $button_spacing );
		}
	}

	if ( has_attribute( $attributes, 'borderColor' ) ) {
		$style                 = sprintf( 'border-color: %s;', get_attribute( $attributes, 'borderColor', '' ) );
		$submit_button_styles .= $style;
		$email_field_styles   .= $style;
	}

	$style                 = sprintf( 'border-radius: %dpx;', get_attribute( $attributes, 'borderRadius', DEFAULT_BORDER_RADIUS_VALUE ) );
	$submit_button_styles .= $style;
	$email_field_styles   .= $style;

	$style                 = sprintf( 'border-width: %dpx;', get_attribute( $attributes, 'borderWeight', DEFAULT_BORDER_WEIGHT_VALUE ) );
	$submit_button_styles .= $style;
	$email_field_styles   .= $style;

	if ( has_attribute( $attributes, 'customBorderColor' ) ) {
		$style = sprintf( 'border-color: %s; border-style: solid;', get_attribute( $attributes, 'customBorderColor' ) );

		$submit_button_styles .= $style;
		$email_field_styles   .= $style;
	}

	if ( ! Request::is_frontend() ) {
		$background_color_style = get_attribute_color( 'buttonBackgroundColor', $attributes, '#113AF5' /* default lettre theme color */ );
		$text_color_style       = get_attribute_color( 'textColor', $attributes, '#FFFFFF' );
		$submit_button_styles  .= sprintf( ' background-color: %s; color: %s;', $background_color_style, $text_color_style );
	}

	return array(
		'email_field'           => $email_field_styles,
		'submit_button'         => $submit_button_styles,
		'submit_button_wrapper' => $submit_button_wrapper_styles,
	);
}

/**
 * Retrieve the resolved color for a given attribute.
 *
 * @param string $attribute_name The name of the attribute to resolve.
 * @param array  $attributes     An array of all attributes.
 * @param string $default_color  A fallback color in case no color can be resolved.
 *
 * @return string Returns the resolved color or the default color if no color is found.
 */
function get_attribute_color( $attribute_name, $attributes, $default_color ) {
	if ( has_attribute( $attributes, $attribute_name ) ) {
		$color_slug     = get_attribute( $attributes, $attribute_name );
		$resolved_color = get_color_from_slug( $color_slug );

		if ( $resolved_color ) {
			return $resolved_color;
		}
	}

	return get_global_style_color( $attribute_name, $default_color );
}

/**
 * Retrieve the global style color based on a provided style key.
 *
 * @param string $style_key     The key for the desired style.
 * @param string $default_color A fallback color in case the global style is not set.
 *
 * @return string Returns the color defined in global styles or the default color if not defined.
 */
function get_global_style_color( $style_key, $default_color ) {
	$global_styles = wp_get_global_styles(
		array( 'color' ),
		array(
			'block_name' => 'core/button',
			'transforms' => array( 'resolve-variables' ),
		)
	);

	if ( isset( $global_styles[ $style_key ] ) ) {
		return $global_styles[ $style_key ];
	}

	return $default_color;
}

/**
 * Convert a color slug into its corresponding color value.
 *
 * @param string $slug The slug representation of the color.
 *
 * @return string|null Returns the color value if found, or null otherwise.
 */
function get_color_from_slug( $slug ) {
	$color_palettes = wp_get_global_settings( array( 'color', 'palette' ) );

	if ( ! is_array( $color_palettes ) ) {
		return null;
	}

	foreach ( $color_palettes as $palette ) {
		if ( is_array( $palette ) ) {
			foreach ( $palette as $color ) {
				if ( isset( $color['slug'] ) && $color['slug'] === $slug && isset( $color['color'] ) ) {
					return $color['color'];
				}
			}
		}
	}

	return null;
}

/**
 * Is the Jetpack_Memberships class loaded.
 */
function is_jetpack_memberships_loaded(): bool {
	return class_exists( '\Jetpack_Memberships' );
}

/**
 * Subscriptions block render callback.
 *
 * @param array $attributes Array containing the block attributes.
 *
 * @return string
 */
function render_block( $attributes ) {
	// If the Subscriptions module is not active, don't render the block.
	if ( ! ( new Modules() )->is_active( 'subscriptions' ) ) {
		return '';
	}

	if ( is_jetpack_memberships_loaded() ) {
		// We only want the sites that have newsletter feature enabled to be graced by this JavaScript.
		Jetpack_Gutenberg::load_assets_as_required( __DIR__ );
	} else {
		Jetpack_Gutenberg::load_styles_as_required( FEATURE_NAME );
	}

	if ( ! class_exists( 'Jetpack_Subscriptions_Widget' ) ) {
		return '';
	}

	// Prefill the email field with the current user's email if they are logged in via Memberships premium content token
	$subscribe_email = Jetpack_Memberships::get_current_user_email();

	// If no email, then prefill the email field with the current user's email if they are logged in
	if ( empty( $subscribe_email ) ) {
		$current_user = wp_get_current_user();
		if ( ! empty( $current_user->user_email ) ) {
			$subscribe_email = $current_user->user_email;
		}
	}

	// The block is using the Jetpack_Subscriptions_Widget backend, hence the need to increase the instance count.
	++Jetpack_Subscriptions_Widget::$instance_count;

	$classes = get_element_class_names_from_attributes( $attributes );
	$styles  = get_element_styles_from_attributes( $attributes );

	// The default value was previously "true" in block.json. We don't want to rely setting "default" in block.json to falsy,
	// because it would change the setting for previously saved blocks. Block editor doesn't store default values in attributes at all.
	// Hence users without this set will still get social counts included in the subscriber counter.
	// Lowering the subscriber count on their behalf with code change would be controversial.
	// We want to disencourage including social count as it's misleading.
	$include_social_followers = isset( $attributes['includeSocialFollowers'] ) ? (bool) get_attribute( $attributes, 'includeSocialFollowers' ) : true;

	$data = array(
		'widget_id'                         => Jetpack_Subscriptions_Widget::$instance_count,
		'subscribe_email'                   => $subscribe_email,
		'is_paid_subscriber'                => get_attribute( $attributes, 'isPaidSubscriber', false ),
		'wrapper_attributes'                => get_block_wrapper_attributes(
			array(
				'class' => $classes['block_wrapper'],
			)
		),
		'subscribe_placeholder'             => get_attribute( $attributes, 'subscribePlaceholder', __( 'Type your email…', 'jetpack' ) ),
		'submit_button_text'                => get_attribute( $attributes, 'submitButtonText', __( 'Subscribe', 'jetpack' ) ),
		'submit_button_text_subscribed'     => get_attribute( $attributes, 'submitButtonTextSubscribed', __( 'Subscribed', 'jetpack' ) ),
		'submit_button_text_upgrade'        => get_attribute( $attributes, 'submitButtonTextUpgrade', __( 'Upgrade subscription', 'jetpack' ) ),
		'success_message'                   => get_attribute(
			$attributes,
			'successMessage',
			esc_html__( "Success! An email was just sent to confirm your subscription. Please find the email now and click 'Confirm' to start subscribing.", 'jetpack' )
		),
		'show_subscribers_total'            => (bool) get_attribute( $attributes, 'showSubscribersTotal' ),
		'subscribers_total'                 => get_attribute( $attributes, 'showSubscribersTotal' ) ? get_subscriber_count( $include_social_followers ) : 0,
		'referer'                           => esc_url_raw(
			( is_ssl() ? 'https' : 'http' ) . '://' . ( isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : '' ) .
			( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '' )
		),
		'source'                            => 'subscribe-block',
		'app_source'                        => get_attribute( $attributes, 'appSource', null ),
		'class_name'                        => get_attribute( $attributes, 'className' ),
		'selected_newsletter_categories'    => get_attribute( $attributes, 'selectedNewsletterCategoryIds', array() ),
		'preselected_newsletter_categories' => get_attribute( $attributes, 'preselectNewsletterCategories', false ),
	);

	// Only render the email version in non-frontend contexts.
	if ( is_feed() || wp_is_xml_request() ||
		( defined( 'REST_REQUEST' ) && REST_REQUEST && ! wp_is_json_request() ) ||
		( defined( 'REST_API_REQUEST' ) && REST_API_REQUEST ) ||
		( defined( 'WP_CLI' ) && WP_CLI ) ||
		wp_is_jsonp_request() ) {
		return render_for_email( $data, $styles );
	}

	return render_for_website( $data, $classes, $styles );
}

/**
 *  Get the post access level for the current post. Defaults to 'everybody' if the query is not for a single post
 *
 * @return string the actual post access level (see projects/plugins/jetpack/extensions/blocks/subscriptions/constants.js for the values).
 */
function get_post_access_level_for_current_post() {
	if ( ! is_singular() ) {
		// There is no "actual" current post.
		return Abstract_Token_Subscription_Service::POST_ACCESS_LEVEL_EVERYBODY;
	}

	return Jetpack_Memberships::get_post_access_level();
}

/**
 * Renders the subscriptions block at the site.
 *
 * @param array $data    Array containing block view data.
 * @param array $classes Array containing the classes for different block elements.
 * @param array $styles  Array containing the styles for different block elements.
 *
 * @return string
 */
function render_for_website( $data, $classes, $styles ) {
	$lang                 = get_locale();
	$blog_id              = \Jetpack_Options::get_option( 'id' );
	$widget_id_suffix     = Jetpack_Subscriptions_Widget::$instance_count > 1 ? '-' . Jetpack_Subscriptions_Widget::$instance_count : '';
	$form_id              = 'subscribe-blog' . $widget_id_suffix;
	$form_url             = 'https://wordpress.com/email-subscriptions';
	$post_access_level    = get_post_access_level_for_current_post();
	$is_button_only_style = ! empty( $data['class_name'] ) ? is_button_only_style( $data['class_name'] ) : false;

	// Post ID is used for pulling post-specific paid status, and returning to the right post after confirming subscription
	$post_id = null;
	if ( in_the_loop() ) {
		$post_id = get_the_ID();
	} elseif ( is_singular( 'post' ) || is_page() ) {
		$post_id = get_queried_object_id();
	} else {
		$post_id = get_option( 'page_on_front' );
	}

	$subscribe_field_id    = apply_filters( 'subscribe_field_id', 'subscribe-field' . $widget_id_suffix, $data['widget_id'] );
	$tier_id               = get_post_meta( $post_id, META_NAME_FOR_POST_TIER_ID_SETTINGS, true );
	$is_subscribed         = Jetpack_Memberships::is_current_user_subscribed();
	$button_text           = get_submit_button_text( $data );
	$show_subscriber_count = $data['show_subscribers_total'] && $data['subscribers_total'] && ! $is_subscribed;

	ob_start();

	Jetpack_Subscriptions_Widget::render_widget_status_messages(
		array(
			'success_message' => $data['success_message'],
		)
	);
	?>
	<div <?php echo wp_kses_data( $data['wrapper_attributes'] ); ?>>
		<div class="wp-block-jetpack-subscriptions__container<?php echo ! $is_subscribed ? ' is-not-subscriber' : ''; ?>">
			<?php if ( is_top_subscription() ) : ?>
				<p id="subscribe-submit" class="is-link"
					<?php if ( ! empty( $styles['submit_button_wrapper'] ) ) : ?>
						style="<?php echo esc_attr( $styles['submit_button_wrapper'] ); ?>"
					<?php endif; ?>
				>
						<a
							href="<?php echo esc_url( 'https://wordpress.com/reader/site/subscription/' . $blog_id ); ?>"
							<?php if ( ! empty( $classes['submit_button'] ) ) : ?>
								class="<?php echo esc_attr( $classes['submit_button'] ); ?>"
							<?php endif; ?>
							<?php if ( ! empty( $styles['submit_button'] ) ) : ?>
								style="<?php echo esc_attr( $styles['submit_button'] ); ?>"
							<?php endif; ?>
						>
							<?php echo sanitize_submit_text( $button_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
				</p>
			<?php else : ?>
				<form
					action="<?php echo esc_url( $form_url ); ?>"
					method="post"
					accept-charset="utf-8"
					data-blog="<?php echo esc_attr( $blog_id ); ?>"
					data-post_access_level="<?php echo esc_attr( $post_access_level ); ?>"
					data-subscriber_email="<?php echo esc_attr( $data['subscribe_email'] ); ?>"
					id="<?php echo esc_attr( $form_id ); ?>"
				>
					<div class="wp-block-jetpack-subscriptions__form-elements">
						<?php if ( ! $is_subscribed && ! $is_button_only_style ) : ?>
						<p id="subscribe-email">
							<label
								id="<?php echo esc_attr( $subscribe_field_id . '-label' ); ?>"
								for="<?php echo esc_attr( $subscribe_field_id ); ?>"
								class="screen-reader-text"
							>
								<?php echo esc_html( $data['subscribe_placeholder'] ); ?>
							</label>
							<?php
							printf(
								'<input
									required="required"
									type="email"
									name="email"
									%1$s
									style="%2$s"
									placeholder="%3$s"
									value="%4$s"
									id="%5$s"
									%6$s
								/>',
								( ! empty( $classes['email_field'] )
									? 'class="' . esc_attr( $classes['email_field'] ) . '"'
									: ''
								),
								( ! empty( $styles['email_field'] )
									? esc_attr( $styles['email_field'] )
									: 'width: 95%; padding: 1px 10px'
								),
								esc_attr( $data['subscribe_placeholder'] ),
								esc_attr( $data['subscribe_email'] ),
								esc_attr( $subscribe_field_id ),
								( ! empty( $data['subscribe_email'] )
									? 'disabled title="' . esc_attr__( "You're logged in with this email", 'jetpack' ) . '"'
									: 'title="' . esc_attr__( 'Please fill in this field.', 'jetpack' ) . '"'
								)
							);
							?>
						</p>
						<?php endif; ?>
						<p id="subscribe-submit"
							<?php if ( ! empty( $styles['submit_button_wrapper'] ) ) : ?>
								style="<?php echo esc_attr( $styles['submit_button_wrapper'] ); ?>"
							<?php endif; ?>
						>
							<input type="hidden" name="action" value="subscribe"/>
							<input type="hidden" name="blog_id" value="<?php echo (int) $blog_id; ?>"/>
							<input type="hidden" name="source" value="<?php echo esc_url( $data['referer'] ); ?>"/>
							<input type="hidden" name="sub-type" value="<?php echo esc_attr( $data['source'] ); ?>"/>
							<input type="hidden" name="app_source" value="<?php echo esc_attr( $data['app_source'] ); ?>"/>
							<input type="hidden" name="redirect_fragment" value="<?php echo esc_attr( $form_id ); ?>"/>
							<input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>"/>
							<?php
							wp_nonce_field( 'blogsub_subscribe_' . $blog_id );

							if ( ! empty( $post_id ) ) {
								echo '<input type="hidden" name="post_id" value="' . esc_attr( $post_id ) . '"/>';
							}

							if ( ! empty( $tier_id ) ) {
								echo '<input type="hidden" name="tier_id" value="' . esc_attr( $tier_id ) . '"/>';
							}

							if ( $data['preselected_newsletter_categories'] && ! empty( $data['selected_newsletter_categories'] ) ) {
								echo '<input type="hidden" name="selected_newsletter_categories" value="' . esc_attr( implode( ',', $data['selected_newsletter_categories'] ) ) . '"/>';
							}
							?>
							<button type="submit"
								<?php if ( ! empty( $classes['submit_button'] ) ) : ?>
									class="<?php echo esc_attr( $classes['submit_button'] ); ?>"
								<?php endif; ?>
								<?php if ( ! empty( $styles['submit_button'] ) ) : ?>
									style="<?php echo esc_attr( $styles['submit_button'] ); ?>"
								<?php endif; ?>
								name="jetpack_subscriptions_widget"
							>
								<?php echo sanitize_submit_text( $button_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</button>
						</p>
					</div>
				</form>
			<?php endif; ?>
			<?php if ( $show_subscriber_count ) : ?>
				<div class="wp-block-jetpack-subscriptions__subscount">
					<?php echo esc_html( Jetpack_Memberships::get_join_others_text( $data['subscribers_total'] ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Renders the email version of the subscriptions block.
 *
 * @param array $data    Array containing block view data.
 * @param array $styles  Array containing the styles for different block elements.
 *
 * @return string
 */
function render_for_email( $data, $styles ) {
	$submit_button_wrapper_style = ! empty( $styles['submit_button_wrapper'] ) ? 'style="' . esc_attr( $styles['submit_button_wrapper'] ) . '"' : '';
	$button_text                 = get_submit_button_text( $data );

	$html = '<div ' . wp_kses_data( $data['wrapper_attributes'] ) . '>
		<div>
			<div>
				<div>
					<p ' . $submit_button_wrapper_style . '>
						<a href="' . esc_url( get_post_permalink() ) . '" style="' . esc_attr( $styles['submit_button'] ) . ' text-decoration: none; white-space: nowrap; margin-left: 0">' . sanitize_submit_text( $button_text ) . '</a>
					</p>
				</div>
			</div>
		</div>
	</div>';

	return $html;
}

/**
 * Filter excerpts looking for subscription data.
 *
 * @param string   $excerpt The extrapolated excerpt string.
 * @param \WP_Post $post    The current post being processed (in `get_the_excerpt`).
 *
 * @return mixed
 */
function jetpack_filter_excerpt_for_newsletter( $excerpt, $post = null ) {
	// The blogmagazine theme is overriding WP core `get_the_excerpt` filter and only passing the excerpt
	// TODO: Until this is fixed, return the excerpt without gating. See https://github.com/Automattic/jetpack/pull/28102#issuecomment-1369161116
	if ( $post && str_contains( $post->post_content, '<!-- wp:jetpack/subscriptions -->' ) ) {
		$excerpt .= sprintf(
			// translators: %s is the permalink url to the current post.
			__( "<p><a href='%s'>View post</a> to subscribe to site newsletter.</p>", 'jetpack' ),
			get_post_permalink()
		);
	}
	return $excerpt;
}

/**
 * Gate access to posts
 *
 * @param string $the_content Post content.
 *
 * @return string
 */
function add_paywall( $the_content ) {
	require_once JETPACK__PLUGIN_DIR . 'modules/memberships/class-jetpack-memberships.php';

	$post_access_level = Jetpack_Memberships::get_post_access_level();

	if ( Jetpack_Memberships::user_can_view_post() ) {
		if ( $post_access_level !== Abstract_Token_Subscription_Service::POST_ACCESS_LEVEL_EVERYBODY ) {
			do_action(
				'earn_track_paywalled_post_view',
				array(
					'post_id' => get_the_ID(),
				)
			);
		}
		return $the_content;
	}

	$paywalled_content = get_paywall_content();

	if ( has_block( \Automattic\Jetpack\Extensions\Paywall\BLOCK_NAME ) ) {
		if ( strpos( $the_content, \Automattic\Jetpack\Extensions\Paywall\BLOCK_HTML ) ) {
			return strstr( $the_content, \Automattic\Jetpack\Extensions\Paywall\BLOCK_HTML, true ) . $paywalled_content;
		}
		// WordPress generates excerpts by either rendering or stripping blocks before invoking the `the_content` filter.
		// In the context of generating an excerpt, the Paywall block specifically renders THE_EXCERPT_BLOCK.
		if ( strpos( $the_content, \Automattic\Jetpack\Extensions\Paywall\THE_EXCERPT_BLOCK ) ) {
			return strstr( $the_content, \Automattic\Jetpack\Extensions\Paywall\THE_EXCERPT_BLOCK, true );
		}
	}

	return $paywalled_content;
}

/**
 * Gate access to comments. We want to close comments on private sites.
 *
 * @param bool $default_comments_open Default state of the comments_open filter.
 * @param int  $post_id Current post id.
 *
 * @return bool
 */
function maybe_close_comments( $default_comments_open, $post_id ) {
	if ( ! $default_comments_open || ! $post_id ) {
		return $default_comments_open;
	}

	require_once JETPACK__PLUGIN_DIR . 'modules/memberships/class-jetpack-memberships.php';
	return Jetpack_Memberships::user_can_view_post();
}

/**
 * Gate access to existing comments
 *
 * @param string $comment The comment.
 *
 * @return string
 */
function maybe_gate_existing_comments( $comment ) {
	if ( empty( $comment ) ) {
		return $comment;
	}

	require_once JETPACK__PLUGIN_DIR . 'modules/memberships/class-jetpack-memberships.php';
	if ( Jetpack_Memberships::user_can_view_post() ) {
		return $comment;
	}
	return '';
}

/**
 * Is the Jetpack_Token_Subscription_Service class loaded
 *
 * @return bool
 */
function is_jetpack_token_subscription_service_loaded(): bool {
	return class_exists( 'Automattic\Jetpack\Extensions\Premium_Content\Subscription_Service\Jetpack_Token_Subscription_Service' );
}

/**
 * Adds support for WP Super cache and Boost cache
 */
function maybe_prevent_super_cache_caching() {
	// Prevents cached page to be served if the Membership cookie is present
	if ( is_jetpack_token_subscription_service_loaded() ) {
		do_action( 'wpsc_add_cookie', Jetpack_Token_Subscription_Service::JWT_AUTH_TOKEN_COOKIE_NAME );
	}

	if ( is_user_auth() ) {
		// Do not cache the page if user is auth with Membership token
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
	}
}

/**
 * Returns paywall content blocks
 *
 * @return string
 */
function get_paywall_content() {
	if ( Jetpack_Memberships::user_is_pending_subscriber() ) {
		return get_paywall_blocks_subscribe_pending();
	}
	if ( doing_filter( 'get_the_excerpt' ) ) {
		return '';
	}
	return get_paywall_blocks();
}

/**
 * Returns the current URL.
 *
 * TODO: Copied from https://github.com/Automattic/jetpack/blob/bb885061dc3ee7a80a78a5f0116ab3fcebfddb09/projects/packages/boost-core/src/lib/class-url.php#L39
 * TODO: Move to a shared package
 *
 * @return string
 */
function get_current_url() {
	// Fallback to the site URL if we're unable to determine the URL from $_SERVER global.
	$current_url = site_url();

	if ( isset( $_SERVER ) && is_array( $_SERVER ) ) {
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitization happens at the end
		$scheme = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
		$host   = ! empty( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : null;
		$path   = ! empty( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';

		// Support for local plugin development and testing using ngrok.
		if ( ! empty( $_SERVER['HTTP_X_ORIGINAL_HOST'] ) && str_contains( $_SERVER['HTTP_X_ORIGINAL_HOST'], 'ngrok.io' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- This is validating.
			$host = wp_unslash( $_SERVER['HTTP_X_ORIGINAL_HOST'] );
		}
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( $host ) {
			$current_url = esc_url_raw( sprintf( '%s://%s%s', $scheme, $host, $path ) );
		}
	}

	return $current_url;
}

/**
 * Get the submit button text based on the subscription status.
 *
 * @param array $data Array containing block view data.
 *
 * @return string
 */
function get_submit_button_text( $data ) {
	if ( ! Jetpack_Memberships::is_current_user_subscribed() ) {
		return $data['submit_button_text'];
	}
	if ( ! Jetpack_Memberships::user_can_view_post() ) {
		return $data['submit_button_text_upgrade'];
	}
	return '✓ ' . $data['submit_button_text_subscribed'];
}

/**
 * Returns true if there are no more tiers to upgrade to.
 *
 * @return boolean
 */
function is_top_subscription() {
	if ( ! Jetpack_Memberships::is_current_user_subscribed() ) {
		return false;
	}
	if ( ! Jetpack_Memberships::user_can_view_post() ) {
		return false;
	}
	return true;
}

/**
 * Sanitize the submit button text.
 *
 * @param string $text String containing the submit button text.
 *
 * @return string
 */
function sanitize_submit_text( $text ) {
	return wp_kses(
		html_entity_decode( $text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ),
		Jetpack_Subscriptions_Widget::$allowed_html_tags_for_submit_button
	);
}

/**
 * Returns paywall content blocks if user is not authenticated
 *
 * @return string
 */
function get_paywall_blocks() {
	$custom_paywall = apply_filters( 'jetpack_custom_paywall_blocks', false );
	if ( ! empty( $custom_paywall ) ) {
		return $custom_paywall;
	}

	if ( ! Request::is_frontend() ) { // emails
		return get_paywall_simple();
	}

	require_once JETPACK__PLUGIN_DIR . 'modules/memberships/class-jetpack-memberships.php';
	$is_paid_post       = is_paid_post();
	$is_paid_subscriber = Jetpack_Memberships::user_is_paid_subscriber();

	$access_heading = $is_paid_subscriber
		? esc_html__( 'Upgrade to continue reading', 'jetpack' )
		: esc_html__( 'Subscribe to continue reading', 'jetpack' );

	$subscribe_text = $is_paid_post
		// translators: %s is the name of the site.
		? (
			$is_paid_subscriber
				? esc_html__( 'Upgrade to get access to the rest of this post and other exclusive content.', 'jetpack' )
				: esc_html__( 'Become a paid subscriber to get access to the rest of this post and other exclusive content.', 'jetpack' )
		)
		// translators: %s is the name of the site.
		: esc_html__( 'Subscribe to get access to the rest of this post and other subscriber-only content.', 'jetpack' );

	$login_block = '';

	if ( is_user_auth() ) {
		if ( ( new Host() )->is_wpcom_simple() ) {
			// We cannot use wpcom_logmein_redirect_url since it returns redirect URL when user is already logged in.
			$login_link           = add_query_arg(
				array(
					'redirect_to' => rawurlencode( get_current_url() ),
					'blog_id'     => get_current_blog_id(),
				),
				'https://wordpress.com/log-in/link'
			);
			$switch_accounts_link = wp_logout_url( $login_link );
			$login_block          = '<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px"}}} -->
<p class="has-text-align-center" style="font-size:14px">
	<a href="' . $switch_accounts_link . '">' . __( 'Switch accounts', 'jetpack' ) . '</a>
</p>
<!-- /wp:paragraph -->';
		}
	} else {
		$access_question = $is_paid_post ? esc_html__( 'Already a paid subscriber?', 'jetpack' ) : esc_html__( 'Already a subscriber?', 'jetpack' );
		$login_block     = '<!-- wp:group {"style":{"typography":{"fontSize":"14px"}},"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-group" style="font-size:14px">
	<!-- wp:jetpack/subscriber-login {"logInLabel":"' . $access_question . '"} /-->
</div>
<!-- /wp:group -->';
	}

	$lock_svg = plugins_url( 'images/lock-paywall.svg', JETPACK__PLUGIN_FILE );

	return '
<!-- wp:group {"style":{"border":{"width":"1px","radius":"4px"},"spacing":{"padding":{"top":"32px","bottom":"32px","left":"32px","right":"32px"}}},"borderColor":"primary","className":"jetpack-subscribe-paywall","layout":{"type":"constrained","contentSize":"400px"}} -->
<div class="wp-block-group jetpack-subscribe-paywall has-border-color has-primary-border-color" style="border-width:1px;border-radius:4px;padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px">
<!-- wp:image {"align":"center","width":24,"height":24,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image aligncenter size-large is-resized"><img src="' . $lock_svg . '" alt="" width="24" height="24"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"24px"},"layout":{"selfStretch":"fit"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:24px;font-style:normal;font-weight:600">' . $access_heading . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px"},"spacing":{"margin":{"top":"10px","bottom":"10px"}}}} -->
<p class="has-text-align-center" style="margin-top:10px;margin-bottom:10px;font-size:14px">' . $subscribe_text . '</p>
<!-- /wp:paragraph -->

<!-- wp:jetpack/subscriptions {"borderRadius":50,"borderColor":"primary","className":"is-style-compact","isPaidSubscriber":' . ( $is_paid_subscriber ? 'true' : 'false' ) . '} /-->
' . $login_block . '
</div>
<!-- /wp:group -->
';
}

/**
 * Returns true if user is auth for subscriptions check, otherwise returns false.
 *
 * @return bool
 */
function is_user_auth(): bool {
	if ( ( new Host() )->is_wpcom_simple() && is_user_logged_in() ) {
		return true;
	}
	if ( current_user_can( 'manage_options' ) ) {
		return true;
	}

	if ( is_jetpack_token_subscription_service_loaded() ) {
		if ( Jetpack_Token_Subscription_Service::has_token_from_cookie() ) {
			return true;
		}
	}
	return false;
}

/**
 * Returns `true` if the post is a paid post.
 */
function is_paid_post(): bool {
	require_once JETPACK__PLUGIN_DIR . 'modules/memberships/class-jetpack-memberships.php';

	// Make sure Stripe is connected and the post is marked for paid subscribers.
	if ( Jetpack_Memberships::has_connected_account() && is_jetpack_token_subscription_service_loaded() ) {
		return Jetpack_Memberships::get_post_access_level() === Jetpack_Token_Subscription_Service::POST_ACCESS_LEVEL_PAID_SUBSCRIBERS;
	}

	return false;
}

/**
 * Returns true if the post is a subscribers post.
 */
function is_subscribers_post(): bool {
	require_once JETPACK__PLUGIN_DIR . 'modules/memberships/class-jetpack-memberships.php';

	// Make sure Stripe is connected and the post is marked for paid subscribers.
	if ( Jetpack_Memberships::has_connected_account() && is_jetpack_token_subscription_service_loaded() ) {
		return Jetpack_Memberships::get_post_access_level() === Jetpack_Token_Subscription_Service::POST_ACCESS_LEVEL_SUBSCRIBERS;
	}

	return false;
}

/**
 * Returns paywall content blocks when email confirmation is pending
 *
 * @return string
 */
function get_paywall_blocks_subscribe_pending() {
	$subscribe_email = Jetpack_Memberships::get_current_user_email();

	/** This filter is documented in \Automattic\Jetpack\Forms\ContactForm\Contact_Form */
	if ( is_wpcom() || false !== apply_filters( 'jetpack_auto_fill_logged_in_user', false ) ) {
		$current_user = wp_get_current_user();
		if ( ! empty( $current_user->user_email ) ) {
			$subscribe_email = $current_user->user_email;
		}
	}

	$access_heading = esc_html__( 'Confirm your subscription to continue reading', 'jetpack' );

	/* translators: %s: email address */
	$subscribe_text = sprintf( esc_html__( 'Head to your inbox and confirm your email address %s.', 'jetpack' ), $subscribe_email );

	$lock_svg = plugins_url( 'images/lock-paywall.svg', JETPACK__PLUGIN_FILE );

	return '
<!-- wp:group {"style":{"border":{"width":"1px","radius":"4px"},"spacing":{"padding":{"top":"32px","bottom":"32px","left":"32px","right":"32px"}}},"borderColor":"primary","className":"jetpack-subscribe-paywall","layout":{"type":"constrained","contentSize":"400px"}} -->
<div class="wp-block-group jetpack-subscribe-paywall has-border-color has-primary-border-color" style="border-width:1px;border-radius:4px;padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px">
<!-- wp:image {"align":"center","width":24,"height":24,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image aligncenter size-large is-resized"><img src="' . $lock_svg . '" alt="" width="24" height="24"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"24px", "maxWidth":"initial"},"layout":{"selfStretch":"fit"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:24px;font-style:normal;font-weight:600;max-width:initial">' . $access_heading . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px"},"spacing":{"margin":{"top":"10px","bottom":"10px"}}}} -->
<p class="has-text-align-center" style="margin-top:10px;margin-bottom:10px;font-size:14px">' . $subscribe_text . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
';
}

/**
 * Return content for non frontend views like Reader, emails.
 */
function get_paywall_simple(): string {
	$is_paid_post        = is_paid_post();
	$is_subscribers_post = is_subscribers_post();
	$is_subscriber       = is_jetpack_memberships_loaded() && Jetpack_Memberships::is_current_user_subscribed();
	$paywall_heading     = esc_html__( 'Subscribe to keep reading', 'jetpack' );

	if ( $is_subscribers_post && ! $is_subscriber ) {
		$paywall_description = esc_html__( "It's a subscribers only post. Subscribe to get access to the rest of this post and other subscriber-only content.", 'jetpack' );
		$paywall_action_btn  = esc_html__( 'Subscribe', 'jetpack' );
	} elseif ( $is_paid_post && $is_subscriber ) {
		$paywall_description = esc_html__( "You're currently a free subscriber. Upgrade your subscription to get access to the rest of this post and other paid-subscriber only content.", 'jetpack' );
		$paywall_action_btn  = esc_html__( 'Upgrade subscription', 'jetpack' );
	} else {
		// - For paid post when the user is not a subscriber.
		// - Default for all other cases.
		$paywall_description = esc_html__( 'Become a paid subscriber to get access to the rest of this post and other exclusive content.', 'jetpack' );
		$paywall_action_btn  = esc_html__( 'Subscribe', 'jetpack' );
	}

	return '
<!-- wp:columns -->
<div class="wp-block-columns jetpack-paywall-simple" style="display: inline-block; width: 90%">
    <!-- wp:column -->
    <div class="wp-block-column" style="background-color: #F6F7F7; padding: 32px; 24px;">
        <!-- wp:heading -->
        <h2 class="has-text-align-center" style="margin: 0 0 12px; font-weight: 600;">' . $paywall_heading . '</h2>
        <!-- /wp:heading -->
        <!-- wp:paragraph -->
        <p class="has-text-align-center"
           style="text-align: center;
                  color: #50575E;
                  font-weight: 400;
                  font-size: 16px;
                  font-family: \'SF Pro Text\', sans-serif;
                  line-height: 28.8px;">
        ' . $paywall_description . '
        </p>
        <!-- /wp:paragraph -->
        <!-- wp:buttons -->
        <div class="wp-block-buttons" style="text-align: center;">
            <!-- wp:button -->
            <div class="wp-block-button" style="display: inline-block; margin: 10px 0; border-style: none; padding: 0;">
                <a href="' . esc_url( get_post_permalink() ) . '" class="wp-block-button__link wp-element-button"
                   data-wpcom-track data-tracks-link-desc="paywall-email-click"
                   style="display: inline-block;
                          padding: 12px 15px;
                          background-color: #3858e9;
                          color: #FFFFFF;
                          text-decoration: none;
                          border-radius: 5px;
                          font-family: \'SF Pro Display\', sans-serif;
                          font-weight: 500;
                          font-size: 16px;
                          text-align: center;">' . $paywall_action_btn . '</a>
            </div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->
    </div>
    <!-- /wp:column -->
</div>
<!-- /wp:columns -->
';
}
