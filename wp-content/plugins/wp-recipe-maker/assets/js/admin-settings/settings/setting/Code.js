import React, { Component } from 'react';
import CodeMirror from '@uiw/react-codemirror';
import * as events from '@uiw/codemirror-extensions-events';
import { css } from '@codemirror/lang-css';

export default class SettingCode extends Component {
    
    constructor(props) {
        super(props);

        this.state = {
            initialValue: props.value,
            value: props.value,
            indicatedChange: false,
        };

        this.onChange = this.onChange.bind(this);
        this.updateParent = this.updateParent.bind(this);
    }

    shouldComponentUpdate(nextProps, nextState) {
        return nextProps.value !== this.props.value
            || nextState.value !== this.state.value
            || nextState.indicatedChange !== this.state.indicatedChange
            || nextState.initialValue !== this.state.initialValue;
    }

    componentDidUpdate(prevProps) {
        if ( prevProps.value !== this.props.value && this.props.value !== this.state.value ) {
            this.setState({
                initialValue: this.props.value,
                value: this.props.value,
                indicatedChange: false,
            });
        }
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
        this.props.onValueChange( this.state.value );
    }

    render() {
        return (
            <CodeMirror
                className="wprm-setting-input"
                value={ this.state.value }
                onChange={ this.onChange }
                extensions={[
                    css(),
                    events.content({
                        blur: () => {
                            this.updateParent();
                        },
                    }),
                ]}
            />
        );
    }
}
