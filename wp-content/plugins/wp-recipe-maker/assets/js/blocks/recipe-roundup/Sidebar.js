const { __ } = wp.i18n;
const {
    PanelBody,
    TextControl,
    TextareaControl,
    SelectControl,
} = wp.components;
const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;

// Backwards compatibility.
let InspectorControls;
if ( wp.hasOwnProperty( 'blockEditor' ) ) {
	InspectorControls = wp.blockEditor.InspectorControls;
} else {
	InspectorControls = wp.editor.InspectorControls;
}

function Sidebar( props ) {
    const { attributes, setAttributes, name, onChangeName, description, onChangeDescription, recipeRoundupCount } = props;

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
        <InspectorControls>
            <p>
                <a href="https://help.bootstrapped.ventures/article/182-itemlist-metadata-for-recipe-roundup-posts" target="_blank">{ __( 'Learn more', 'wp-recipe-maker' ) }</a>
            </p>
            <PanelBody title={ __( 'Recipe Roundup', 'wp-recipe-maker' ) }>
                <TextControl
                    label={ __( 'Name', 'wp-recipe-maker' ) }
                    value={ name }
                    onChange={ onChangeName }
                />
                <TextareaControl
                    label={ __( 'Description', 'wp-recipe-maker' ) }
                    value={ description }
                    onChange={ onChangeDescription }
                />
                <TextControl
                    label={ __( 'Number of Roundup Recipes', 'wp-recipe-maker' ) }
                    value={ recipeRoundupCount }
                    disabled
                />

            </PanelBody>
            <PanelBody title={ __( 'Recipe Details', 'wp-recipe-maker' ) }>
                {
                    attributes.id
                    ?
                    <TextControl
                        label={ __( 'Recipe ID', 'wp-recipe-maker' ) }
                        value={ attributes.id }
                        disabled
                    />
                    :
                    null
                }
                {
                    attributes.link
                    ?
                    <TextControl
                        label={ __( 'Recipe Link', 'wp-recipe-maker' ) }
                        value={ attributes.link }
                        disabled
                    />
                    :
                    null
                }
                <SelectControl
                    label={ __( 'Recipe Template', 'wp-recipe-maker' ) }
                    value={ attributes.template }
                    options={ templateOptions }
                    onChange={ (template) => setAttributes({
                        template,
                    }) }
                />
            </PanelBody>
        </InspectorControls>
    )
}

const applyWithSelect = withSelect( ( select, ownProps ) => {
    const meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' );
    const { getGlobalBlockCount } = select( 'core/block-editor' );

    const nameMeta = meta['wprm-recipe-roundup-name'];
    const name = nameMeta instanceof Array ? nameMeta[0] : nameMeta;

    const descriptionMeta = meta['wprm-recipe-roundup-description'];
    const description = descriptionMeta instanceof Array ? descriptionMeta[0] : descriptionMeta;
    
    return {
        meta,
        name,
        description,
        recipeRoundupCount: getGlobalBlockCount( 'wp-recipe-maker/recipe-roundup-item' ),
    }
} );

const applyWithDispatch = withDispatch( ( dispatch, ownProps ) => {
    const { editPost } = dispatch( 'core/editor' );

    return {
        onChangeName: ( name ) => {
            let meta = {
                ...ownProps.meta,
            };
            meta['wprm-recipe-roundup-name'] = name;
            return editPost( { meta } );
        },
        onChangeDescription: ( description ) => {
            let meta = {
                ...ownProps.meta,
            };
            meta['wprm-recipe-roundup-description'] = description;
            return editPost( { meta } );
        },
    }
} );

export default compose(
    applyWithSelect,
    applyWithDispatch
)( Sidebar );
