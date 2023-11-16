import React, { Component, Fragment } from 'react';
import Select from 'react-select';

import { __wprm } from 'Shared/Translations';

const thumbnailSizes = ! Array.isArray( wprm_admin_template.thumbnail_sizes ) ? Object.values( wprm_admin_template.thumbnail_sizes ) : wprm_admin_template.thumbnail_sizes;

export default class PropertyImageSize extends Component {
    constructor(props) {
        super(props);

        this.state = {
            width: '',
            height: '',
            force: false,
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
            let force = '!' === size.substr(-1) ? true : false;

            width = 0 < width ? width : '';
            height = 0 < height ? height : '';

            if ( width !== this.state.width || height !== this.state.height ) {
                this.setState({
                    width,
                    height,
                    force,
                })
            }
        }
    }

    changeSize(property, value) {
        if ( 'width' === property || 'height' === property || 'force' === property ) {
            let newState = this.state;
            newState[property] = 'force' === property ? !!value : parseInt( value );

            this.setState(newState, () => {
                if ( 0 < this.state.width || 0 < this.state.height ) {
                    this.props.onValueChange(`${this.state.width}x${this.state.height}${this.state.force ? '!' : ''}`);
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

        const usingThumbnailSize = thumbnailSizes.includes(this.props.value);

        return (
            <Fragment>
                <label>Select existing thumbnail size:</label>
                <Select
                    className="wprm-template-property-input"
                    menuPlacement="top"
                    value={usingThumbnailSize ? selectOptions.filter(({value}) => value === this.props.value) : ''}
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
                        value={ usingThumbnailSize ? '' : this.state.width }
                        onChange={(e) => this.changeSize('width', e.target.value)}
                    /> x <input
                        className="wprm-template-property-input"
                        type="number"
                        value={ usingThumbnailSize ? '' : this.state.height }
                        onChange={(e) => this.changeSize('height', e.target.value)}
                    />
                </div>
                {
                    ! usingThumbnailSize
                    && 0 < this.state.width
                    && 0 < this.state.height
                    &&
                    <div className="wprm-template-property-input-force-image-size">
                        <label>
                            <input
                                type="checkbox"
                                checked={ this.state.force }
                                onChange={(e) => {
                                    this.changeSize('force', e.target.checked)
                                }}
                            /> { __wprm( 'Force this size using CSS' ) }
                        </label>
                    </div>
                }
            </Fragment>
        );
    }
}