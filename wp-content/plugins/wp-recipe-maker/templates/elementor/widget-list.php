<?php
/**
 * Elementor WPRM Roundup List Widget.
 *
 * Elementor widget for inserting WP Recipe Maker roundup list.
 *
 * @since 9.0.0
 */
class WPRM_Elementor_List_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 9.0.0
	 */
	public function get_name() {
		return 'wprm-list';
	}

	/**
	 * Get widget title.
	 * 
	 * @since 9.0.0
	 */
	public function get_title() {
		return 'WPRM Roundup List';
	}

	/**
	 * Get widget icon.
	 *
	 * @since 9.0.0
	 */
	public function get_icon() {
		return 'eicon-post-list';
	}

	/**
	 * Get widget categories.
	 *
	 * @since 9.0.0
	 */
	public function get_categories() {
		return array( 'wp-recipe-maker' );
	}

	/**
	 * Register widget controls.
	 *
	 * @since 9.0.0
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => 'WP Recipe Maker Roundup List',
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'wprm_create',
			[
				'type' => \Elementor\Controls_Manager::BUTTON,
				'text' => __( 'Create new list', 'wp-recipe-maker' ),
				'event' => 'wprm:list:create',
				'conditions' => [
					'terms' => [
						[
							'name' => 'wprm_list_id',
							'operator' => '<=',
							'value' => '0'
						],
					]
				]
			]
		);

		$this->add_control(
			'wprm_edit',
			[
				'type' => \Elementor\Controls_Manager::BUTTON,
				'text' => __( 'Edit selected list', 'wp-recipe-maker' ),
				'event' => 'wprm:list:edit',
				'conditions' => [
					'terms' => [
						[
							'name' => 'wprm_list_id',
							'operator' => '>',
							'value' => '0'
						],
					]
				]
			]
		);

		$this->add_control(
			'wprm_list_id',
			array(
				'type' => \Elementor\Controls_Manager::HIDDEN,
				'default' => false,
			)
		);
		$this->add_control(
			'wprm_list_select',
			array(
				'type' => 'wprm-list-select',
			)
		);

		$this->add_control(
			'wprm_unset',
			[
				'type' => \Elementor\Controls_Manager::BUTTON,
				'text' => __( 'Unset selected list', 'wp-recipe-maker' ),
				'event' => 'wprm:list:unset',
				'conditions' => [
					'terms' => [
						[
							'name' => 'wprm_list_id',
							'operator' => '>',
							'value' => '0'
						],
					]
				]
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 9.0.0
	 */
	protected function render() {
		$output = '';
		$id = intval( $this->get_settings_for_display( 'wprm_list_id' ) );

		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			if ( $id ) {
				$list = WPRM_List_Manager::get_list( $atts['id'] );

				// No list found? ID is incorrect => show warning.
				if ( $list ) {
					if ( 'default' !== $list->template() ) {
						$template = WPRM_Template_Manager::get_template_by_slug( $list->template() );
					} else {
						$template = WPRM_Template_Manager::get_template_by_type( 'roundup' );
					}
					$output .= '<style type="text/css">' . WPRM_Template_Manager::get_template_css( $template ) . '</style>';
				}

				$output .= do_shortcode( '[wprm-list id="' . $id . '"]' );
			} else {
				$output = '<div style="font-family: monospace;font-style:italic;cursor:pointer;">&lt;' . __( 'Click and select a WP Recipe Maker list to display in the sidebar.', 'wp-recipe-maker' ) . '&gt;</div>';
			}
		} else {
			// Output recipe in frontend.
			if ( $id ) {
				$output = '[wprm-list id="' . $id . '"]';
			}
		}

		echo $output;
	}

}