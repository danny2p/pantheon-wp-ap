import React from 'react';
import he from 'he';

import TextFilter from '../general/TextFilter';
import { __wprm } from 'Shared/Translations';

export default {
    getColumns( datatable ) {
        let columns = [
            {
                Header: __wprm( 'Date' ),
                id: 'created_at',
                accessor: 'created_at',
                width: 150,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Change' ),
                id: 'type',
                accessor: 'type',
                width: 150,
                sortable: false,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Change Details' ),
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
                Header: __wprm( 'Object ID' ),
                id: 'object_id',
                accessor: 'object_id',
                width: 300,
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
                Header: __wprm( 'Object Details' ),
                id: 'object_meta',
                accessor: 'object_meta',
                width: 300,
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
                Header: __wprm( 'User Details' ),
                id: 'user_meta',
                accessor: 'user_meta',
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
            }
        ];

        return columns;
    }
};