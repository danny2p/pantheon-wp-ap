const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const {
    Button,
    ToolbarGroup,
    ToolbarButton,
} = wp.components;
const { Fragment } = wp.element;

// Backwards compatibility.
let BlockControls;
if ( wp.hasOwnProperty( 'blockEditor' ) ) {
	BlockControls = wp.blockEditor.BlockControls;
} else {
	BlockControls = wp.editor.BlockControls;
}

let ServerSideRender;
if ( wp.hasOwnProperty( 'serverSideRender' ) ) {
    ServerSideRender = wp.serverSideRender;
} else {
    ServerSideRender = wp.components.ServerSideRender;
}

import Sidebar from './Sidebar';

import '../../../css/blocks/recipe.scss';

const cleanUpShortcodeAttribute = (value) => {
    value = value.replace(/"/gm, '%22');
    value = value.replace(/\[/gm, '%5B');
    value = value.replace(/\]/gm, '%5D');
    value = value.replace(/\r?\n|\r/gm, '%0A');
    return value;
}

registerBlockType( 'wp-recipe-maker/recipe-roundup-item', {
    title: __( 'WPRM Recipe Roundup Item', 'wp-recipe-maker' ),
    description: __( 'Output your Recipe Roundup as ItemList metadata.', 'wp-recipe-maker' ),
    icon: 'media-document',
    keywords: [ 'wprm', 'wp recipe maker' ],
    example: {
		attributes: {
            id: 0,
            link: 'https://bootstrapped.ventures',
            name: 'Demo Recipe',
            summary: 'This is a demo recipe.',
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
                tag: 'wprm-recipe-roundup-item',
                attributes: {
                    id: {
                        type: 'number',
                        shortcode: ( { named: { id = '' } } ) => {
                            const parsedId = parseInt( id.replace( 'id', '' ) );
                            return isNaN( parsedId ) ? 0 : parsedId;
                        },
                    },
                    link: {
                        type: 'string',
                        shortcode: ( { named: { link = '' } } ) => {
                            return link.replace( 'link', '' );
                        },
                    },
                    nofollow: {
                        type: 'string',
                        shortcode: ( { named: { nofollow = '' } } ) => {
                            return nofollow.replace( 'nofollow', '' );
                        },
                    },
                    newtab: {
                        type: 'string',
                        shortcode: ( { named: { newtab = '' } } ) => {
                            return newtab.replace( 'newtab', '' );
                        },
                    },
                    image: {
                        type: 'number',
                        shortcode: ( { named: { image = '' } } ) => {
                            const parsedImage = parseInt( image.replace( 'image', '' ) );
                            return isNaN( parsedImage ) ? 0 : parsedImage;
                        },
                    },
                    image_url: {
                        type: 'string',
                        shortcode: ( { named: { image_url = '' } } ) => {
                            return image_url.replace( 'image_url', '' );
                        },
                    },
                    name: {
                        type: 'string',
                        shortcode: ( { named: { name = '' } } ) => {
                            return name.replace( 'name', '' );
                        },
                    },
                    summary: {
                        type: 'string',
                        shortcode: ( { named: { summary = '' } } ) => {
                            return summary.replace( 'summary', '' );
                        },
                    },
                    button: {
                        type: 'string',
                        shortcode: ( { named: { button = '' } } ) => {
                            return button.replace( 'button', '' );
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

        const modalCallback = ( fields ) => {
            setAttributes({
                id: 'external' !== fields.type ? fields.recipe.id : 0,
                link: fields.link,
                nofollow: fields.nofollow ? '1' : '',
                newtab: fields.newtab ? '1' : '',
                image: parseInt( fields.image.id ),
                image_url: fields.image.url,
                name: fields.name,
                button: fields.button,
                summary: fields.summary.replace(/\r?\n|\r/gm, '%0A'),
            });
        }

        return (
            <div className={ className }>{
                attributes.id || attributes.link
                ?
                <Fragment>
                    <Sidebar {...props} />
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon="edit"
                                label={ __( 'Edit Recipe', 'wp-recipe-maker' ) }
                                onClick={ () => {
                                    WPRM_Modal.open( 'roundup', {
                                        fields: {
                                            roundup: attributes,
                                        },
                                        insertCallback: ( fields ) => {
                                            modalCallback( fields );
                                        },
                                    } );
                                } }
                            />
                        </ToolbarGroup>
                    </BlockControls>
                    <ServerSideRender
                        block="wp-recipe-maker/recipe-roundup-item"
                        attributes={ attributes }
                    />
                </Fragment>
                :
                <Fragment>
                    <h2>WPRM { __( 'Recipe Roundup Item', 'wp-recipe-maker' ) }</h2>
                    <Button
                        isLarge
                        onClick={ () => {
                            WPRM_Modal.open( 'roundup', {
                                insertCallback: ( fields ) => {
                                    modalCallback( fields );
                                },
                            } );
                        }}>
                        { __( 'Select Recipe', 'wp-recipe-maker' ) }
                    </Button>
                </Fragment>
            }</div>
        )
    },
    save: (props) => {
        const { attributes } = props;

        if ( attributes.id ) {
            let shortcode = `[wprm-recipe-roundup-item id="${attributes.id}"`;
            if ( attributes.template ) {
                shortcode += ` template="${attributes.template}"`;
            }
            if ( attributes.name ) {
                shortcode += ` name="${ cleanUpShortcodeAttribute( attributes.name ) }"`;
            }
            if ( attributes.summary ) {
                shortcode += ` summary="${ cleanUpShortcodeAttribute( attributes.summary ) }"`;
            }
            if ( attributes.button ) {
                shortcode += ` button="${ cleanUpShortcodeAttribute( attributes.button ) }"`;
            }
            shortcode += ']';
            return shortcode;
        } else if ( attributes.link ) {
            let shortcode = `[wprm-recipe-roundup-item link="${ cleanUpShortcodeAttribute( attributes.link )}"`;
            
            shortcode += attributes.nofollow ? ' nofollow="1"' : '';
            shortcode += attributes.newtab ? '' : ' newtab="0"';
            shortcode += ` name="${ cleanUpShortcodeAttribute( attributes.name ) }"`;
            shortcode += ` summary="${ cleanUpShortcodeAttribute( attributes.summary ) }"`;

            if ( attributes.button ) {
                shortcode += ` button="${ cleanUpShortcodeAttribute( attributes.button ) }"`;
            }

            shortcode += attributes.image ? ` image="${ attributes.image }"` : '';

            if ( -1 === attributes.image && attributes.image_url ) {
                shortcode += attributes.image_url ? ` image_url="${ attributes.image_url }"` : '';
            }
            
            shortcode += ']';
            return shortcode;
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
                link: {
                    type: 'string',
                    default: '',
                },
                nofollow: {
                    type: 'string',
                    default: '',
                },
                newtab: {
                    type: 'string',
                    default: '1',
                },
                image: {
                    type: 'number',
                    default: 0,
                },
                name: {
                    type: 'string',
                    default: '',
                },
                summary: {
                    type: 'string',
                    default: '',
                },
                template: {
                    type: 'string',
                    default: '',
                },
            },
            supports: {
                html: false,
            },
            save: (props) => {
                const { attributes } = props;

                if ( attributes.id ) {
                    let shortcode = `[wprm-recipe-roundup-item id="${attributes.id}"`;
                    if ( attributes.template ) {
                        shortcode += ` template="${attributes.template}"`;
                    }
                    shortcode += ']';
                    return shortcode;
                } else if ( attributes.link ) {
                    let shortcode = `[wprm-recipe-roundup-item link="${ cleanUpShortcodeAttribute( attributes.link )}"`;
                    
                    shortcode += attributes.nofollow ? ' nofollow="1"' : '';
                    shortcode += attributes.newtab ? '' : ' newtab="0"';
                    shortcode += attributes.image ? ` image="${ attributes.image }"` : '';
                    shortcode += ` name="${ cleanUpShortcodeAttribute( attributes.name ) }"`;
                    shortcode += ` summary="${ cleanUpShortcodeAttribute( attributes.summary ) }"`;
                    
                    shortcode += ']';
                    return shortcode;
                } else {
                    return null;
                }
            },
        }
    ],
} );