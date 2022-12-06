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

registerBlockType( 'wp-recipe-maker/recipe-part', {
    title: __( 'Recipe Part', 'wp-recipe-maker' ),
    description: __( 'Display a specific recipe part', 'wp-recipe-maker' ),
    icon: 'shortcode',
    keywords: [],
    example: {
		attributes: {
            id: -1,
            part: 'recipe-name',
		},
	},
    category: 'wp-recipe-maker',
    supports: {
		html: false,
    },
    edit: (props) => {
        const { attributes, setAttributes, isSelected, className } = props;

        const partOptions = [
            { label: 'Add to Collection Button', value: 'recipe-add-to-collection' },
            { label: 'Adjustable Servings', value: 'recipe-adjustable-servings' },
            { label: 'Author', value: 'recipe-author' },
            { label: 'Cost', value: 'recipe-cost' },
            { label: 'Counter', value: 'recipe-counter' },
            { label: 'Email Share', value: 'recipe-email-share' },
            { label: 'Equipment', value: 'recipe-equipment' },
            { label: 'Facebook Share', value: 'recipe-facebook-share' },
            { label: 'Grow.me Button', value: 'recipe-grow.me' },
            { label: 'Image', value: 'recipe-image' },
            { label: 'Ingredients', value: 'recipe-ingredients' },
            { label: 'Instructions', value: 'recipe-instructions' },
            { label: 'Media Toggle', value: 'recipe-media-toggle' },
            { label: 'Name', value: 'recipe-name' },
            { label: 'Notes', value: 'recipe-notes' },
            { label: 'Pin Button', value: 'recipe-pin' },
            { label: 'Rating', value: 'recipe-rating' },
            { label: 'Servings', value: 'recipe-servings' },
            { label: 'Summary', value: 'recipe-summary' },
            { label: 'Text Share', value: 'recipe-text-share' },
            { label: 'Unit Conversion', value: 'recipe-unit-conversion' },
            { label: 'Video', value: 'recipe-video' },
        ];

        return (
            <div className={ className }>
                <InspectorControls>
                    <PanelBody title={ __( 'Recipe Part Details', 'wp-recipe-maker' ) }>
                        <TextControl
                            label={ __( 'Recipe ID', 'wp-recipe-maker' ) }
                            help={ __( 'Leave blank to use the first recipe on the page', 'wp-recipe-maker' ) }
                            value={ attributes.id }
                            onChange={ (id) => {
                                let newId = parseInt( id );

                                if ( isNaN( newId) || newId <= 0 ) {
                                    newId = '';
                                }

                                setAttributes({
                                    id: newId,
                                })
                            } }
                        />
                        <SelectControl
                            label={ __( 'Recipe Part', 'wp-recipe-maker' ) }
                            value={ attributes.part }
                            options={ partOptions }
                            onChange={ (part) => setAttributes({
                                part,
                            }) }
                        />
                    </PanelBody>
                </InspectorControls>
                <ServerSideRender
                    block="wp-recipe-maker/recipe-part"
                    attributes={ attributes }
                />
            </div>
        )
    },
    save: (props) => {
        return null;
    },
} );