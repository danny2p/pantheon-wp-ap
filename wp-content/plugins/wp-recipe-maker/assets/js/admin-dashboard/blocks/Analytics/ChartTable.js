import React, { Fragment } from 'react';

import { __wprm } from 'Shared/Translations';

const ChartTable = (props) => {
    if ( 0 === props.data.length ) {
        return null;
    }

    return (
        <div className="wprm-admin-dashboard-block-chart-table-container">
            <div className="wprm-admin-dashboard-block-chart-title">{ props.title }</div>
            <table className="wprm-admin-dashboard-block-chart-table">
                <thead>
                    <tr>
                        <th>{ props.label }</th>
                        <th>{ __wprm( 'Total' ) }</th>
                        <th>{ __wprm( 'Unique' ) }</th>
                    </tr>
                </thead>
                <tbody>
                    {
                        props.data.map( (row, index) => {
                            return (
                                <tr key={ index }>
                                    <td>{ row.name }</td>
                                    <td>{ row.total }</td>
                                    <td>{ row.unique }</td>
                                </tr>
                            )
                        })
                    }
                </tbody>
            </table>
        </div>
    );

    
}
export default ChartTable;