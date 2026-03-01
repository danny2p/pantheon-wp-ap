import React, { Component } from 'react';
import ReactTable from 'react-table';
import 'react-table/react-table.css';

import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';
import { downloadToCsv } from 'Shared/CSV';

let reportData = false;

if ( typeof window.wprm_reports_data !== 'undefined' ) {
    reportData = window.wprm_reports_data;
}

export default class App extends Component {
    constructor(props) {
        super(props);

        this.state = {
            valueType: 'total',
        };
    }

    render() {
        if ( false === reportData ) {
            return (
                <div>{ __wprm( 'No data found.' ) }</div>
            );
        }

        const valueType = this.state.valueType;
        const data = Object.values( reportData );
        const valueOptions = [
            { value: 'total', label: __wprm( 'Total Interactions' ) },
            { value: 'unique', label: __wprm( 'Unique Visitor Interactions' ) },
            { value: 'print', label: __wprm( 'Total Prints' ) },
            { value: 'print_unique', label: __wprm( 'Unique Visitor Prints' ) },
        ];

        const selectedValueOption = valueOptions.find(
            ( option ) => option.value === valueType
        ) || valueOptions[0];

        const dailyKey = valueType + '_daily';
        const sevenDayKey = valueType + '_7_days';
        const thirtyOneDayKey = valueType + '_31_days';
        const threeHundredSixtyFiveDayKey = valueType + '_365_days';

        const columns = [{
            Header: __wprm( 'Sort:' ),
            id: 'actions',
            accessor: 'recipe_id',
            headerClassName: 'wprm-admin-table-help-text',
            sortable: false,
            width: 70,
            Filter: () => (
                <div>
                    { __wprm( 'Filter:' ) }
                </div>
            ),
            Cell: row => (
                <div className="wprm-report-table-actions">
                    <Icon
                        type="pencil"
                        title={ __wprm( 'Edit Recipe' ) }
                        onClick={() => {
                            WPRM_Modal.open( 'recipe', {
                                recipeId: row.value,
                            } );
                        }}
                    />
                    {
                        row.original.recipe_permalink
                        &&
                        <Icon
                            type="link"
                            title={ __wprm( 'View Recipe' ) }
                            onClick={() => {
                                window.open( row.original.recipe_permalink, '_blank' );
                            }}
                        />
                    }
                </div>
            ),
        },{
            Header: __wprm( 'Recipe Name' ),
            accessor: 'recipe_name',
            width: 300,
        },{
            Header: __wprm( 'Recipe Date' ),
            accessor: 'recipe_date',
            width: 150,
        },{
            Header: __wprm( 'Average per Day' ),
            accessor: valueType + '_daily',
            width: 120,
            filterable: false,
            Cell: row => (
                <div>
                    { Math.ceil( row.value * 100 ) / 100 }
                </div>
            ),
            className: 'wprm-report-table-center',
        },{
            Header: __wprm( 'Last 7 Days' ),
            accessor: valueType + '_7_days',
            width: 120,
            filterable: false,
            className: 'wprm-report-table-center',
        },{
            Header: __wprm( 'Last 31 Days' ),
            accessor: valueType + '_31_days',
            width: 120,
            filterable: false,
            className: 'wprm-report-table-center',
        },{
            Header: __wprm( 'Last 365 Days' ),
            accessor: valueType + '_365_days',
            width: 120,
            filterable: false,
            className: 'wprm-report-table-center',
        }];
        
        return (
            <div>
                <div className="wprm-report-table-value-container">
                    { valueOptions.map((option) => (
                        <label key={option.value}>
                            <input
                                type="radio"
                                value={option.value}
                                checked={this.state.valueType === option.value}
                                onChange={() => this.setState({ valueType: option.value })}
                            />
                            {option.label}
                        </label>
                    ))}
                </div>
                <div className="wprm-report-table-export-container">
                    <button
                        type="button"
                        className="button"
                        onClick={() => {
                            const rows = data
                                .slice()
                                .sort((a, b) => {
                                    const aValue = Number( a[ dailyKey ] ) || 0;
                                    const bValue = Number( b[ dailyKey ] ) || 0;

                                    return bValue - aValue;
                                })
                                .map((recipe) => [
                                    recipe.recipe_name,
                                    recipe.recipe_date,
                                    Math.ceil( ( Number( recipe[ dailyKey ] ) || 0 ) * 100 ) / 100,
                                    recipe[ sevenDayKey ],
                                    recipe[ thirtyOneDayKey ],
                                    recipe[ threeHundredSixtyFiveDayKey ],
                                ]);

                            downloadToCsv(
                                `recipe-interactions-${ selectedValueOption.value }`,
                                [
                                    __wprm( 'Recipe Name' ),
                                    __wprm( 'Recipe Date' ),
                                    __wprm( 'Average per Day' ),
                                    __wprm( 'Last 7 Days' ),
                                    __wprm( 'Last 31 Days' ),
                                    __wprm( 'Last 365 Days' ),
                                ],
                                rows
                            );
                        }}
                    >
                        { __wprm( 'Download to CSV' ) }
                    </button>
                </div>
                <ReactTable
                    data={data}
                    columns={columns}
                    showPagination={false}
                    defaultPageSize={data.length}
                    defaultSorted={[
                        {
                            id: valueType + '_daily',
                            desc: true,
                        },
                    ]}
                    filterable={true}
                    defaultFilterMethod={(filter, row, column) => {
                        const id = filter.pivotId || filter.id;
                        return row[id] !== undefined
                            ? String(row[id]).toLocaleLowerCase().includes(filter.value.toLowerCase())
                            : true;
                    }}
                    resizable={false}
                    className="wprm-admin-table wprm-report-table -highlight"
                />
            </div>
        );
    }
}
