<?php
/**
 * Collapsible button component for WPRM.
 *
 * @package WPRM
 */

class WPRM_Shortcode_Reusable_Collapsible_Button {
	/**
	 * Get the collapsible button HTML.
	 *
	 * @param string $icon_collapsed_id Icon ID for collapsed state.
	 * @param string $icon_expanded_id Icon ID for expanded state.
	 * @param string $icon_color Icon color.
	 * @return string Collapsible button HTML.
	 */
	public static function get_html( $icon_collapsed_id, $icon_expanded_id, $icon_color, $type ) {
		$icon_collapsed = '';
		if ( $icon_collapsed_id ) {
			$icon_collapsed = WPRM_Icon::get( $icon_collapsed_id, $icon_color );

			if ( $icon_collapsed ) {
				$icon_collapsed = '<span class="wprm-recipe-icon wprm-collapsible-icon">' . $icon_collapsed . '</span> ';
			}
		}

		$icon_expanded = '';
		if ( $icon_expanded_id ) {
			$icon_expanded = WPRM_Icon::get( $icon_expanded_id, $icon_color );

			if ( $icon_expanded ) {
				$icon_expanded = '<span class="wprm-recipe-icon wprm-collapsible-icon">' . $icon_expanded . '</span> ';
			}
		}

		if ( $icon_collapsed && $icon_expanded ) {
			$button_style = '';
			if ( '#333333' !== $icon_color ) {
				$button_style = ' style="color: ' . esc_attr( $icon_color ) . '"';
			}

			$collapsible_output = '<div class="wprm-' . esc_attr( $type ) . '-toggle">';
			$collapsible_output .= '<a role="button" aria-expanded="false" class="wprm-expandable-button wprm-expandable-button-show"' . $button_style . ' aria-label="' . esc_attr__( 'Show Section', 'wp-recipe-maker' ) . '">' . $icon_collapsed . '</a>';
			$collapsible_output .= '<a role="button" aria-expanded="true" class="wprm-expandable-button wprm-expandable-button-hide"' . $button_style . ' aria-label="' . esc_attr__( 'Hide Section', 'wp-recipe-maker' ) . '">' . $icon_expanded . '</a>';
			$collapsible_output .= '</div>';

			return $collapsible_output;
		}

		return '';
	}
}
