import React from 'react';
import he from 'he';

import TextFilter from '../general/TextFilter';
import bulkEditCheckbox from '../general/bulkEditCheckbox';
import Api from 'Shared/Api';
import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';

export default {
    getColumns( datatable ) {
        let columns = [
            bulkEditCheckbox( datatable ),
            {
                Header: __wprm( 'Sort:' ),
                id: 'actions',
                headerClassName: 'wprm-admin-table-help-text',
                sortable: false,
                width: 40,
                Filter: () => (
                    <div>
                        { __wprm( 'Filter:' ) }
                    </div>
                ),
                Cell: row => (
                    <div className="wprm-admin-manage-actions">
                        <Icon
                            type="trash"
                            title={ __wprm( 'Delete Action' ) }
                            onClick={() => {
                                if( confirm( __wprm( 'Are you sure you want to delete this action?' ) ) ) {
                                    Api.analytics.delete(row.original.id).then(() => datatable.refreshData());
                                }
                            }}
                        />
                    </div>
                ),
            },{
                Header: __wprm( 'Date' ),
                id: 'created_at',
                accessor: 'created_at',
                width: 150,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Action' ),
                id: 'type',
                accessor: 'type',
                width: 150,
                sortable: false,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Action Details' ),
                id: 'meta',
                accessor: 'meta',
                width: 200,
                sortable: false,
                filterable: false,
                Cell: row => (
                    <div>
                        {
                            typeof row.value === 'object'
                            ?
                            Object.keys( row.value ).map( ( field, index ) => (
                                <div key={ index }><strong>{ field }:</strong> { row.value[ field ].toString() }</div>
                            ))
                            :
                            null
                        }
                    </div>
                ),
            },{
                Header: __wprm( 'Recipe ID' ),
                id: 'recipe_id',
                accessor: 'recipe_id',
                width: 350,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    if ( ! row.value || '0' === row.value ) {
                        return (<div></div>);
                    }

                    const label = `${ row.value } - ${ row.original.recipe ? row.original.recipe : __wprm( 'n/a' ) }`;
                    return (
                        <div>
                            {
                                row.original.recipe
                                ?
                                <a
                                    href="#"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        WPRM_Modal.open( 'recipe', {
                                            recipeId: row.value,
                                            saveCallback: () => datatable.refreshData(),
                                        } );
                                    }}
                                >{ label }</a>
                                :
                                label
                            }
                        </div>
                    )
                },
            },{
                Header: __wprm( 'Parent Post ID' ),
                id: 'post_id',
                accessor: 'post_id',
                width: 350,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    if ( ! row.value || '0' === row.value ) {
                        return (<div></div>);
                    }

                    const label = `${ row.value } - ${ row.original.post ? row.original.post : __wprm( 'n/a' ) }`;
                    return (
                        <div>
                            {
                                row.original.post_link
                                ?
                                <a href={ he.decode( row.original.post_link ) } target="_blank">{ label }</a>
                                :
                                label
                            }
                        </div>
                    )
                },
            },{
                Header: __wprm( 'User ID' ),
                id: 'user_id',
                accessor: 'user_id',
                width: 150,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    if ( ! row.value || '0' === row.value ) {
                        return (<div></div>);
                    }

                    const label = `${ row.value } - ${ row.original.user ? row.original.user : __wprm( 'n/a' ) }`;
                    return (
                        <div>
                            {
                                row.original.user_link
                                ?
                                <a href={ he.decode( row.original.user_link ) } target="_blank">{ label }</a>
                                :
                                label
                            }
                        </div>
                    )
                },
            },{
                Header: __wprm( 'Visitor UID' ),
                id: 'visitor_id',
                accessor: 'visitor_id',
                width: 175,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Visitor Details' ),
                id: 'visitor',
                accessor: 'visitor',
                width: 200,
                sortable: false,
                filterable: false,
                Cell: row => (
                    <div>
                        {
                            typeof row.value === 'object'
                            ?
                            Object.keys( row.value ).map( ( field, index ) => (
                                <div key={ index }><strong>{ field }:</strong> { row.value[ field ].toString() }</div>
                            ))
                            :
                            null
                        }
                    </div>
                ),
            },
        ];

        return columns;
    }
};