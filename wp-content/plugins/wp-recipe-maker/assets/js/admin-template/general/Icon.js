import React from 'react';
import SVG from 'react-inlinesvg';
import Tooltip from 'Shared/Tooltip';

import IconManage from '../../../icons/template/manage.svg';
import IconProperties from '../../../icons/template/properties.svg';
import IconPatterns from '../../../icons/template/patterns.svg';
import IconBlocks from '../../../icons/template/blocks.svg';
import IconAdd from '../../../icons/template/add.svg';
import IconRemove from '../../../icons/template/remove.svg';
import IconMove from '../../../icons/template/move.svg';
import IconHTML from '../../../icons/template/html.svg';
import IconCSS from '../../../icons/template/css.svg';
import IconArrowUp from '../../../icons/arrow-small-up.svg';
import IconArrowDown from '../../../icons/arrow-small-down.svg';
 
const icons = {
    manage: IconManage,
    properties: IconProperties,
    patterns: IconPatterns,
    blocks: IconBlocks,
    add: IconAdd,
    remove: IconRemove,
    move: IconMove,
    html: IconHTML,
    css: IconCSS,
    'arrow-up': IconArrowUp,
    'arrow-down': IconArrowDown,
};

const Icon = (props) => {
    let icon = icons.hasOwnProperty(props.type) ? icons[props.type] : false;

    if ( !icon ) {
        return <span className="wprm-template-noicon">&nbsp;</span>;
    }

    const className = props.onClick ? 'wprm-template-icon wprm-template-icon-clickable' : 'wprm-template-icon';
    const iconElement = (
        <span 
            className={className}
            onClick={props.onClick}
        >
            <SVG
                src={icon}
            />
        </span>
    );

    // Wrap with tooltip if title is provided
    if (props.title) {
        return (
            <Tooltip content={props.title} placement="top">
                {iconElement}
            </Tooltip>
        );
    }

    return iconElement;
}
export default Icon;