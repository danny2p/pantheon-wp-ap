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
        let columns = [
            bulkEditCheckbox( datatable, 'term_id' ),
            {
                Header: __wprm( 'Sort:' ),
                id: 'actions',
                headerClassName: 'wprm-admin-table-help-text',
                sortable: false,
                width: 100,
                Filter: () => (
                    <div>
                        { __wprm( 'Filter:' ) }
                    </div>
                ),
                Cell: row => (
                    <div className="wprm-admin-manage-actions">
                        <Fragment>
                            <Icon
                                type="pencil"
                                title={ `${ __wprm( 'Rename' ) } ${ datatable.props.options.label.singular }` }
                                onClick={() => {
                                    let newName = prompt( `${ __wprm( 'What do you want to be the new name for' ) } "${row.original.name}"? ${ __wprm( 'This will update the unit for all recipes using it. Take note that terms are case insensitive (t and T will be seen as the same unit and both get replaced).' ) }`, row.original.name );
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
                    </div>
                ),
            },{
                Header: __wprm( 'ID' ),
                id: 'id',
                accessor: 'term_id',
                width: 65,
                Filter: (props) => (<TextFilter {...props}/>),
            },{
                Header: __wprm( 'Unit' ),
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

        return columns;
    }
};