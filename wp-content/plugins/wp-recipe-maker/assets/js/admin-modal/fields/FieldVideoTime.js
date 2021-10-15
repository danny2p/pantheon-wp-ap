import React, { Component } from 'react';
// Based on react-time-input.

const isValid = ( val ) => {
    const regexp = /^\d*:?\d{0,2}$/;

    const [minutesStr, secondsStr] = val.split(':');

    if (!regexp.test(val)) {
        return false;
    }

    const minutes = Number(minutesStr);
    const seconds = Number(secondsStr);

    const isValidMinute = (minute) => Number.isInteger(minute) && minute >= 0;
    const isValidSeconds = (seconds) => (Number.isInteger(seconds) && seconds >= 0) || Number.isNaN(seconds);
    if (!isValidMinute(minutes) || !isValidSeconds(seconds)) {
        return false;
    }

    if (seconds < 10 && Number(secondsStr[0]) > 5) {
        return false;
    }

    const valArr = val.indexOf(':') !== -1
        ? val.split(':')
        : [val];

    // check mm and HH
    if (valArr[0] && valArr[0].length && (parseInt(valArr[0], 10) < 0)) {
        return false;
    }

    if (valArr[1] && valArr[1].length && (parseInt(valArr[1], 10) < 0 || parseInt(valArr[1], 10) > 59)) {
        return false;
    }

    return true;
}

const FieldVideoTime = (props) => {
    const disabled = props.hasOwnProperty( 'disabled' ) ? props.disabled : false;

    return (
        <input
            type="text"
            disabled={ disabled }
            name={props.name}
            value={props.value}
            placeholder="0:00"
            onChange={(e) => {
                if ( isValid( e.target.value ) ) {
                    props.onChange( e.target.value );
                }
            }}
        />
    );
}
export default FieldVideoTime;