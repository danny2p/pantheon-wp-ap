import React, { Fragment } from 'react';

import Tooltip from 'Shared/Tooltip';
import { __wprm } from 'Shared/Translations';
 
const SeoIndicator = (props) => {
    if ( ! props.seo ) {
        return null;
    }

    return (
        <Tooltip
            content={ props.seo.message }
            style={ { width: '100%' } }
        >
            <div
                className={ `wprm-admin-manage-seo-indicator wprm-admin-manage-seo-indicator-${ props.seo.type.trim() }` }
                onClick={ props.onClick }
            >
                {
                    'other' === props.seo.type
                    ?
                    'n/a'
                    :
                    <Fragment>
                        {
                            'missing' === props.seo.type
                            ?
                            __wprm( 'missing' )
                            :
                            <Fragment>
                                <div className="wprm-admin-manage-seo-indicator-block"></div>
                                <div className="wprm-admin-manage-seo-indicator-block"></div>
                                <div className="wprm-admin-manage-seo-indicator-block"></div>
                                <div className="wprm-admin-manage-seo-indicator-block"></div>
                            </Fragment>
                        }
                    </Fragment>
                }
            </div>
        </Tooltip>
    );
}
export default SeoIndicator;