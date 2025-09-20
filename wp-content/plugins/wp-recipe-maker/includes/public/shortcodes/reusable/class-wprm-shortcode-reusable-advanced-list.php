<?php
/**
 * Reusable advanced list component for shortcodes.
 *
 * @link       http://bootstrapped.ventures
 * @since      10.1.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 */

/**
 * Reusable advanced list component for shortcodes.
 *
 * @since      10.1.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/reusable
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Reusable_Advanced_List {

	/**
	 * Get attributes for list checkboxes.
	 *
	 * @since	10.0.0
	 */
	public static function get_atts() {
		return array(
			'advanced_list_style_header' => array(
				'type' => 'header',
				'default' => __( 'Advanced List Style', 'wp-recipe-maker' ),
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'list_style_continue_numbers' => array(
				'name' => 'Continue Numbers',
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'list_style_top_position' => array(
				'name' => 'Top Position',
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'list_style_left_position' => array(
				'name' => 'Left Position',
				'default' => '0px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'list_style_background' => array(
				'name' => 'Background',
				'default' => '#444444',
				'type' => 'color',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'list_style_size' => array(
				'name' => 'Size',
				'default' => '18px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'list_style_text' => array(
				'name' => 'Text',
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
			'list_style_text_size' => array(
				'name' => 'Text Size',
				'default' => '12px',
				'type' => 'size',
				'dependency' => array(
					'id' => 'list_style',
					'value' => 'advanced',
				),
			),
		);
	}
}
