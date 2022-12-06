import React, { Component, Fragment } from 'react';

import '../../../../css/admin/template/manage.scss';

import ManageTemplate from './ManageTemplate';

export default class ManageTemplates extends Component {

    constructor(props) {
        super(props);

        this.state = {
            type: false,
        }
    }

    render() {
        const props = this.props;

        let templatesGrouped = {
            'Our Default Templates': [],
            'Theme Templates': [],
            'Your Own Templates': [],
        }
    
        // Put templates in correct categories.
        if ( false !== this.state.type ) {
            Object.entries(props.templates).forEach(([slug, template]) => {    
                if ( 'file' === template.location ) {
                    if ( template.custom ) {
                        if ( this.state.type === template.type ) {
                            templatesGrouped['Theme Templates'].push(template);
                        }
                    } else {
                        if ( this.state.type === template.type ) {
                            templatesGrouped['Our Default Templates'].push(template);
                        }
                    }
                } else {
                    if ( this.state.type === template.type ) {
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
                            ].map( ( type, index ) => (
                                <div
                                    className={ `wprm-manage-templates-type${ type.id === this.state.type ? ' wprm-manage-templates-type-selected' : '' }` }
                                    onClick={() => {
                                        if ( type.id !== this.state.type ) {
                                            this.setState({
                                                type: type.id,
                                            }, () => {
                                                props.onChangeTemplate( false );
                                            });
                                        }
                                    }}
                                    key={ index }
                                >
                                    <div className="wprm-manage-templates-type-name">{ type.name }</div>
                                    <div className="wprm-manage-templates-type-description">{ type.description }</div>
                                </div>
                            ))
                        }
                    </div>
                    <div className="wprm-manage-templates-type-container">
                        <div
                            className={ `wprm-manage-templates-type${ 'import' === this.state.type ? ' wprm-manage-templates-type-selected' : '' }` }
                            onClick={() => {
                                if ( 'import' !== this.state.type ) {
                                    this.setState({
                                        type: 'import',
                                    }, () => {
                                        props.onChangeTemplate( false );
                                    });
                                }
                            }}
                        >Import template...</div>
                    </div>
                    {
                        'import' === this.state.type
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
                                        this.setState({
                                            type: importedTemplate.type,
                                        }, () => {
                                            props.onSaveTemplate({
                                                ...importedTemplate,
                                                oldSlug: importedTemplate.slug,
                                                slug: false, // Importing, so generate new slug.
                                            });
                                            alert( 'The template has been imported.' );
                                        });
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
                                    </Fragment>
                                )
                            }
                        })
                    }
                </div>
                {
                    props.template
                    && props.template.type === this.state.type
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