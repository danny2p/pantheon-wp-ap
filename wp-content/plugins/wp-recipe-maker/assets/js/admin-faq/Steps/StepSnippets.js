import React, { Component, Fragment } from 'react';

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
                    Most people have content before the actual recipe. Often, there are some paragrahs with additional information or backstory. Maybe a few ads in between? You want people to read this, but if they are in a hurry you could <strong>give your visitors the option to jump directly to the recipe as well</strong>!
                </p>
                <p>
                    That's where the Recipe Snippets feature comes in. These snippets usually contain a "Jump to Recipe" and "Print Recipe" button but can include any field you want, really. Have a look at the <em>Snippet Summary</em> template below, for example.
                </p>
                <p>
                    These snippets are <strong>fully customizable in the Template Editor</strong> as well. So you can change colors, text and add more information afterwards.
                </p>
                <h2>Select a snippet template</h2>
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
                                }}>You need <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium</a> to use this template.</p>
                            }
                            <PreviewTemplate
                                template={ this.state.template }
                                mode={ 'onboarding' }
                                onChangeMode={() => {}}
                                onChangeHTML={() => {}}
                            />
                            <p>This would be the start of your regular post content, so the snippet appears right at the top of your post.</p>
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
                    >Previous</button>
                    <button
                        type="button"
                        className="button button-primary"
                        id="skip-button"
                        onClick={() => {
                            this.props.jumpToStep(4);
                        }}
                    >Do not enable snippets right now</button>
                    <button
                        type="button"
                        className="button button-primary"
                        id="next-button"
                        onClick={() => {
                            if ( ! this.state.template ) {
                                alert( 'Please select a template above.' );
                            } else if ( this.state.template.premium && ! wprm_admin.addons.premium ) {
                                alert( 'This template is only available in WP Recipe Maker Premium.' );
                            } else {
                                Api.settings.save({
                                    recipe_snippets_automatically_add_modern: true,
                                    recipe_snippets_template: this.state.template.slug,
                                });
                                this.props.jumpToStep(4);
                            }
                        }}
                    >Use the above Snippet Template</button>
                </div>
            </div>
        );
    }
}