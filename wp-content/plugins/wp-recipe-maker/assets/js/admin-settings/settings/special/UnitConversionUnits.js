import React from 'react';
import PropTypes from 'prop-types';
import { Element } from 'react-scroll';

const SettingUnitConversionUnits = (props) => {
    const units = props.value;

    const onChange = (unit, field, value) => {
        let newUnit = props.value[unit];
        newUnit[field] = value;

        let newUnits = props.value;
        newUnits[unit] = newUnit;

        props.onValueChange(newUnits);
    };

    return (
        <Element className="wprm-setting-container" name={props.setting.id} >
            <table className="wprm-setting-unit-conversion-units">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Aliases</th>
                        <th>Singular</th>
                        <th>Plural</th>
                    </tr>
                </thead>
                <tbody>
                {
                    Object.keys(units).map((unit, index) =>
                        <tr key={index}>
                            <th scope="row">{ units[unit].label }</th>
                            <td>
                                <input
                                    className="wprm-setting-unit-conversion-units-aliases"
                                    type="text"
                                    value={ units[unit].aliases }
                                    onChange={(e) => onChange(unit, 'aliases', e.target.value)}
                                />
                            </td>
                            <td>
                                <input
                                    className="wprm-setting-unit-conversion-units-singular"
                                    type="text"
                                    value={ units[unit].singular }
                                    onChange={(e) => onChange(unit, 'singular', e.target.value)}
                                />
                            </td>
                            <td>
                                <input
                                    className="wprm-setting-unit-conversion-units-plural"
                                    type="text"
                                    value={ units[unit].plural }
                                    onChange={(e) => onChange(unit, 'plural', e.target.value)}
                                />
                            </td>
                        </tr>
                    )
                }
                </tbody>
            </table>
        </Element>
    );
}

SettingUnitConversionUnits.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}

export default SettingUnitConversionUnits;