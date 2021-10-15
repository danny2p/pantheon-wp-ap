<?php
/**
 * Elementor Modal Controll.
 *
 * Elementor control for inserting WP Recipe Maker recipes.
 *
 * @since 5.1.0
 */
class WPRM_Elementor_Control extends \Elementor\Base_Data_Control {

	public function get_type() {
		return 'wprm-recipe-select';
	}

	public function enqueue() {
		wp_enqueue_script( 'wprm-elementor', WPRM_URL . 'templates/elementor/control.min.js', array( 'jquery' ), WPRM_VERSION, true );

		wp_localize_script( 'wprm-elementor', 'wprm_elementor', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wprm' ),
			'latest_recipes' => WPRM_Recipe_Manager::get_latest_recipes( 20, 'id' ),
		) );
	}

	public function get_default_value() {
		return false;
	}

	public function get_value( $control, $settings ) {
		return 6;
	}

	public function content_template() {
		?>
		<div id="wprm-recipe-select-placeholder"></div>
		<?php
	}
}