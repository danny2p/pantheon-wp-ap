import React, { Fragment } from 'react';
import he from 'he';
 
import bulkEditCheckbox from '../general/bulkEditCheckbox';
import TextFilter from '../general/TextFilter';
import Api from 'Shared/Api';
import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';
import CopyToClipboardIcon from 'Shared/CopyToClipboardIcon';

import '../../../css/admin/manage/recipes.scss';
import SeoIndicator from './SeoIndicator';

const getFormattedTime = ( timeMins, showZero = false ) => {
    const time = parseInt( timeMins );

    let days = 0,
        hours = 0,
        minutes = 0,
        formatted = '';

    if ( time > 0 ) {
        days = Math.floor( time / 24 / 60 );
        hours = Math.floor( time / 60 % 24 );
        minutes = Math.floor( time % 60 );

        if ( days ) { formatted += `${days} ${days === 1 ? __wprm( 'day' ) : __wprm( 'days' ) } `; }
        if ( hours ) { formatted += `${hours} ${hours === 1 ? __wprm( 'hr' ) : __wprm( 'hrs' ) } `; }
        if ( minutes ) { formatted += `${minutes} ${minutes === 1 ? __wprm( 'min' ) : __wprm( 'mins' ) } `; }
    } else {
        if ( showZero ) {
            formatted = `0 ${ __wprm( 'mins' ) }`;
        }
    }

    return formatted.trim();
}

export default {
    getColumns( recipes ) {
        let columns = [
            bulkEditCheckbox( recipes ),
            {
                Header: __wprm( 'Sort:' ),
                id: 'actions',
                headerClassName: 'wprm-admin-table-help-text',
                sortable: false,
                width: wprm_admin.addons.premium ? 130 : 100,
                Filter: () => (
                    <div>
                        { __wprm( 'Filter:' ) }
                    </div>
                ),
                Cell: row => (
                    <div className="wprm-admin-manage-actions">
                        {
                            row.original.editable
                            ?
                            <Icon
                                type="pencil"
                                title={ __wprm( 'Edit Recipe' ) }
                                onClick={() => {
                                    WPRM_Modal.open( 'recipe', {
                                        recipe: row.original,
                                        saveCallback: () => recipes.refreshData(),
                                    } );
                                }}
                            />
                            :
                            <Icon
                                type="lock"
                                title={ __wprm( 'You do not have the correct permissions to edit this recipe' ) }
                            />
                        }
                        <Icon
                            type="print"
                            title={ __wprm( 'Print Recipe' ) }
                            onClick={() => {
                                const urlParts = wprm_admin.home_url.split(/\?(.+)/);
                                let printUrl = urlParts[0];

                                if ( wprm_admin.permalinks ) {
                                    printUrl += wprm_admin.print_slug + '/' + row.original.id;

                                    if ( urlParts[1] ) {
                                        printUrl += '?' + urlParts[1];
                                    }
                                } else {
                                    printUrl += '?' + wprm_admin.print_slug + '=' + row.original.id;

                                    if ( urlParts[1] ) {
                                        printUrl += '&' + urlParts[1];
                                    }
                                }
                                window.open( printUrl, '_blank' );
                            }}
                        />
                        {
                            true === wprm_admin.addons.premium
                            &&
                            <Icon
                                type="duplicate"
                                title={ __wprm( 'Clone Recipe' ) }
                                onClick={() => {
                                    WPRM_Modal.open( 'recipe', {
                                        recipeId: row.original.id,
                                        cloneRecipe: true,
                                        saveCallback: () => recipes.refreshData(),
                                    }, true );
                                }}
                            />
                        }                    
                        <Icon
                            type="trash"
                            title={ __wprm( 'Delete Recipe' ) }
                            onClick={() => {
                                if( confirm( `${ __wprm( 'Are you sure you want to delete' ) } "${row.original.name}"?` ) ) {
                                    Api.recipe.delete(row.original.id).then(() => recipes.refreshData());
                                }
                            }}
                        />
                    </div>
                ),
            },{
                Header: __wprm( 'SEO' ),
                id: 'seo',
                accessor: 'seo',
                width: 80,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'Show All' ) }</option>
                        <option value="other">{ 'n/a' }</option>
                        <option value="bad">{ __wprm( 'Bad' ) }</option>
                        <option value="warning">{ __wprm( 'Warnings' ) }</option>
                        <option value="rating">{ __wprm( 'No Ratings' ) }</option>
                        <option value="good">{ __wprm( 'Good' ) }</option>
                    </select>
                ),
                Cell: row => (
                    <SeoIndicator
                        seo={ row.value }
                    />
                ),
            },{
                Header: __wprm( 'Type' ),
                id: 'type',
                accessor: 'type',
                width: 80,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'All' ) }</option>
                        <option value="food">{ __wprm( 'Food' ) }</option>
                        <option value="howto">{ __wprm( 'How-to' ) }</option>
                        <option value="other">{ __wprm( 'Other' ) }</option>
                    </select>
                ),
                Cell: row => (
                    <div>
                        { 'other' === row.value ? __wprm( 'Other' ) : 'howto' === row.value ? __wprm( 'How-to' ) : __wprm( 'Food' ) }
                    </div>
                ),
            },{
                Header: __wprm( 'ID' ),
                id: 'id',
                accessor: 'id',
                width: 65,
                Filter: (props) => (<TextFilter {...props}/>),
            },
        ];

        if ( 'public' === wprm_admin.settings.post_type_structure ) {
            columns.push({
                Header: __wprm( 'Slug' ),
                id: 'slug',
                accessor: 'slug',
                width: 200,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    return (
                        <span>
                            {
                                row.original.permalink
                                ?
                                <a href={ row.original.permalink } target="_blank">{ row.value }</a>
                                :
                                row.value
                            }
                        </span>
                    )
                },
            });
        }

        columns.push({
                Header: __wprm( 'Shortcode' ),
                id: 'shortcode',
                accessor: 'id',
                sortable: false,
                filterable: false,
                width: 200,
                Cell: row => {
                    const shortcode = `[wprm-recipe id="${ row.value }"]`;

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
                Header: __wprm( 'Image' ),
                id: 'image',
                accessor: 'image_url',
                width: 100,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'Show All' ) }</option>
                        <option value="yes">{ __wprm( 'Has Recipe Image' ) }</option>
                        <option value="no">{ __wprm( 'Does not have Recipe Image' ) }</option>
                    </select>
                ),
                Cell: row => (
                    <div style={ { width: '100%' } }>
                        {
                            row.value
                            ?
                            <img src={ row.value } className="wprm-admin-manage-image" />
                            :
                            null
                        }
                    </div>
                ),
            },{
                Header: __wprm( 'Pin Image' ),
                id: 'pin_image',
                accessor: 'pin_image_url',
                width: 100,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'Show All' ) }</option>
                        <option value="yes">{ __wprm( 'Has Custom Pin Image' ) }</option>
                        <option value="no">{ __wprm( 'Does not have Custom Pin Image' ) }</option>
                    </select>
                ),
                Cell: row => (
                    <div style={ { width: '100%' } }>
                        {
                            row.value
                            ?
                            <img src={ row.value } className="wprm-admin-manage-image" />
                            :
                            null
                        }
                    </div>
                ),
            },{
                Header: __wprm( 'Repin ID' ),
                id: 'pin_image_repin_id',
                accessor: 'pin_image_repin_id',
                width: 170,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Video' ),
                id: 'video',
                accessor: 'video_url',
                width: 200,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'Show All' ) }</option>
                        <option value="yes">{ __wprm( 'Has any video' ) }</option>
                        <option value="id">{ __wprm( 'Has an uploaded video' ) }</option>
                        <option value="embed">{ __wprm( 'Has an embedded video' ) }</option>
                        <option value="no">{ __wprm( 'Does not have a video' ) }</option>
                    </select>
                ),
                Cell: row => (
                    <div style={ { width: '100%' } }>
                        {
                            row.value
                            ?
                            row.value
                            :
                            <Fragment>
                                {
                                    row.original.video_embed
                                    ?
                                    row.original.video_embed
                                    :
                                    null
                                }
                            </Fragment>
                        }
                    </div>
                ),
            },{
                Header: __wprm( 'Name' ),
                id: 'name',
                accessor: 'name',
                width: 300,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Summary' ),
                id: 'summary',
                accessor: 'summary',
                width: 300,
                sortable: false,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {                    
                    if ( ! row.value ) {
                        return ( <div></div> );
                    }
                    return ( <div dangerouslySetInnerHTML={ { __html: row.value } } /> );
                },
            },{
                Header: __wprm( 'Author' ),
                id: 'post_author',
                accessor: 'post_author',
                width: 150,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'All Authors' ) }</option>
                        {
                            Object.keys(wprm_admin_manage.authors).map((author, index) => {
                                const data = wprm_admin_manage.authors[ author ].data;
                                return (
                                    <option value={ data.ID } key={index}>{ data.ID }{ data.display_name ? ` - ${ he.decode( data.display_name ) }` : '' } </option>
                                )
                            })
                        }
                    </select>
                ),
                Cell: row => {
                    // Add to list of authors if it wasn't already in there.
                    if ( ! wprm_admin_manage.authors.hasOwnProperty( row.value ) ) {
                        wprm_admin_manage.authors[ row.value ] = {
                            data: {
                                ID: row.value,
                                display_name: row.original.post_author_name,
                            }
                        }   
                    }
                    
                    return (
                        <div>
                            {
                                row.value && '0' !== row.value
                                ?
                                <a href={ row.original.post_author_link } target="_blank">{ row.value } - { row.original.post_author_name }</a>
                                :
                                null
                            }
                        </div>
                    )
                },
            },{
                Header: __wprm( 'Display Author Type' ),
                id: 'author_display',
                accessor: 'author_display',
                width: 250,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'All Display Author Types' ) }</option>
                        {
                            wprm_admin_modal.options.author.map((author, index) => {
                                if ( 'same' === author.value ) {
                                    return null;
                                }

                                return (
                                    <option value={ author.value } key={index}>{ author.label }</option>
                                )
                            })
                        }
                    </select>
                ),
                Cell: row => {
                    const author = wprm_admin_modal.options.author.find((option) => option.value === row.value );
                    
                    if ( ! author ) {
                        return (<div></div>);
                    }

                    return (
                        <div>{ author.label }</div>
                    )
                },
            },{
                Header: __wprm( 'Display Author' ),
                id: 'author',
                accessor: 'author',
                width: 150,
                sortable: false,
                filterable: false,
                Cell: row => {                    
                    if ( ! row.value ) {
                        return ( <div></div> );
                    }
                    return ( <div dangerouslySetInnerHTML={ { __html: row.original.author } } /> );
                },
            },{
                Header: __wprm( 'Status' ),
                id: 'status',
                accessor: 'post_status',
                width: 120,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'All Statuses' ) }</option>
                        {
                            Object.keys(wprm_admin_manage.post_statuses).map((status, index) => (
                                <option value={status} key={index}>{ he.decode( wprm_admin_manage.post_statuses[status] ) }</option>
                            ))
                        }
                    </select>
                ),
                Cell: row => {
                    const postStatusLabel = Object.keys(wprm_admin_manage.post_statuses).includes(row.value) ? wprm_admin_manage.post_statuses[row.value] : row.value;

                    return (
                        <div>{ postStatusLabel }</div>
                    );
                },
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
        );

        if ( wprm_admin_manage.multilingual ) {
            columns.push(
                {
                    Header: __wprm( 'Parent Language' ),
                    id: 'parent_post_language',
                    accessor: 'parent_post_language',
                    width: 150,
                    sortable: false,
                    Filter: ({ filter, onChange }) => (
                        <select
                            onChange={event => onChange(event.target.value)}
                            style={{ width: '100%', fontSize: '1em' }}
                            value={filter ? filter.value : 'all'}
                        >
                            <option value="all">{ __wprm( 'All Languages' ) }</option>
                            {
                                Object.values(wprm_admin_manage.multilingual.languages).map((language, index) => {
                                    return (
                                        <option value={ language.value } key={index}>{ `${ language.value } - ${ he.decode( language.label ) }` }</option>
                                    )
                                })
                            }
                        </select>
                    ),
                    Cell: row => {
                        const language = wprm_admin_manage.multilingual.languages.hasOwnProperty( row.value ) ? wprm_admin_manage.multilingual.languages[ row.value ] : false;
                
                        if ( ! language ) {
                            return (<div></div>);
                        } else {
                            return (
                                <div>{ `${ language.value } - ${ he.decode( language.label ) }` }</div>
                            )
                        }
                    },
                }
            );
        }
        
        columns.push({
                Header: __wprm( 'Ratings' ),
                id: 'rating',
                accessor: 'rating',
                width: 200,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <optgroup label={ __wprm( 'General' ) }>
                            <option value="all">{ __wprm( 'All Ratings' ) }</option>
                            <option value="none">{ __wprm( 'No Ratings' ) }</option>
                            <option value="any">{ __wprm( 'Any Rating' ) }</option>
                        </optgroup>
                        <optgroup label={ __wprm( 'Stars' ) }>
                            <option value="1">{ `1 ${ __wprm( 'star' ) }` }</option>
                            <option value="2">{ `2 ${ __wprm( 'stars' ) }` }</option>
                            <option value="3">{ `3 ${ __wprm( 'stars' ) }` }</option>
                            <option value="4">{ `4 ${ __wprm( 'stars' ) }` }</option>
                            <option value="5">{ `5 ${ __wprm( 'stars' ) }` }</option>
                        </optgroup>
                    </select>
                ),
                Cell: row => {
                    const ratings = row.value;

                    if ( ! ratings.average || "0" === ratings.average ) {
                        return null;
                    }

                    return (
                        <div className="wprm-admin-manage-recipes-ratings-container">
                            <div className="wprm-admin-manage-recipes-ratings-average">{ ratings.average }</div>
                            <div className="wprm-admin-manage-recipes-ratings-details">
                                {
                                    false === ratings.comment_ratings
                                    ?
                                    <div className="wprm-admin-manage-recipes-ratings-details-none">{ __wprm( 'no comment ratings' ) }</div>
                                    :
                                    <div>{ `${ ratings.comment_ratings.average } ${ __wprm( 'from' ) } ${ ratings.comment_ratings.count } ${ 1 === ratings.comment_ratings.count ? __wprm( 'comment' ) : __wprm( 'comments' ) }` }</div>
                                }
                                {
                                    false === ratings.user_ratings
                                    ?
                                    <div className="wprm-admin-manage-recipes-ratings-details-none">{ __wprm( 'no user ratings' ) }</div>
                                    :
                                    <div>
                                        { `${ ratings.user_ratings.average } ${ __wprm( 'from' ) } ${ ratings.user_ratings.count } ${ 1 === ratings.user_ratings.count ? __wprm( 'vote' ) : __wprm( 'votes' ) }` }
                                        <a
                                            href="#"
                                            onClick={(e) => {
                                                e.preventDefault();
                                                if( confirm( `${ __wprm( 'Are you sure you want to delete the user ratings for' ) } "${row.original.name}"?` ) ) {
                                                    Api.manage.deleteUserRatings(row.original.id).then(() => recipes.refreshData());
                                                }
                                            }}
                                        >(reset)</a>
                                    </div>
                                }
                            </div>
                        </div>
                    );
                },
            }
        );

        for (let key in wprm_admin_modal.categories) {
            const taxonomy = wprm_admin_modal.categories[key];
            taxonomy.terms.sort((a,b) => a.name.localeCompare(b.name));
        
            columns.push({
                Header: taxonomy.label,
                id: `tag_${ key }`,
                accessor: d => d.tags[key],
                width: 300,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <optgroup label={ __wprm( 'General' ) }>
                            <option value="all">{ `${ __wprm( 'All' ) } ${ taxonomy.label }` }</option>
                            <option value="none">{ `${ __wprm( 'No' ) } ${ taxonomy.label }` }</option>
                            <option value="any">{ `${ __wprm( 'Any' ) } ${ taxonomy.label }` }</option>
                        </optgroup>
                        <optgroup label={ __wprm( 'Terms' ) }>
                            {
                                taxonomy.terms.map((term, index) => (
                                    <option value={term.term_id} key={index}>{ he.decode( term.name ) }{ term.count ? ` (${ term.count })` : '' }</option>
                                ))
                            }
                        </optgroup>
                    </select>
                ),
                Cell: row => {
                    const names = row.value.map(t => t.name);
                    const joined = names.join(', ');
                    return (
                        <div>{ joined ? he.decode( joined ) : null }</div>
                    )
                },
            });
        }

        columns.push({
                Header: __wprm( 'Prep Time' ),
                id: 'prep_time',
                accessor: 'prep_time',
                width: 100,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => (<div>{ getFormattedTime( row.value, row.original.prep_time_zero ) }</div>),
            },{
                Header: __wprm( 'Cook Time' ),
                id: 'cook_time',
                accessor: 'cook_time',
                width: 100,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => (<div>{ getFormattedTime( row.value, row.original.cook_time_zero ) }</div>),
            },{
                Header: __wprm( 'Custom Time' ),
                id: 'custom_time',
                accessor: 'custom_time',
                width: 120,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => (
                    <div>
                        <div>{ row.original.custom_time_label }</div>
                        <div>{ getFormattedTime( row.value, row.original.custom_time_zero ) }</div>
                    </div>
                ),
            },{
                Header: __wprm( 'Total Time' ),
                id: 'total_time',
                accessor: 'total_time',
                width: 100,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => (<div>{ getFormattedTime( row.value ) }</div>),
            },{
                Header: __wprm( 'Servings' ),
                id: 'servings',
                accessor: 'servings',
                width: 100,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => (<div>{ '0' === row.value ? '' : row.value } { row.original.servings_unit }</div>),
            },{
                Header: __wprm( 'Equipment' ),
                id: 'equipment',
                accessor: 'equipment',
                width: 300,
                sortable: false,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => (
                    <div>
                        {
                            row.value
                            ?
                            row.value.map( (equipment, equipment_index) => {
                                if ( equipment.name ) {
                                    const name = equipment.name.replace( /(<([^>]+)>)/ig, '' ).trim();

                                    if ( name ) {
                                        return (
                                            <div key={equipment_index}>{ he.decode( name ) }</div>
                                        )
                                    }
                                }
                            })
                            :
                            null
                        }
                    </div>
                ),
            },{
                Header: __wprm( 'Ingredients' ),
                id: 'ingredient',
                accessor: 'ingredients',
                width: 300,
                sortable: false,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => (
                    <div>
                        {
                            row.value
                            ?
                            row.value.map( (group, index) => {
                                group.name = group.name.replace( /(<([^>]+)>)/ig, '' ).trim();

                                return (
                                    <div key={index}>
                                        { group.name && <div style={{ fontWeight: 'bold' }}>{ he.decode( group.name ) }</div> }
                                        {
                                            group.ingredients.map( (ingredient, ingredient_index) => {
                                                let fields = [];
                                                
                                                if ( ingredient.amount ) { fields.push( ingredient.amount ); }
                                                if ( ingredient.unit ) { fields.push( ingredient.unit ); }
                                                if ( ingredient.name ) { fields.push( ingredient.name ); }
                                                if ( ingredient.notes ) { fields.push( ingredient.notes ); }
                                                
                                                if ( fields.length ) {
                                                    const ingredientString = fields.join( ' ' ).replace( /(<([^>]+)>)/ig, '' ).trim();

                                                    if ( ingredientString ) {
                                                        return (
                                                            <div key={ingredient_index}>{ he.decode( ingredientString ) }</div>
                                                        )
                                                    }
                                                }
                                            })
                                        }
                                    </div>
                                )
                            })
                            :
                            null
                        }
                    </div>
                ),
            },{
                Header: __wprm( 'Converted Ingredients' ),
                id: 'unit_conversion',
                accessor: 'unit_conversion',
                width: 300,
                sortable: false,
                filterable: false,
                Cell: row => {
                    if ( Array.isArray( row.value ) ) {
                        return (
                            <div>
                                { row.value.map( (line, index) => {
                                    line = line.replace( /(<([^>]+)>)/ig, '' ).trim();

                                    if ( line ) {
                                        return (
                                            <div key={index}>
                                                { he.decode(line) }
                                            </div>
                                        )
                                    }
                                }) }
                            </div>
                        );
                    }

                    return (
                        <div>{ row.value }</div>
                    )
                },
            },{
                Header: __wprm( 'Nutrition' ),
                id: 'nutrition',
                accessor: 'nutrition',
                width: 250,
                sortable: false,
                filterable: false,
                Cell: row => (
                    <div className="wprm-manage-recipes-nutrition-container">
                        {
                            Object.keys(wprm_admin_modal.nutrition).map((nutrient, index ) => {
                                const options = wprm_admin_modal.nutrition[nutrient];
                                const value = row.value.hasOwnProperty(nutrient) ? row.value[nutrient] : false;
                                let unit = options.unit;

                                if ( 'serving_size' === nutrient && row.value.hasOwnProperty( 'serving_unit' ) && row.value.serving_unit ) {
                                    unit = row.value.serving_unit;
                                }
        
                                if ( false === value ) {
                                    return null;
                                }
        
                                if ( 'calories' !== nutrient && ! wprm_admin.addons.premium ) {
                                    return null;
                                }
        
                                return (
                                    <div
                                        className="wprm-manage-recipes-nutrition"
                                        key={index}
                                    >
                                        <div className="wprm-manage-recipes-nutrition-label">{ options.label }</div>
                                        <div className="wprm-manage-recipes-nutrition-value-unit">
                                            <span className="wprm-manage-recipes-nutrition-value">{ value }</span>
                                            <span className="wprm-manage-recipes-nutrition-unit">{ unit }</span>
                                        </div>
                                    </div>
                                )
                            })
                        }
                    </div>
                ),
            }
        );

        if ( wprm_admin.addons.elite ) {
            columns.push({
                Header: __wprm( 'Recipe Submission User' ),
                id: 'submission_author',
                accessor: 'submission_author',
                width: 300,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'Show All' ) }</option>
                        <option value="yes">{ __wprm( 'Was Recipe Submission' ) }</option>
                        <option value="no">{ __wprm( 'Was not a Recipe Submission' ) }</option>
                    </select>
                ),
                Cell: row => {
                    const user = row.value;
                    if ( ! user ) {
                        return null;
                    }
    
                    const name = user.name ? user.name : ( row.original.submission_author_user_name ? row.original.submission_author_user_name : '' );
    
                    return (
                        <div className="wprm-admin-manage-recipe-submission-user">
                            <div className="wprm-admin-manage-recipe-submission-user-name">
                                {
                                    user.id
                                    ?
                                    <a href={ row.original.submission_author_user_link } target="_blank">#{ user.id }</a>
                                    :
                                    null
                                }
                                {
                                    name
                                    ?
                                    <span> - { name }</span>
                                    :
                                    null
                                }
                            </div>
                            {
                                user.email
                                ?
                                <div className="wprm-admin-manage-recipe-submission-user-email">{ user.email }</div>
                                :
                                null
                            }
                        </div>
                    )
                },
            });
        }

        const customFields = wprm_admin_modal.custom_fields && wprm_admin_modal.custom_fields.fields ? Object.values( wprm_admin_modal.custom_fields.fields ) : [];

        for ( let customField of customFields ) {
            columns.push({
                Header: customField.name,
                id: `custom_field_${ customField.key }`,
                accessor: 'custom_fields',
                width: 150,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    if ( ! row.value.hasOwnProperty( customField.key ) || ! row.value[ customField.key ] ) {
                        return null;
                    }

                    const value = row.value[ customField.key ];

                    if ( 'image' === customField.type ) {
                        return (
                            <div style={ { width: '100%' } }>
                                {
                                    value.hasOwnProperty( 'url' ) && value.url
                                    ?
                                    <img src={ value.url } className="wprm-admin-manage-image" />
                                    :
                                    null
                                }
                            </div>
                        )
                    }

                    if ( 'textarea' === customField.type ) {
                        return ( <div dangerouslySetInnerHTML={ { __html: value } } /> );
                    }

                    if ( 'link' === customField.type ) {
                        return ( <a href={ value } target="_blank">{ value }</a> );
                    }

                    if ( 'email' === customField.type ) {
                        return ( <a href={ `mailto:${ value }` }>{ value }</a> );
                    }

                    return ( <div>{ value }</div> );
                },
            });
        }

        return columns;
    }
};