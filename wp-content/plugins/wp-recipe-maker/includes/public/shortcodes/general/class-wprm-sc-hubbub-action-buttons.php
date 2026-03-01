<?php
/**
 * Handle the Hubbub Action Buttons shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 */

/**
 * Handle the Hubbub Action Buttons shortcode.
 *
 * Outputs the Hubbub Action Buttons reusable pattern (the same set of buttons
 * that can be auto-inserted via Hubbub). Wraps [hubbub_action_buttons] which
 * accepts two optional parameters:
 * - id: (optional) Override the default pattern by supplying a pattern/post ID.
 * - slug: (optional) Override the default pattern by supplying a post slug.
 * If both id and slug are provided, Hubbub uses id.
 *
 * @since      10.4.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/general
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Hubbub_Action_Buttons extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-hubbub-action-buttons';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '',
				'type' => 'text',
				'name' => __( 'Pattern ID', 'wp-recipe-maker' ),
				'help' => __( 'Optional. Override the default Action Buttons pattern by supplying its post/pattern ID. Leave blank to use the pattern set in Hubbub.', 'wp-recipe-maker' ),
			),
			'slug' => array(
				'default' => '',
				'type' => 'text',
				'name' => __( 'Pattern slug', 'wp-recipe-maker' ),
				'help' => __( 'Optional. Override the default pattern by supplying the post slug of a pattern. Ignored when Pattern ID is set.', 'wp-recipe-maker' ),
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since 10.4.0
	 * @param array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		// Pass through to [hubbub_action_buttons]. When both id and slug are set, we pass both; Hubbub uses id.
		$hubbub_atts = array();
		$pattern_id = absint( $atts['id'] );
		if ( $pattern_id > 0 ) {
			$hubbub_atts['id'] = $pattern_id;
		}
		if ( '' !== $atts['slug'] ) {
			$hubbub_atts['slug'] = sanitize_title( $atts['slug'] );
		}

		// Construct shortcode.
		$hubbub_shortcode = '[hubbub_action_buttons';
		if ( ! empty( $hubbub_atts ) ) {
			foreach ( $hubbub_atts as $key => $value ) {
				$hubbub_shortcode .= ' ' . $key . '="' . esc_attr( $value ) . '"';
			}
		}
		$hubbub_shortcode .= ']';

		// Try to output.
		$output = do_shortcode( $hubbub_shortcode );

		// Maybe show message in template editor.
		if ( $atts['is_template_editor_preview'] ) {
			if ( $output === $hubbub_shortcode ) {
				$output = '<div class="wprm-template-editor-premium-only">' . __( 'Make sure the Hubbub plugin is installed and an Action Buttons pattern is set', 'wp-recipe-maker' ) . '</div>';
			} elseif ( '' === $output ) {
				$output = '<div class="wprm-template-editor-premium-only">' . __( 'Hubbub Action Buttons: set a pattern in Hubbub or use the id/slug attributes', 'wp-recipe-maker' ) . '</div>';
			}
		} else {
			// Do not output the shortcode itself when Hubbub is not active.
			if ( $output === $hubbub_shortcode ) {
				$output = '';
			}
		}

		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Hubbub_Action_Buttons::init();
