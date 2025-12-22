<?php
/**
 * Divi 5 WPRM Recipe module.
 *
 * @package WP_Recipe_Maker\Divi5
 */

namespace WPRM\Divi5\Modules\Recipe;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class Recipe implements DependencyInterface {
    /**
     * Sanitizer that passes through content unchanged (for already-sanitized shortcode output).
     *
     * @param string $content Content to sanitize.
     * @return string Unchanged content.
     */
    public static function passthrough_sanitizer( $content ) {
        return $content;
    }

    /**
     * Bootstrap the module registration.
     */
    public function load() {
        $module_json_folder_path = trailingslashit( WPRM_DIVI5_JSON_PATH ) . 'wprm-recipe/';

        add_action(
            'init',
            function () use ( $module_json_folder_path ) {
                if ( ! class_exists( ModuleRegistration::class ) ) {
                    return;
                }

                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [ self::class, 'render_callback' ],
                    ]
                );
            }
        );
    }

    /**
     * Front-end render callback.
     *
     * @param array          $attrs    Saved attributes.
     * @param string         $content  Inner content string.
     * @param \WP_Block      $block    Parsed block instance.
     * @param ModuleElements $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback( $attrs, $content, $block, $elements ) {
        // Try multiple possible attribute structures
        $recipe_value = '';
        
        // Try the standard structure first
        if ( isset( $attrs['recipe']['innerContent']['desktop']['value'] ) ) {
            $recipe_value = $attrs['recipe']['innerContent']['desktop']['value'];
        }
        // Try alternative structure (processed attributes)
        elseif ( isset( $attrs['recipe']['innerContent']['value'] ) ) {
            $recipe_value = $attrs['recipe']['innerContent']['value'];
        }
        // Try direct value
        elseif ( isset( $attrs['recipe']['value'] ) ) {
            $recipe_value = $attrs['recipe']['value'];
        }
        // Try from parsed block attributes
        elseif ( isset( $block->parsed_block['attrs']['recipe']['innerContent']['desktop']['value'] ) ) {
            $recipe_value = $block->parsed_block['attrs']['recipe']['innerContent']['desktop']['value'];
        }
        
        $recipe_id = intval( $recipe_value );

        if ( ! $recipe_id ) {
            return '';
        }

        // Get template value
        $template_value = '';
        if ( isset( $attrs['recipe']['template']['desktop']['value'] ) ) {
            $template_value = $attrs['recipe']['template']['desktop']['value'];
        } elseif ( isset( $attrs['recipe']['template']['value'] ) ) {
            $template_value = $attrs['recipe']['template']['value'];
        } elseif ( isset( $block->parsed_block['attrs']['recipe']['template']['desktop']['value'] ) ) {
            $template_value = $block->parsed_block['attrs']['recipe']['template']['desktop']['value'];
        }

        // Build shortcode
        $shortcode = '[wprm-recipe id="' . esc_attr( $recipe_id ) . '"';
        if ( ! empty( $template_value ) ) {
            $shortcode .= ' template="' . esc_attr( $template_value ) . '"';
        }
        $shortcode .= ']';

        $recipe_html = do_shortcode( $shortcode );

        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
        $parent_attrs = $parent->attrs ?? [];

        return Module::render(
            [
                'orderIndex'         => $block->parsed_block['orderIndex'],
                'storeInstance'      => $block->parsed_block['storeInstance'],
                'attrs'              => $attrs,
                'elements'           => $elements,
                'id'                 => $block->parsed_block['id'],
                'name'               => $block->block_type->name,
                'moduleCategory'     => $block->block_type->category,
                'classnamesFunction' => null,
                'stylesComponent'    => [ self::class, 'module_styles' ],
                'parentAttrs'        => $parent_attrs,
                'parentId'           => $parent->id ?? '',
                'parentName'         => $parent->blockName ?? '',
                'children'           => [
                    ElementComponents::component(
                        [
                            'attrs'         => $attrs['module']['decoration'] ?? [],
                            'id'            => $block->parsed_block['id'],
                            'orderIndex'    => $block->parsed_block['orderIndex'],
                            'storeInstance' => $block->parsed_block['storeInstance'],
                        ]
                    ),
                    HTMLUtility::render(
                        [
                            'tag'               => 'div',
                            'attributes'        => [
                                'class' => 'wprm-divi5-recipe__preview',
                            ],
                            // No sanitizer needed - shortcode output is already sanitized by WPRM
                            'childrenSanitizer' => [ self::class, 'passthrough_sanitizer' ],
                            'children'          => $recipe_html,
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Mirror JS ModuleStyles helper.
     *
     * @param array $args Function args from Divi runtime.
     */
    public static function module_styles( $args ) {
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];

        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => [
                    $elements->style(
                        [
                            'attrName'   => 'module',
                            'styleProps' => [
                                'disabledOn' => [
                                    'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                                ],
                            ],
                        ]
                    ),
                ],
            ]
        );
    }
}
