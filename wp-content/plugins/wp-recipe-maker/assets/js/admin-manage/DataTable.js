import React, { Component } from 'react';
import ReactTable from 'react-table';
import 'react-table/react-table.css';

import '../../css/admin/manage/table.scss';

import { __wprm } from 'Shared/Translations';
import ErrorBoundary from 'Shared/ErrorBoundary';
import SelectColumns from './general/SelectColumns';
import Api from 'Shared/Api';
import Totals from './general/Totals';

const initState = {
    data: [],
    pages: null,
    filtered: [],
    countFiltered: false,
    countTotal: false,
    loading: true,
    columns: [],
    selectedColumns: false,
    selectedRows: {},
    selectedAllRows: 0,
};

export default class DataTable extends Component {
    constructor(props) {
        super(props);

        this.state = {
            ...initState,
        };

        this.initDataTable = this.initDataTable.bind(this);
        this.refreshData = this.refreshData.bind(this);
        this.fetchData = this.fetchData.bind(this);
        this.toggleSelectRow = this.toggleSelectRow.bind(this);
        this.toggleSelectAll = this.toggleSelectAll.bind(this);
        this.getSelectedRows = this.getSelectedRows.bind(this);
        this.onColumnsChange = this.onColumnsChange.bind(this);
        this.requirementMet = this.requirementMet.bind(this);
    }

    componentDidMount() {
        this.initDataTable();
    }

    componentDidUpdate( prevProps ) {
        if ( this.props.type !== prevProps.type || this.props.filter !== prevProps.filter ) {
            this.initDataTable( true );
        }
    }

    initDataTable( forceRefresh = false ) {
        // Only init when requirement is met.
        if ( ! this.requirementMet() ) {
            return;
        }

        // Use default selectedColumns or restore from LocalStorage.
        let selectedColumns = this.props.options.selectedColumns;

        if ( false !== selectedColumns ) {
            let savedSelectedColumns = localStorage.getItem( `wprm-admin-manage-${ this.props.options.id }-columns` );

            if ( savedSelectedColumns ) {
                savedSelectedColumns = JSON.parse(savedSelectedColumns);

                if (Array.isArray(savedSelectedColumns)) {
                    selectedColumns = savedSelectedColumns;
                }
            }
        }

        this.setState({
            ...initState,
            columns: this.props.options.columns.getColumns( this ),
            selectedColumns: selectedColumns,
        }, () => {
            if ( forceRefresh ) {
                this.refreshData();
            }
        });
    }

    toggleSelectRow(id) {
        let newSelected = { ...this.state.selectedRows };

        newSelected[id] = !newSelected[id];

        const nbrSelected = Object.values(newSelected).filter(value => value).length;
        let selectedAllRows = 2;

        if ( 0 === nbrSelected ) {
            selectedAllRows = 0;
        } else if ( this.state.data.length === nbrSelected ) {
            selectedAllRows = 1;
        }

        this.setState({
            selectedRows: newSelected,
            selectedAllRows,
        });
    }

    toggleSelectAll() {
        const bulkEditKey = 'taxonomy' === this.props.options.route ? 'term_id' : 'id';
        let newSelected = {};

        if ( 0 === this.state.selectedAllRows ) {
            for ( let row of this.state.data ) {
                newSelected[ row[ bulkEditKey ] ] = true;
            }
        }

        this.setState({
            selectedRows: newSelected,
            selectedAllRows: 0 === this.state.selectedAllRows ? 1 : 0,
        });
    }

    getSelectedRows() {
        return Object.keys(this.state.selectedRows).filter(id => this.state.selectedRows[id]).map(id => parseInt(id));
    }

    refreshData() {
        if ( this.refReactTable ) {
            this.refReactTable.fireFetchData();
        }
    }

    fetchData(state, instance) {
        const currentData = state.data;

        this.setState({
            loading: true,
        }, () => {
            if ( this.requirementMet() ) {
                Api.manage.getData({
                    route: this.props.options.route,
                    type: this.props.options.id,
                    pageSize: state.pageSize,
                    page: state.page,
                    sorted: state.sorted,
                    filtered: this.state.filtered,
                    filter: this.props.filter,
                }).then(data => {
                    if ( data ) {
                        let newState = {
                            data: data.rows,
                            pages: data.pages,
                            countFiltered: data.filtered,
                            countTotal: data.total,
                            loading: false,
                        };
        
                        const bulkEditKey = 'taxonomy' === this.props.options.route ? 'term_id' : 'id';
                        if ( JSON.stringify( data.rows.map( row => row[ bulkEditKey ] ) ) !== JSON.stringify( currentData.map( row => row[ bulkEditKey ] )  ) ) {
                            newState.selectedRows = {};
                            newState.selectedAllRows = 0;
                        }
        
                        this.setState(newState);
                    }
                });
            } 
        });
    }

    onColumnsChange(id, checked) {
        let selectedColumns = [ ...this.state.selectedColumns ];

        if (checked) {
            selectedColumns.push(id);
        } else {
            selectedColumns = selectedColumns.filter(c => c !== id);
        }

        this.setState({
            selectedColumns
        });

        localStorage.setItem( `wprm-admin-manage-${ this.props.options.id }-columns`, JSON.stringify( selectedColumns ) );
    }

    requirementMet() {
        if ( this.props.options.hasOwnProperty( 'required' ) && ( ! wprm_admin.addons.hasOwnProperty( this.props.options.required ) || true !== wprm_admin.addons[ this.props.options.required ] ) ) {
            return false;
        }

        return true;
    }

    render() {
        if ( ! this.props.options ) {
            return null;
        }

        // Check if Premium requirement is met.
        if ( ! this.requirementMet() ) {
            const bundle = this.props.options.required[0].toUpperCase() + this.props.options.required.substring(1);

            return (
                <div className="wprm-admin-manage-requirement">
                    <div>*{ __wprm( 'This feature is only available in' ) }</div>
                    <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">
                        { `WP Recipe Maker ${bundle} Bundle` }
                    </a>
                </div>
            );
        }

        const { data, pages, loading } = this.state;
        const selectedColumns = this.state.columns.filter(column => 'actions' === column.id || false === this.state.selectedColumns || this.state.selectedColumns.includes(column.id));
        const filteredColumns = this.state.filtered.filter( filter => '' !== filter.value && 'all' !== filter.value ).map( filter => filter.id );

        return (
            <div className="wprm-admin-manage-page">
                {
                    false !== this.state.selectedColumns
                    || this.props.options.bulkEdit
                    || this.props.options.createButton
                    ?
                    <div className="wprm-admin-manage-header">
                        {
                            false === this.state.selectedColumns
                            ?
                            <div></div>
                            :
                            <SelectColumns
                                onColumnsChange={this.onColumnsChange}
                                columns={this.state.columns}
                                selectedColumns={this.state.selectedColumns}
                                filteredColumns={filteredColumns}
                            />
                        }
                        <div className="wprm-admin-manage-header-buttons">
                            {
                                ( false === this.state.selectedColumns || this.state.selectedColumns.includes( 'bulk_edit' ) )
                                && this.props.options.bulkEdit
                                && <button
                                    className="button"
                                    onClick={ () => {
                                        WPRM_Modal.open( 'bulk-edit', {
                                            route: this.props.options.bulkEdit.route,
                                            type: this.props.options.bulkEdit.type,
                                            ids: this.getSelectedRows(),
                                            saveCallback: () => this.refreshData(),
                                        } );
                                    }}
                                    disabled={ 0 === this.getSelectedRows().length }
                                >{ __wprm( 'Bulk Edit' ) } { this.getSelectedRows().length } { 1 === this.getSelectedRows().length ? this.props.options.label.singular : this.props.options.label.plural }...</button>
                            }
                            {
                                this.props.options.createButton
                                ?
                                <button
                                    className="button button-primary"
                                    onClick={ () => this.props.options.createButton( this ) }
                                >{ `${__wprm( 'Create' )} ${ this.props.options.label.singular }` }</button>
                                :
                                null
                            }
                        </div>
                    </div>
                    :
                    null
                }
                <div className="wprm-admin-manage-table-container">
                    <ErrorBoundary module="Datatable">
                        <Totals
                            filtered={this.state.countFiltered}
                            total={this.state.countTotal}
                            filter={this.props.filter}
                            onRemoveFilter={this.props.onRemoveFilter}
                        />
                        <div className="wprm-admin-manage-table-inner">
                            <ReactTable
                                ref={(refReactTable) => {this.refReactTable = refReactTable;}}
                                manual
                                columns={selectedColumns}
                                data={data}
                                pages={pages}
                                filtered={this.state.filtered}
                                onFilteredChange={ filtered => {
                                    this.setState( { filtered } );
                                } }
                                loading={ loading }
                                onFetchData={this.fetchData}
                                defaultPageSize={ this.props.options.hasOwnProperty( 'defaultPageSize' ) ? this.props.options.defaultPageSize : 25 }
                                pageSizeOptions={ [5, 10, 20, 25, 50, 100, 500] }
                                defaultSorted={[{
                                    id: 'rating' === this.props.type ? 'date' : 'id',
                                    desc: true
                                }]}
                                filterable
                                resizable={false}
                                className="wprm-admin-manage-table wprm-admin-table -highlight"
                            />
                        </div>
                    </ErrorBoundary>
                </div>
            </div>
        );
    }
}