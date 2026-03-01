import React, { useEffect, useRef, useState } from 'react';

const PropertyText = (props) => {
    const [value, setValue] = useState(props.value || '');
    const debounceTimer = useRef(null);

    useEffect(() => {
        const nextValue = props.value || '';
        setValue((currentValue) => currentValue === nextValue ? currentValue : nextValue);
    }, [props.value]);

    useEffect(() => {
        return () => {
            if (debounceTimer.current) {
                clearTimeout(debounceTimer.current);
                debounceTimer.current = null;
            }
        };
    }, []);

    const scheduleChange = (newValue) => {
        if (debounceTimer.current) {
            clearTimeout(debounceTimer.current);
        }

        debounceTimer.current = setTimeout(() => {
            debounceTimer.current = null;
            props.onValueChange(newValue, {
                historyMode: 'debounced',
            });
        }, 400);
    };

    return (
        <input
            className="wprm-template-property-input"
            type="text"
            value={value}
            onChange={(e) => {
                const newValue = e.target.value;
                setValue(newValue);
                scheduleChange(newValue);
            }}
            onBlur={() => {
                if (debounceTimer.current) {
                    clearTimeout(debounceTimer.current);
                    debounceTimer.current = null;
                }

                props.onValueChange(value, {
                    historyMode: 'debounced',
                    historyBoundary: true,
                });
            }}
        />
    );
}

export default PropertyText;
