import React, { Fragment } from 'react';
import PropTypes from 'prop-types';

const SettingNumber = (props) => {
    return (
        <Fragment>
            <input
                className="wprm-setting-input"
                type="number"
                value={props.value}
                onChange={(e) => props.onValueChange(e.target.value)}
            />
            {
                props.setting.hasOwnProperty('suffix')
                ?
                <span className="wprm-setting-number-suffix"> { props.setting.suffix }</span>
                :
                null
            }
        </Fragment>
    );
}

SettingNumber.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}

export default SettingNumber;