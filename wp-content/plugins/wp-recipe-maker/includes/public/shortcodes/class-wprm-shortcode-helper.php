<?php
/**
 * Providing helper functions to use in the recipe shortcodes.
 *
 * @link       http://bootstrapped.ventures
 * @since      6.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes
 */

// Include reusable components.
require_once( WPRM_DIR . 'includes/public/shortcodes/reusable/class-wprm-shortcode-reusable-advanced-list.php' );
require_once( WPRM_DIR . 'includes/public/shortcodes/reusable/class-wprm-shortcode-reusable-label-container.php' );
require_once( WPRM_DIR . 'includes/public/shortcodes/reusable/class-wprm-shortcode-reusable-section-header.php' );
require_once( WPRM_DIR . 'includes/public/shortcodes/reusable/class-wprm-shortcode-reusable-checkbox.php' );
require_once( WPRM_DIR . 'includes/public/shortcodes/reusable/class-wprm-shortcode-reusable-collapsible-button.php' );
require_once( WPRM_DIR . 'includes/public/shortcodes/reusable/class-wprm-shortcode-reusable-interactivity-container.php' );
require_once( WPRM_DIR . 'includes/public/shortcodes/reusable/class-wprm-shortcode-reusable-internal-container.php' );
require_once( WPRM_DIR . 'includes/public/shortcodes/reusable/class-wprm-shortcode-reusable-toggle-switch.php' );

/**
 * Providing helper functions to use in the recipe shortcodes.
 *
 * @since      6.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public/shortcodes
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Shortcode_Helper {
	/**
	 * Insert an array of attributes after a specific key.
	 *
	 * @since	10.0.0
	 * @param	array $atts Attributes to insert into.
	 * @param	string $key Key to insert after.
	 * @param	array $atts_to_add Attributes to insert.
	 */
	public static function insert_atts_after_key( $atts, $key, $atts_to_add ) {
		$key_position = array_search( $key, array_keys( $atts ) );

		if ( false === $key_position ) {
			return $atts;
		}

		$new_atts = array_merge( array_slice( $atts, 0, $key_position + 1 ), $atts_to_add );
		$new_atts = array_merge( $new_atts, array_slice( $atts, $key_position + 1 ) );

		return $new_atts;
	}

    /**
	 * Get attributes for the label container.
	 *
	 * @since	6.0.0
	 */
	public static function get_label_container_atts() {
		return WPRM_Shortcode_Reusable_Label_Container::get_atts();
    }

    /**
	 * Get label container.
	 *
	 * @since	6.0.0
	 * @param	mixed $atts Attributes for the shortcode.
	 * @param	string $fields Field to get the container for.
     * @param	string $content Content to put inside the container.
	 */
	public static function get_label_container( $atts, $fields, $content ) {
		return WPRM_Shortcode_Reusable_Label_Container::get_container( $atts, $fields, $content );
	}

	/**
	 * Get attributes for a section.
	 *
	 * @since	6.0.0
	 */
	public static function get_section_atts() {
		return WPRM_Shortcode_Reusable_Section_Header::get_atts();
	}
	
	/**
	 * Get section header to output.
	 *
	 * @since	6.0.0
	 * @param	mixed $atts Attributes for the shortcode.
	 * @param	string $field Field to get the header for.
	 * @param	string $args Optional arguments.
	 */
	public static function get_section_header( $atts, $field, $args = array() ) {
		return WPRM_Shortcode_Reusable_Section_Header::get_header( $atts, $field, $args );
	}

	/**
	 * Get attributes for the advanced list style.
	 *
	 * @since	10.1.0
	 */
	public static function get_advanced_list_atts() {
		return WPRM_Shortcode_Reusable_Advanced_List::get_atts();
	}

	/**
	 * Get attributes for list checkboxes.
	 *
	 * @since	10.0.0
	 */
	public static function get_checkbox_atts() {
		return WPRM_Shortcode_Reusable_Checkbox::get_atts();
	}

	/**
	 * Get attributes for an interactivity container.
	 *
	 * @since	10.1.0
	 */
	public static function get_interactivity_container_atts() {
		return WPRM_Shortcode_Reusable_Interactivity_Container::get_atts();
	}
	
	/**
	 * Get interactivity container to output.
	 *
	 * @since	10.1.0
	 * @param	mixed $atts Attributes for the shortcode.
	 * @param	string $field Field to get the container for.
	 */
	public static function get_interactivity_container( $atts, $field ) {
		return WPRM_Shortcode_Reusable_Interactivity_Container::get_container( $atts, $field );
	}

	/**
	 * Get attributes for an internal container.
	 *
	 * @since	10.0.0
	 */
	public static function get_internal_container_atts() {
		return WPRM_Shortcode_Reusable_Internal_Container::get_atts();
	}
	
	/**
	 * Get internal container to output.
	 *
	 * @since	10.0.0
	 * @param	mixed $atts Attributes for the shortcode.
	 * @param	string $field Field to get the container for.
	 */
	public static function get_internal_container( $atts, $field ) {
		return WPRM_Shortcode_Reusable_Internal_Container::get_container( $atts, $field );
	}

	/**
	 * Get toggle switch to output.
	 *
	 * @since	7.0.0
	 * @param	mixed $atts 	Attributes for the shortcode.
	 * @param	string $args	Arguments for the output.
	 */
	public static function get_toggle_switch( $atts, $args ) {
		return WPRM_Shortcode_Reusable_Toggle_Switch::get_switch( $atts, $args );
	}

	/**
	 * Get attributes for a toggle switch.
	 *
	 * @since	7.0.0
	 */
	public static function get_toggle_switch_atts() {
		return WPRM_Shortcode_Reusable_Toggle_Switch::get_atts();
	}

	/**
	 * Sanitize HTML in shortcode for output.
	 *
	 * @since	8.6.0
	 */
	public static function sanitize_html( $text ) {
		if ( $text ) {
			$text = str_replace( '&quot;', '"', $text );
			$text = wp_kses_post( $text );
		}

		return $text;
	}

	/**
	 * Sanitize HTML element in shortcode for output.
	 *
	 * @since	9.2.0
	 */
	public static function sanitize_html_element( $tag ) {
		$allowed = array(
			'p' => 'p',
			'span' => 'span',
			'div' => 'div',
			'h1' => 'h1',
			'h2' => 'h2',
			'h3' => 'h3',
			'h4' => 'h4',
			'h5' => 'h5',
			'h6' => 'h6',
		);

		if ( ! isset( $allowed[ $tag ] ) ) {
			$tag = 'span';
		}

		return $tag;
	}

	/**
	 * Add inline style to image.
	 *
	 * @since	9.1.0
	 */
	public static function add_inline_style( $html, $style, $tag = 'img' ) {
		if ( $html && $style ) {
			$start_pos = stripos( $html, '<' . $tag );

			if ( false !== $start_pos ) {
				$end_pos = stripos( $html, '>', $start_pos );

				if ( false !== $end_pos ) {
					$element = substr( $html, $start_pos, $end_pos - $start_pos + 1 );

					if ( false !== stripos( $element, ' style="' ) ) {
						$element = preg_replace( '/ style="(.*?);?"/i', ' style="$1;wprm_new_style_placeholder"', $element );
						$element = str_replace( 'wprm_new_style_placeholder', esc_attr( $style ), $element );
					} else {
						$element = str_ireplace( '<' . $tag, '<' . $tag . ' style="' . esc_attr( $style ) . '"', $element );
					}

					$html = substr_replace( $html, $element, $start_pos, $end_pos - $start_pos + 1 );
				}
			}
		}

		return $html;
	}

	/**
	 * Get style to force image size.
	 *
	 * @since	9.1.0
	 */
	public static function get_force_image_size_style( $size ) {
		$style = '';

		$style .= 'width: ' . $size[0] . 'px;';
		$style .= 'max-width: 100%;';
		$style .= 'height: ' . $size[1] . 'px;';
		$style .= 'object-fit: cover;';

		return $style;
	}

	/**
	 * Get the thumbnail size they need to get.
	 *
	 * @since	9.2.0
	 */
	public static function get_thumbnail_image_size( $image_id, $size, $force_size ) {
		$thumbnail_size = $size;

		if ( is_array( $size ) && $size[0] && $size[1] && $force_size ) {
			$ratio = $size[0] / $size[1];

			$original_image = wp_get_attachment_image_src( $image_id, 'full' );

			if ( $original_image && $original_image[1] && $original_image[2] ) {
				$original_image_ratio = $original_image[1] / $original_image[2];

				if ( $ratio > $original_image_ratio ) {
					// Need at least the full width.
					$thumbnail_size = array( $size[0], 999999 );
				} else {
					// Need at least the full height.
					$thumbnail_size = array( 999999, $size[1] );
				}
			}
		}

		return $thumbnail_size;
	}

	/**
	 * Get inline style.
	 *
	 * @since	10.0.0
	 * @param string $inline_css CSS to add to the style attribute.
	 */
	public static function get_inline_style( $inline_css ) {
		$style = '';

		// TODO Add setting.
		if ( true ) {
			if ( $inline_css ) {
				$style = ' style="' . esc_attr( $inline_css ) . '"';
			}
		}

		return $style;
	}

	/**
	 * Get inline CSS variables to set.
	 *
	 * @since	10.0.0
	 * @param string $prefix 			Prefix for the variables.
	 * @param array  $atts   			Attributes for the shortcode.
	 * @param array  $defaults 			Default values for the attributes.
	 * @param array  $keys   			Keys to include in the variables.
	 */
	public static function get_inline_css_variables( $prefix, $atts, $defaults, $keys ) {
		$output = '';

		foreach ( $keys as $key ) {
			if ( isset( $atts[ $key ] ) ) {
				$value = $atts[ $key ];
				$default = isset( $defaults[ $key ] ) ? $defaults[ $key ] : null;

				if ( is_null( $default ) || $value !== $default ) {
					$output .= self::get_inline_css_variable_output( $prefix, $key, $value );
				}
			}
		}

		return $output;
	}

	public static function test_inline_css_variables( $prefix, $defaults, $keys ) {
		foreach ( $keys as $key ) {
			$default = isset( $defaults[ $key ] ) ? $defaults[ $key ] : 'DEFAULT NOT FOUND';

			$output = self::get_inline_css_variable_output( $prefix, $key, $default );
			echo $output . '<br/>';
		}
		die();
	}

	public static function get_inline_css_variable_output( $prefix, $key, $value ) {
		$variable_key = strtolower( str_replace( '_', '-', $key ) );

		// Prevent border-width styling problem caused by WordPress.
		$variable_key = str_replace( 'border-width', 'borderwidth', $variable_key );
		$variable_key = str_replace( 'border-top-width', 'border-topwidth', $variable_key );
		$variable_key = str_replace( 'border-right-width', 'border-rightwidth', $variable_key );
		$variable_key = str_replace( 'border-bottom-width', 'border-bottomwidth', $variable_key );
		$variable_key = str_replace( 'border-left-width', 'border-leftwidth', $variable_key );
		$variable_key = str_replace( 'border-top-color', 'border-topcolor', $variable_key );
		$variable_key = str_replace( 'border-right-color', 'border-rightcolor', $variable_key );
		$variable_key = str_replace( 'border-bottom-color', 'border-bottomcolor', $variable_key );
		$variable_key = str_replace( 'border-left-color', 'border-leftcolor', $variable_key );

		$variable = '--wprm-' . $prefix . '-' . $variable_key;
		return $variable . ': ' . $value . ';';
	}
}
