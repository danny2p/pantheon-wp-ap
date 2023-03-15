<?php
/**
 * Handle the recipe meta container shortcode.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 */

/**
 * Handle the recipe meta container shortcode.
 *
 * @since      6.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes/recipe
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_SC_Meta_Container extends WPRM_Template_Shortcode {
	public static $shortcode = 'wprm-recipe-meta-container';

	public static function init() {
		$atts = array(
			'id' => array(
				'default' => '0',
			),
			'fields' => array(
				'default' => 'tags',
				'type' => 'dropdown',
				'options' => array(
					'tags' => 'All Recipe Taxonomies',
					'times' => 'All Recipe Times',
					'custom' => 'Custom',
				),
			),
			'selected_fields' => array(
				'default' => '',
				'type' => 'text',
				'help' => 'Comma seperated list of fields to show. Author, cost, servings, recipe times, nutrients, custom fields and recipe taxonomies can all be used. For example: author, cost, prep_time, calories, my_custom_field, course, cuisine, my_recipe_taxonomy',
				'dependency' => array(
					'id' => 'fields',
					'value' => 'custom',
				),
			),
			'style' => array(
				'default' => 'separate',
				'type' => 'dropdown',
				'options' => array(
					'inline' => 'Inline',
					'separate' => 'On its own line',
					'separated' => 'On separate lines',
					'columns' => 'Columns',
					'table' => 'Table',
				),
			),
			'table_borders' => array(
				'default' => 'top-bottom',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'None',
					'all' => 'All around',
					'top-bottom' => 'Top and Bottom',
					'left-right' => 'Left and Right',
					'top' => 'Top',
					'bottom' => 'Bottom',
					'left' => 'Left',
					'right' => 'Right',
				),
				'dependency' => array(
					'id' => 'style',
					'value' => 'table',
				),
			),
			'table_borders_inside' => array(
				'default' => '1',
				'type' => 'toggle',
				'dependency' => array(
					array(
						'id' => 'style',
						'value' => 'table',
					),
					array(
						'id' => 'table_borders',
						'value' => 'none',
						'type' => 'inverse',
					),
				),
			),
			'table_border_width' => array(
				'default' => '1px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'style',
						'value' => 'table',
					),
					array(
						'id' => 'table_borders',
						'value' => 'none',
						'type' => 'inverse',
					),
				),
			),
			'table_border_style' => array(
				'default' => 'dotted',
				'type' => 'dropdown',
				'options' => 'border_styles',
				'dependency' => array(
					array(
						'id' => 'style',
						'value' => 'table',
					),
					array(
						'id' => 'table_borders',
						'value' => 'none',
						'type' => 'inverse',
					),
				),
			),
			'table_border_color' => array(
				'default' => '#666666',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'style',
						'value' => 'table',
					),
					array(
						'id' => 'table_borders',
						'value' => 'none',
						'type' => 'inverse',
					),
				),
			),
			'text_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
				'dependency' => array(
					'id' => 'container',
					'value' => '1',
				),
			),
			'tag_separator' => array(
				'default' => ', ',
				'type' => 'text',
				'dependency' => array(
					array(
						'id' => 'fields',
						'value' => 'tags',
					),
					// Custom dependencies set dynamically.
				),
				'dependency_compare' => 'OR',
			),
			'tag_display_style' => array(
				'default' => 'text',
				'type' => 'dropdown',
				'options' => array(
					'text' => 'Text',
					'images' => 'Images',
					'text_images' => 'Text with Images',
				),
				'dependency' => array(
					array(
						'id' => 'fields',
						'value' => 'tags',
					),
					// Custom dependencies set dynamically.
				),
				'dependency_compare' => 'OR',
			),
			'tag_image_tooltip' => array(
				'default' => 'none',
				'type' => 'dropdown',
				'options' => array(
					'none' => 'No Tooltip',
					'term' => 'Show term name',
					'title' => 'Show image title attribute',
					'caption' => 'Show image caption attribute',
					'description' => 'Show image description attribute',
				),
				'dependency' => array(
					'id' => 'tag_display_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'tag_image_size' => array(
				'default' => '30x30',
				'type' => 'image_size',
				'dependency' => array(
					'id' => 'tag_display_style',
					'value' => 'text',
					'type' => 'inverse',
				),
			),
			'tag_image_position' => array(
				'default' => 'left',
				'type' => 'dropdown',
				'options' => array(
					'left' => 'Left',
					'top' => 'Top',
					'right' => 'Right',
					'bottom' => 'Bottom',
				),
				'dependency' => array(
					'id' => 'tag_display_style',
					'value' => 'text_images',
				),
			),
			'time_shorthand' => array(
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(
					array(
						'id' => 'fields',
						'value' => 'times',
					),
					// Custom dependencies set dynamically.
				),
				'dependency_compare' => 'OR',
			),
			'nutrition_unit' => array(
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(), // Custom dependencies set dynamically.
				'dependency_compare' => 'OR',
			),
			'nutrition_unit_separator' => array(
				'default' => '',
				'type' => 'text',
				'dependency' => array(), // Custom dependencies set dynamically.
				'dependency_compare' => 'OR',
			),
			'nutrition_daily' => array(
				'default' => '0',
				'type' => 'toggle',
				'dependency' => array(), // Custom dependencies set dynamically.
				'dependency_compare' => 'OR',
			),
			'custom_field_image_size' => array(
				'default' => 'thumbnail',
				'type' => 'image_size',
				'dependency' => array(
					array(
						'id' => 'selected_fields',
						'value' => 'actual_values_set_in_parse_shortcode_below',
					),
				),
				'dependency_compare' => 'OR',
			),
			'servings_adjustable' => array (
				'default' => 'tooltip',
				'type' => 'dropdown',
				'options' => 'adjustable_servings',
				'dependency' => array(
					array(
						'id' => 'selected_fields',
						'value' => 'servings',
						'type' => 'includes',
					),
				),
			),
			'servings_button_background' => array(
				'default' => '#333333',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'selected_fields',
						'value' => 'servings',
						'type' => 'includes',
					),
					array(
						'id' => 'servings_adjustable',
						'value' => 'text-buttons',
					),
				),
			),
			'servings_button_accent' => array(
				'default' => '#ffffff',
				'type' => 'color',
				'dependency' => array(
					array(
						'id' => 'selected_fields',
						'value' => 'servings',
						'type' => 'includes',
					),
					array(
						'id' => 'servings_adjustable',
						'value' => 'text-buttons',
					),
				),
			),
			'servings_button_radius' => array(
				'default' => '3px',
				'type' => 'size',
				'dependency' => array(
					array(
						'id' => 'selected_fields',
						'value' => 'servings',
						'type' => 'includes',
					),
					array(
						'id' => 'servings_adjustable',
						'value' => 'text-buttons',
					),
				),
			),
			'icon' => array(
				'default' => '',
				'type' => 'icon',
			),
			'icon_color' => array(
				'default' => '#333333',
				'type' => 'color',
			),
			'label_separator' => array(
				'default' => ' ',
				'type' => 'text',
			),
			'label_style' => array(
				'default' => 'normal',
				'type' => 'dropdown',
				'options' => 'text_styles',
			),
			// Not actually shown in Template Editor. Only for backwards compatibility.
			'container' => array(
				'default' => '1',
			),
			// Label attributes get added dynamically.
		);

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
		if ( 'wprm-recipe-meta-container' === $shortcode ) {
			$fields_with_labels = array(
				'author' => __( 'Author', 'wp-recipe-maker' ),
				'cost' => __( 'Cost', 'wp-recipe-maker' ),
				'servings' => __( 'Servings', 'wp-recipe-maker' ),
			);

			// Add Times and set dependency for time specific attributes.
			$times = array(
				'prep_time' => __( 'Prep Time', 'wp-recipe-maker' ),
				'cook_time' => __( 'Cook Time', 'wp-recipe-maker' ),
				'custom_time' => __( 'Custom Time', 'wp-recipe-maker' ),
				'total_time' => __( 'Total Time', 'wp-recipe-maker' ),
			);
			foreach ( $times as $key => $label ) {
				$fields_with_labels[ $key ] = $label;
				
				$shortcodes[ $shortcode ][ 'time_shorthand' ]['dependency'][] = array(
					'id' => 'selected_fields',
					'value' => $key,
					'type' => 'includes',
				);
			}

			// Add Nutrients.
			$nutrition_fields = WPRM_Nutrition::get_fields();
			foreach ( $nutrition_fields as $nutrient => $options ) {
				$fields_with_labels[ $nutrient ] = $options['label'];

				$shortcodes[ $shortcode ][ 'nutrition_unit' ]['dependency'][] = array(
					'id' => 'selected_fields',
					'value' => $nutrient,
					'type' => 'includes',
				);
				$shortcodes[ $shortcode ][ 'nutrition_unit_separator' ]['dependency'][] = array(
					'id' => 'selected_fields',
					'value' => $nutrient,
					'type' => 'includes',
				);
				$shortcodes[ $shortcode ][ 'nutrition_daily' ]['dependency'][] = array(
					'id' => 'selected_fields',
					'value' => $nutrient,
					'type' => 'includes',
				);
			}

			// Add Custom Fields.
			$custom_fields = class_exists( 'WPRM_Addons' ) && WPRM_Addons::is_active( 'custom-fields' ) ? WPRMPCF_Manager::get_custom_fields() : array();
			foreach ( $custom_fields as $key => $custom_field ) {
				$fields_with_labels[ $key ] = $custom_field['name'];

				if ( 'image' === $custom_field['type'] ) {
					$shortcodes[ $shortcode ]['custom_field_image_size']['dependency'][] = array(
						'id' => 'selected_fields',
						'value' => $key,
						'type' => 'includes',
					);
				}
			}
			
			// Add Taxonomies and set dependency for time specific attributes.
			$taxonomies = WPRM_Taxonomies::get_taxonomies();
			foreach ( $taxonomies as $taxonomy => $options ) {
				$key = substr( $taxonomy, 5 );
				$fields_with_labels[ $key ] = $options['singular_name'];

				$shortcodes[ $shortcode ]['tag_separator']['dependency'][] = array(
					'id' => 'selected_fields',
					'value' => $key,
					'type' => 'includes',
				);
				$shortcodes[ $shortcode ]['tag_display_style']['dependency'][] = array(
					'id' => 'selected_fields',
					'value' => $key,
					'type' => 'includes',
				);

				// Add a second set for wprm_% so that it matches both wprm_course and course. But only if not set yet.
				$key = 'wprm_' . $key;
				if ( ! array_key_exists( $key, $fields_with_labels ) ) {
					$fields_with_labels[ $key ] = $options['singular_name'];

					$shortcodes[ $shortcode ]['tag_separator']['dependency'][] = array(
						'id' => 'selected_fields',
						'value' => $key,
						'type' => 'includes',
					);
					$shortcodes[ $shortcode ]['tag_display_style']['dependency'][] = array(
						'id' => 'selected_fields',
						'value' => $key,
						'type' => 'includes',
					);
				}
			}

			// Add label and icon attributes to shortcode.
			foreach ( $fields_with_labels as $key => $label ) {
				$dependency = array(
					array(
						'id' => 'selected_fields',
						'value' => $key,
						'type' => 'includes',
					),
				);

				// Special case when all times are displayed.
				if ( in_array( $key, array_keys( $times ) ) ) {
					$dependency[] = array(
						'id' => 'fields',
						'value' => 'times',
					);
				}

				// Special case when all tags are displayed.
				if ( in_array( 'wprm_' . $key, array_keys( $taxonomies ) ) ) {
					$dependency[] = array(
						'id' => 'fields',
						'value' => 'tags',
					);
				}

				// Add Label and Icon attributes.
				if ( 'custom_time' !== $key ) { // Special case, doesn't have a label.
					$shortcodes[ $shortcode ]['label_' . $key] = array(
						'default' => $label,
						'type' => 'text',
						'dependency' => $dependency,
						'dependency_compare' => 'OR',
					);
				}
				$shortcodes[ $shortcode ]['icon_' . $key] = array(
					'default' => '',
					'type' => 'icon',
					'dependency' => $dependency,
					'dependency_compare' => 'OR',
				);
			}
		}

		return $shortcodes;
	}

	/**
	 * Output for the shortcode.
	 *
	 * @since	6.0.0
	 * @param	array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = parent::get_attributes( $atts );

		$recipe = WPRM_Template_Shortcodes::get_recipe( $atts['id'] );
		if ( ! $recipe ) {
			return '';
		}

		// Get all possible fields (but only when needed).
		$times = array( 'prep_time', 'cook_time', 'custom_time', 'total_time' );
		$taxonomies = 'times' === $atts['fields'] ? array() : WPRM_Taxonomies::get_taxonomies();

		if ( 'custom' === $atts['fields'] ) {
			$custom_fields = class_exists( 'WPRM_Addons' ) && WPRM_Addons::is_active( 'custom-fields' ) ? WPRMPCF_Manager::get_custom_fields() : array();
			$nutrition_fields = WPRM_Nutrition::get_fields();
		} else {
			$custom_fields = array();
			$nutrition_fields = array();
		}

		// Get all meta fields to output.
		$fields = array();

		switch ( $atts['fields'] ) {
			case 'times':
				$fields = $times;
				break;
			case 'tags':
				$taxonomies = WPRM_Taxonomies::get_taxonomies();
				foreach ( $taxonomies as $taxonomy => $options ) {
					$key = substr( $taxonomy, 5 );
		
					// Hide keywords from template setting.
					if ( 'keyword' === $key && ! WPRM_Settings::get( 'metadata_keywords_in_template' ) ) {
						continue;
					}

					$fields[] = $key;
				}
				break;
			case 'custom':
				$fields = explode( ',', str_replace( ';', ',', $atts['selected_fields'] ) );
				break;
		}

		// Loop over all fields to output.
		$fields_output = array();

		foreach ( $fields as $field ) {
			$field = strtolower( trim( $field ) );

			$field_shortcode = false;
			$field_atts = $atts;
			$field_atts['label_container'] = '1';
			$field_atts['label'] = isset( $atts['label_' . $field ] ) ? $atts['label_' . $field ] : '';
			$field_atts['icon'] = isset( $atts['icon_' . $field] ) && $atts['icon_' . $field] ? $atts['icon_' . $field] : $atts['icon'];

			if ( 'author' === $field ) {
				$field_shortcode = 'WPRM_SC_Author';
			} elseif ( 'cost' === $field ) {
				$field_shortcode = 'WPRM_SC_Cost';
			} elseif ( 'servings' === $field ) {
				$field_shortcode = 'WPRM_SC_Servings';
				$field_atts['adjustable'] = $atts['servings_adjustable'];
				$field_atts['button_background'] = $atts['servings_button_background'];
				$field_atts['button_accent'] = $atts['servings_button_accent'];
				$field_atts['button_radius'] = $atts['servings_button_radius'];
			} elseif ( in_array( $field, $times ) ) {
				$field_shortcode = 'WPRM_SC_Time';
				$field_atts['type'] = substr( $field, 0, strlen( $field ) - 5 );
				$field_atts['shorthand'] = $atts['time_shorthand'];
			} elseif ( in_array( 'wprm_' . $field, array_keys( $taxonomies ) ) ) {
				$field_shortcode = 'WPRM_SC_Tag';
				$field_atts['key'] = $field;
				$field_atts['separator'] = $atts['tag_separator'];
				$field_atts['display_style'] = $atts['tag_display_style'];
				$field_atts['image_tooltip'] = $atts['tag_image_tooltip'];
				$field_atts['image_size'] = $atts['tag_image_size'];
				$field_atts['image_position'] = $atts['tag_image_position'];
			} elseif ( in_array( $field, array_keys( $nutrition_fields ) ) ) {
				$field_shortcode = 'WPRM_SC_Nutrition';
				$field_atts['field'] = $field;
				$field_atts['unit'] = $atts['nutrition_unit'];
				$field_atts['unit_separator'] = $atts['nutrition_unit_separator'];
				$field_atts['daily'] = $atts['nutrition_daily'];
			} elseif ( in_array( $field, array_keys( $custom_fields ) ) ) {
				$field_shortcode = 'WPRM_SC_Custom_Field';
				$field_atts['key'] = $field;
				$field_atts['image_size'] = $atts['custom_field_image_size'];
			} elseif ( in_array( $field, array_keys( $taxonomies ) ) ) {
				// Added afterwards to make sure both wprm_course and course match, without accidentally interfering with existing custom fields.
				$field_shortcode = 'WPRM_SC_Tag';
				$field_atts['key'] = substr( $field, 5 );
				$field_atts['separator'] = $atts['tag_separator'];
				$field_atts['display_style'] = $atts['tag_display_style'];
				$field_atts['image_tooltip'] = $atts['tag_image_tooltip'];
				$field_atts['image_size'] = $atts['tag_image_size'];
				$field_atts['image_position'] = $atts['tag_image_position'];
			}

			if ( $field_shortcode && class_exists( $field_shortcode ) ) {
				$field_output = call_user_func( array( $field_shortcode, 'shortcode' ), $field_atts );

				if ( $field_output ) {
					$fields_output[] = $field_output;
				}
			}
		}

		if ( ! $fields_output ) {
			return '';
		}

		$show_container = (bool) $atts['container'];

		// Border style.
		$style = '';
		if ( 'table' === $atts['style'] ) {
			if ( 'none' === $atts['table_borders'] ) {
				$atts['table_border_width'] = 0;
			}

			$style .= 'border-width: ' . $atts['table_border_width'] . ';';
			$style .= 'border-style: ' . $atts['table_border_style'] . ';';
			$style .= 'border-color: ' . $atts['table_border_color'] . ';';
		}

		// Output.
		$classes = array(
			'wprm-recipe-meta-container',
			'wprm-recipe-' . $atts['fields'] . '-container',
			'wprm-recipe-details-container',
			'wprm-recipe-details-container-' . $atts['style'],
			'wprm-block-text-' . $atts['text_style'],
		);

		// Add custom class if set.
		if ( $atts['class'] ) { $classes[] = esc_attr( $atts['class'] ); }

		if ( 'table' === $atts['style'] ) {
			$classes[] = 'wprm-recipe-table-borders-' . $atts['table_borders'];

			if ( (bool) $atts['table_borders_inside'] ) {
				$classes[] = 'wprm-recipe-table-borders-inside';
			} else {
				$classes[] = 'wprm-recipe-table-borders-empty';
			}
		}

		$output = $show_container ? '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">' : '';

		foreach ( $fields_output as $field_output ) {
			$output .= $field_output;
		}

		if ( $show_container ) {
			$output .= '</div>';
		}

		return apply_filters( parent::get_hook(), $output, $atts, $recipe );
	}
}

WPRM_SC_Meta_Container::init();