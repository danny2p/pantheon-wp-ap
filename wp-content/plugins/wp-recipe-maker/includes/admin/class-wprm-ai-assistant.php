<?php
/**
 * Handle the AI Assistant page.
 *
 * @link       https://bootstrapped.ventures
 * @since      10.4.1
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 */

/**
 * Handle the AI Assistant page.
 *
 * @since      10.4.1
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/admin
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_AI_Assistant {
	/**
	 * Register actions and filters.
	 *
	 * @since    10.4.1
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_submenu_page' ) );
	}

	/**
	 * Add the AI Assistant submenu to the WPRM menu.
	 *
	 * @since    10.4.1
	 */
	public static function add_submenu_page() {
		add_submenu_page(
			'wprecipemaker',
			__( 'AI Assistant', 'wp-recipe-maker' ),
			self::get_submenu_title(),
			WPRM_Settings::get( 'features_manage_access' ),
			'wprm_ai_assistant',
			array( __CLASS__, 'page_template' )
		);
	}

	/**
	 * Get formatted submenu title.
	 *
	 * @since    10.4.1
	 *
	 * @return	string
	 */
	public static function get_submenu_title() {
		return '<span class="wprm-admin-submenu-label"><span class="wprm-admin-submenu-icon wprm-admin-submenu-icon-ai-assistant" aria-hidden="true"></span><span>' . esc_html__( 'AI Assistant', 'wp-recipe-maker' ) . '</span></span>';
	}

	/**
	 * Get AI tools to show on the AI page.
	 *
	 * @since    10.4.1
	 *
	 * @return	array
	 */
	public static function get_tools() {
		$documentation_url = 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/ai-assistant/';
		$locked_tooltip = __( 'AI features are only available in the Elite Bundle during beta. Click to learn more.', 'wp-recipe-maker' );

		$tools = array(
			'import_with_ai' => array(
				'name' => __( 'Import with AI', 'wp-recipe-maker' ),
				'description' => __( 'Paste in recipe text and let AI turn it into a new recipe you can review and save in the recipe modal.', 'wp-recipe-maker' ),
				'documentation_url' => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/import-from-text-with-ai/',
				'button' => array(
					'label' => __( 'Import with AI', 'wp-recipe-maker' ),
					'url' => $documentation_url,
					'tooltip' => $locked_tooltip,
					'class' => 'button button-primary button-compact wprm-button-ai wprm-button-required wprm-ai-assistant-tool-button',
				),
			),
			'suggest_tags' => array(
				'name' => __( 'Suggest Tags', 'wp-recipe-maker' ),
				'description' => __( 'Use AI to suggest recipe tags and taxonomy terms based on the recipe content.', 'wp-recipe-maker' ),
				'documentation_url' => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/suggest-tags/',
				'button' => array(
					'label' => __( 'Suggest Tags', 'wp-recipe-maker' ),
					'url' => $documentation_url,
					'tooltip' => $locked_tooltip,
					'class' => 'button button-primary button-compact wprm-button-ai wprm-button-required wprm-ai-assistant-tool-button',
				),
			),
			'generate_ideas' => array(
				'name' => __( 'Generate Ideas', 'wp-recipe-maker' ),
				'description' => __( 'Generate new ideas for recipes, roundups, and other content you can use as inspiration for future posts.', 'wp-recipe-maker' ),
				'documentation_url' => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/generate-ideas-with-ai/',
				'button' => array(
					'label' => __( 'Generate Ideas', 'wp-recipe-maker' ),
					'url' => $documentation_url,
					'tooltip' => $locked_tooltip,
					'class' => 'button button-primary button-compact wprm-button-ai wprm-button-required wprm-ai-assistant-tool-button',
				),
			),
			'nutrition_review' => array(
				'name' => __( 'Nutrition Review', 'wp-recipe-maker' ),
				'description' => __( 'Review ingredient matches and units with AI, let Spoonacular calculate the nutrition facts, and queue recipes that still need manual review.', 'wp-recipe-maker' ),
				'documentation_url' => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/ai-nutrition-review/',
				'button' => array(
					'label' => __( 'Nutrition Review', 'wp-recipe-maker' ),
					'url' => $documentation_url,
					'tooltip' => $locked_tooltip,
					'class' => 'button button-primary button-compact wprm-button-ai wprm-button-required wprm-ai-assistant-tool-button',
				),
			),
			'unit_conversion_review' => array(
				'name' => __( 'Unit Conversion Review', 'wp-recipe-maker' ),
				'description' => __( 'Review direct-rule and AI-assisted converted ingredient units in batches, then apply the confirmed second unit system to recipes that are ready.', 'wp-recipe-maker' ),
				'documentation_url' => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/ai-unit-conversion-review/',
				'button' => array(
					'label' => __( 'Unit Conversion Review', 'wp-recipe-maker' ),
					'url' => $documentation_url,
					'tooltip' => $locked_tooltip,
					'class' => 'button button-primary button-compact wprm-button-ai wprm-button-required wprm-ai-assistant-tool-button',
				),
			),
		);

		return apply_filters( 'wprm_ai_assistant_tools', $tools );
	}

	/**
	 * Get the current tool from the query parameter.
	 *
	 * @since    10.5.0
	 *
	 * @return	string|false
	 */
	public static function get_current_tool() {
		return isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : false;
	}

	/**
	 * Get the template for this submenu.
	 *
	 * @since    10.4.1
	 */
	public static function page_template() {
		$tool = self::get_current_tool();

		if ( $tool ) {
			require_once( WPRM_DIR . 'templates/admin/menu/ai-assistant-tool.php' );
		} else {
			$tools = self::get_tools();
			require_once( WPRM_DIR . 'templates/admin/menu/ai-assistant.php' );
		}
	}
}

WPRM_AI_Assistant::init();
