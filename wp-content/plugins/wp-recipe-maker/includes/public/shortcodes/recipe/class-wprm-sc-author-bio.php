<?php
/**
 * Handle the recipe author bio shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.6.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe author bio shortcode.
 *
 * @since      9.6.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Author_Bio extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-author-bio';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'section_header' => array(
				'type' => 'header',
				'default' => __( 'Header', 'wp-recipe-maker' ),
			),
			'bio_header' => array(
				'type' => 'header',
				'default' => __( 'Author Bio', 'wp-recipe-maker' ),
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			'tag' => array(
				'default' => 'p',
				'type' => 'dropdown',
				'options' => array(
					'p' => 'p',
					'span' => 'span',
					'div' => 'div',
				),
			),
			'align' => array(
				'default' => 'left',
				'type' => 'dropdown',
				'options' => array(
					'left' => 'Left',
					'center' => 'Center',
					'right' => 'Right',
				),
				'dependency' => array(
                    array(
                        'id' => 'tag',
                        'value' => 'span',
                        'type' => 'inverse',
					),
				),
			),
		);

		$atts = WPRM_Shortcode_Helper::insert_atts_after_key( $atts, 'section_header', WPRM_Shortcode_Helper::get_section_atts() );
		self::$attributes = $atts;

		parent::init();
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	3.2.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe ) {
			return apply_filters( parent::get_hook(), '', $atts );
		}

		// Get author bio based on author display option.
		$bio = false;
		$author_display = $recipe->author_display();

		switch ( $author_display ) {
			case 'post_author':
				$author = $recipe->post_author();
				if ( $author ) {
					$bio = get_the_author_meta( 'description', $author );
				}
				break;
			case 'custom':
				$bio = $recipe->custom_author_bio();
				break;
			case 'same':
				$bio = WPRM_Settings::get( 'recipe_author_same_bio' );
				break;
		}

		if ( ! $bio ) {
			return apply_filters( parent::get_hook(), '', $atts );
		}

		// Output.
		$classes = array(
			'wprm-recipe-details',
			'wprm-recipe-author-bio',
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		$output = '';
		$tag = WPRM_Shortcode_Helper::sanitize_html_element( $atts['tag'] );

		// Alignment.
		if ( 'span' !== $tag && 'left' !== $atts['align'] ) {
			$classes[] = 'wprm-align-' . esc_attr( $atts['align'] );
		}

		// Optional header.
		$header = WPRM_Shortcode_Helper::get_section_header( $atts, 'author-bio' );

		if ( $header ) {
			$output .= '<div class="wprm-recipe-author-bio-container">';
			$output .= $header;
		}

		$output .= '<' . $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '">' . WPRM_Shortcode_Helper::sanitize_html( $bio ) . '</' . $tag . '>';

		if ( $header ) {
			$output .= '</div>';
		}

		return apply_filters( parent::get_hook(), $output, $atts );
	}
}

WPRM_SC_Author_Bio::init();