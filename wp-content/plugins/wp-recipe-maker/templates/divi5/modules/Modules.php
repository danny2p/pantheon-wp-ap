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

// Register via dependency tree (for Visual Builder)
add_action(
    'divi_module_library_modules_dependency_tree',
    function ( $dependency_tree ) {
        $recipe_module = new Recipe();
        $dependency_tree->add_dependency( $recipe_module );
        // Explicitly call load() to ensure registration happens
        $recipe_module->load();
    }
);

// Register directly on init (for frontend rendering)
add_action(
    'init',
    function () {
        if ( class_exists( 'ET\Builder\Packages\ModuleLibrary\ModuleRegistration' ) ) {
            $module_json_folder_path = trailingslashit( WPRM_DIVI5_JSON_PATH ) . 'wprm-recipe/';
            
            \ET\Builder\Packages\ModuleLibrary\ModuleRegistration::register_module(
                $module_json_folder_path,
                [
                    'render_callback' => [ 'WPRM\Divi5\Modules\Recipe\Recipe', 'render_callback' ],
                ]
            );
        }
    },
    20 // Higher priority to run after Divi initializes
);
