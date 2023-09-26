<?php
/**
 * Responsible for the admin bar.
 *
 * @link       http://bootstrapped.ventures
 * @since      8.10.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for the admin bar.
 *
 * @since      8.10.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Admin_Bar {
	private static $editable_recipes = false;

	/**
	 * Register actions and filters.
	 *
	 * @since    8.10.0
	 */
	public static function init() {
		add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_menu' ), 80 );
		add_action( 'wp', array( __CLASS__, 'load_modal' ) );
	}

	/**
	 * Check if admin bar functionality should be loaded.
	 *
	 * @since    8.10.0
	 */
	public static function should_load_admin_bar() {
		if ( is_admin() || ! is_admin_bar_showing() ) {
			return false;
		}

		// Prevent admin bar when editing in Divi (breaks their text editor).
		if ( isset( $_GET['et_fb'] ) ) {
			return false;
		}

		return WPRM_Settings::get( 'admin_bar_menu_item' );
	}

	/**
	 * Load the recipe modal.
	 *
	 * @since    8.10.0
	 */
	public static function load_modal() {
		if ( ! self::should_load_admin_bar() ) {
			return;
		}

		// Check if there is anything to edit.
		$recipes = self::get_editable_recipes();
		if ( ! $recipes ) {
			return;
		}

		require_once( WPRM_DIR . 'includes/admin/class-wprm-modal.php' );
		WPRM_Modal::load_public();
	}

	/**
	 * Get editable recipes on current page.
	 *
	 * @since    8.10.1
	 */
	public static function get_editable_recipes() {
		if ( false === self::$editable_recipes ) {
			// Check for recipes in this post first.
			$recipe_ids = WPRM_Recipe_Manager::get_recipe_ids_from_post();
			$recipes = array();

			if ( $recipe_ids ) {
				foreach ( $recipe_ids as $recipe_id ) {
					$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

					if ( $recipe && current_user_can( 'edit_post', $recipe_id ) ) {
						$recipes[] = $recipe;
					}
				}
			}

			self::$editable_recipes = $recipes;
		}

		return self::$editable_recipes;
	}

	/**
	 * Add item to admin bar.
	 *
	 * @since    8.10.0
	 */
	public static function admin_bar_menu( $wp_admin_bar ) {
		if ( ! self::should_load_admin_bar() ) {
			return;
		}

		$should_output_menu = false;
		$main_menu = array(
			'id'    => 'wp-recipe-maker',
			'parent' => null,
			'group'  => null,
			'title' => '<span class="ab-icon" aria-hidden="true"></span><span class="ab-label">WP Recipe Maker</span>',
			'meta'  => array( 'title' => 'WP Recipe Maker' ),
		);

		// If there are recipes, make the main menu open the first one for editing.
		$recipes = self::get_editable_recipes();
		if ( $recipes ) {
			$main_menu['href'] = '#';
			$main_menu['meta'] = array(
				'onclick' => self::get_edit_recipe( $recipes[0]->id() ),
			);
		}

		// Shortcuts
		if ( current_user_can( WPRM_Settings::get( 'features_dashboard_access' ) ) || current_user_can( WPRM_Settings::get( 'features_manage_access' ) ) ) {
			$should_output_menu = true;
			$wp_admin_bar->add_node(
				array(
					'parent' => 'wp-recipe-maker',
					'id'     => 'wprm-shortcuts-header',
					'title'  => __( 'Shortcuts', 'wp-recipe-maker' ),
				)
			);

			if ( current_user_can( WPRM_Settings::get( 'features_dashboard_access' ) ) ) {
				$wp_admin_bar->add_node(
					array(
						'parent' => 'wp-recipe-maker',
						'id'     => 'wprm-dashboard',
						'title'  => __( 'Dashboard', 'wp-recipe-maker' ),
						'href'   => admin_url( 'admin.php?page=wprecipemaker' ),
					)
				);
			}
			if ( current_user_can( WPRM_Settings::get( 'features_manage_access' ) ) ) {
				$wp_admin_bar->add_node(
					array(
						'parent' => 'wp-recipe-maker',
						'id'     => 'wprm-manage',
						'title'  => __( 'Manage', 'wp-recipe-maker' ),
						'href'   => admin_url( 'admin.php?page=wprm_manage' ),
					)
				);

				// Check for pending recipe submissions.
				if ( WPRM_Settings::get( 'recipe_submission_admin_bar' ) ) {
					$count_posts = wp_count_posts( WPRM_POST_TYPE );
					$submissions_count = $count_posts->pending;

					if ( 0 < $submissions_count ) {
						$wp_admin_bar->add_node(
							array(
								'parent' => 'wp-recipe-maker',
								'id'     => 'wprm-recipe-submissions',
								'title'  => __( 'Recipe Submissions', 'wp-recipe-maker' ) . ' (' . $submissions_count . ')',
								'href'   => admin_url( 'admin.php?page=wprm_manage#recipe-submission' ),
							)
						);

						$label = sprintf( _n( '%s pending recipe submission', '%s pending recipe submissions', $submissions_count, 'wp-recipe-maker' ), number_format_i18n( $submissions_count ) );
						$badge = ' ' . sprintf( '<span style="float:right" class="wprm-admin-bar-badge"><span aria-hidden="true">%1$d</span><span class="screen-reader-text">%2$s</span></span>', $submissions_count, $label );

						// Insert badge before last </span>.
						$pos = strrpos( $main_menu['title'], '</span>' );
						$main_menu['title'] = substr_replace( $main_menu['title'], $badge . '</span>', $pos, strlen( '</span>' ) );
					}
				}
			}
		}

		// Show recipes to edit, if there are any.
		if ( $recipes ) {
			$should_output_menu = true;
			$wp_admin_bar->add_group(
				array(
					'parent' => 'wp-recipe-maker',
					'id'     => 'wprm-recipes',
				)
			);

			$wp_admin_bar->add_node(
				array(
					'parent' => 'wprm-recipes',
					'id'     => 'wprm-recipes-header',
					'title'  => __( 'Edit Recipes', 'wp-recipe-maker' ),
				)
			);
			
			foreach ( $recipes as $recipe ) {
				$wp_admin_bar->add_node(
					array(
						'parent' => 'wprm-recipes',
						'id'     => 'wprm-edit-recipe-' . $recipe->id(),
						'title'  => '#' . $recipe->id() . ' - ' . $recipe->name(),
						'href'   => '#',
						'meta'	 => array(
							'onclick' => self::get_edit_recipe( $recipe->id() ),
						)
					)
				);
			}
		}
		
		// Only output if there is actually something to see.
		if ( $should_output_menu ) {
			$wp_admin_bar->add_menu( $main_menu );
		}
	}

	/**
	 * Get onclick action for editing a recipe.
	 *
	 * @since    8.10.0
	 */
	public static function get_edit_recipe( $recipe_id ) {
		return 'WPRM_Modal.open( "recipe", { recipeId: ' . $recipe_id . ', saveCallback: function() { location.reload(); } } ); return false;';
	}
}

WPRM_Admin_Bar::init();
