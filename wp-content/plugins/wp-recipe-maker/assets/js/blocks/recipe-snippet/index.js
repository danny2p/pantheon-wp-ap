const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const {
    Button,
    PanelBody,
    Toolbar,
    TextControl,
    SelectControl,
} = wp.components;
const { Fragment } = wp.element;

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

registerBlockType( 'wp-recipe-maker/recipe-snippet', {
    title: __( 'Recipe Snippet', 'wp-recipe-maker' ),
    description: __( 'Displays the recipe snippet template. Usually used for a Jump to Recipe button at the top of the post content.', 'wp-recipe-maker' ),
    icon: 'button',
    keywords: [],
    example: {
		attributes: {
            id: -1,
		},
	},
    category: 'wp-recipe-maker',
    supports: {
		html: false,
    },
    transforms: {
        from: [
            {
                type: 'shortcode',
                tag: 'wprm-recipe-snippet',
                attributes: {
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

        let templateOptions = [
            { label: 'Use default from settings', value: '' },
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
            <div className={ className }>
                <InspectorControls>
                    <PanelBody title={ __( 'Recipe Snippet Details', 'wp-recipe-maker' ) }>
                        <SelectControl
                            label={ __( 'Recipe Snippet Template', 'wp-recipe-maker' ) }
                            value={ attributes.template }
                            options={ templateOptions }
                            onChange={ (template) => setAttributes({
                                template,
                            }) }
                        />
                    </PanelBody>
                </InspectorControls>
                <ServerSideRender
                    block="wp-recipe-maker/recipe-snippet"
                    attributes={ attributes }
                />
            </div>
        )
    },
    save: (props) => {
        return null;
    },
} );