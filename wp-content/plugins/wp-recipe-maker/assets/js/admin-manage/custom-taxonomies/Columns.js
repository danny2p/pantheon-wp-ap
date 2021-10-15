import React from 'react';
import he from 'he';

import Api from 'Shared/Api';
import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';

export default {
    getColumns( datatable ) {
        let columns = [{
            Header: '',
            id: 'actions',
            headerClassName: 'wprm-admin-table-help-text',
            sortable: false,
            filterable: false,
            width: 70,
            Cell: row => (
                <div className="wprm-admin-manage-actions">
                    <Icon
                        type="pencil"
                        title={ __wprm( 'Edit Taxonomy' ) }
                        onClick={() => {
                            WPRM_Modal.open( 'taxonomy', {
                                taxonomy: row.original,
                                saveCallback: () => datatable.refreshData(),
                            } );
                        }}
                    />
                    {
                        false === row.original.default
                        &&
                        <Icon
                            type="trash"
                            title={ __wprm( 'Delete Taxonomy' ) }
                            onClick={() => {
                                if( confirm( `${ __wprm( 'Are you sure you want to delete' ) } "wprm_${row.original.key}"?` ) ) {
                                    Api.customTaxonomy.delete(row.original.key).then(() => datatable.refreshData());
                                }
                            }}
                        />
                    }
                </div>
            ),
        },{
            Header: __wprm( 'Key' ),
            id: 'key',
            accessor: 'key',
            sortable: false,
            filterable: false,
            Cell: row => (<div>wprm_{ row.value }</div>),
        },{
            Header: __wprm( 'Singular Name' ),
            id: 'singular_name',
            accessor: 'singular_name',
            sortable: false,
            filterable: false,
            Cell: row => row.value ? he.decode(row.value) : null,
        },{
            Header: __wprm( 'Plural Name' ),
            id: 'name',
            accessor: 'name',
            sortable: false,
            filterable: false,
            Cell: row => row.value ? he.decode(row.value) : null,
        },{
            Header: __wprm( 'Archive' ),
            id: 'archive',
            accessor: 'archive',
            sortable: false,
            filterable: false,
            width: 70,
            Cell: row => (
                row.value
                ?
                __wprm( 'Yes' )
                :
                __wprm( 'No' )
            ),
        },{
            Header: __wprm( 'Slug' ),
            id: 'slug',
            accessor: 'slug',
            sortable: false,
            filterable: false,
            Cell: row => row.value ? he.decode(row.value) : null,
        }];

        return columns;
    }
};