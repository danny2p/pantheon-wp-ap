<?php
/**
 * Handle the QR Code shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the QR Code shortcode.
 *
 * @since      9.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Qr_Code extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-qr-code';

	public static function init() {
		self::$attributes = array(
			'id' => array(
				'default' => '0',
			),
			'type' => array(
				'default' => 'recipe',
				'type' => 'dropdown',
				'options' => array(
					'recipe' => 'Link to Recipe',
					'video' => 'Link to Recipe Video',
					'link' => 'Custom Link',
					'text' => 'Custom Text',
				),
			),
			'link' => array(
				'default' => '',
				'type' => 'text',
				'dependency' => array(
					'id' => 'type',
					'value' => 'link',
				),
			),
			'link_target' => array(
				'default' => '_blank',
				'type' => 'dropdown',
				'options' => array(
					'_self' => 'Open in same tab',
					'_blank' => 'Open in new tab',
				),
				'dependency' => array(
					'id' => 'type',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'text' => array(
				'default' => '',
				'type' => 'text',
				'dependency' => array(
					'id' => 'type',
					'value' => 'text',
				),
			),
		);
		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	9.6.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		// Maybe get recipe.
		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );

		$type = $atts['type'];
		$text = 'link' === $type ? trim( $atts['link'] ) : trim( $atts['text'] );

		// Output.
		$classes = array(
			'wprm-qr-code',
			'wprm-qr-code-' . esc_attr( $type ),
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		// Data for QR code.
		$data = false;
		switch ( $type ) {
			case 'recipe':
				if ( $recipe ) {
					$data = $recipe->permalink();
				}
				break;
			case 'video':
				if ( $recipe ) {
					$embed_code = $recipe->video_embed();

					// Check if it's a regular URL.
					$url = filter_var( $embed_code, FILTER_SANITIZE_URL );
					if ( $url ) {
						$data = $url;
					}
				}
				break;
			default:
				if ( $recipe ) {
					$text = $recipe->replace_placeholders( $text );
				}

				$data = $text;
		}

		if ( ! $data ) {
			return apply_filters( parent::get_hook(), '', $atts );
		}

		// Generate QR code.
		require_once( WPRM_DIR . 'vendor/phpqrcode/phpqrcode.php' );
		$img = QRCode::png( $data );
 
		$alt = 'text' === $type ? $text : __( 'QR Code', 'wp-recipe-maker' );
		$qr_code = '<img src="data:image/jpg;base64,' . base64_encode( $img ) . '" alt="' . esc_attr( $alt ) . '" data-pin-nopin="true" />';

		if ( 'text' !== $type ) {
			$qr_code = '<a href="' . esc_url( $data ) . '" target="' . esc_attr( $atts['link_target'] ) . '">' . $qr_code . '</a>';
		}


		$output = '<span class="' . esc_attr( implode( ' ', $classes ) ). '">' . $qr_code . '</span>';
		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Qr_Code::init();