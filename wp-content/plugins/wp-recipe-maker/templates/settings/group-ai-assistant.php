<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       https://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

// Common language names mapping (ISO 639-1 codes).
$common_languages = array(
	'en' => __( 'English', 'wp-recipe-maker' ),
	'es' => __( 'Spanish', 'wp-recipe-maker' ),
	'fr' => __( 'French', 'wp-recipe-maker' ),
	'de' => __( 'German', 'wp-recipe-maker' ),
	'it' => __( 'Italian', 'wp-recipe-maker' ),
	'pt' => __( 'Portuguese', 'wp-recipe-maker' ),
	'nl' => __( 'Dutch', 'wp-recipe-maker' ),
	'pl' => __( 'Polish', 'wp-recipe-maker' ),
	'ru' => __( 'Russian', 'wp-recipe-maker' ),
	'ja' => __( 'Japanese', 'wp-recipe-maker' ),
	'zh' => __( 'Chinese', 'wp-recipe-maker' ),
	'ko' => __( 'Korean', 'wp-recipe-maker' ),
	'ar' => __( 'Arabic', 'wp-recipe-maker' ),
	'hi' => __( 'Hindi', 'wp-recipe-maker' ),
	'sv' => __( 'Swedish', 'wp-recipe-maker' ),
	'da' => __( 'Danish', 'wp-recipe-maker' ),
	'no' => __( 'Norwegian', 'wp-recipe-maker' ),
	'fi' => __( 'Finnish', 'wp-recipe-maker' ),
	'cs' => __( 'Czech', 'wp-recipe-maker' ),
	'hu' => __( 'Hungarian', 'wp-recipe-maker' ),
	'ro' => __( 'Romanian', 'wp-recipe-maker' ),
	'el' => __( 'Greek', 'wp-recipe-maker' ),
	'tr' => __( 'Turkish', 'wp-recipe-maker' ),
	'th' => __( 'Thai', 'wp-recipe-maker' ),
	'vi' => __( 'Vietnamese', 'wp-recipe-maker' ),
	'id' => __( 'Indonesian', 'wp-recipe-maker' ),
	'he' => __( 'Hebrew', 'wp-recipe-maker' ),
);

// Build language options with "Auto" as first option.
$language_options = array(
	'auto' => __( 'Auto (detect automatically)', 'wp-recipe-maker' ),
);
$language_options = array_merge( $language_options, $common_languages );

// Get default language from WordPress locale.
$default_language = 'auto';
if ( function_exists( 'get_locale' ) ) {
	$locale = get_locale();
	$wp_language_code = substr( $locale, 0, 2 ); // Extract language code from locale (e.g., 'en' from 'en_US').
	if ( isset( $common_languages[ $wp_language_code ] ) ) {
		$default_language = $wp_language_code;
	}
}

$ai_assistant = array(
	'id' => 'aiAssistant',
	'icon' => 'sparks',
	'name' => __( 'AI Assistant', 'wp-recipe-maker' ),
	'required' => 'elite',
	'description' => __( 'A suite of tools designed to streamline your workflow, reduce data entry fatigue, and help you manage your content more efficiently with the help of AI. The assistant is currently in beta.', 'wp-recipe-maker' ),
	'documentation' => 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/ai-assistant/',
	'subGroups' => array(
		array(
			'name' => __( 'General', 'wp-recipe-maker' ),
			'settings' => array(
				array(
					'id' => 'ai_assistant_default_language',
					'name' => __( 'Default Language', 'wp-recipe-maker' ),
					'description' => __( 'The default language to use when the AI Assistant processes recipes. Choose "Auto" to let the AI detect the language automatically, or select a specific language to help the AI understand the recipe content if it cannot detect the language.', 'wp-recipe-maker' ),
					'type' => 'dropdown',
					'options' => $language_options,
					'default' => $default_language,
				),
			),
		),
	),
);
