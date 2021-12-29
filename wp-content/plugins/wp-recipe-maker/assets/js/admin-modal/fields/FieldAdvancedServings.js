import React, { Fragment } from 'react';

import { __wprm } from 'Shared/Translations';

import FieldCheckbox from './FieldCheckbox';
import FieldDropdown from './FieldDropdown';
import FieldText from './FieldText';
 
const FieldAdvancedServings = (props) => {
    const servings = {
        shape: 'round',
        unit: 'inch',
        diameter: 0,
        width: 0,
        length: 0,
        height: 0,
        ...props.servings,
    }

    return (
        <div className="wprm-admin-modal-field-advanced-servings">
            <FieldCheckbox
                name="advanced-servings"
                value={ props.enabled }
                onChange={ (enabled) => {
                    props.onChangeEnabled( enabled );
                }}
            />
            {
                props.enabled
                &&
                <Fragment>
                    {
                        ! wprm_admin.addons.premium
                        &&
                        <p>{ __wprm( 'This feature is only available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium</a>. { __wprm( 'You can already fill in the values, but the adjustable changer will not show up yet.' ) }</p>
                    }
                    <div className="wprm-admin-modal-field-advanced-servings-details">
                        <FieldDropdown
                            options={ [{ value: 'round', label: __wprm( 'Round' ) }, { value: 'rectangle', label: __wprm( 'Rectangle' ) } ] }
                            value={ servings.shape }
                            onChange={ (shape) => {
                                props.onChangeServings( {
                                    ...servings,
                                    shape,
                                } );
                            }}
                            width={ 147 }
                        />
                        <div className="wprm-admin-modal-field-advanced-servings-details-text">{ __wprm( 'measured in' ) }</div>
                        <FieldDropdown
                            options={ [{ value: 'inch', label: __wprm( 'inch' ) }, { value: 'cm', label: __wprm( 'cm' ) } ] }
                            value={ servings.unit }
                            onChange={ (unit) => {
                                props.onChangeServings( {
                                    ...servings,
                                    unit,
                                } );
                            }}
                            width={ 100 }
                        />
                    </div>
                    <div className="wprm-admin-modal-field-advanced-servings-details">
                        {
                            'round' === servings.shape
                            ?
                            <Fragment>
                                <FieldText
                                    placeholder="6"
                                    type="number"
                                    min="0"
                                    step="any"
                                    value={ 0 != servings.diameter ? servings.diameter : '' }
                                    onChange={ (diameter) => {
                                        props.onChangeServings( {
                                            ...servings,
                                            diameter,
                                        } );
                                    }}
                                />
                                <div className="wprm-admin-modal-field-advanced-servings-details-text">{ servings.unit } { __wprm( 'diameter' ) }</div>
                            </Fragment>
                            :
                            <Fragment>
                                <FieldText
                                    placeholder="6"
                                    type="number"
                                    min="0"
                                    step="any"
                                    value={ 0 != servings.width ? servings.width : '' }
                                    onChange={ (width) => {
                                        props.onChangeServings( {
                                            ...servings,
                                            width,
                                        } );
                                    }}
                                />
                                <div className="wprm-admin-modal-field-advanced-servings-details-text">x</div>
                                <FieldText
                                    placeholder="6"
                                    type="number"
                                    min="0"
                                    step="any"
                                    value={ 0 != servings.length ? servings.length : '' }
                                    onChange={ (length) => {
                                        props.onChangeServings( {
                                            ...servings,
                                            length,
                                        } );
                                    }}
                                />
                                <div className="wprm-admin-modal-field-advanced-servings-details-text">{ servings.unit } { __wprm( 'area' ) }</div>
                            </Fragment>
                        }
                    </div>
                    <div className="wprm-admin-modal-field-advanced-servings-details">
                        <FieldText
                            placeholder=""
                            type="number"
                            min="0"
                            step="any"
                            value={ 0 != servings.height ? servings.height : '' }
                            onChange={ (height) => {
                                props.onChangeServings( {
                                    ...servings,
                                    height,
                                } );
                            }}
                        />
                        <div className="wprm-admin-modal-field-advanced-servings-details-text">{ servings.unit } { __wprm( 'height' ) } ({ __wprm( 'optional' ) })</div>
                    </div>
                </Fragment>
            }
        </div>
    );
}
export default FieldAdvancedServings;