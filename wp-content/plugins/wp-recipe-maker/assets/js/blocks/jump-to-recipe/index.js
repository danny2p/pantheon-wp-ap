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

registerBlockType( 'wp-recipe-maker/jump-to-recipe', {
    apiVersion: 3,
    title: __( 'Jump to Recipe', 'wp-recipe-maker' ),
    description: __( 'A button to jump to a WPRM Recipe on the same page.', 'wp-recipe-maker' ),
    icon: 'button',
    keywords: [ 'wprm' ],
    category: 'wp-recipe-maker',
    supports: {
		html: false,
    },
    example: {
		attributes: {
            id: -1,
		},
	},
    transforms: {
        from: [
            {
                type: 'shortcode',
                tag: 'wprm-recipe-jump',
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