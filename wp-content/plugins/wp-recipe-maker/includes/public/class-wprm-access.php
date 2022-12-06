<?php
/**
 * Handle access to recipes.
 *
 * @link       https://bootstrapped.ventures
 * @since      8.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle access to recipes.
 *
 * @since      8.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Access {

	/**
	 * Check if using membership plugin.
	 *
	 * @since	8.0.0
	 */
	public static function membership_plugin() {
		if ( function_exists( 'pmpro_has_membership_access' ) ) {
			return 'paidmembershipspro';
		}
		if ( class_exists( 'MeprRule' ) ) {
			return 'memberpress';
		}

		return false;
	}

	/**
	 * Check if the current user can access a specific recipe.
	 *
	 * @since	8.0.0
	 * @param	int $post_id Post ID to check access for.
	 */
	public static function can_access( $post_id ) {
		// Allow overriding of this function.
		$override_can_access = apply_filters( 'wprm_can_access', null, $post_id );
		
		if ( ! is_null( $override_can_access ) ) {
			return $override_can_access;
		}

		// Default checks.
		$membership_plugin = self::membership_plugin();

		// No membership plugin active? Don't check anything.
		if ( false === $membership_plugin ) {
			return true;
		}

		// Only return false here, not true as we might have to check parent post as well.
		switch ( $membership_plugin ) {
			case 'paidmembershipspro':
				if ( ! pmpro_has_membership_access( $post_id ) ) {
					return false;
				}
				break;
			case 'memberpress':
				$post = get_post( $post_id );

				if ( $post && MeprRule::is_locked( $post ) ) {
					return false;
				}
				break;
		}

		// If we were checking a recipe, check the parent post restrictions as well.
		if ( WPRM_POST_TYPE === get_post_type( $post_id ) ) {
			$recipe = WPRM_Recipe_Manager::get_recipe( $post_id );
			$parent_post_id = $recipe->parent_post_id();

			if ( $parent_post_id && $post_id !== $parent_post_id ) {
				return self::can_access( $parent_post_id );
			}
		}

		// Couldn't find any restrictions, allow access.
		return true;
	}
}
