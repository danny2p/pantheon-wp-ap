import React, { Component, Fragment } from 'react';
import { __wprm } from 'Shared/Translations';

import PreviewTemplate from '../../admin-template/main/preview-template';
import Api from 'Shared/Api';

export default class StepSnippets extends Component {

    constructor(props) {
        super(props);

        let template = false;
        if ( wprm_admin_template.templates.hasOwnProperty( 'snippet-basic-buttons' ) ) {
            template = wprm_admin_template.templates['snippet-basic-buttons'];
        }

        this.state = {
            template,
        }
    }

    render() {
        let templates = []

        // Put templates in correct categories.
        Object.values(wprm_admin_template.templates).forEach((template) => {    
            if ( 'snippet' === template.type ) {
                templates.push(template);
            }
        });
        
        return (
            <div className="wprm-admin-onboarding-step-snippet">
                <p>
                    { __wprm( 'Most people have content before the actual recipe, often with extra information, story, or ads in between. You want visitors to read this, but if they are in a hurry you can still' ) } <strong>{ __wprm( 'give them the option to jump directly to the recipe' ) }</strong>.
                </p>
                <p>
                    { __wprm( 'That is where the Recipe Snippets feature comes in. Snippets usually contain "Jump to Recipe" and "Print Recipe" buttons, but can include any fields you want. Have a look at the' ) } <em>{ __wprm( 'Snippet Summary' ) }</em> { __wprm( 'template below.' ) }
                </p>
                <p>
                    { __wprm( 'These snippets are also' ) } <strong>{ __wprm( 'fully customizable in the Template Editor' ) }</strong>. { __wprm( 'You can change colors, text, and add more information later.' ) }
                </p>
                <h2>{ __wprm( 'Select a snippet template' ) }</h2>
                <div className="wprm-admin-onboarding-step-template-select">
                    {
                        templates.map((template, index) => {
                            let classes = 'wprm-manage-templates-template';
                            classes += false !== this.state.template && this.state.template.slug === template.slug ? ' wprm-manage-templates-template-selected' : '';
                            classes += template.premium && ! wprm_admin.addons.premium ? ' wprm-manage-templates-template-premium' : '';

                            return (
                                <div
                                    key={index}
                                    className={ classes }
                                    onClick={ () => {
                                        this.setState({
                                            template,
                                        });
                                    }}
                                >{ template.name }</div>
                            )
                        })
                    }
                </div>
                <div className="wprm-admin-onboarding-step-template-preview">
                    {
                        false !== this.state.template
                        &&
                        <Fragment>
                            {
                                this.state.template.premium && ! wprm_admin.addons.premium
                                &&
                                <p style={{
                                    color: 'darkred',
                                    textAlign: 'center',
                                }}>{ __wprm( 'You need' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank" rel="noopener noreferrer">{ __wprm( 'WP Recipe Maker Premium' ) }</a> { __wprm( 'to use this template.' ) }</p>
                            }
                            <PreviewTemplate
                                template={ this.state.template }
                                mode={ 'onboarding' }
                                onChangeMode={() => {}}
                                onChangeHTML={() => {}}
                            />
                            <p>{ __wprm( 'This would be the start of your regular post content, so the snippet appears right at the top of your post.' ) }</p>
                        </Fragment>
                    }
                </div>
                <div className="footer-buttons">
                    <button
                        type="button"
                        className="button"
                        id="prev-button"
                        onClick={() => {
                            this.props.jumpToStep(2);
                        }}
                    >{ __wprm( 'Previous' ) }</button>
                    <button
                        type="button"
                        className="button button-primary"
                        id="skip-button"
                        onClick={() => {
                            this.props.jumpToStep(4);
                        }}
                    >{ __wprm( 'Do not enable snippets right now' ) }</button>
                    <button
                        type="button"
                        className="button button-primary"
                        id="next-button"
                        onClick={() => {
                            if ( ! this.state.template ) {
                                alert( __wprm( 'Please select a template above.' ) );
                            } else if ( this.state.template.premium && ! wprm_admin.addons.premium ) {
                                alert( __wprm( 'This template is only available in WP Recipe Maker Premium.' ) );
                            } else {
                                Api.settings.save({
                                    recipe_snippets_automatically_add_modern: true,
                                    recipe_snippets_template: this.state.template.slug,
                                });
                                this.props.jumpToStep(4);
                            }
                        }}
                    >{ __wprm( 'Use the above snippet template' ) }</button>
                </div>
            </div>
        );
    }
}
