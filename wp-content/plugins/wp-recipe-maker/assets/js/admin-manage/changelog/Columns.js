import React from 'react';
import he from 'he';

import TextFilter from '../general/TextFilter';
import { __wprm } from 'Shared/Translations';

const SETTINGS_SOURCE_LABELS = {
    settings_page: __wprm( 'Settings Page' ),
    settings_import: __wprm( 'Settings Import' ),
};

const formatSettingsChangeValue = ( value ) => {
    if ( undefined === value || null === value || '' === value ) {
        return __wprm( 'Empty' );
    }

    return value.toString();
};

const renderObjectMeta = ( value ) => {
    if ( ! value || 'object' !== typeof value ) {
        return null;
    }

    return Object.keys( value ).map( ( field, index ) => (
        <div key={ index }><strong>{ field }:</strong> { value[ field ].toString() }</div>
    ));
};

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
                Cell: row => {
                    const settingsChanges = row.value && Array.isArray( row.value.changes ) ? row.value.changes : false;

                    if ( 'settings_updated' === row.original.type && settingsChanges ) {
                        return (
                            <div>
                                {
                                    settingsChanges.map( ( change, index ) => (
                                        <div key={ index }>
                                            <strong>{ change.label ? change.label : change.id }:</strong> { formatSettingsChangeValue( change.before ) } &rarr; { formatSettingsChangeValue( change.after ) }
                                        </div>
                                    ))
                                }
                            </div>
                        );
                    }

                    return (
                        <div>
                            {
                                'object' === typeof row.value
                                ?
                                renderObjectMeta( row.value )
                                :
                                null
                            }
                        </div>
                    );
                },
            },{
                Header: __wprm( 'Object ID' ),
                id: 'object_id',
                accessor: 'object_id',
                width: 300,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    if ( row.original.object_meta && 'settings' === row.original.object_meta.type ) {
                        return (
                            <div>{ row.original.object_meta.name ? row.original.object_meta.name : __wprm( 'Plugin Settings' ) }</div>
                        );
                    }

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
                Cell: row => {
                    if ( row.value && 'settings' === row.value.type ) {
                        const source = row.value.source && SETTINGS_SOURCE_LABELS[ row.value.source ]
                            ? SETTINGS_SOURCE_LABELS[ row.value.source ]
                            : row.value.source;

                        return (
                            <div>
                                <div><strong>{ __wprm( 'Source' ) }:</strong> { source ? source : __wprm( 'n/a' ) }</div>
                                <div><strong>{ __wprm( 'Changed Settings' ) }:</strong> { row.value.changed_count ? row.value.changed_count : 0 }</div>
                            </div>
                        );
                    }

                    return (
                        <div>
                            {
                                'object' === typeof row.value
                                ?
                                renderObjectMeta( row.value )
                                :
                                null
                            }
                        </div>
                    );
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
