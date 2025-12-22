const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

// Backwards compatibility.
let RichText;
let useBlockProps;
if ( wp.hasOwnProperty( 'blockEditor' ) ) {
	RichText = wp.blockEditor.RichText;
	useBlockProps = wp.blockEditor.useBlockProps;
} else {
	RichText = wp.editor.RichText;
	useBlockProps = wp.blockEditor ? wp.blockEditor.useBlockProps : ( () => ( { className: '' } ) );
}

import '../../../css/public/snippets.scss';

registerBlockType( 'wp-recipe-maker/print-recipe', {
    apiVersion: 3,
    title: __( 'Print Recipe', 'wp-recipe-maker' ),
    description: __( 'A button to print a WPRM Recipe.', 'wp-recipe-maker' ),
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
        const { attributes, setAttributes, isSelected } = props;
        const { text } = attributes;
        const blockProps = useBlockProps();

        const richToString = ( text ) => {
            setAttributes( {
                text: text[0],
            } );
        }

        return (
            <div { ...blockProps }>
                <RichText
                    tagName="a"
                    placeholder="Link Text"
                    value={ [text] }
                    onChange={ ( nextValue ) => richToString( nextValue ) }
                    multiline={ false }
                    allowedFormats={ [] }
                />
            </div>
        )
    },
    save: (props) => {
        return null;
    },
} );