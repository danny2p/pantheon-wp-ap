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

registerBlockType( 'wp-recipe-maker/list', {
    title: __( 'WPRM Roundup List', 'wp-recipe-maker' ),
    description: __( 'Display a recipe roundup list with optional itemlist metadata.', 'wp-recipe-maker' ),
    icon: 'list-view',
    keywords: [ 'wprm', 'wp recipe maker' ],
    category: 'wp-recipe-maker',
    supports: {
        html: false,
        align: true,
    },
    transforms: {
        from: [
            {
                type: 'shortcode',
                tag: 'wprm-list',
                attributes: {
                    id: {
                        type: 'number',
                        shortcode: ( { named: { id = '' } } ) => {
                            return parseInt( id.replace( 'id', '' ) );
                        },
                    },
                },
            },
        ]
    },
    edit: (props) => {
        const { attributes, setAttributes, isSelected, className } = props;

        const modalCallback = ( list ) => {
            setAttributes({
                id: list.id,
                updated: Date.now(),
            });
        };

        return (
            <div className={ className }>{
                attributes.id
                ?
                <Fragment>
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon="edit"
                                label={ __( 'Edit List', 'wp-recipe-maker' ) }
                                onClick={
                                    () => {
                                        WPRM_Modal.open( 'list', {
                                            listId: attributes.id,
                                            saveCallback: modalCallback,
                                        } );
                                    }
                                }
                            />
                        </ToolbarGroup>
                    </BlockControls>
                    <InspectorControls>
                        <PanelBody title={ __( 'List Details', 'wp-recipe-maker' ) }>
                            <TextControl
                                label={ __( 'List ID', 'wp-recipe-maker' ) }
                                value={ attributes.id }
                                disabled
                            />
                            <PanelRow>
                                <Button
                                    variant="secondary"
                                    onClick={ () => {
                                        WPRM_Modal.open( 'list', {
                                            listId: attributes.id,
                                            saveCallback: modalCallback,
                                        } );
                                    }}>
                                    { __( 'Edit List', 'wp-recipe-maker' ) }
                                </Button>
                            </PanelRow>
                        </PanelBody>
                    </InspectorControls>
                    <ServerSideRender
                        block="wp-recipe-maker/list"
                        attributes={ attributes }
                    />
                </Fragment>
                :
                <Fragment>
                    <h2>WPRM { __( 'List', 'wp-recipe-maker' ) }</h2>
                    <Button
                        variant="primary"
                        onClick={ () => {
                            WPRM_Modal.open( 'list', {
                                saveCallback: modalCallback,
                            } );
                        }}>
                        { __( 'Create new List', 'wp-recipe-maker' ) }
                    </Button> <Button
                        variant="secondary"
                        onClick={ () => {
                            WPRM_Modal.open( 'select', {
                                title: __( 'Insert existing List', 'wp-recipe-maker' ),
                                button: __( 'Insert', 'wp-recipe-maker' ),
                                type: 'list',
                                insertCallback: ( fields ) => {
                                    modalCallback( fields.list );
                                },
                            } );
                        }}>
                        { __( 'Insert existing List', 'wp-recipe-maker' ) }
                    </Button> {
                        wprm_admin.addons.premium
                        &&
                        <Button
                            variant="secondary"
                            onClick={ () => {
                                WPRM_Modal.open( 'select', {
                                    title: __( 'Create new from existing List', 'wp-recipe-maker' ),
                                    button: __( 'Clone List', 'wp-recipe-maker' ),
                                    type: 'list',
                                    nextStepCallback: ( fields ) => {
                                        WPRM_Modal.open( 'list', {
                                            listId: fields.list.id,
                                            cloneList: true,
                                            saveCallback: modalCallback,
                                        }, true );
                                    },
                                } );
                            }}>
                            { __( 'Create new from existing List', 'wp-recipe-maker' ) }
                        </Button>
                    }
                </Fragment>
            }</div>
        )
    },
    save: (props) => {
        const { attributes } = props;

        if ( attributes.id ) {
            return `[wprm-list id="${props.attributes.id}"]`;
        } else {
            return null;
        }
    },
} );