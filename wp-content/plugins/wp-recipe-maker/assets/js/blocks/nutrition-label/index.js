const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;

// Backwards compatibility.
let BlockControls;
let AlignmentToolbar;
let useBlockProps;
if ( wp.hasOwnProperty( 'blockEditor' ) ) {
	BlockControls = wp.blockEditor.BlockControls;
	AlignmentToolbar = wp.blockEditor.AlignmentToolbar;
	useBlockProps = wp.blockEditor.useBlockProps;
} else {
	BlockControls = wp.editor.BlockControls;
	AlignmentToolbar = wp.editor.AlignmentToolbar;
	useBlockProps = wp.blockEditor ? wp.blockEditor.useBlockProps : ( () => ( { className: '' } ) );
}

import '../../../css/blocks/nutrition-label.scss';

registerBlockType( 'wp-recipe-maker/nutrition-label', {
    apiVersion: 3,
    title: __( 'Nutrition Label', 'wp-recipe-maker' ),
    description: __( 'The nutrition label for a WPRM Recipe.', 'wp-recipe-maker' ),
    icon: 'analytics',
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
                tag: 'wprm-nutrition-label',
                attributes: {
                    id: {
                        type: 'number',
                        shortcode: ( { named: { id = '' } } ) => {
                            return parseInt( id.replace( 'id', '' ) );
                        },
                    },
                    align: {
                        type: 'string',
                        shortcode: ( { named: { align = '' } } ) => {
                            return align.replace( 'align', '' );
                        },
                    },
                },
            },
        ]
    },
    edit: (props) => {
        const { attributes, setAttributes, isSelected } = props;
        const { align } = attributes;
        const blockProps = useBlockProps( { style: { textAlign: align } } );

        return (
            <Fragment>
                <BlockControls>
					<AlignmentToolbar
						value={ align }
						onChange={ ( nextAlign ) => {
							setAttributes( { align: nextAlign } );
						} }
					/>
				</BlockControls>
                <div { ...blockProps }>
                    <div className="wprm-nutrition-label-placeholder">
                        WPRM Nutrition Label Placeholder
                    </div>
                </div>
            </Fragment>
        )
    },
    save: (props) => {
        return null;
    },
} );