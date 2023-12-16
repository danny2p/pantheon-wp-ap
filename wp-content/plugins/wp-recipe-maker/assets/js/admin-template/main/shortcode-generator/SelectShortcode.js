import React, { Component } from 'react';
import Select from 'react-select';

import Shortcodes from '../../general/shortcodes';
import Helpers from '../../general/Helpers';

const { shortcodeGroups, shortcodeKeysAlphebetically } = Shortcodes;

let allShortcodes = [];
let selectShortcodes = [];

for ( let groupKey of Object.keys( shortcodeGroups ) ) {
    const shortcodeGroup = shortcodeGroups[ groupKey ];

    let group = {
        label: shortcodeGroup.group,
        options: [],
    };

    for ( let id of shortcodeGroup.shortcodes ) {
        if ( shortcodeKeysAlphebetically.includes( id ) ) {
            const shortcodeOption = {
                label: Helpers.getShortcodeName(id),
                value: id,
            }

            group.options.push(shortcodeOption);
            allShortcodes.push(shortcodeOption);
        }
    }

    if ( group.options.length ) {
        selectShortcodes.push(group);
    }
}

export default class SelectShortcode extends Component {
    constructor(props) {
        super(props);

        this.state = {
            shortcode: false,
            input: '',
            error: false,
        }
    }

    componentDidUpdate( prevProps, prevState ) {
        if ( this.state.input !== prevState.input ) {
            const shortcode = this.findShortcode( this.state.input );
            const error = false === shortcode && '' !== this.state.input;

            if ( JSON.stringify( shortcode ) !== JSON.stringify( this.state.shortcode ) || error !== this.state.error ) {
                this.setState({
                    shortcode,
                    error,
                });
            }
        }
    }

    findShortcode(input) {
        input = input.trim();

        // Remove starting [ and ending ] from input.
        input = input.replace(/^\[/, '');
        input = input.replace(/\]$/, '');

        const parts = input.split(' ');
        const id = parts[0];

        if ( shortcodeKeysAlphebetically.includes( id ) ) {
            return {
                uid: 0,
                id,
                name: Helpers.getShortcodeName(id),
                attributes: Helpers.getShortcodeAttributes( input ),
                full: `[${input}]`,
            };
        }

        return false;
    }

    render() {
        return (
            <div className="wprm-main-container">
                <h2 className="wprm-main-container-name">Select Shortcode to Generate</h2>
                <p style={{ textAlign: 'center'}}>Select from list:</p>
                <Select
                    className="wprm-select-shortcode"
                    value={allShortcodes.filter(({value}) => this.state.shortcode && value === this.state.shortcode.id)}
                    onChange={(option) => {
                        this.setState({
                            shortcode: {
                                uid: 0,
                                id: option.value,
                                name: option.label,
                                attributes: {},
                                full: `[${option.value}]`,
                            },
                            input: '',
                        });   
                    }}
                    options={selectShortcodes}
                    clearable={false}
                    styles={{
                        control: (provided) => ({
                            ...provided,
                            backgroundColor: 'white',
                        }),
                        container: (provided) => ({
                            ...provided,
                            width: '100%',
                            maxWidth: '300px',
                            margin: '0 auto',
                        }),
                    }}
                />
                <p style={{ textAlign: 'center'}}>Or paste in existing shortcode:</p>
                <input
                    type="text"
                    className="wprm-select-shortcode-input"
                    value={this.state.input}
                    onChange={(e) => {
                        this.setState({
                            input: e.target.value,
                            shortcode: false,
                        });
                    }}
                    style={{
                        width: '100%',
                        maxWidth: '500px',
                        display: 'block',
                        margin: '0 auto',
                    }}
                />
                <div
                    style={{
                        textAlign: 'center',
                        color: 'darkred',
                        height: '30px',
                        marginTop: '10px',
                    }}
                >{
                    this.state.error
                    ?
                    'Shortcode not found in input.'
                    :
                    ' '
                }</div>
                <button
                    className="button button-primary"
                    onClick={() => {
                        if ( this.state.shortcode ) {
                            this.props.onChangeShortcode( this.state.shortcode );
                        }
                    }}
                    disabled={ false === this.state.shortcode }
                    style={{
                        display: 'block',
                        margin: '0 auto',
                    }}
                >{
                    false === this.state.shortcode
                    ?
                    'Generate Shortcode'
                    :
                    `Generate "${ this.state.shortcode.name }" Shortcode`    
                }</button>
            </div>
        );
    }
}