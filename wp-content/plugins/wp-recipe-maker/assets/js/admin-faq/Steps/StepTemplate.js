import React, { Component, Fragment } from 'react';
import { __wprm } from 'Shared/Translations';

import PreviewTemplate from '../../admin-template/main/preview-template';
import Api from 'Shared/Api';

export default class StepTemplate extends Component {

    constructor(props) {
        super(props);

        let template = false;
        if ( wprm_admin_template.templates.hasOwnProperty( 'meadow' ) ) {
            template = wprm_admin_template.templates['meadow'];
        }

        this.state = {
            template,
        }
    }

    render() {
        let templates = []

        // Put templates in correct categories.
        Object.values(wprm_admin_template.templates).forEach((template) => {    
            if ( 'recipe' === template.type && 'excerpt' !== template.slug && 'compact-howto' !== template.slug ) {
                templates.push(template);
            }
        });
        
        return (
            <div className="wprm-admin-onboarding-step-template">
                <p>
                    { __wprm( 'WP Recipe Maker includes a' ) } <strong>{ __wprm( 'full Template Editor to customize the entire look and feel of your recipes' ) }</strong> { __wprm( 'to match your needs. You can access it through the' ) } <em>{ __wprm( 'WP Recipe Maker > Settings' ) }</em> { __wprm( 'page.' ) }
                </p>
                <p>
                    { __wprm( 'For now, start by choosing one of our default templates. You can dive deeper into customization later.' ) }
                </p>
                <h2>{ __wprm( 'Select a template for your recipes' ) }</h2>
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
                        </Fragment>
                    }
                </div>
                <div className="footer-buttons">
                    <button
                        type="button"
                        className="button"
                        id="prev-button"
                        onClick={() => {
                            this.props.jumpToStep(1);
                        }}
                    >{ __wprm( 'Previous' ) }</button>
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
                                    recipe_template_mode: 'modern',
                                    default_recipe_template_modern: this.state.template.slug,
                                });
                                this.props.jumpToStep(3);
                            }
                        }}
                    >{ __wprm( 'Use the above template' ) }</button>
                </div>
            </div>
        );
    }
}
