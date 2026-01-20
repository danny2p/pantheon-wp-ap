import React from 'react';

import Tippy from '@tippyjs/react';
import 'tippy.js/dist/tippy.css';

const OurTooltip = (props) => {
    if ( ! props.content ) {
        return props.children;
    }

    const style = props.hasOwnProperty( 'style' ) ? props.style : {};

    let child = props.children;

    // Determine whether the child can safely receive a ref from Tippy.
    const isDomElement = React.isValidElement( child ) && 'string' === typeof child.type;
    const isClassComponent = React.isValidElement( child ) && child.type && child.type.prototype && child.type.prototype.isReactComponent;
    const isForwardRef = React.isValidElement( child ) && child.type && child.type.$$typeof && 'Symbol(react.forward_ref)' === child.type.$$typeof.toString();
    const needsWrapper = ! isDomElement && ! isClassComponent && ! isForwardRef;

    if ( needsWrapper ) {
        // Ensure Tippy always receives a DOM node so refs work (e.g. when wrapping Icon components).
        child = <span style={ style }>{ child }</span>;
    } else if ( React.isValidElement( child ) && React.Children.count( props.children ) === 1 ) {
        child = React.cloneElement( child, {
            style: { ...child.props.style, ...style }
        } );
    } else {
        child = <span style={ style }>{ child }</span>;
    }

    const placement = props.hasOwnProperty( 'placement' ) ? props.placement : 'top';

    return (
        <Tippy
            content={
                <div
                    dangerouslySetInnerHTML={ { __html: props.content } }
                />
            }
            allowHTML={ true }
            placement={ placement }
            popperOptions={ {
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
            } }
        >
            { child }
        </Tippy>
    );
}
export default OurTooltip;