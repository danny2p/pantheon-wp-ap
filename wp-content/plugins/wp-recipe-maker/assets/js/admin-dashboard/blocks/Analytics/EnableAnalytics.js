import React, { Fragment } from 'react';

import { __wprm } from 'Shared/Translations';

import Toggle from '../../fields/Toggle';
 
const EnableAnalytics = (props) => {
    return (
        <Fragment>
            <p>{ __wprm( 'Track different visitor actions related to recipes.' ) } { __wprm( 'Find out what recipes visitors are interacting with, what affiliate links are getting clicked on, and more...' ) }</p>
            <Toggle
                value={ false }
                onChange={() => {
                    props.onEnable();
                }}
            >{ __wprm( 'Enable Analytics' ) }</Toggle>
        </Fragment>
    );

    
}
export default EnableAnalytics;