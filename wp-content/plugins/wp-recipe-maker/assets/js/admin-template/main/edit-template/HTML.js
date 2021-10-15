import React from 'react';

import CodeMirror from 'react-codemirror';
require('codemirror/lib/codemirror.css');
require('codemirror/mode/xml/xml');

const HTML = (props) => {
    return (
        <div className="wprm-main-container">
            <h2 className="wprm-main-container-name">HTML</h2>
            <CodeMirror
                className="wprm-main-container-html"
                value={props.template.html}
                onChange={(value) => props.onChangeValue(value)}
                options={{
                    lineNumbers: true,
                    mode: 'xml',
                    htmlMode: true,
                }}
            />
        </div>
    );
}

export default HTML;