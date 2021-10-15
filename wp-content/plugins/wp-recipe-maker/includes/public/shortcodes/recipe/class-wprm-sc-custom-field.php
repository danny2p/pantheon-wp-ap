<?php
/**
 * Handle the recipe custom field shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe custom field shortcode.
 *
 * @since      5.2.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Custom_Field extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-custom-field';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'key' => array(
				'default' => '',
				'type' => 'dropdown',
				'options' => 'custom_fields',
			),
			'image_size' => array(
				'default' => 'thumbnail',
				'type' => 'image_size',
				'dependency' => array(
					array(
						'id' => 'key',
						'value' => 'actual_values_set_in_parse_shortcode_below',
					),
				),
				'dependency_compare' => 'OR',
			),
		);

		$atts = array_merge( WPRM_Shortcode_Helper::get_section_atts(), $atts );
		$atts = array_merge( $atts, WPRM_Shortcode_Helper::get_label_container_atts() );
		self::$attributes = $atts;

		add_filter( 'wprm_template_parse_shortcode', array( __CLASS__, 'parse_shortcode' ), 10, 3 );

		parent::init();
	}

	/**
	 * Add dynamic shortcode attributes.
	 *
	 * @since	6.0.0
	 * @param	array $shortcodes 	All shortcodes to parse.
	 * @param	array $shortcode 	Shortcode getting parsed.
	 * @param	array $atts 		Shortcode attributes.
	 */
	public static function parse_shortcode( $shortcodes, $shortcode, $attributes ) {
		if ( 'wprm-recipe-custom-field' === $shortcode ) {
			$custom_fields = class_exists( 'WPRM_Addons' ) && WPRM_Addons::is_active( 'custom-fields' ) ? WPRMPCF_Manager::get_custom_fields() : array();
			
			foreach ( $custom_fields as $key => $custom_field ) {
				if ( 'image' === $custom_field['type'] ) {
					$shortcodes[ $shortcode ]['image_size']['dependency'][] = array(
						'id' => 'key',
						'value' => $key,
					);
				}
			}
		}

		return $shortcodes;
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	5.2.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$output = '';
		// Show teaser for Premium only shortcode in Template editor.
		if ( $atts['is_template_editor_preview'] ) {
			$output = '<div class="wprm-template-editor-premium-only">Custom Fields are only available in the <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/">WP Recipe Maker Pro Bundle</a>.</div>';
		}

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Custom_Field::init();