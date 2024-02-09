<?php
/**
 * Default collection add layout settings change.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.2.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$user_settings = WPRM_Settings::get_settings();

$update_settings = array();

if ( isset( $user_settings['recipe_collections_link'] ) && $user_settings['recipe_collections_link'] ) {
	$update_settings['recipe_collections_appearance_adding_layout'] = 'column';

	// Need to make sure setting exists when Premium plugin hasn't been updated yet.
	if ( defined( 'WPRMP_VERSION') && version_compare( WPRMP_VERSION, '9.2.0' ) < 0 ) {
		add_filter( 'wprm_settings_structure', function( $structure ) {
			$structure['recipeCollections']['settings'] = array(
				array(
					'id' => 'recipe_collections_appearance_adding_layout',
					'type' => 'dropdown',
					'options' => array(
						'column' => __( 'Add directly in column (backwards compatibility)', 'wp-recipe-maker-premium' ),
						'modal' => __( 'Open modal to select the item to add', 'wp-recipe-maker-premium' ),
					),
					'default' => 'modal',
				),
			);

			return $structure;
		} );
	}
}

if ( $update_settings ) {
	WPRM_Settings::update_settings( $update_settings );
}