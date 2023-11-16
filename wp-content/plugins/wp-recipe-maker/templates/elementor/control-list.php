<?php
/**
 * Elementor Modal Controll.
 *
 * Elementor control for inserting WP Recipe Maker recipes.
 *
 * @since 9.0.0
 */
class WPRM_Elementor_Control_List extends \Elementor\Base_Data_Control {

	public function get_type() {
		return 'wprm-list-select';
	}

	public function enqueue() {
		// Let other control handle this.
	}

	public function get_default_value() {
		return false;
	}

	public function get_value( $control, $settings ) {
		return 6;
	}

	public function content_template() {
		?>
		<div id="wprm-list-select-placeholder"></div>
		<?php
	}
}