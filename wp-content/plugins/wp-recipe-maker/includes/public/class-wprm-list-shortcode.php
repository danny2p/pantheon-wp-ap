<?php
/**
 * Responsible for the list shortcode.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the list shortcode.
 *
 * @since      9.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_List_Shortcode {

	/**
	 * Register actions and filters.
	 *
	 * @since    9.0.0
	 */
	public static function init() {
		add_shortcode( 'wprm-list', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Output for the list shortcode.
	 *
	 * @since    9.0.0
	 * @param    array $atts Options passed along with the shortcode.
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => false,
				'align' => '',
			),
			$atts,
			'wprm_list'
		);

		$output = '';
		$list_id = intval( $atts['id'] );
		$list = WPRM_List_Manager::get_list( $list_id );

		if ( $list ) {
			$internal_post_ids = array();
			$items = $list->items();

			foreach ( $items as $item ) {
				if ( 'roundup' === $item['type'] ) {
					$data = $item['data'];

					// Get ID for metadata if internal recipe.
					if ( 'internal' === $data['type'] || 'post' === $data['type'] ) {
						$internal_post_ids[] = $data['id'];
					}

					// Set template for roundup item.
					if ( 'default' !== $list->template() ) {
						$data['template'] = $list->template();
					}

					// Generate shortcode.
					$shortcode = '[wprm-recipe-roundup-item ';
					foreach ( $data as $key => $value ) {
						$shortcode .= $key . '="' . self::clean_up_shortcode_attribute( $value ) . '" ';
					}
					$shortcode = trim( $shortcode ) . ']';

					$output .= $shortcode;
				}

				if ( 'text' === $item['type'] ) {					
					$output .= do_shortcode( $item['data']['text'] );
				}
			}

			// Maybe output itemList metadata.
			$metadata = '';
			if ( is_singular() && ( ! WPRM_Metadata::has_outputted_metadata() || false === WPRM_Settings::get( 'recipe_roundup_no_metadata_when_recipe' ) ) ) {
				$internal_post_ids = array_unique( $internal_post_ids );

				if ( 1 < count( $internal_post_ids ) && $list->metadata_output() ) {
					$url = get_permalink( get_the_ID() );
					$name = $list->metadata_name();
					$description = $list->metadata_description();

					ob_start();
					WPRM_Recipe_Roundup::output_itemlist_metadata( $url, $name, $description, $internal_post_ids );
					$metadata = ob_get_contents();
					ob_end_clean();
				}
			}

			// Optional align class.
			$align_class = '';
			if ( isset( $atts['align'] ) && $atts['align'] ) {
				$align_class = ' align' . esc_attr( $atts['align'] );
			}

			// Output for list.
			$output = '<div id="wprm-list-' . esc_attr( $atts['id'] ) . '" class="wprm-list' . esc_attr( $align_class ) . '">' . $metadata . do_shortcode( $output ) . '</div>';
		}

		return $output;
	}

	/**
	 * Clean up values to use in a shortcode attribute..
	 *
	 * @since    9.0.0
	 * @param    array $value Value to clean up.
	 */
	public static function clean_up_shortcode_attribute( $value ) {
		$value = preg_replace('/"/', '%22', $value);
		$value = preg_replace('/\x5B/', '%5B', $value); // \x5B is the hex code for '['
		$value = preg_replace('/\x5D/', '%5D', $value); // \x5D is the hex code for ']'
		$value = preg_replace('/\r?\n|\r/', '%0A', $value);

		return $value;
	}
}

WPRM_List_Shortcode::init();
