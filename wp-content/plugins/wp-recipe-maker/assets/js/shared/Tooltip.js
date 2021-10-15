import React from 'react';

import { Tooltip } from 'react-tippy';
import 'react-tippy/dist/tippy.css'

const OurTooltip = (props) => {
    if ( ! props.content ) {
        return props.children;
    }

    const style = props.hasOwnProperty( 'style' ) ? props.style : {};

    return (
        <Tooltip
            html={
                <div
                    dangerouslySetInnerHTML={ { __html: props.content } }
                />
            }
            popperOptions={ {
                modifiers: {
                    addZIndex: {
                      enabled: true,
                      order: 810,
                      fn: data => ({
                        ...data,
                        styles: {
                          ...data.styles,
                          zIndex: 100000,
                        },
                      })
                    },
                    preventOverflow: {
                        boundariesElement: 'window',
                    },
                },
            } }
            style={ style }
        >
            { props.children }
        </Tooltip>
    );
}
export default OurTooltip;