<?php
/**
 * Handle Elementor compatibility.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.1
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Handle Elementor compatibility.
 *
 * @since      10.4.1
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Compatibility_Elementor {

	/**
	 * Register Elementor compatibility hooks.
	 *
	 * @since	10.4.1
	 */
	public static function init() {
		add_action( 'elementor/editor/before_enqueue_scripts', array( __CLASS__, 'elementor_assets' ) );
		add_action( 'elementor/controls/register', array( __CLASS__, 'elementor_controls' ) );
		add_action( 'elementor/preview/enqueue_styles', array( __CLASS__, 'elementor_styles' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'elementor_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'elementor_categories' ) );
	}

	/**
	 * Elementor Compatibility.
	 *
	 * @since	5.0.0
	 */
	public static function elementor_assets() {
		WPRM_Modal::add_modal_content();
		WPRM_Assets::enqueue_admin();
		WPRM_Modal::enqueue();

		if ( class_exists( 'WPRMP_Assets' ) ) {
			WPRMP_Assets::enqueue_admin();
		}

		wp_enqueue_script( 'wprm-admin-elementor', WPRM_URL . 'assets/js/other/elementor.js', array( 'wprm-admin', 'wprm-admin-modal' ), WPRM_VERSION, true );
	}

	/**
	 * Register Elementor controls.
	 *
	 * @since	5.0.0
	 * @param	object $controls_manager Elementor controls manager.
	 */
	public static function elementor_controls( $controls_manager ) {
		include( WPRM_DIR . 'templates/elementor/control.php' );
		include( WPRM_DIR . 'templates/elementor/control-list.php' );

		$controls_manager->register( new WPRM_Elementor_Control() );
		$controls_manager->register( new WPRM_Elementor_Control_List() );
	}

	/**
	 * Load Elementor preview styles.
	 *
	 * @since	5.0.0
	 */
	public static function elementor_styles() {
		// Make sure default assets load.
		WPRM_Assets::load();
	}

	/**
	 * Register Elementor widgets.
	 *
	 * @since	5.0.0
	 * @param	object $widgets_manager Elementor widgets manager.
	 */
	public static function elementor_widgets( $widgets_manager ) {
		include( WPRM_DIR . 'templates/elementor/widget-recipe.php' );
		include( WPRM_DIR . 'templates/elementor/widget-list.php' );
		include( WPRM_DIR . 'templates/elementor/widget-roundup.php' );

		$widgets_manager->register( new WPRM_Elementor_Recipe_Widget() );
		$widgets_manager->register( new WPRM_Elementor_List_Widget() );
		$widgets_manager->register( new WPRM_Elementor_Roundup_Widget() );
	}

	/**
	 * Add custom widget categories to Elementor.
	 *
	 * @since 8.6.0
	 * @param	object $elements_manager Elementor elements manager.
	 */
	public static function elementor_categories( $elements_manager ) {
		$elements_manager->add_category(
			'wp-recipe-maker',
			array(
				'title' => __( 'WP Recipe Maker', 'wp-recipe-maker' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}
}

WPRM_Compatibility_Elementor::init();
