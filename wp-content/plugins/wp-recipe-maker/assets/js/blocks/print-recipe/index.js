const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { RichText } = wp.editor;

import '../../../css/public/snippets.scss';

registerBlockType( 'wp-recipe-maker/print-recipe', {
    title: __( 'Print Recipe' ),
    description: __( 'A button to print a WPRM Recipe.' ),
    icon: 'button',
    keywords: [ 'wprm' ],
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
                tag: 'wprm-recipe-print',
                attributes: {
                    id: {
                        type: 'number',
                        shortcode: ( { named: { id = '' } } ) => {
                            return parseInt( id.replace( 'id', '' ) );
                        },
                    },
                    text: {
                        type: 'string',
                        shortcode: ( { named: { text = '' } } ) => {
                            return text.replace( 'text', '' );
                        },
                    },
                },
            },
        ]
    },
    edit: (props) => {
        const { attributes, setAttributes, isSelected, className } = props;
        const { text } = attributes;

        const richToString = ( text ) => {
            setAttributes( {
                text: text[0],
            } );
        }

        return (
            <div className={ className }>
                <RichText
                    tagName="a"
                    placeholder="Link Text"
                    value={ [text] }
                    onChange={ ( nextValue ) => richToString( nextValue ) }
                    multiline={ false }
                    formattingControls={ [] }
                />
            </div>
        )
    },
    save: (props) => {
        return null;
    },
} );