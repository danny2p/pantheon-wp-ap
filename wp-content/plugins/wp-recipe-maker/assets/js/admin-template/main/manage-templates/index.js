import React, { Component, Fragment } from 'react';

import '../../../../css/admin/template/manage.scss';

import ManageTemplate from './ManageTemplate';

export default class ManageTemplates extends Component {

    constructor(props) {
        super(props);
    }

    render() {
        const props = this.props;

        let templatesGrouped = {
            'Our Default Templates': [],
            'Theme Templates': [],
            'Your Own Templates': [],
        }
    
        // Use type from props instead of state
        const type = props.type !== undefined ? props.type : false;
        
        // Put templates in correct categories.
        if ( false !== type ) {
            Object.entries(props.templates).forEach(([slug, template]) => {    
                if ( 'file' === template.location ) {
                    if ( template.custom ) {
                        if ( type === template.type ) {
                            templatesGrouped['Theme Templates'].push(template);
                        }
                    } else {
                        if ( type === template.type ) {
                            templatesGrouped['Our Default Templates'].push(template);
                        }
                    }
                } else {
                    if ( type === template.type ) {
                        templatesGrouped['Your Own Templates'].push(template);
                    }
                }
            });
        }
    
        return (
            <Fragment>
                <div className="wprm-main-container">
                    <h2 className="wprm-main-container-name">Need help?</h2>
                    <p style={{ textAlign: 'center'}}>Have a look at the <a href="https://help.bootstrapped.ventures/article/53-template-editor" target="_blank">documentation for the Template Editor</a>!</p>
                </div>
                <div className="wprm-main-container">
                    <h2 className="wprm-main-container-name">Templates</h2>
                    <div className="wprm-manage-templates-type-container">
                        {
                            [
                                {
                                    id: 'recipe',
                                    name: 'Recipe Templates',
                                    description: 'Used for the layout of the regular recipe box. This is what your recipes look like.',
                                },
                                {
                                    id: 'snippet',
                                    name: 'Snippet Templates',
                                    description: 'Used for the layout of the recipe snippets at the top of the post, like a jump to recipe button.',
                                },
                                {
                                    id: 'roundup',
                                    name: 'Roundup Templates',
                                    description: 'Used for the layout of the recipe roundup items that can be added to posts with lists of recipes.',
                                },
                            ].map( ( templateType, index ) => (
                                <div
                                    className={ `wprm-manage-templates-type${ templateType.id === type ? ' wprm-manage-templates-type-selected' : '' }` }
                                    onClick={() => {
                                        if ( templateType.id !== type ) {
                                            // Clear template first, then change type
                                            // This ensures template is cleared before type changes
                                            props.onChangeTemplate( false );
                                            if (props.onChangeType) {
                                                props.onChangeType(templateType.id);
                                            }
                                        }
                                    }}
                                    key={ index }
                                >
                                    <div className="wprm-manage-templates-type-name">{ templateType.name }</div>
                                    <div className="wprm-manage-templates-type-description">{ templateType.description }</div>
                                </div>
                            ))
                        }
                    </div>
                    <div className="wprm-manage-templates-type-container">
                        <div
                            className={ `wprm-manage-templates-type${ 'import' === type ? ' wprm-manage-templates-type-selected' : '' }` }
                            onClick={() => {
                                if ( 'import' !== type ) {
                                    // Clear template first, then change type
                                    props.onChangeTemplate( false );
                                    if (props.onChangeType) {
                                        props.onChangeType('import');
                                    }
                                }
                            }}
                        >Import template...</div>
                    </div>
                    {
                        'import' === type
                        &&
                        <textarea
                            className="wprm-manage-templates-import"
                            placeholder="Paste in template to import"
                            rows="10"
                            value=""
                            onChange={ (e) => {
                                const value = e.target.value;
                                if ( value ) {
                                    try {
                                        const importedTemplate = JSON.parse( value );
                                        if (props.onChangeType) {
                                            props.onChangeType(importedTemplate.type);
                                        }
                                        props.onSaveTemplate({
                                            ...importedTemplate,
                                            oldSlug: importedTemplate.slug,
                                            slug: false, // Importing, so generate new slug.
                                        });
                                        alert( 'The template has been imported.' );
                                    } catch (e) {
                                        alert( 'No valid template found.' );
                                    }
                                }
                            }}
                        />
                    }
                    {
                        Object.keys(templatesGrouped).map((header, i) => {
                            let templates = templatesGrouped[header];
                            
                            // Helper function to create blank template
                            const createBlankTemplate = () => {
                                if ( props.savingTemplate ) {
                                    return; // Don't allow creating while saving
                                }
                                
                                const name = prompt( 'Choose a name for the blank template' );
                                
                                if ( name && name.trim() ) {
                                    // Create blank template
                                    // Set slug to false to let backend generate it from the name
                                    const blankTemplate = {
                                        mode: 'modern',
                                        type: type,
                                        slug: false,
                                        name: name.trim(),
                                        html: '',
                                        css: '',
                                        fonts: [],
                                        premium: false,
                                    };
                                    
                                    props.onSaveTemplate(blankTemplate);
                                }
                            };
                            
                            if ( templates.length > 0 ) {
                                return (
                                    <Fragment key={i}>
                                        <h3>{ header }</h3>
                                        {
                                            templates.map((template, j) => {
                                                let classes = 'wprm-manage-templates-template';
                                                classes += props.template.slug === template.slug ? ' wprm-manage-templates-template-selected' : '';
                                                classes += template.premium && ! wprm_admin.addons.premium ? ' wprm-manage-templates-template-premium' : '';

                                                if ( template.hasOwnProperty( 'brokenSlug' ) && template.brokenSlug ) {
                                                    classes += ' wprm-manage-templates-template-broken';
                                                }
    
                                                return (
                                                    <div
                                                        key={j}
                                                        className={ classes }
                                                        onClick={ () => {
                                                            const newTemplate = props.template.slug === template.slug ? false : template.slug;
                                                            return props.onChangeTemplate(newTemplate);
                                                        }}
                                                    >{ template.name }</div>
                                                )
                                            })
                                        }
                                        {
                                            'Your Own Templates' === header && false !== type && 'import' !== type
                                            &&
                                            <div
                                                className="wprm-manage-templates-template"
                                                style={{ fontStyle: 'italic', borderStyle: 'dashed' }}
                                                onClick={ createBlankTemplate }
                                            >+ Create Blank Template</div>
                                        }
                                    </Fragment>
                                )
                            } else if ( 'Your Own Templates' === header && false !== type && 'import' !== type ) {
                                // Show message when "Your Own Templates" is empty
                                return (
                                    <Fragment key={i}>
                                        <h3>{ header }</h3>
                                        <p style={{ margin: '20px 0' }}>
                                            Click on one of our default templates to clone and use as a starting point or{' '}
                                            <a 
                                                href="#" 
                                                onClick={ (e) => {
                                                    e.preventDefault();
                                                    createBlankTemplate();
                                                }}
                                                style={{ cursor: 'pointer', textDecoration: 'underline' }}
                                            >create a blank template</a>
                                            {' '}to start from scratch.
                                        </p>
                                    </Fragment>
                                )
                            }
                            return null;
                        })
                    }
                </div>
                {
                    props.template
                    && props.template.type === type
                    &&
                    <ManageTemplate
                        onChangeEditing={ props.onChangeEditing }
                        template={ props.template }
                        onDeleteTemplate={ props.onDeleteTemplate }
                        onChangeTemplate={ props.onChangeTemplate }
                        savingTemplate={ props.savingTemplate }
                        onSaveTemplate={ props.onSaveTemplate }
                    />
                }
            </Fragment>
        );
    }
}