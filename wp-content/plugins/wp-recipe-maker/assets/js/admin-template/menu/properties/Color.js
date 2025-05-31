import React, { Component, Fragment } from 'react';
import { __wprm } from 'Shared/Translations';
import Tooltip from 'Shared/Tooltip';

import Icon from '../../general/Icon';
import reactCSS from 'reactcss';
import { SketchPicker } from 'react-color';

export default class PropertyColor extends Component {
    constructor(props) {
        super(props);

        // Check if value is hex color
        const isHexColor = /^#([A-Fa-f0-9]{3}){1,2}$/.test(this.props.value);

        this.state = {
            displayColorPicker: false,
            isHexColor,
        }
    }

    handleClick() {
        this.setState({ displayColorPicker: !this.state.displayColorPicker })
    };
    
    handleClose() {
        this.setState({ displayColorPicker: false })
    };
    
    handleChange(color) {
        this.setState({ isHexColor: true });
        this.props.onValueChange(color.hex);
    };

    render() {
        const styles = reactCSS({
            'default': {
                color: {
                    width: '36px',
                    height: '14px',
                    borderRadius: '2px',
                    background: `${ this.state.isHexColor ? this.props.value : '#ffffff' }`,
                    textAlign: 'center',
                    fontSize: '8px',
                    overflow: 'hidden',
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
                    right: '0',
                    bottom: '30px',
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
            <Fragment>
                <div className="wprm-template-property-input">
                    <div
                        className="wprm-template-property-input-color-variable-icon"
                        onClick={() => {
                            const newColor = prompt( __wprm( 'Set color manually, for example when using CSS variables:' ), this.props.value);
                            
                            if ( newColor !== null ) {
                                // Check if value is hex color for UI state
                                const isHexColor = /^#([A-Fa-f0-9]{3}){1,2}$/.test(newColor);
                                this.setState({ isHexColor });

                                // Update the color value
                                this.props.onValueChange(newColor);
                            }
                        }}
                    >
                        <Icon type="html" />
                    </div>
                    <Tooltip content={ this.props.value }>
                        <div style={ styles.swatch } onClick={ this.handleClick.bind(this) }>
                            <div style={ styles.color }>
                                {
                                    ! this.state.isHexColor
                                    && this.props.value
                                }
                            </div>
                        </div>
                    </Tooltip>
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
            </Fragment>
        );
    }
}
