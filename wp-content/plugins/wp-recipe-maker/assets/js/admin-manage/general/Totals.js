import React, { Fragment } from 'react';

import { __wprm } from 'Shared/Translations';
 
const Totals = (props) => {
    if ( ! props.filtered && ! props.total ) {
        return <div className="wprm-admin-table-totals">&nbsp;</div>;
    }

    const isFiltered = false !== props.filtered && props.filtered != props.total;

    // Try to decode filter.
    let decodedFilter = '';
    if ( false !== props.filter ) {
        decodedFilter = props.filter[1];
        try {
            decodedFilter = decodeURIComponent( props.filter[1] );
        } catch(e) {}
    }

    return (
        <div className="wprm-admin-table-totals">
            {
                props.total
                ?
                <Fragment>
                    {
                    isFiltered
                    ?
                    `${ __wprm( 'Showing' ) } ${ Number(props.filtered).toLocaleString() } ${ __wprm( 'filtered of' ) } ${ Number(props.total).toLocaleString() } ${ __wprm( 'total' ) }`
                    :
                    `${ __wprm( 'Showing' ) } ${ Number(props.total).toLocaleString() } ${ __wprm( 'total' ) }`
                }
                </Fragment>
                :
                `${ Number(props.filtered).toLocaleString() } ${ __wprm( 'rows' ) }`
            }
            {
                false !== props.filter
                &&
                <Fragment>
                    <div className="wprm-admin-table-totals-filter">
                        { `${__wprm( 'Filter' ) }: ${ props.filter[0] } = ${ decodedFilter }` }
                    </div>
                    <a
                        href="#"
                        onClick={ () => props.onRemoveFilter() }
                    >{ __wprm( 'Remove fixed filter' ) }</a>
                </Fragment>
            }
        </div>
    );
}
export default Totals;