import React, { Component } from 'react';
import CodeMirror from '@uiw/react-codemirror';
import * as events from '@uiw/codemirror-extensions-events';
import { html } from '@codemirror/lang-html';

export default class HTML extends Component {
    
    constructor(props) {
        super(props);

        this.state = {
            initialValue: props.template.html,
            value: props.template.html,
            indicatedChange: false,
        };

        this.onChange = this.onChange.bind(this);
        this.updateParent = this.updateParent.bind(this);
    }

    shouldComponentUpdate(nextProps) {
        return false;
    }

    onChange( value ) {
        let newState = {
            value: value,
        };

        let updateParent = false;
        if ( value !== this.state.initialValue ) {
            updateParent = true;
            newState.indicatedChange = true;
        } else {
            if ( this.state.indicatedChange ) {
                updateParent = true;
                newState.indicatedChange = false;
            }
        }

        this.setState( newState, () => {
            if ( updateParent ) {
                this.updateParent();
            }
        } );
    }

    updateParent() {
        this.props.onChangeValue( this.state.value );
    }

    render() {
        return (
            <div className="wprm-main-container">
                <h2 className="wprm-main-container-name">HTML</h2>
                <CodeMirror
                    className="wprm-main-container-html"
                    value={ this.state.value }
                    onChange={ this.onChange }
                    extensions={[
                        html(),
                        events.content({
                            blur: () => {
                                this.updateParent();
                            },
                        }),
                    ]}
                />
            </div>
        );
    }
}