import React, { Fragment } from 'react';
import CopyToClipboard from 'react-copy-to-clipboard';

import Loader from 'Shared/Loader';

const ManageTemplate = (props) => {
    const editable = 'database' === props.template.location;

    return (
        <div className="wprm-main-container">
            <h2 className="wprm-main-container-name">Selected Template</h2>
            <div className="wprm-manage-templates-template-fields">
                <span>Slug: { props.template.slug }</span> | <span>Name: { props.template.name }</span>
            </div>
            <div className="wprm-manage-templates-template-actions">
                {
                    props.template.hasOwnProperty( 'brokenSlug' ) && props.template.brokenSlug
                    &&
                    <p style={{ color: 'darkred', fontWeight: 'bold' }}>This template was created with a slug that might be breaking the CSS styling. That bug has since been fixed, but this template will need to get recreated. Simply cloning might be enough. Contact support@bootstrapped.ventures if you need more help!</p>
                }
                {
                    props.template.premium && ! wprm_admin.addons.premium
                    ?
                    <p style={{ color: 'darkred', fontWeight: 'bold' }}>This template is only available in <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium</a>.</p>
                    :
                    <Fragment>
                    {
                        props.savingTemplate
                        ?
                        <Loader/>
                        :
                        <Fragment>
                            {
                                editable
                                ?
                                <Fragment>
                                    <button
                                        className="button button-primary"
                                        onClick={ () => props.onChangeEditing(true) }
                                    >Edit Template</button>
                                    <button
                                        className="button button-primary"
                                        onClick={() => {
                                            const name = prompt( 'Choose a name for the cloned template' );
                                            
                                            if (name) {
                                                props.onSaveTemplate({
                                                    ...props.template,
                                                    oldSlug: props.template.slug,
                                                    slug: false, // Cloning, so generate new slug.
                                                    name,
                                                });
                                            }
                                        } }
                                    >Clone Template</button>
                                </Fragment>
                                :
                                <button
                                    className="button button-primary"
                                    onClick={() => {
                                        const name = prompt( 'Choose a name for the cloned template' );
                                        
                                        if (name) {
                                            props.onSaveTemplate({
                                                ...props.template,
                                                oldSlug: props.template.slug,
                                                slug: false, // Cloning, so generate new slug.
                                                name,
                                            });
                                            props.onChangeEditing(true);
                                        }
                                    } }
                                >Clone & Edit Template</button>
                            }
                            <CopyToClipboard
                                text={JSON.stringify( props.template )}
                                onCopy={(text, result) => {
                                    if ( result ) {
                                        alert( 'The template has been copied to your clipboard.' );
                                    } else {
                                        alert( 'Something went wrong. Please contact support.' );
                                    }
                                }}
                            >
                                <button
                                    className="button"
                                >Export</button>
                            </CopyToClipboard>
                            <button
                                className="button"
                                onClick={() => {
                                    const name = prompt( 'Choose a new name for this template', props.template.name );
                                    
                                    if ( name && name !== props.template.name ) {
                                        props.onSaveTemplate({
                                            ...props.template,
                                            name,
                                        });
                                    }
                                } }
                                disabled={ ! editable }
                            >Rename</button>
                            <button
                                className="button"
                                onClick={() => {
                                    if (confirm( 'Are you sure you want to delete the "' + props.template.name + '" template?' )) {
                                        props.onDeleteTemplate(props.template.slug);
                                    }
                                } }
                                disabled={ ! editable }
                            >Delete</button>
                        </Fragment>        
                    }
                    </Fragment>
                }
            </div>
        </div>
    );
}

export default ManageTemplate;