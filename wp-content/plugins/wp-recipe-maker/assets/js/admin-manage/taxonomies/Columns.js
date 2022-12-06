import React, { Fragment } from 'react';
import { NavLink } from 'react-router-dom';
import he from 'he';
 
import Media from 'Modal/general/Media';
import TextFilter from '../general/TextFilter';
import bulkEditCheckbox from '../general/bulkEditCheckbox';
import Api from 'Shared/Api';
import Icon from 'Shared/Icon';
import Tooltip from 'Shared/Tooltip';
import { __wprm } from 'Shared/Translations';

import '../../../css/admin/manage/taxonomies.scss';

export default {
    getColumns( datatable ) {
        const link_nofollow_options = wprm_admin_modal.options.hasOwnProperty( `${datatable.props.options.id}_link_nofollow` ) ? wprm_admin_modal.options[`${datatable.props.options.id}_link_nofollow`] : wprm_admin_modal.options.term_link_nofollow;

        let columns = [];

        if ( 'suitablefordiet' !== datatable.props.options.id ) {
            columns.push( bulkEditCheckbox( datatable, 'term_id' ) );
        }

        columns = [
            ...columns,
            {
                Header: __wprm( 'Sort:' ),
                id: 'actions',
                headerClassName: 'wprm-admin-table-help-text',
                sortable: false,
                width: 'suitablefordiet' === datatable.props.options.id ? 65 : 100,
                Filter: () => (
                    <div>
                        { __wprm( 'Filter:' ) }
                    </div>
                ),
                Cell: row => (
                    <div className="wprm-admin-manage-actions">
                        {
                            'suitablefordiet' === datatable.props.options.id
                            ?
                            <Icon
                                type="pencil"
                                title={ `${ __wprm( 'Rename' ) } ${ datatable.props.options.label.singular }` }
                                onClick={() => {
                                    let newName = prompt( `${ __wprm( 'What do you want to be the new name for' ) } "${row.original.label}"?`, row.original.label );
                                    if( newName && newName.trim() ) {
                                        Api.manage.renameTermLabel(datatable.props.options.id, row.original.term_id, newName).then(() => datatable.refreshData());
                                    }
                                }}
                            />
                            :
                            <Fragment>
                                <Icon
                                    type="pencil"
                                    title={ `${ __wprm( 'Rename' ) } ${ datatable.props.options.label.singular }` }
                                    onClick={() => {
                                        let newName = prompt( `${ __wprm( 'What do you want to be the new name for' ) } "${row.original.name}"?`, row.original.name );
                                        if( newName && newName.trim() ) {
                                            Api.manage.renameTerm(datatable.props.options.id, row.original.term_id, newName).then(() => datatable.refreshData());
                                        }
                                    }}
                                />
                                <Icon
                                    type="merge"
                                    title={ `${ __wprm( 'Merge into another' ) } ${ datatable.props.options.label.singular }` }
                                    onClick={() => {
                                        let newId = prompt( `${ __wprm( 'What is the ID of the term you want the merge' ) } "${row.original.name}" ${ __wprm( 'into' ) }?` );
                                        if( newId && newId != row.original.term_id && newId.trim() ) {
                                            Api.manage.getTerm(datatable.props.options.id, newId).then(newTerm => {
                                                if ( newTerm ) {
                                                    if ( confirm( `${ __wprm( 'Are you sure you want to merge' ) } "${row.original.name}" ${ __wprm( 'into' ) } "${newTerm.name}"?` ) ) {
                                                        Api.manage.mergeTerm(datatable.props.options.id, row.original.term_id, newId).then(() => datatable.refreshData());
                                                    }
                                                } else {
                                                    alert( __wprm( 'We could not find a term with that ID.' ) );
                                                }
                                            });
                                        }
                                    }}
                                />
                                <Icon
                                    type="trash"
                                    title={ `${ __wprm( 'Delete' ) } ${ datatable.props.options.label.singular }` }
                                    onClick={() => {
                                        if( confirm( `${ __wprm( 'Are you sure you want to delete' ) } "${row.original.name}"?` ) ) {
                                            Api.manage.deleteTerm(datatable.props.options.id, row.original.term_id).then(() => datatable.refreshData());
                                        }
                                    }}
                                />
                            </Fragment>
                        }
                    </div>
                ),
            },{
                Header: __wprm( 'ID' ),
                id: 'id',
                accessor: 'term_id',
                width: 65,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Slug' ),
                id: 'slug',
                accessor: 'slug',
                width: 200,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    return (
                        <div className="wprm-manage-ingredients-group-container">
                            <Icon
                                type="pencil"
                                title={ __wprm( 'Change Slug' ) }
                                onClick={() => {
                                    const newSlug = prompt( `${ __wprm( 'What do you want the slug to be for' ) } "${row.original.name}"?`, row.value );
                                    if( false !== newSlug ) {
                                        Api.manage.changeTermSlug(datatable.props.options.id, row.original.term_id, newSlug).then(() => datatable.refreshData());
                                    }
                                }}
                            />
                            {
                                row.original.permalink
                                ?
                                <span><a href={ row.original.permalink } target="_blank">{ row.value }</a></span>
                                :
                                <span>{ row.value }</span>
                            }
                        </div>
                    )
                },
            },{
                Header: 'suitablefordiet' === datatable.props.options.id ? __wprm( 'Diet' ) : __wprm( 'Name' ),
                id: 'name',
                accessor: 'name',
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => row.value ? he.decode(row.value) : null,
            },{
                Header: __wprm( 'Recipes' ),
                id: 'count',
                accessor: 'count',
                filterable: false,
                width: 65,
                Cell: row => {
                    return (
                        <NavLink to={ `/recipe/${ datatable.props.options.id }=${row.original.term_id}` }>{ row.value }</NavLink>
                    )
                }
            }
        ];

        if ( 'suitablefordiet' === datatable.props.options.id ) {
            columns.push({
                Header: __wprm( 'Label' ),
                id: 'label',
                accessor: 'label',
                sortable: false,
                filterable: false,
                Cell: row => row.value ? he.decode(row.value) : null,
            });
        }

        if ( 'ingredient' === datatable.props.options.id ) {
            columns.push({
                Header: __wprm( 'Plural' ),
                id: 'plural',
                accessor: 'plural',
                width: 200,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    return (
                        <div className="wprm-manage-ingredients-group-container">
                            <Icon
                                type="pencil"
                                title={ __wprm( 'Change Plural' ) }
                                onClick={() => {
                                    const newPlural = prompt( `${ __wprm( 'What do you want the plural to be for' ) } "${row.original.name}"?`, row.value );
                                    if( false !== newPlural ) {
                                        Api.manage.updateTaxonomyMeta('ingredient', row.original.term_id, { plural: newPlural }).then(() => datatable.refreshData());
                                    }
                                }}
                            />
                            {
                                row.value
                                ?
                                <span>{ row.value }</span>
                                :
                                null
                            }
                        </div>
                    )
                },
            });
        }

        if ( 'ingredient' === datatable.props.options.id && wprm_admin.addons.premium ) {
            columns.push({
                Header: __wprm( 'Shopping List Group' ),
                id: 'group',
                accessor: 'group',
                width: 200,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    return (
                        <div className="wprm-manage-ingredients-group-container">
                            <Icon
                                type="pencil"
                                title={ __wprm( 'Change Group' ) }
                                onClick={() => {
                                    const newGroup = prompt( `${ __wprm( 'What do you want to be the new group for' ) } "${row.original.name}"?`, row.value );
                                    if( false !== newGroup ) {
                                        Api.manage.updateTaxonomyMeta('ingredient', row.original.term_id, { group: newGroup }).then(() => datatable.refreshData());
                                    }
                                }}
                            />
                            {
                                row.value
                                ?
                                <span>{ row.value }</span>
                                :
                                null
                            }
                        </div>
                    )
                },
            });
        }

        if ( wprm_admin.addons.premium ) {
            // Term images.
            columns.push({
                Header: __wprm( 'Image' ),
                id: 'image_id',
                accessor: 'image_id',
                width: 110,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'Show All' ) }</option>
                        <option value="yes">{ __wprm( 'Has Image' ) }</option>
                        <option value="no">{ __wprm( 'Does not have Image' ) }</option>
                    </select>
                ),
                Cell: row => {
                    const selectImage = (e) => {
                        e.preventDefault();
                                
                        Media.selectImage((attachment) => {
                            Api.manage.updateTaxonomyMeta(datatable.props.options.id, row.original.term_id, { image_id: attachment.id }).then(() => datatable.refreshData());
                        });
                    };

                    return (
                        <div className="wprm-manage-image-container">
                            {
                                row.value
                                ?
                                <div className="wprm-manage-image-preview">
                                    <Tooltip content={ __wprm( 'Edit Image' ) }>
                                        <img
                                            src={ row.original.image_url }
                                            onClick={ selectImage }
                                        />
                                    </Tooltip>
                                    <Icon
                                        type="trash"
                                        title={ __wprm( 'Remove Image' ) }
                                        onClick={ () => {
                                            Api.manage.updateTaxonomyMeta(datatable.props.options.id, row.original.term_id, { image_id: 0 }).then(() => datatable.refreshData());
                                        } }
                                    />
                                </div>
                                :
                                <Icon
                                    type="photo"
                                    title={ __wprm( 'Add Image' ) }
                                    onClick={ selectImage }
                                />
                            }
                        </div>
                    )
                },
            });
        }

        if ( wprm_admin.addons.premium ) {
            // Easy Affiliate Links plugin integration.
            if ( window.hasOwnProperty( 'EAFL_Modal' ) ) {
                columns.push({
                    Header: __wprm( 'Easy Affiliate Links' ),
                    id: 'eafl',
                    accessor: 'eafl',
                    width: 300,
                    Filter: (props) => (<TextFilter {...props}/>),
                    Cell: row => {
                        return (
                            <div className="wprm-manage-ingredients-eafl-container">
                                {
                                    row.value
                                    ?
                                    <Fragment>
                                        <Icon
                                            type="eafl-link"
                                            title={ __wprm( 'Edit Link' ) }
                                            onClick={() => {
                                                if ( row.original.hasOwnProperty( 'eafl_details' ) ) {
                                                    EAFL_Modal.open('edit', { link: row.original.eafl_details, saveCallback: () => datatable.refreshData() });
                                                } else {
                                                    alert( __wprm( 'An Affiliate Link with this ID cannot be found. Try deleting and adding it again.' ) );
                                                }
                                            }}
                                        />
                                        &nbsp;
                                        <Icon
                                            type="eafl-unlink"
                                            title={ __wprm( 'Remove Link' ) }
                                            onClick={() => {
                                                if( confirm( __wprm( 'Are you sure you want to delete this link?' ) ) ) {
                                                    Api.manage.updateTaxonomyMeta(datatable.props.options.id, row.original.term_id, { eafl: '' }).then(() => datatable.refreshData());
                                                }
                                            }}
                                        />
                                        <div className="wprm-manage-ingredients-eafl-details">
                                            {
                                                row.original.hasOwnProperty( 'eafl_details' )
                                                ?
                                                <Fragment>
                                                    <div>#{row.value} - {row.original.eafl_details.name}</div>
                                                    <div><a href={row.original.eafl_details.url} target="_blank">{row.original.eafl_details.url}</a></div>
                                                </Fragment>
                                                :
                                                <div>#{row.value} - { __wprm( 'n/a' ) }</div>
                                            }
                                        </div>
                                    </Fragment>
                                    :
                                    <Icon
                                        type="eafl-link"
                                        title={ __wprm( 'Set Affiliate Link' ) }
                                        onClick={() => {
                                            EAFL_Modal.open('insert', {
                                                insertCallback: function(link) {
                                                    Api.manage.updateTaxonomyMeta(datatable.props.options.id, row.original.term_id, { eafl: link.id }).then(() => datatable.refreshData());
                                                },
                                                selectedText: row.original.name,
                                            });
                                        }}
                                    />
                                }
                            </div>
                        )
                    },
                })
            }

            columns.push({
                Header: __wprm( 'Link' ),
                id: 'link',
                accessor: 'link',
                width: 300,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    return (
                        <div className="wprm-manage-ingredients-link-container">
                            <Icon
                                type="pencil"
                                title={ __wprm( 'Change Link' ) }
                                onClick={() => {
                                    const newLink = prompt( `${ __wprm( 'What do you want to be the new link for' ) } "${row.original.name}"?`, row.value );
                                    if( false !== newLink ) {
                                        if ( '' === newLink || 'http' === newLink.substring( 0, 4 ) || confirm( `"${newLink}" ${ __wprm( 'does not start with http:// or https:// as we would expect for a link. Are you sure you want to use this? Regular HTML code will not work here.' ) }` ) ) {
                                            Api.manage.updateTaxonomyMeta(datatable.props.options.id, row.original.term_id, { link: newLink }).then(() => datatable.refreshData());   
                                        }
                                    }
                                }}
                            />
                            {
                                row.value
                                ?
                                <a href={ row.value } target="_blank">{ row.value }</a>
                                :
                                null
                            }
                        </div>
                    )
                },
            },{
                Header: __wprm( 'Link Nofollow' ),
                id: 'link_nofollow',
                accessor: 'link_nofollow',
                width: 250,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'Any Nofollow' ) }</option>
                        {
                            link_nofollow_options.map((option, index) => (
                                <option value={option.value} key={index}>{ option.label }</option>
                            ))
                        }
                    </select>
                ),
                Cell: row => {
                    return (
                        <div>
                            {
                                row.original.link
                                ?
                                <select
                                    onChange={event => {
                                        Api.manage.updateTaxonomyMeta(datatable.props.options.id, row.original.term_id, { link_nofollow: event.target.value }).then(() => datatable.refreshData());
                                    }}
                                    style={{ width: '100%', fontSize: '1em' }}
                                    value={row.value}
                                >
                                    {
                                        link_nofollow_options.map((option, index) => (
                                            <option value={option.value} key={index}>{ option.label }</option>
                                        ))
                                    }
                                </select>
                                :
                                null
                            }
                        </div>
                    )
                },
            });
        }

        if ( 'equipment' === datatable.props.options.id && wprm_admin.addons.premium ) {
            columns.push({
                Header: __wprm( 'Affiliate HTML' ),
                id: 'affiliate_html',
                accessor: 'affiliate_html',
                width: 500,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    return (
                        <div className="wprm-manage-equipment-affiliate-html-container">
                            <Icon
                                type="pencil"
                                title={ __wprm( 'Change HTML' ) }
                                onClick={() => {
                                    WPRM_Modal.open( 'input-fields', {
                                        header: __wprm( 'Change Affiliate HTML' ),
                                        fields: [{
                                            label: 'HTML',
                                            type: 'textarea',
                                            value: row.value,
                                        }],
                                        insertCallback: ( args ) => {
                                            const affiliate_html = args.fields[0].value;
                                            Api.manage.updateTaxonomyMeta('equipment', row.original.term_id, { affiliate_html }).then(() => datatable.refreshData());
                                        },
                                    } );
                                }}
                            />
                            <span className="wprm-manage-equipment-affiliate-html">{ row.value }</span>
                        </div>
                    )
                },
            });

            columns.push({
                Header: __wprm( 'Affiliate HTML Preview' ),
                id: 'affiliate_html_preview',
                accessor: 'affiliate_html',
                width: 250,
                filterable: false,
                sortable: false,
                Cell: row => {
                    return (
                        <div className="wprm-manage-equipment-affiliate-html-preview-container">
                            <div dangerouslySetInnerHTML={ { __html: row.value } } />
                        </div>
                    )
                },
            });
        }

        // TODO Products.
        if ( false && wprm_admin.addons.elite && ( 'ingredient' === datatable.props.options.id || 'equipment' === datatable.props.options.id ) ) {
            columns.push({
                Header: __wprm( 'Product' ),
                id: 'product',
                accessor: 'product',
                width: 300,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'Show All' ) }</option>
                        <option value="yes">{ __wprm( 'Has Product' ) }</option>
                        <option value="no">{ __wprm( 'Does not have Product' ) }</option>
                    </select>
                ),
                Cell: row => {
                    console.log( row.value );

                    return (
                        <div className="wprm-manage-product-container">
                            <Icon
                                type="pencil"
                                title={ __wprm( 'Change Product' ) }
                                onClick={() => {
                                    WPRM_Modal.open( 'product', {
                                        label: row.original.name,
                                        taxonomy: datatable.props.options.id,
                                        term: row.original.term_id,
                                        product: row.value,
                                        saveCallback: () => datatable.refreshData(),
                                    } );
                                }}
                            />
                            {
                                row.value
                                ?
                                <a href={ row.value.url } target="_blank">{ row.value.name } (#{ row.value.id })</a>
                                :
                                null
                            }
                        </div>
                    )
                },
            });
        }

        if ( window.hasOwnProperty( 'wpupg_admin' ) ) {
            columns.push({
                Header: __wprm( 'Grid Link' ),
                id: 'wpupg_custom_link',
                accessor: 'wpupg_custom_link',
                width: 300,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    return (
                        <div className="wprm-manage-ingredients-link-container">
                            <Icon
                                type="pencil"
                                title={ __wprm( 'Change Link' ) }
                                onClick={() => {
                                    const newLink = prompt( `${ __wprm( 'What do you want to be the new grid link for' ) } "${row.original.name}"?`, row.value );
                                    if( false !== newLink ) {
                                        if ( '' === newLink || 'http' === newLink.substring( 0, 4 ) || confirm( `"${newLink}" ${ __wprm( 'does not start with http:// or https:// as we would expect for a link. Are you sure you want to use this? Regular HTML code will not work here.' ) }` ) ) {
                                            Api.manage.updateTaxonomyMeta(datatable.props.options.id, row.original.term_id, { wpupg_custom_link: newLink }).then(() => datatable.refreshData());   
                                        }
                                    }
                                }}
                            />
                            {
                                row.value
                                ?
                                <a href={ row.value } target="_blank">{ row.value }</a>
                                :
                                null
                            }
                        </div>
                    )
                },
            });

            columns.push({
                Header: __wprm( 'Grid Image' ),
                id: 'wpupg_custom_image',
                accessor: 'wpupg_custom_image',
                width: 110,
                sortable: false,
                Filter: ({ filter, onChange }) => (
                    <select
                        onChange={event => onChange(event.target.value)}
                        style={{ width: '100%', fontSize: '1em' }}
                        value={filter ? filter.value : 'all'}
                    >
                        <option value="all">{ __wprm( 'Show All' ) }</option>
                        <option value="yes">{ __wprm( 'Has Image' ) }</option>
                        <option value="no">{ __wprm( 'Does not have Image' ) }</option>
                    </select>
                ),
                Cell: row => {
                    const selectImage = (e) => {
                        e.preventDefault();
                                
                        Media.selectImage((attachment) => {
                            Api.manage.updateTaxonomyMeta(datatable.props.options.id, row.original.term_id, { wpupg_custom_image: attachment.id }).then(() => datatable.refreshData());
                        });
                    };

                    return (
                        <div className="wprm-manage-image-container">
                            {
                                row.value
                                ?
                                <div className="wprm-manage-image-preview">
                                    <Tooltip content={ __wprm( 'Edit Image' ) }>
                                        <img
                                            src={ row.original.wpupg_custom_image_url }
                                            width="80"
                                            onClick={ selectImage }
                                        />
                                    </Tooltip>
                                    <Icon
                                        type="trash"
                                        title={ __wprm( 'Remove Image' ) }
                                        onClick={ () => {
                                            Api.manage.updateTaxonomyMeta(datatable.props.options.id, row.original.term_id, { wpupg_custom_image: 0 }).then(() => datatable.refreshData());
                                        } }
                                    />
                                </div>
                                :
                                <Icon
                                    type="photo"
                                    title={ __wprm( 'Add Image' ) }
                                    onClick={ selectImage }
                                />
                            }
                        </div>
                    )
                },
            });
        }

        return columns;
    }
};