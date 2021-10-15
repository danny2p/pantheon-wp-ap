import React, { Component } from 'react';
import PropTypes from 'prop-types';

import reactCSS from 'reactcss';
import { SketchPicker } from 'react-color';

export default class SettingColor extends Component {
    constructor(props) {
        super(props);

        this.state = {
            displayColorPicker: false,
        }
    }

    handleClick() {
        this.setState({ displayColorPicker: !this.state.displayColorPicker })
    };
    
    handleClose() {
        this.setState({ displayColorPicker: false })
    };
    
    handleChange(color) {
        this.props.onValueChange(color.hex);
    };

    render() {
        const styles = reactCSS({
            'default': {
                color: {
                    width: '36px',
                    height: '14px',
                    borderRadius: '2px',
                    background: `${ this.props.value }`,
                },
                swatch: {
                    padding: '5px',
                    background: '#fff',
                    borderRadius: '1px',
                    boxShadow: '0 0 0 1px rgba(0,0,0,.1)',
                    display: 'inline-block',
                    cursor: 'pointer',
                },
                popover: {
                    position: 'absolute',
                    zIndex: '2',
                },
                cover: {
                    position: 'fixed',
                    top: '0px',
                    right: '0px',
                    bottom: '0px',
                    left: '0px',
                },
            },
        });

        return (
            <div className="wprm-setting-input">
                <div style={ styles.swatch } onClick={ this.handleClick.bind(this) }>
                    <div style={ styles.color } />
                </div>
                {
                    this.state.displayColorPicker
                    ?
                    <div style={ styles.popover }>
                        <div style={ styles.cover } onClick={ this.handleClose.bind(this) }/>
                        <SketchPicker
                            color={ this.props.value }
                            onChange={ this.handleChange.bind(this) }
                            disableAlpha={ true }
                        />
                    </div>
                    :
                    null
                }
            </div>
        );
    }
}

SettingColor.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}