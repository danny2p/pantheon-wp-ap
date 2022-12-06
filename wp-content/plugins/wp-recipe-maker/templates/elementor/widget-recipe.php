<?php
/**
 * Elementor WPRM Recipe Widget.
 *
 * Elementor widget for inserting WP Recipe Maker recipes.
 *
 * @since 5.0.0
 */
class WPRM_Elementor_Recipe_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 5.0.0
	 */
	public function get_name() {
		return 'wprm-recipe';
	}

	/**
	 * Get widget title.
	 * 
	 * @since 5.0.0
	 */
	public function get_title() {
		return 'WPRM Recipe';
	}

	/**
	 * Get widget icon.
	 *
	 * @since 5.0.0
	 */
	public function get_icon() {
		return 'eicon-info-box';
	}

	/**
	 * Get widget categories.
	 *
	 * @since 5.0.0
	 */
	public function get_categories() {
		return array( 'wp-recipe-maker' );
	}

	/**
	 * Register widget controls.
	 *
	 * @since 5.0.0
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => 'WP Recipe Maker',
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'wprm_create',
			[
				'type' => \Elementor\Controls_Manager::BUTTON,
				'text' => __( 'Create new recipe', 'wp-recipe-maker' ),
				'event' => 'wprm:recipe:create',
				'conditions' => [
					'terms' => [
						[
							'name' => 'wprm_recipe_id',
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
				'text' => __( 'Edit selected recipe', 'wp-recipe-maker' ),
				'event' => 'wprm:recipe:edit',
				'conditions' => [
					'terms' => [
						[
							'name' => 'wprm_recipe_id',
							'operator' => '>',
							'value' => '0'
						],
					]
				]
			]
		);

		$this->add_control(
			'wprm_recipe_id',
			array(
				'type' => \Elementor\Controls_Manager::HIDDEN,
				'default' => false,
			)
		);
		$this->add_control(
			'wprm_recipe_select',
			array(
				'type' => 'wprm-recipe-select',
			)
		);

		$this->add_control(
			'wprm_unset',
			[
				'type' => \Elementor\Controls_Manager::BUTTON,
				'text' => __( 'Unset selected recipe', 'wp-recipe-maker' ),
				'event' => 'wprm:recipe:unset',
				'conditions' => [
					'terms' => [
						[
							'name' => 'wprm_recipe_id',
							'operator' => '>',
							'value' => '0'
						],
					]
				]
			]
		);

		$this->add_control(
			'wrpm_create',
			array(
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => '&gt; <a href="' . esc_url( admin_url( 'admin.php?page=wprm_manage' ) ) .'" target="_blank">' . __( 'Go to WPRM Manage page', 'wp-recipe-maker' ) . '</a>',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 5.0.0
	 */
	protected function render() {
		$output = '';
		$id = intval( $this->get_settings_for_display( 'wprm_recipe_id' ) );

		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			if ( $id ) {
				// Get Template Style.
				$template = WPRM_Template_Manager::get_template_by_type( 'single' );
				if ( 'modern' === $template['mode'] ) {
					$output .= '<style type="text/css">' . WPRM_Template_Manager::get_template_css( $template ) . '</style>';
				} else {
					$output .= '<style type="text/css">' . WPRM_Assets::get_custom_css( 'recipe' ) . '</style>';
				}

				$output .= do_shortcode( '[wprm-recipe id="' . $id . '" template="' . $template['slug'] . '"]' );
			} else {
				$output = '<div style="font-family: monospace;font-style:italic;cursor:pointer;">&lt;' . __( 'Click and select a WP Recipe Maker recipe to display in the sidebar.', 'wp-recipe-maker' ) . '&gt;</div>';
			}
		} else {
			// Output recipe in frontend.
			if ( $id ) {
				$output = '[wprm-recipe id="' . $id . '"]';
			}
		}

		echo $output;
	}

}