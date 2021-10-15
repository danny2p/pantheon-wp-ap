<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      5.7.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$nutrition_calculation = array(
	'id' => 'nutritionCalculation',
	'icon' => 'measure-apple',
	'name' => __( 'Nutrition Facts Calculation', 'wp-recipe-maker' ),
	'required' => 'pro',
	'description' => __( 'Our API integration helps calculate the nutition facts for you.', 'wp-recipe-maker' ),
	'documentation' => 'https://help.bootstrapped.ventures/article/21-nutrition-facts-calculation',
	'settings' => array(
		array(
			'id' => 'nutrition_facts_calculation_round_to_decimals',
			'name' => __( 'Round quantity to', 'wp-recipe-maker' ),
			'description' => __( 'Number of decimals to round a quantity to when calculating nutrition facts.', 'wp-recipe-maker' ),
			'type' => 'number',
			'suffix' => 'decimals',
			'default' => '0',
		),
	),
);
