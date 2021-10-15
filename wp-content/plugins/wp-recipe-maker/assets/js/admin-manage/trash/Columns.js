import React from 'react';

import bulkEditCheckbox from '../general/bulkEditCheckbox';
import TextFilter from '../general/TextFilter';
import Api from 'Shared/Api';
import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';

export default {
    getColumns( recipes ) {
        let columns = [
            bulkEditCheckbox( recipes ),
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
                        type="restore"
                        title={ __wprm( 'Restore Recipe' ) }
                        onClick={() => {
                            Api.recipe.updateStatus(row.original.id, 'draft').then(() => recipes.refreshData());
                        }}
                    />
                    <Icon
                        type="trash"
                        title={ __wprm( 'Permanently Delete' ) }
                        onClick={() => {
                            if( confirm( `${ __wprm( 'Are you sure you want to delete' ) } "${row.original.name}"?` ) ) {
                                Api.recipe.delete(row.original.id, true).then(() => recipes.refreshData());
                            }
                        }}
                    />
                </div>
            ),
        },{
            Header: __wprm( 'ID' ),
            id: 'id',
            accessor: 'id',
            width: 65,
            Filter: (props) => (<TextFilter {...props}/>),
        },{
            Header: __wprm( 'Date' ),
            id: 'date',
            accessor: 'date',
            width: 150,
            Filter: (props) => (<TextFilter {...props}/>),
        },{
            Header: __wprm( 'Name' ),
            id: 'name',
            accessor: 'name',
            width: 300,
            Filter: (props) => (<TextFilter {...props}/>),
        }]

        return columns;
    }
};