<?php
/**
 * Default CLS settings change.
 *
 * @link       https://bootstrapped.ventures
 * @since      7.3.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin/migrations
 */

$user_settings = WPRM_Settings::get_settings();

// If user never changed the "Default in Footer" settings.
if ( ! isset( $user_settings['snippet_templates_in_footer'] ) && ! isset( $user_settings['recipe_templates_in_footer'] ) ) {
	if ( ! isset( $user_settings['only_load_assets_when_needed'] ) || true === $user_settings['only_load_assets_when_needed'] ) {
		$update_settings = array(
			'only_load_assets_when_needed' => true,
			'snippet_templates_in_footer' => true,
			'recipe_templates_in_footer' => true,
		);

		WPRM_Settings::update_settings( $update_settings );
	}
}