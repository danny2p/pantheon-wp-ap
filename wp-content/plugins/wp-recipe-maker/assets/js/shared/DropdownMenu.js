import React from 'react';

import Tippy from '@tippyjs/react';
import 'tippy.js/dist/tippy.css';

const DropdownMenu = (props) => {
    const { children, items, placement = 'top', onItemClick } = props;

    if (!items || !items.length) {
        return children;
    }

    let child = children;

    // Determine whether the child can safely receive a ref from Tippy.
    const isDomElement = React.isValidElement(child) && 'string' === typeof child.type;
    const isClassComponent = React.isValidElement(child) && child.type && child.type.prototype && child.type.prototype.isReactComponent;
    const isForwardRef = React.isValidElement(child) && child.type && child.type.$$typeof && 'Symbol(react.forward_ref)' === child.type.$$typeof.toString();
    const needsWrapper = !isDomElement && !isClassComponent && !isForwardRef;

    if (needsWrapper) {
        child = <span style={{ display: 'inline-block' }}>{child}</span>;
    }

    const handleItemClick = (item, e) => {
        e.stopPropagation();
        if (item.onClick) {
            item.onClick(e);
        }
        if (onItemClick) {
            onItemClick(item, e);
        }
    };

    const menuContent = (
        <div className="wprm-dropdown-menu">
            {items.map((item, index) => {
                if (item.divider) {
                    return <div key={index} className="wprm-dropdown-menu-divider" />;
                }

                const isDisabled = item.disabled || false;
                const className = `wprm-dropdown-menu-item${isDisabled ? ' wprm-dropdown-menu-item-disabled' : ''}`;

                return (
                    <div
                        key={index}
                        className={className}
                        onClick={isDisabled ? undefined : (e) => handleItemClick(item, e)}
                    >
                        {item.icon && (
                            <span className="wprm-dropdown-menu-item-icon">
                                {item.icon}
                            </span>
                        )}
                        <span className="wprm-dropdown-menu-item-label">{item.label}</span>
                    </div>
                );
            })}
        </div>
    );

    return (
        <Tippy
            content={menuContent}
            interactive={true}
            trigger="click"
            placement={placement}
            popperOptions={{
                modifiers: [
                    {
                        name: 'addZIndex',
                        enabled: true,
                        phase: 'write',
                        fn: ({ state }) => {
                            state.styles.popper.zIndex = '100000';
                        },
                    },
                    {
                        name: 'preventOverflow',
                        options: {
                            boundary: 'window',
                        },
                    },
                ],
            }}
            theme="wprm"
            arrow={true}
            hideOnClick={true}
        >
            {child}
        </Tippy>
    );
};

export default DropdownMenu;
