const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const {
    Button,
    ServerSideRender,
    PanelBody,
    Toolbar,
    TextControl,
    SelectControl,
} = wp.components;
const { Fragment } = wp.element;
const {
    InspectorControls,
    BlockControls,
} = wp.editor;

registerBlockType( 'wp-recipe-maker/recipe-snippet', {
    title: __( 'Recipe Snippet' ),
    description: __( 'Display the recipe snippet' ),
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
                    <PanelBody title={ __( 'Recipe Snippet Details' ) }>
                        <SelectControl
                            label={ __( 'Recipe Snippet Template' ) }
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