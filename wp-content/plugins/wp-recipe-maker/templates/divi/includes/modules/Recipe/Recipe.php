<?php

class WPRM_Divi_Module_Recipe extends ET_Builder_Module {

	public $slug       = 'divi_wprm_recipe';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://bootstrapped.ventures/wp-recipe-maker',
		'author'     => 'Bootstrapped Ventures',
		'author_uri' => 'https://bootstrapped.ventures',
	);

	public function init() {
		$this->name = 'WPRM Recipe';

		$this->settings_modal_toggles  = array(
			// Content tab's slug is "general"
			'general'  => array(
				'toggles' => array(
					'main_content' => 'WP Recipe Maker',
				),
			),
		);
	}

	public function get_fields() {
		$latest_recipes = WPRM_Recipe_Manager::get_latest_recipes( 20, 'id' );

		$latest_recipe_options = array();
		foreach( $latest_recipes as $recipe ) {
			$latest_recipe_options[ $recipe['id'] ] = $recipe['text'];
		}

		return array(
			'recipe_id' => array(
				'label'           => esc_html__( 'Recipe ID (required)', 'wp-recipe-maker' ),
				'type'            => 'text',
				'description'     => esc_html__( 'Fill in the ID of the recipe to display.', 'wp-recipe-maker' ),
				'toggle_slug'     => 'main_content',
			),
			'latest_recipes' => array(
				'label'           => esc_html__( 'Find the ID of the latest recipes', 'wp-recipe-maker' ),
				'type'            => 'select',
				'options'         => $latest_recipe_options,
				'description'     => esc_html__( 'Use this select to quickly find the ID of the latest recipes. You still have to fill in the Recipe ID field above', 'wp-recipe-maker' ),
				'toggle_slug'     => 'main_content',
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'link' => false,
			'background' => false,
			'fonts' => false,
			'borders' => false,
			'text' => false,
			'max_width' => false,
			'margin_padding' => false,
			'padding' => false,
			'filters' => false,
			'text_shadow' => false,
			'box_shadow' => false,
			'transform' => false,
			'animation' => false,
			'css_fields' => false,
			'css' => false,
			'visibility' => false,
			'transitions' => false,
			'position' => false,
			'scroll_effects' => false,
			'button' => false,
		);
	}

	public function render( $attrs, $content = null, $render_slug ) {
		$recipe_id = intval( $this->props['recipe_id'] );

		if ( ! $recipe_id ) {
			return '';
		}

		return do_shortcode( '[wprm-recipe id="' . $recipe_id . '"]' );
	}
}

new WPRM_Divi_Module_Recipe;
