<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$nutrition_label = array(
	'id' => 'nutritionLabel',
	'icon' => 'doc-apple',
	'name' => __( 'Nutrition Label', 'wp-recipe-maker' ),
	'required' => 'premium',
	'subGroups' => array(
		array(
			'name' => __( 'Display Manually', 'wp-recipe-maker' ),
			'description' => __( 'Type the [wprm-nutrition-label] or [wprm-nutrition-label id="123" align="center"] shortcode where you want the label to appear.', 'wp-recipe-maker' ),
			'documentation' => 'https://help.bootstrapped.ventures/article/22-nutrition-label',
		),
		array(
			'name' => __( 'Display Automatically', 'wp-recipe-maker' ),
			'dependency' => array(
				'id' => 'recipe_template_mode',
				'value' => 'legacy',
			),
			'settings' => array(
				array(
					'id' => 'show_nutrition_label',
					'name' => __( 'Nutrition Label in template', 'wp-recipe-maker' ),
					'description' => __( 'Display the nutrition label at its default location in the recipe template.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => array(
						'disabled' => __( "Don't show", 'wp-recipe-maker' ),
						'left' => __( 'Show - Aligned Left', 'wp-recipe-maker' ),
						'center' => __( 'Show - Aligned Center', 'wp-recipe-maker' ),
						'right' => __( 'Show - Aligned Right', 'wp-recipe-maker' ),
					),
					'default' => 'disabled',
				),
			),
		),
		array(
			'name' => __( 'Customize', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'nutrition_label_style',
					'name' => __( 'Nutrition Label Style', 'wp-recipe-maker-premium' ),
					'description' => __( 'Modern style was released in WP Recipe Maker 6.8.0 and the recommended option. Legacy style is around for backwards compatibility.', 'wp-recipe-maker-premium' ),
					'type' => 'dropdown',
					'options' => array(
						'legacy' => __( 'Legacy - Fixed layout', 'wp-recipe-maker-premium' ),
						'modern' => __( 'Modern - Editable layout', 'wp-recipe-maker-premium' ),
					),
					'default' => 'modern',
				),
				array(
					'name' => __( 'Nutrition Label Layout', 'wp-recipe-maker-premium' ),
					'description' => __( 'This is the layout that will get used if "Modern" nutrition label style is selected above.', 'wp-recipe-maker-premium' ),
					'type' => 'button',
					'button' => __( 'Edit the Nutrition Label Layout', 'wp-recipe-maker-premium' ),
					'link' => admin_url( 'admin.php?page=wprmp_nutrition_label_layout' ),
					'required' => 'premium',
				),
				array(
					'id' => 'nutrition_default_serving_unit',
					'name' => __( 'Default Serving Size Unit', 'wp-recipe-maker' ),
					'description' => __( 'Default unit to use for the nutrition serving size.', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => 'g',
				),
				array(
					'id' => 'nutrition_label_legacy_servings_type',
					'name' => __( 'Show nutrition values', 'wp-recipe-maker-premium' ),
					'description' => __( 'How to display the nutrition values in the label.', 'wp-recipe-maker-premium' ),
					'type' => 'dropdown',
					'options' => array(
						'serving' => __( 'Per serving', 'wp-recipe-maker' ),
						'100g' => __( 'Per 100g', 'wp-recipe-maker' ),
					),
					'default' => 'serving',
					'dependency' => array(
						'id' => 'nutrition_label_style',
						'value' => 'legacy',
					),
				),
				array(
					'id' => 'nutrition_label_zero_values',
					'name' => __( 'Show values when 0', 'wp-recipe-maker' ),
					'description' => __( 'Show nutrient in the nutrition label when it has a value of 0.', 'wp-recipe-maker' ),
					'type' => 'toggle',
					'default' => false,
				),
				array(
					'id' => 'nutrition_label_custom_daily_values_disclaimer',
					'name' => __( 'Daily Values Disclaimer', 'wp-recipe-maker' ),
					'type' => 'text',
					'default' => __( 'Percent Daily Values are based on a 2000 calorie diet.', 'wp-recipe-maker-premium' ),
					'dependency' => array(
						'id' => 'nutrition_label_style',
						'value' => 'legacy',
					),
				),
			),
		),
	),
);

if ( class_exists( 'WPRM_Addons' ) && ! WPRM_Addons::is_active( 'pro' ) ) {
	$nutrition_label['description'] = __( 'Get the Pro Bundle to have our Nutrition API integration help calculate the nutrition facts for you.', 'wp-recipe-maker' );
	$nutrition_label['documentation'] = 'https://help.bootstrapped.ventures/article/21-nutrition-facts-calculation';
}
