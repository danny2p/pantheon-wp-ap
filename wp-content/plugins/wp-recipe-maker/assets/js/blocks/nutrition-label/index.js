const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const {
    BlockControls,
    AlignmentToolbar,
} = wp.editor;
const { Fragment } = wp.element;

import '../../../css/blocks/nutrition-label.scss';

registerBlockType( 'wp-recipe-maker/nutrition-label', {
    title: __( 'Nutrition Label' ),
    description: __( 'The nutrition label for a WPRM Recipe.' ),
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
        const { attributes, setAttributes, isSelected, className } = props;
        const { align } = attributes;

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
                <div className={ className } style={ { textAlign: align } }>
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