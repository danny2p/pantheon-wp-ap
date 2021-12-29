import React, { Fragment } from 'react';

import Item from './Item';

const ItemCompatibility = ( props ) => {
    return (
        <Fragment>
            {
                props.item.includes( 'litespeed-cache' )
                &&
                <Item
                    header="🔌 LiteSpeed Cache might be breaking recipe saving"
                >
                    <p>There have been issues with LiteSpeed Cache breaking the recipe saving if the "Cache REST API" setting is enabled on the <em>LiteSpeed Cache &gt; Cache</em> page.</p>
                    <p>We recommend setting that setting to "Off".</p>
                </Item>
            }
        </Fragment>
    );
}
export default ItemCompatibility;