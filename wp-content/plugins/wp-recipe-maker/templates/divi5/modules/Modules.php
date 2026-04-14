<?php
/**
 * Register Divi 5 modules with Divi's dependency tree.
 */

namespace WPRM\Divi5\Modules;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use WPRM\Divi5\Modules\Recipe\Recipe;

require_once __DIR__ . '/Recipe/Recipe.php';

/**
 * Register all WPRM Divi 5 modules.
 */
function register_modules() {
	$recipe_module = new Recipe();
	$recipe_module->load();
}

// Register via dependency tree (for Visual Builder)
add_action(
	'divi_module_library_modules_dependency_tree',
	function ( $dependency_tree ) {
		$recipe_module = new Recipe();
		$dependency_tree->add_dependency( $recipe_module );
		$recipe_module->load();
	}
);
