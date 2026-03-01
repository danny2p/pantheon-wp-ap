import React, { Component } from 'react';
import ReactTable from 'react-table';
import 'react-table/react-table.css';

import '../../css/admin/manage/table.scss';

import { __wprm } from 'Shared/Translations';
import ErrorBoundary from 'Shared/ErrorBoundary';
import SelectColumns from './general/SelectColumns';
import Api from 'Shared/Api';
import Totals from './general/Totals';
import { downloadToCsv } from 'Shared/CSV';

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
    exporting: false,
};

const exportPageSize = 500;

const normalizeExportText = ( value ) => {
    if ( null === value || 'undefined' === typeof value ) {
        return '';
    }

    return `${ value }`.replace( /\s+/g, ' ' ).trim();
};

const getHeaderText = ( header ) => {
    if ( 'string' === typeof header || 'number' === typeof header ) {
        return normalizeExportText( header );
    }

    if ( Array.isArray( header ) ) {
        return normalizeExportText( header.map( ( item ) => getHeaderText( item ) ).filter( ( item ) => item ).join( ' ' ) );
    }

    if ( React.isValidElement( header ) ) {
        return getHeaderText( header.props.children );
    }

    return '';
};

const stripHtml = ( value ) => {
    return `${ value }`.replace( /<[^>]*>/g, ' ' );
};

const shouldRunOnce = (() => {
    let executed = false;
    return () => {
        if ( ! executed ) {
            return true;
        }
        return false;
    };
})();

// Get default page size.
let defaultPageSize = 25;
const pageSizeOptions = [5, 10, 20, 25, 50, 100, 500];
let savedPageSize = localStorage.getItem( 'wprm-admin-manage-page-size' );

if ( savedPageSize ) {
    savedPageSize = parseInt( savedPageSize );

    if ( pageSizeOptions.includes( savedPageSize ) ) {
        defaultPageSize = savedPageSize;
    }
}

export default class DataTable extends Component {
    constructor(props) {
        super(props);

        this.state = {
            ...initState,
        };

        this.tableInnerRef = React.createRef();
        this.latestFetchState = {
            page: 0,
            pageSize: defaultPageSize,
            sorted: this.getDefaultSort(),
        };

        this.initDataTable = this.initDataTable.bind(this);
        this.refreshData = this.refreshData.bind(this);
        this.fetchData = this.fetchData.bind(this);
        this.toggleSelectRow = this.toggleSelectRow.bind(this);
        this.toggleSelectAll = this.toggleSelectAll.bind(this);
        this.getSelectedRows = this.getSelectedRows.bind(this);
        this.onColumnsChange = this.onColumnsChange.bind(this);
        this.requirementMet = this.requirementMet.bind(this);
        this.getVisibleColumns = this.getVisibleColumns.bind(this);
        this.getExportColumns = this.getExportColumns.bind(this);
        this.openExportModal = this.openExportModal.bind(this);
        this.exportToCsv = this.exportToCsv.bind(this);
        this.fetchRowsForExport = this.fetchRowsForExport.bind(this);
        this.getExportCellValue = this.getExportCellValue.bind(this);
        this.getRawColumnValue = this.getRawColumnValue.bind(this);
        this.getFormattedExportValue = this.getFormattedExportValue.bind(this);
        this.getDomRowMap = this.getDomRowMap.bind(this);
        this.getCellTextFromDom = this.getCellTextFromDom.bind(this);
        this.getColumnHeader = this.getColumnHeader.bind(this);
        this.hasActiveFilters = this.hasActiveFilters.bind(this);
        this.getMeaningfulFilters = this.getMeaningfulFilters.bind(this);
        this.isMeaningfulFilterValue = this.isMeaningfulFilterValue.bind(this);
        this.getExportFileName = this.getExportFileName.bind(this);
    }

    componentDidMount() {
        this.initDataTable();

        // check if action query paramater is set.
        const urlParams = new URLSearchParams( window.location.search );
        const action = urlParams.get( 'action' );

        if ( 'create' === action ) {
            if ( this.props.options.createButton ) {
                if ( shouldRunOnce() ) {
                    setTimeout(() => {
                        this.props.options.createButton( this );
                    });
                }
            }
        }

        // Remove query parameter again.
        if ( history.replaceState ) {
            urlParams.delete( 'action' );
            const searchString = urlParams.toString().length > 0 ? '?' + urlParams.toString() : '';
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + searchString + window.location.hash;
            history.replaceState (null, '', newUrl );
        }
    }

    componentDidUpdate( prevProps ) {
        if ( this.props.type !== prevProps.type || this.props.filter !== prevProps.filter ) {
            this.initDataTable( true );
        }
    }

    getDefaultSort() {
        return [{
            id: 'rating' === this.props.type ? 'date' : 'id',
            desc: true,
        }];
    }

    isMeaningfulFilterValue( value ) {
        if ( null === value || 'undefined' === typeof value ) {
            return false;
        }

        if ( 'string' === typeof value ) {
            const normalizedValue = value.trim().toLowerCase();
            return '' !== normalizedValue && 'all' !== normalizedValue;
        }

        if ( Array.isArray( value ) ) {
            return value.length > 0;
        }

        return true;
    }

    getMeaningfulFilters( filters = this.state.filtered ) {
        return ( filters || [] ).filter( ( filter ) => {
            return filter && this.isMeaningfulFilterValue( filter.value );
        });
    }

    hasActiveFilters() {
        const hasInteractiveFilters = this.getMeaningfulFilters().length > 0;
        const hasFixedFilter = Array.isArray( this.props.filter ) && 2 === this.props.filter.length;

        return hasInteractiveFilters || hasFixedFilter;
    }

    getVisibleColumns() {
        return this.state.columns.filter( ( column ) => {
            return 'actions' === column.id || false === this.state.selectedColumns || this.state.selectedColumns.includes( column.id );
        });
    }

    getExportColumns( visibleColumns = this.getVisibleColumns() ) {
        return visibleColumns.filter( ( column ) => {
            return ! [ 'actions', 'bulk_edit' ].includes( column.id );
        });
    }

    getColumnHeader( column ) {
        const headerText = getHeaderText( column.Header );

        return headerText || normalizeExportText( column.id );
    }

    getCellTextFromDom( cellElement ) {
        if ( ! cellElement ) {
            return '';
        }

        const fieldElement = cellElement.querySelector( 'input, textarea, select' );

        if ( fieldElement ) {
            if ( 'SELECT' === fieldElement.tagName ) {
                if ( fieldElement.selectedOptions && fieldElement.selectedOptions.length ) {
                    return normalizeExportText(
                        Array.from( fieldElement.selectedOptions )
                            .map( ( option ) => option.textContent )
                            .join( ', ' )
                    );
                }

                return normalizeExportText( fieldElement.value );
            }

            if ( 'checkbox' === fieldElement.type ) {
                return fieldElement.checked ? __wprm( 'Yes' ) : __wprm( 'No' );
            }

            return normalizeExportText( fieldElement.value );
        }

        return normalizeExportText( cellElement.textContent );
    }

    getDomRowMap( visibleColumns ) {
        const rowMap = [];

        if ( ! this.tableInnerRef.current ) {
            return rowMap;
        }

        const rowElements = this.tableInnerRef.current.querySelectorAll( '.ReactTable .rt-tbody .rt-tr-group .rt-tr:not(.-padRow)' );

        rowElements.forEach( ( rowElement ) => {
            const cellElements = rowElement.querySelectorAll( '.rt-td' );
            const rowValues = {};

            visibleColumns.forEach( ( column, index ) => {
                rowValues[ column.id ] = this.getCellTextFromDom( cellElements[ index ] );
            });

            rowMap.push( rowValues );
        });

        return rowMap;
    }

    getRawColumnValue( row, column ) {
        if ( ! row || ! column ) {
            return '';
        }

        if ( 'function' === typeof column.accessor ) {
            try {
                return column.accessor( row );
            } catch ( error ) {
                return '';
            }
        }

        if ( 'string' === typeof column.accessor && row.hasOwnProperty( column.accessor ) ) {
            return row[ column.accessor ];
        }

        if ( column.id && row.hasOwnProperty( column.id ) ) {
            return row[ column.id ];
        }

        return '';
    }

    isRatingsObject( value ) {
        if ( ! value || 'object' !== typeof value || Array.isArray( value ) ) {
            return false;
        }

        return value.hasOwnProperty( 'average' ) || value.hasOwnProperty( 'comment_ratings' ) || value.hasOwnProperty( 'user_ratings' );
    }

    getRatingsCount( value ) {
        if ( ! this.isRatingsObject( value ) ) {
            return '';
        }

        const commentRatings = value.comment_ratings && 'object' === typeof value.comment_ratings ? parseInt( value.comment_ratings.count ) || 0 : 0;
        const userRatings = value.user_ratings && 'object' === typeof value.user_ratings ? parseInt( value.user_ratings.count ) || 0 : 0;

        return commentRatings + userRatings;
    }

    getRatingsSummary( value ) {
        if ( ! this.isRatingsObject( value ) ) {
            return '';
        }

        const average = value.average && '0' !== `${ value.average }` ? normalizeExportText( value.average ) : '';
        return average;
    }

    getFormattedExportValue( value ) {
        if ( null === value || 'undefined' === typeof value ) {
            return '';
        }

        if ( 'boolean' === typeof value ) {
            return value ? __wprm( 'Yes' ) : __wprm( 'No' );
        }

        if ( Array.isArray( value ) ) {
            return normalizeExportText(
                value
                    .map( ( item ) => this.getFormattedExportValue( item ) )
                    .filter( ( item ) => '' !== item )
                    .join( ', ' )
            );
        }

        if ( 'object' === typeof value ) {
            if ( value.hasOwnProperty( 'post_title' ) ) {
                return this.getFormattedExportValue( value.post_title );
            }

            if ( value.hasOwnProperty( 'title' ) ) {
                return this.getFormattedExportValue( value.title );
            }

            if ( value.hasOwnProperty( 'name' ) ) {
                return this.getFormattedExportValue( value.name );
            }

            if ( value.hasOwnProperty( 'label' ) ) {
                return this.getFormattedExportValue( value.label );
            }

            if ( value.hasOwnProperty( 'value' ) && ( 'string' === typeof value.value || 'number' === typeof value.value ) ) {
                return this.getFormattedExportValue( value.value );
            }

            if ( this.isRatingsObject( value ) ) {
                return this.getRatingsSummary( value );
            }

            if ( value.hasOwnProperty( 'type' ) ) {
                if ( value.hasOwnProperty( 'message' ) && 'string' === typeof value.message ) {
                    return normalizeExportText( `${ value.type }: ${ stripHtml( value.message ) }` );
                }

                return this.getFormattedExportValue( value.type );
            }

            if ( value.hasOwnProperty( 'message' ) && 'string' === typeof value.message ) {
                return normalizeExportText( stripHtml( value.message ) );
            }

            if ( value.hasOwnProperty( 'url' ) ) {
                return this.getFormattedExportValue( value.url );
            }

            // Avoid dumping entire object payloads (e.g. WP_Post) into CSV.
            return '';
        }

        return normalizeExportText( value );
    }

    getExportCellValue( row, column, domValue = '' ) {
        const rawValue = this.getRawColumnValue( row, column );

        if ( 'rating_count' === column.id ) {
            const totalRatings = this.getRatingsCount( rawValue );
            return totalRatings ? `${ totalRatings }` : '';
        }

        if ( 'rating' === column.id && this.isRatingsObject( rawValue ) ) {
            return this.getRatingsSummary( rawValue );
        }

        if ( domValue ) {
            return domValue;
        }

        return this.getFormattedExportValue( rawValue );
    }

    getExportFileName( scope ) {
        const date = new Date().toISOString().slice( 0, 10 );
        return `wprm-manage-${ this.props.options.id }-${ scope }-${ date }`;
    }

    async fetchRowsForExport( args ) {
        const sorted = this.latestFetchState.sorted && this.latestFetchState.sorted.length ? this.latestFetchState.sorted : this.getDefaultSort();
        const rows = [];
        let page = 0;
        let pages = 1;

        while ( page < pages ) {
            const response = await Api.manage.getData({
                route: this.props.options.route,
                type: this.props.options.id,
                pageSize: exportPageSize,
                page,
                sorted,
                filtered: args.filtered,
                filter: args.filter,
            });

            if ( ! response ) {
                break;
            }

            const responseRows = Array.isArray( response.rows ) ? response.rows : [];
            rows.push( ...responseRows );

            pages = response.pages ? parseInt( response.pages ) : 0;
            if ( ! pages ) {
                break;
            }

            page += 1;
        }

        return rows;
    }

    async exportToCsv( scope = 'current_view' ) {
        if ( this.state.exporting ) {
            return false;
        }

        const visibleColumns = this.getVisibleColumns();
        const exportColumns = this.getExportColumns( visibleColumns );

        if ( 0 === exportColumns.length ) {
            return false;
        }

        this.setState({
            exporting: true,
        });

        let success = false;

        try {
            let rows = [];
            let scopeKey = 'current';
            let domRowMap = [];

            switch ( scope ) {
                case 'all_filtered':
                    rows = await this.fetchRowsForExport({
                        filtered: this.getMeaningfulFilters(),
                        filter: this.props.filter,
                    });
                    scopeKey = 'filtered';
                    break;
                case 'all_rows':
                    rows = await this.fetchRowsForExport({
                        filtered: [],
                        filter: false,
                    });
                    scopeKey = 'all';
                    break;
                default:
                    rows = this.state.data;
                    domRowMap = this.getDomRowMap( visibleColumns );
                    scopeKey = 'current';
                    break;
            }

            const headers = exportColumns.map( ( column ) => this.getColumnHeader( column ) );
            const csvRows = rows.map( ( row, rowIndex ) => {
                return exportColumns.map( ( column ) => {
                    const domValue = domRowMap[ rowIndex ] && domRowMap[ rowIndex ].hasOwnProperty( column.id ) ? domRowMap[ rowIndex ][ column.id ] : '';
                    return this.getExportCellValue( row, column, domValue );
                });
            });

            downloadToCsv( this.getExportFileName( scopeKey ), headers, csvRows );
            success = true;
        } catch ( error ) {
            console.error( 'WPRM manage CSV export failed', error );
            alert( __wprm( 'Could not export this table. Please try again.' ) );
        } finally {
            this.setState({
                exporting: false,
            });
        }

        return success;
    }

    openExportModal() {
        const currentRows = Array.isArray( this.state.data ) ? this.state.data.length : 0;
        const filteredRows = false !== this.state.countFiltered ? parseInt( this.state.countFiltered ) : currentRows;
        const totalRows = false !== this.state.countTotal ? parseInt( this.state.countTotal ) : filteredRows;
        const showFilteredOption = this.hasActiveFilters() && filteredRows !== totalRows;

        if ( 'undefined' === typeof WPRM_Modal || ! WPRM_Modal || ! WPRM_Modal.open ) {
            this.exportToCsv( 'current_view' );
            return;
        }

        WPRM_Modal.open( 'manage-export-csv', {
            currentRows,
            filteredRows,
            totalRows,
            showFilteredOption,
            onConfirm: ( scope ) => this.exportToCsv( scope ),
        });
    }

    initDataTable( forceRefresh = false ) {
        // Only init when requirement is met.
        if ( ! this.requirementMet() ) {
            return;
        }

        this.latestFetchState = {
            page: 0,
            pageSize: defaultPageSize,
            sorted: this.getDefaultSort(),
        };

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

        this.latestFetchState = {
            page: state.page,
            pageSize: state.pageSize,
            sorted: state.sorted && state.sorted.length ? state.sorted : this.getDefaultSort(),
        };

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

        const { data, pages, loading, exporting } = this.state;
        const selectedColumns = this.getVisibleColumns();
        const exportColumns = this.getExportColumns( selectedColumns );
        const filteredColumns = this.getMeaningfulFilters().map( ( filter ) => filter.id );

        return (
            <div className="wprm-admin-manage-page">
                <div className="wprm-admin-manage-header">
                    <SelectColumns
                        onColumnsChange={this.onColumnsChange}
                        columns={this.state.columns}
                        selectedColumns={this.state.selectedColumns}
                        filteredColumns={filteredColumns}
                    />
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
                        <button
                            className="button"
                            onClick={ this.openExportModal }
                            disabled={ loading || exporting || 0 === exportColumns.length }
                        >{ __wprm( 'Export to CSV' ) }</button>
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
                <div className="wprm-admin-manage-table-container">
                    <ErrorBoundary module="Datatable">
                        <Totals
                            filtered={this.state.countFiltered}
                            total={this.state.countTotal}
                            filter={this.props.filter}
                            onRemoveFilter={this.props.onRemoveFilter}
                        />
                        <div className="wprm-admin-manage-table-inner" ref={ this.tableInnerRef }>
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
                                defaultPageSize={ defaultPageSize }
                                pageSizeOptions={ pageSizeOptions }
                                onPageSizeChange={ (pageSize) => {
                                    localStorage.setItem( 'wprm-admin-manage-page-size', pageSize );
                                }}
                                defaultSorted={ this.getDefaultSort() }
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
