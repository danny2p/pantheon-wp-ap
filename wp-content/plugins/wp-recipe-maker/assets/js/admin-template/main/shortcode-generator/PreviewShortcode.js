import React, { Component, Fragment } from 'react';
import CopyToClipboard from 'react-copy-to-clipboard';

import PreviewTemplate from '../preview-template';

const PreviewShortcode = (props) => {
    const template = {
        type: 'shortcode',
        slug: 'shortcode-generator',
        html: props.shortcode.full,
        style: {
            properties: {},
            css: '',
        },
    }

    return (
        <Fragment>
            <div className="wprm-main-container">
                <h2 className="wprm-main-container-name">Generating "{ props.shortcode.name }" Shortcode</h2>
                <textarea
                    className="wprm-select-shortcode-result"
                    rows="5"
                    value={ props.shortcode.full }
                    style={{
                        width: '100%',
                        maxWidth: '500px',
                        display: 'block',
                        margin: '20px auto',
                    }}
                    disabled
                />
                <div
                    style={{
                        textAlign: 'center',
                        marginTop: '10px',
                    }}
                >
                    <CopyToClipboard
                        text={ props.shortcode.full }
                        onCopy={(text, result) => {
                            if ( ! result ) {
                                alert( 'Something went wrong. Please contact support.' );
                            }
                        }}
                    >
                        <button
                            className="button button-primary"
                        >Copy to Clipboard</button>
                    </CopyToClipboard> <button
                        className="button"
                        onClick={() => {
                            props.onChangeShortcode(false);
                        }}
                    >Stop Generating</button>
                </div>
            </div>
            <PreviewTemplate
                template={ template }
                mode="shortcode-generator"
                onChangeMode={() => {}}
                onChangeHTML={(html) => {
                    props.onChangeShortcode({
                        ...props.shortcode,
                        full: html,
                    });
                }}
            />
        </Fragment>
    );
}

export default PreviewShortcode;