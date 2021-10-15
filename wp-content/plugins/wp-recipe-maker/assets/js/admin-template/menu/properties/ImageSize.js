import React, { Component, Fragment } from 'react';
import Select from 'react-select';

const thumbnailSizes = ! Array.isArray( wprm_admin_template.thumbnail_sizes ) ? Object.values( wprm_admin_template.thumbnail_sizes ) : wprm_admin_template.thumbnail_sizes;

export default class PropertyImageSize extends Component {
    constructor(props) {
        super(props);

        this.state = {
            width: '',
            height: '',
        }
    }

    componentDidMount() {
        this.checkSize();
    }

    componentDidUpdate() {
        this.checkSize();
    }

    checkSize() {
        const size = this.props.value;        

        if ( '' !== size ) {
            const separator = size.indexOf('x');

            let width = separator > 0 ? parseInt( size.substr(0, separator) ) : 0;
            let height = separator > 0 ? parseInt( size.substr(separator + 1) ) : 0;

            width = 0 < width ? width : '';
            height = 0 < height ? height : '';

            if ( width !== this.state.width || height !== this.state.height ) {
                this.setState({
                    width,
                    height,
                })
            }
        }
    }

    changeSize(property, value) {
        if ( 'width' === property || 'height' === property ) {
            let newState = this.state;
            newState[property] = parseInt( value );

            this.setState(newState, () => {
                if ( 0 < this.state.width || 0 < this.state.height ) {
                    this.props.onValueChange(`${this.state.width}x${this.state.height}`);
                }
            });
        }
    }

    render() {
        let selectOptions = [];

        for (let thumbnail of thumbnailSizes) {
            selectOptions.push({
                value: thumbnail,
                label: thumbnail,
            });
        }

        return (
            <Fragment>
                <label>Select existing thumbnail size:</label>
                <Select
                    className="wprm-template-property-input"
                    menuPlacement="top"
                    value={thumbnailSizes.includes(this.props.value) ? selectOptions.filter(({value}) => value === this.props.value) : ''}
                    onChange={(option) => {
                        if ( ! option ) {
                            return this.props.onValueChange('');
                        }
                        return this.props.onValueChange(option.value);
                    }}
                    options={selectOptions}
                    clearable={true}
                />
                <label>...or set a specific width and height:</label>
                <div className="wprm-template-property-input-width-height">
                    <input
                        className="wprm-template-property-input"
                        type="number"
                        value={this.state.width}
                        onChange={(e) => this.changeSize('width', e.target.value)}
                    /> x <input
                        className="wprm-template-property-input"
                        type="number"
                        value={this.state.height}
                        onChange={(e) => this.changeSize('height', e.target.value)}
                    />
                </div>
            </Fragment>
        );
    }
}