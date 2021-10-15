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
                            title={ __wprm( 'Click on the stars to edit the rating.' ) }
                            onClick={() => {
                                alert( __wprm( 'Click on the stars to edit the rating.' ) );
                            }}
                        />
                        <Icon
                            type="trash"
                            title={ __wprm( 'Delete Rating' ) }
                            onClick={() => {
                                if( confirm( __wprm( 'Are you sure you want to delete this rating?' ) ) ) {
                                    Api.rating.delete(row.original.id).then(() => datatable.refreshData());
                                }
                            }}
                        />
                    </div>
                ),
            },{
                Header: __wprm( 'Date' ),
                id: 'date',
                accessor: 'date',
                width: 150,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Rating' ),
                id: 'rating',
                accessor: 'rating',
                width: 100,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'All Ratings' ) }</option>
                        <option value="1">{ `1 ${ __wprm( 'star' ) }` }</option>
                        <option value="2">{ `2 ${ __wprm( 'stars' ) }` }</option>
                        <option value="3">{ `3 ${ __wprm( 'stars' ) }` }</option>
                        <option value="4">{ `4 ${ __wprm( 'stars' ) }` }</option>
                        <option value="5">{ `5 ${ __wprm( 'stars' ) }` }</option>
                    </select>
                ),
                Cell: row => {
                    return (
                        <div className="wprm-admin-manage-ratings-rating">
                            {
                                [1,2,3,4,5].map((rating, index) => {
                                    return (
                                        <Icon
                                            type={ rating <= row.value ? 'star-full' : 'star-empty' }
                                            title={ `${__wprm( 'Click to change this rating to:' )} ${rating}` }
                                            onClick={() => {
                                                const newRating = {
                                                    ...row.original,
                                                    rating,
                                                }
                                                Api.rating.update(newRating).then(() => datatable.refreshData());
                                            }}
                                            key={index}
                                        />
                                    );
                                })
                            }
                        </div>
                    );
                },
            },{
                Header: __wprm( 'Type' ),
                id: 'type',
                accessor: 'type',
                width: 150,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'All Types' ) }</option>
                        <option value="user">{ __wprm( 'User Rating' ) }</option>
                        <option value="comment">{ __wprm( 'Comment Rating' ) }</option>
                    </select>
                ),
                Cell: row => (
                    <div>
                        { 'user' === row.value ? __wprm( 'User Rating' ) : __wprm( 'Comment Rating' ) }
                    </div>
                ),
            },{
                Header: __wprm( 'Approved' ),
                id: 'approved',
                accessor: 'approved',
                width: 100,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'All' ) }</option>
                        <option value="yes">{ __wprm( 'Approved' ) }</option>
                        <option value="no">{ __wprm( 'Not Approved' ) }</option>
                    </select>
                ),
                Cell: row => (
                    <div>
                        { 0 == row.value ? __wprm( 'No' ) : __wprm( 'Yes' ) }
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
                Header: __wprm( 'IP' ),
                id: 'ip',
                accessor: 'ip',
                width: 150,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Comment ID' ),
                id: 'comment_id',
                accessor: 'comment_id',
                width: 350,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    if ( ! row.value || '0' === row.value ) {
                        return (<div></div>);
                    }

                    const label = `${ row.value } - ${ row.original.comment ? `${row.original.comment}` : __wprm( 'n/a' ) }`;
                    return (
                        <div>
                            {
                                row.original.comment_link
                                ?
                                <a href={ he.decode( row.original.comment_link ) } target="_blank">{ label }</a>
                                :
                                label
                            }
                        </div>
                    )
                },
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
            }
        ];

        return columns;
    }
};