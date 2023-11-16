import React, { Fragment } from 'react';
import he from 'he';
 
import TextFilter from '../general/TextFilter';
import Api from 'Shared/Api';
import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';
import CopyToClipboardIcon from 'Shared/CopyToClipboardIcon';

import '../../../css/admin/manage/recipes.scss';

export default {
    getColumns( datatable ) {
        let columns = [
            {
                Header: __wprm( 'Sort:' ),
                id: 'actions',
                headerClassName: 'wprm-admin-table-help-text',
                sortable: false,
                width: wprm_admin.addons.premium ? 100 : 70,
                Filter: () => (
                    <div>
                        { __wprm( 'Filter:' ) }
                    </div>
                ),
                Cell: row => (
                    <div className="wprm-admin-manage-actions">
                        <Icon
                            type="pencil"
                            title={ __wprm( 'Edit List' ) }
                            onClick={() => {
                                WPRM_Modal.open( 'list', {
                                    list: row.original,
                                    saveCallback: () => datatable.refreshData(),
                                } );
                            }}
                        />
                        {
                            true === wprm_admin.addons.premium
                            &&
                            <Icon
                                type="duplicate"
                                title={ __wprm( 'Clone List' ) }
                                onClick={() => {
                                    WPRM_Modal.open( 'list', {
                                        listId: row.original.id,
                                        cloneList: true,
                                        saveCallback: () => datatable.refreshData(),
                                    }, true );
                                }}
                            />
                        }
                        <Icon
                            type="trash"
                            title={ __wprm( 'Delete List' ) }
                            onClick={() => {
                                if( confirm( __wprm( 'Are you sure you want to delete this list?' ) ) ) {
                                    Api.list.delete(row.original.id).then(() => datatable.refreshData());
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
                Header: __wprm( 'Shortcode' ),
                id: 'shortcode',
                accessor: 'id',
                sortable: false,
                filterable: false,
                width: 200,
                Cell: row => {
                    const shortcode = `[wprm-list id="${ row.value }"]`;

                    return (
                        <div className="wprm-admin-manage-shortcode-container">
                            <CopyToClipboardIcon
                                text={shortcode}
                                type="text"
                            />
                        </div>
                    )
                },
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
            },{
                Header: __wprm( 'Note' ),
                id: 'note',
                accessor: 'note',
                width: 300,
                sortable: false,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( '# Items' ),
                id: 'nbr_items',
                accessor: 'nbr_items',
                width: 75,
                filterable: false
            },{
                Header: __wprm( '# Internal' ),
                id: 'nbr_items_internal',
                accessor: 'nbr_items_internal',
                width: 75,
                filterable: false
            },{
                Header: __wprm( '# External' ),
                id: 'nbr_items_external',
                accessor: 'nbr_items_external',
                width: 75,
                filterable: false
            },{
                Header: __wprm( 'Metadata Name' ),
                id: 'metadata_name',
                accessor: 'metadata_name',
                width: 300,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Metadata Description' ),
                id: 'metadata_description',
                accessor: 'metadata_description',
                width: 300,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Parent ID' ),
                id: 'parent_post_id',
                accessor: 'parent_post_id',
                width: 65,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    if ( ! row.value ) {
                        return (<div></div>);
                    } else {
                        return (
                            <div>{ row.value }</div>
                        )
                    }
                },
            },{
                Header: __wprm( 'Parent Name' ),
                id: 'parent_post',
                accessor: 'parent_post',
                width: 300,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'Show All' ) }</option>
                        <option value="yes">{ __wprm( 'Has Parent Post' ) }</option>
                        <option value="no">{ __wprm( 'Does not have Parent Post' ) }</option>
                    </select>
                ),
                Cell: row => {
                    const parent_post = row.value;
                    const view_url = row.original.parent_post_url ? he.decode( row.original.parent_post_url ) : false;
                    const edit_url = row.original.parent_post_edit_url ? he.decode( row.original.parent_post_edit_url ) : false;
            
                    if ( ! parent_post ) {
                        return null;
                    }

                    return (
                        <div className="wprm-admin-manage-recipes-parent-post-container">
                            {
                                view_url
                                &&
                                <a href={ view_url } target="_blank">
                                    <Icon
                                        type="eye"
                                        title={ __wprm( 'View Parent Post' ) }
                                    />
                                </a>
                            }
                            {
                                edit_url
                                &&
                                <a href={ edit_url } target="_blank">
                                    <Icon
                                        type="pencil"
                                        title={ __wprm( 'Edit Parent Post' ) }
                                    />
                                </a>
                            }
                            { parent_post.post_title }
                        </div>
                    );
                },
            }
        ];

        return columns;
    }
};