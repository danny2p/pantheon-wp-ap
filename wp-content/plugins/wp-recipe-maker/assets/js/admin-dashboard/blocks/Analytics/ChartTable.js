import React, { Fragment } from 'react';

import Icon from 'Shared/Icon';
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
                            const recipeId = row.hasOwnProperty( 'recipeId' ) ? row.recipeId : false;
                            const permalink = row.hasOwnProperty( 'permalink' ) ? row.permalink : false;

                            return (
                                <tr key={ index }>
                                    <td>
                                        {
                                            recipeId
                                            ?
                                            <div className="wprm-admin-dashboard-block-chart-table-recipe">
                                                <div className="wprm-admin-dashboard-block-chart-table-recipe-name">{ row.name }</div>
                                                <div className="wprm-admin-dashboard-block-chart-table-actions">
                                                    {
                                                        permalink
                                                        &&
                                                        <div className="wprm-admin-dashboard-block-chart-table-action">
                                                            <a href={ permalink } target="_blank">
                                                                <Icon
                                                                    type="eye"
                                                                    title={ __wprm( 'View Recipe' ) }
                                                                />
                                                            </a>
                                                        </div>
                                                    }
                                                    <div className="wprm-admin-dashboard-block-chart-table-action">
                                                        <Icon
                                                            type="pencil"
                                                            title={ __wprm( 'Edit Recipe' ) }
                                                            onClick={() => {
                                                                WPRM_Modal.open( 'recipe', {
                                                                    recipeId,
                                                                } );
                                                            }}
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                            :
                                            row.name
                                        }
                                    </td>
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