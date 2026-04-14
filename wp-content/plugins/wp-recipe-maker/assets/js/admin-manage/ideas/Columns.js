import React from 'react';
import he from 'he';

import bulkEditCheckbox from '../general/bulkEditCheckbox';
import TextFilter from '../general/TextFilter';
import Api from 'Shared/Api';
import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';
import { getIdeaLabel, ideaSourceOptions, ideaStatusFilterOptions, ideaStatusOptions, ideaTypeOptions } from 'Shared/Ideas';

const renderSelectFilter = ( options, value, onChange, showAllLabel = __wprm( 'All' ) ) => (
    <select
        onChange={ ( event ) => onChange( event.target.value ) }
        style={ { width: '100%', fontSize: '1em' } }
        value={ value }
    >
        <option value="all">{ showAllLabel }</option>
        {
            options.map( ( option ) => (
                <option value={ option.value } key={ option.value }>{ option.label }</option>
            ) )
        }
    </select>
);

export default {
    getColumns( datatable ) {
        return [
            bulkEditCheckbox( datatable ),
            {
                Header: __wprm( 'Sort:' ),
                id: 'actions',
                headerClassName: 'wprm-admin-table-help-text',
                sortable: false,
                width: 70,
                Filter: () => (
                    <div>
                        { __wprm( 'Filter:' ) }
                    </div>
                ),
                Cell: row => (
                    <div className="wprm-admin-manage-actions">
                        <Icon
                            type="pencil"
                            title={ __wprm( 'Edit Idea' ) }
                            onClick={ () => {
                                WPRM_Modal.open( 'idea', {
                                    idea: row.original,
                                    saveCallback: () => datatable.refreshData(),
                                } );
                            } }
                        />
                        <Icon
                            type="trash"
                            title={ __wprm( 'Delete Idea' ) }
                            onClick={ () => {
                                if ( confirm( `${ __wprm( 'Are you sure you want to delete' ) } "${ row.original.name }"?` ) ) {
                                    Api.idea.delete( row.original.id, true ).then( () => datatable.refreshData() );
                                }
                            } }
                        />
                    </div>
                ),
            },
            {
                Header: __wprm( 'ID' ),
                id: 'id',
                accessor: 'id',
                width: 65,
                Filter: ( props ) => <TextFilter { ...props } />,
            },
            {
                Header: __wprm( 'Name' ),
                id: 'name',
                accessor: 'name',
                Filter: ( props ) => <TextFilter { ...props } />,
                Cell: row => (
                    <a
                        href="#"
                        onClick={ ( event ) => {
                            event.preventDefault();
                            WPRM_Modal.open( 'idea', {
                                idea: row.original,
                                saveCallback: () => datatable.refreshData(),
                            } );
                        } }
                    >
                        { row.value ? he.decode( row.value ) : '' }
                    </a>
                ),
            },
            {
                Header: __wprm( 'Summary' ),
                id: 'summary',
                accessor: 'summary',
                width: 350,
                sortable: false,
                Filter: ( props ) => <TextFilter { ...props } />,
                Cell: row => {
                    if ( ! row.value ) {
                        return <div></div>;
                    }

                    return <div className="wprm-admin-manage-rich-text" dangerouslySetInnerHTML={ { __html: row.value } } />;
                },
            },
            {
                Header: __wprm( 'Notes' ),
                id: 'notes',
                accessor: 'notes',
                width: 500,
                sortable: false,
                Filter: ( props ) => <TextFilter { ...props } />,
                Cell: row => {
                    if ( ! row.value ) {
                        return <div></div>;
                    }

                    return <div className="wprm-admin-manage-rich-text" dangerouslySetInnerHTML={ { __html: row.value } } />;
                },
            },
            {
                Header: __wprm( 'Status' ),
                id: 'status',
                accessor: 'status',
                width: 150,
                Filter: ( { filter, onChange } ) => renderSelectFilter( ideaStatusFilterOptions.filter( ( option ) => 'all' !== option.value ), filter ? filter.value : 'all', onChange, __wprm( 'All' ) ),
                Cell: row => (
                    <select
                        className="wprm-admin-manage-inline-select"
                        value={ row.value }
                        onChange={ ( event ) => {
                            Api.idea.updateStatus( row.original.id, event.target.value ).then( () => datatable.refreshData() );
                        } }
                    >
                        {
                            ideaStatusOptions.map( ( option ) => (
                                <option value={ option.value } key={ option.value }>{ option.label }</option>
                            ) )
                        }
                    </select>
                ),
            },
            {
                Header: __wprm( 'Type' ),
                id: 'type',
                accessor: 'type',
                width: 140,
                Filter: ( { filter, onChange } ) => renderSelectFilter( ideaTypeOptions, filter ? filter.value : 'all', onChange ),
                Cell: row => getIdeaLabel( 'type', row.value ),
            },
            {
                Header: __wprm( 'Source' ),
                id: 'source',
                accessor: 'source',
                width: 180,
                Filter: ( { filter, onChange } ) => renderSelectFilter( ideaSourceOptions, filter ? filter.value : 'all', onChange ),
                Cell: row => getIdeaLabel( 'source', row.value ),
            },
            {
                Header: __wprm( 'Created' ),
                id: 'date',
                accessor: 'date',
                width: 150,
                Filter: ( props ) => <TextFilter { ...props } />,
            },
            {
                Header: __wprm( 'Last Updated' ),
                id: 'last_updated',
                accessor: 'last_updated',
                width: 150,
                Filter: ( props ) => <TextFilter { ...props } />,
            },
        ];
    },
};
