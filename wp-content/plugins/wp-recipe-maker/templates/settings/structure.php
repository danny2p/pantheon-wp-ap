<?php
/**
 * Template for the plugin settings structure.
 *
 * @link       http://bootstrapped.ventures
 * @since      3.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/templates/settings
 */

$premium_active = class_exists( 'WPRM_Addons' ) && WPRM_Addons::is_active( 'premium' );
$premium_only = $premium_active ? '' : ' (' . __( 'WP Recipe Maker Premium only', 'wp-recipe-maker' ) . ')';

// Appearance.
// Appearance - Legacy.
require_once( 'group-template-mode.php' );
require_once( 'group-template-legacy.php' );
require_once( 'group-template-legacy-labels.php' );

// Appearance - Modern.
require_once( 'group-template-editor.php' );
require_once( 'group-recipe-template.php' );

// Appearance - Shared.
require_once( 'group-recipe-print.php' );
require_once( 'group-recipe-snippets.php' );
require_once( 'group-nutrition-label.php' );
require_once( 'group-integrations.php' );
require_once( 'group-custom-style.php' );

// Interactivity.
require_once( 'group-recipe-roundup.php' );
require_once( 'group-media.php' );
require_once( 'group-recipe-ratings.php' );
require_once( 'group-adjustable-servings.php' );
require_once( 'group-social-sharing.php' );
require_once( 'group-private-notes.php' );
require_once( 'group-links.php' );
require_once( 'group-nutrition-calculation.php' );
require_once( 'group-translate.php' );
require_once( 'group-unit-conversion.php' );
require_once( 'group-recipe-submission.php' );
require_once( 'group-recipe-collections.php' );
require_once( 'group-analytics.php' );

// Backend.
require_once( 'group-recipe-defaults.php' );
require_once( 'group-import.php' );

// Advanced.
require_once( 'group-post-type.php' );
require_once( 'group-metadata.php' );
require_once( 'group-performance.php' );
require_once( 'group-permissions.php' );
require_once( 'group-legacy.php' );
require_once( 'group-settings-tools.php' );

$settings_structure = array(
	array( 'header' => __( 'Appearance', 'wp-recipe-maker' ) ),
	// Legacy Only.
	$template_mode,
	$template_legacy,
	$template_legacy_labels,

	// Modern Only.
	$template_editor,
	$recipe_template,

	// Shared.
	$recipe_print,
	$recipe_snippets,
	$nutrition_label,
	$integrations,
	$custom_style,
	array( 'header' => __( 'Interactivity', 'wp-recipe-maker' ) ),
	$recipe_roundup,
	$media,
	$recipe_ratings,
	$adjustable_servings,
	$social_sharing,
	$private_notes,
	$links,
	$nutrition_calculation,
	$translate,
	$unit_conversion,
	$recipe_submission,
	$recipe_collections,
	$analytics,
	array( 'header' => __( 'Backend', 'wp-recipe-maker' ) ),
	$recipe_defaults,
	$import,
	array( 'header' => __( 'Advanced', 'wp-recipe-maker' ) ),
	$post_type,
	$metadata,
	$performance,
	$permissions,
	$legacy,
	$settings_tools,
);
