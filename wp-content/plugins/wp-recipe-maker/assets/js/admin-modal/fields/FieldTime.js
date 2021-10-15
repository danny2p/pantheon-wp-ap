import React from 'react';

import { __wprm } from 'Shared/Translations';
 
const FieldTime = (props) => {
    const time = parseInt( props.value.time );

    let days = 0,
        hours = 0,
        minutes = 0;

    if ( time > 0 ) {
        days = Math.floor( time / 24 / 60 );
        hours = Math.floor( time / 60 % 24 );
        minutes = Math.floor( time % 60 );
    }

    return (
        <div className="wprm-admin-modal-field-time">
            <div className="wprm-admin-modal-field-time-parts">
                <input
                    type="number"
                    value={ 0 < days ? '' + days : '' }
                    onChange={ (e) => {
                        let newDays = parseInt( e.target.value );
                        newDays = isNaN( newDays ) ? 0 : newDays;

                        const newTime = 24 * 60 * Math.max( 0, newDays ) + 60 * hours + minutes;
                        props.onChange( newTime );
                    }}
                /> { __wprm( 'days' ) }
                <input
                    type="number"
                    value={ 0 < hours ? '' + hours : '' }
                    onChange={ (e) => {
                        let newHours = parseInt( e.target.value );
                        newHours = isNaN( newHours ) ? 0 : newHours;

                        const newTime = 24 * 60 * days + 60 * Math.max( 0, newHours ) + minutes;
                        props.onChange( newTime );
                    }}
                /> { __wprm( 'hours' ) }
                <input
                    type="number"
                    value={ 0 < minutes ? '' + minutes : ( props.value.zero ? '0' : '' ) }
                    onChange={ (e) => {
                        let newMinutes = parseInt( e.target.value );
                        newMinutes = isNaN( newMinutes ) ? 0 : newMinutes;

                        const newTime = 24 * 60 * days + 60 * hours + Math.max( 0, newMinutes );
                        props.onChange( newTime );
                    }}
                /> { __wprm( 'minutes' ) }
            </div>
            {
                0 === time
                && props.hasOwnProperty( 'onChangeZero' )
                &&
                <div className="wprm-admin-modal-field-time-none">
                    <input
                        id={ `wprm-admin-modal-field-time-none-${props.id}` }
                        type="checkbox"
                        checked={ props.value.zero }
                        onChange={(e) => {
                            props.onChangeZero( e.target.checked );
                        }}
                    /> <label htmlFor={ `wprm-admin-modal-field-time-none-${props.id}` }>{ __wprm( 'Show "0" in template' ) }</label>
                </div>
            }
        </div>
    );
}
export default FieldTime;