<?php
/**
 * Default collection layout settings change.
 *
 * @link       https://bootstrapped.ventures
 * @since      8.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$user_settings = WPRM_Settings::get_settings();

$update_settings = array();

if ( isset( $user_settings['recipe_collections_link'] ) && $user_settings['recipe_collections_link'] ) {
	$update_settings['recipe_collections_appearance_layout'] = 'classic';

	// Need to make sure setting exists when Premium plugin hasn't been updated yet.
	if ( defined( 'WPRMP_VERSION') && version_compare( WPRMP_VERSION, '8.0.0' ) < 0 ) {
		add_filter( 'wprm_settings_structure', function( $structure ) {
			$structure['recipeCollections']['settings'] = array(
				array(
					'id' => 'recipe_collections_appearance_layout',
					'type' => 'dropdown',
					'options' => array(
						'classic' => 'Classic',
						'grid' => 'Grid',
					),
					'default' => 'grid',
				)
			);

			return $structure;
		} );
	}
}

if ( $update_settings ) {
	WPRM_Settings::update_settings( $update_settings );
}