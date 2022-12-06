const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const {
    Button,
    PanelBody,
    PanelRow,
    ToolbarGroup,
    ToolbarButton,
    TextControl,
    SelectControl,
} = wp.components;
const { Fragment } = wp.element;
const { select } = wp.data;

// Backwards compatibility.
let InspectorControls;
let BlockControls;
if ( wp.hasOwnProperty( 'blockEditor' ) ) {
	InspectorControls = wp.blockEditor.InspectorControls;
	BlockControls = wp.blockEditor.BlockControls;
} else {
	InspectorControls = wp.editor.InspectorControls;
	BlockControls = wp.editor.BlockControls;
}

let ServerSideRender;
if ( wp.hasOwnProperty( 'serverSideRender' ) ) {
    ServerSideRender = wp.serverSideRender;
} else {
    ServerSideRender = wp.components.ServerSideRender;
}

import '../../../css/blocks/recipe.scss';

registerBlockType( 'wp-recipe-maker/recipe', {
    title: __( 'WPRM Recipe', 'wp-recipe-maker' ),
    description: __( 'Display a recipe box with recipe metadata.', 'wp-recipe-maker' ),
    icon: 'media-document',
    keywords: [ 'wprm', 'wp recipe maker' ],
    example: {
		attributes: {
            id: -1,
		},
	},
    category: 'wp-recipe-maker',
    supports: {
        html: false,
        align: true,
    },
    transforms: {
        from: [
            {
                type: 'shortcode',
                tag: 'wprm-recipe',
                attributes: {
                    id: {
                        type: 'number',
                        shortcode: ( { named: { id = '' } } ) => {
                            return parseInt( id.replace( 'id', '' ) );
                        },
                    },
                    template: {
                        type: 'string',
                        shortcode: ( { named: { template = '' } } ) => {
                            return template.replace( 'template', '' );
                        },
                    },
                },
            },
        ]
    },
    edit: (props) => {
        const { attributes, setAttributes, isSelected, className } = props;

        const modalCallback = ( recipe ) => {
            setAttributes({
                id: recipe.id,
                updated: Date.now(),
            });
        };

        let templateOptions = [
            { label: __( 'Use default from settings', 'wp-recipe-maker' ), value: '' },
        ];
        const templates = wprm_admin.recipe_templates.modern;

        for (let template in templates) {
            // Don't show Premium templates in list if we're not Premium.
            if ( ! templates[template].premium || wprm_admin.addons.premium ) {
                templateOptions.push({
                    value: template,
                    label: templates[template].name,
                });
            }
        }

        return (
            <div className={ className }>{
                attributes.id
                ?
                <Fragment>
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon="edit"
                                label={ __( 'Edit Recipe', 'wp-recipe-maker' ) }
                                onClick={
                                    () => {
                                        WPRM_Modal.open( 'recipe', {
                                            recipeId: attributes.id,
                                            saveCallback: modalCallback,
                                        } );
                                    }
                                }
                            />
                        </ToolbarGroup>
                    </BlockControls>
                    <InspectorControls>
                        <PanelBody title={ __( 'Recipe Details', 'wp-recipe-maker' ) }>
                            <TextControl
                                label={ __( 'Recipe ID', 'wp-recipe-maker' ) }
                                value={ attributes.id }
                                disabled
                            />
                            <SelectControl
                                label={ __( 'Recipe Template', 'wp-recipe-maker' ) }
                                value={ attributes.template }
                                options={ templateOptions }
                                onChange={ (template) => setAttributes({
                                    template,
                                    updated: Date.now(),
                                }) }
                            />
                            <PanelRow>
                                <Button
                                    variant="secondary"
                                    onClick={ () => {
                                        WPRM_Modal.open( 'recipe', {
                                            recipeId: attributes.id,
                                            saveCallback: modalCallback,
                                        } );
                                    }}>
                                    { __( 'Edit Recipe', 'wp-recipe-maker' ) }
                                </Button>
                            </PanelRow>
                        </PanelBody>
                    </InspectorControls>
                    <ServerSideRender
                        block="wp-recipe-maker/recipe"
                        attributes={ attributes }
                    />
                </Fragment>
                :
                <Fragment>
                    <h2>WPRM { __( 'Recipe', 'wp-recipe-maker' ) }</h2>
                    <Button
                        isPrimary
                        isLarge
                        onClick={ () => {
                            let args = {
                                saveCallback: modalCallback,
                            };

                            // Default recipe name to post title.
                            if ( wprm_admin.settings.hasOwnProperty( 'recipe_name_from_post_title' ) && wprm_admin.settings.recipe_name_from_post_title ) {
                                let recipe = JSON.parse( JSON.stringify( wprm_admin_modal.recipe ) );
                                recipe.name = select('core/editor').getEditedPostAttribute( 'title' );

                                args.recipe = recipe;
                            }

                            WPRM_Modal.open( 'recipe', args );
                        }}>
                        { __( 'Create new Recipe', 'wp-recipe-maker' ) }
                    </Button> <Button
                        isLarge
                        onClick={ () => {
                            WPRM_Modal.open( 'select', {
                                title: __( 'Insert existing Recipe', 'wp-recipe-maker' ),
                                button: __( 'Insert', 'wp-recipe-maker' ),
                                fields: {
                                    recipe: {},
                                },
                                insertCallback: ( fields ) => {
                                    modalCallback( fields.recipe );
                                },
                            } );
                        }}>
                        { __( 'Insert existing Recipe', 'wp-recipe-maker' ) }
                    </Button> {
                        wprm_admin.addons.premium
                        &&
                        <Button
                            isLarge
                            onClick={ () => {
                                WPRM_Modal.open( 'select', {
                                    title: __( 'Create new from existing Recipe', 'wp-recipe-maker' ),
                                    button: __( 'Clone Recipe', 'wp-recipe-maker' ),
                                    fields: {
                                        recipe: {},
                                    },
                                    nextStepCallback: ( fields ) => {
                                        WPRM_Modal.open( 'recipe', {
                                            recipeId: fields.recipe.id,
                                            cloneRecipe: true,
                                            saveCallback: modalCallback,
                                        }, true );
                                    },
                                } );
                            }}>
                            { __( 'Create new from existing Recipe', 'wp-recipe-maker' ) }
                        </Button>
                    }
                </Fragment>
            }</div>
        )
    },
    save: (props) => {
        const { attributes } = props;

        if ( attributes.id ) {
            return `[wprm-recipe id="${props.attributes.id}"]`;
        } else {
            return null;
        }
    },
    deprecated: [
        {
            attributes: {
                id: {
                    type: 'number',
                    default: 0,
                },
                template: {
                    type: 'string',
                    default: '',
                },
                updated: {
                    type: 'number',
                    default: 0,
                },
            },
            save: (props) => {
                return null;
            },
        }
    ],
} );