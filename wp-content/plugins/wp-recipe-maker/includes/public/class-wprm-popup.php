<?php
/**
 * Handler for any front-end popup modals.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handler for any front-end popup modals.
 *
 * @since      9.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Popup {

	private static $modals = array();

	/**
	 * Register actions and filters.
	 *
	 * @since    9.2.0
	 */
	public static function init() {
		add_action( 'wp_footer', array( __CLASS__, 'output_html_for_all_modals' ), 99 );
	}

	/**
	 * Add a modal that needs to get output.
	 *
	 * @since    9.2.0
	 */
	public static function add( $args ) {
		if ( isset( $args['type'] ) && isset( $args['reuse'] ) && true === $args['reuse'] ) {
			$uid = $args['type']; // Only create modal once and reuse for this type.
		} else {
			$uid = count( self::$modals ) + 1; // Don't use 0 to prevent falsey checks.
		}
		self::$modals[ $uid ] = $args;

		return $uid;
	}

	/**
	 * Output HTML needed for modals.
	 *
	 * @since    9.2.0
	 */
	public static function output_html_for_all_modals() {
		foreach ( self::$modals as $uid => $modal ) {
			// Set variables.
			$id = 'wprm-popup-modal-' . $uid;
			$type = isset( $modal['type'] ) ? $modal['type'] : 'default';

			$classes = array(
				'wprm-popup-modal',
				'wprm-popup-modal-' . $type,
			);

			// Set entire container.
			$container = isset( $modal['container'] ) ? $modal['container'] : false;

			// Or set individual parts.
			$title = isset( $modal['title'] ) ? $modal['title'] : '';
			$content = isset( $modal['content'] ) ? $modal['content'] : '';
			$buttons = isset( $modal['buttons'] ) && is_array( $modal['buttons'] ) ? $modal['buttons'] : array();

			// Allow overriding the template and output.
			$template = apply_filters( 'wprm_template_popup_modal', WPRM_DIR . 'templates/public/popup-modal.php' );
			include( $template );
		}
	}

}

WPRM_Popup::init();
