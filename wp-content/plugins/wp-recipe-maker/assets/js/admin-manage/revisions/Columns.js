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
                        type="eye"
                        title={ __wprm( 'View Revision' ) }
                        onClick={() => {
                            WPRM_Modal.open( 'recipe', {
                                recipe: row.original.recipe_data,
                                restoreRevision: true,
                                saveCallback: () => recipes.refreshData(),
                            } );
                        }}
                    />             
                    <Icon
                        type="trash"
                        title={ __wprm( 'Delete Revision' ) }
                        onClick={() => {
                            if( confirm( `${ __wprm( 'Are you sure you want to delete' ) } "${row.original.post_title}"?` ) ) {
                                Api.recipe.deleteRevision(row.original.id).then(() => recipes.refreshData());
                            }
                        }}
                    />
                </div>
            ),
        },{
            Header: __wprm( 'Recipe ID' ),
            id: 'recipe_id',
            accessor: 'post_parent',
            width: 75,
            Filter: (props) => (<TextFilter {...props}/>),
        },{
            Header: __wprm( 'Revision ID' ),
            id: 'id',
            accessor: 'id',
            width: 75,
            Filter: (props) => (<TextFilter {...props}/>),
        },{
            Header: __wprm( 'Date' ),
            id: 'date',
            accessor: 'post_date',
            width: 150,
            Filter: (props) => (<TextFilter {...props}/>),
        },{
            Header: __wprm( 'Name' ),
            id: 'name',
            accessor: 'post_title',
            Filter: (props) => (<TextFilter {...props}/>),
        },{
            Header: __wprm( 'Recipe Data Size' ),
            id: 'recipe_data',
            accessor: 'recipe_data',
            sortable: false,
            filterable: false,
            width: 150,
            Cell: row => {
                const emptyRecipeLength = JSON.stringify( wprm_admin_modal.recipe ).length;
                const revisionRecipeLength = JSON.stringify( row.value ).length;

                return (
                    <div>
                        { Math.max(revisionRecipeLength - emptyRecipeLength, 0 ).toLocaleString() }
                    </div>
                )
            },
        }]

        return columns;
    }
};