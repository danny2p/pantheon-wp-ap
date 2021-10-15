import React from 'react';

import CodeMirror from 'react-codemirror';
require('codemirror/lib/codemirror.css');
require('codemirror/mode/css/css');

const CSS = (props) => {
    return (
        <div className="wprm-main-container">
            <h2 className="wprm-main-container-name">CSS</h2>
            <CodeMirror
                className="wprm-main-container-css"
                value={props.template.style.css}
                onChange={(value) => props.onChangeValue(value)}
                options={{
                    lineNumbers: true,
                    mode: 'css',
                }}
            />
        </div>
    );
}

export default CSS;