<?php
/**
 * Default shopping list shortcut settings change.
 *
 * @link       https://bootstrapped.ventures
 * @since      9.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$user_settings = WPRM_Settings::get_settings();

$update_settings = array();

if ( isset( $user_settings['recipe_collections_link'] ) && $user_settings['recipe_collections_link'] ) {
	$update_settings['recipe_collections_shopping_list_shortcut'] = false;

	// Need to make sure setting exists when Premium plugin hasn't been updated yet.
	if ( defined( 'WPRMP_VERSION') && version_compare( WPRMP_VERSION, '9.0.0' ) < 0 ) {
		add_filter( 'wprm_settings_structure', function( $structure ) {
			$structure['recipeCollections']['settings'] = array(
				array(
					'id' => 'recipe_collections_shopping_list_shortcut',
					'type' => 'toggle',
					'default' => true,
				)
			);

			return $structure;
		} );
	}
}

if ( $update_settings ) {
	WPRM_Settings::update_settings( $update_settings );
}