import React, { Component, Fragment } from 'react';
import SVG from 'react-inlinesvg';

import IconPickerModal from './IconPickerModal';

export default class PropertyIcon extends Component {
    constructor(props) {
        super(props);

        this.state = {
            modalOpen: false,
        }
    }

    render() {
        const customSelected = wprm_admin_template.icons.hasOwnProperty(this.props.value);
        const iconUrl = customSelected ? wprm_admin_template.icons[this.props.value].url : this.props.value;

        return (
            <Fragment>
                <span className="wprm-template-property-icon-selected-container">
                    {
                        iconUrl
                        &&
                        <SVG
                            src={iconUrl}
                            className="wprm-template-property-icon-select"
                        />
                    }
                    <a href="#" onClick={(e) => {
                        e.preventDefault();
                        this.setState({ modalOpen: true });
                    }}>{ iconUrl ? 'Change...' : 'Select...' }</a>
                </span>
                <IconPickerModal
                    isOpen={ this.state.modalOpen }
                    onClose={ () => this.setState({ modalOpen: false }) }
                    currentValue={ this.props.value }
                    onSelect={ ( value ) => {
                        this.setState({ modalOpen: false });
                        if ( value !== this.props.value ) {
                            this.props.onValueChange( value );
                        }
                    } }
                />
            </Fragment>
        );
    }
}
