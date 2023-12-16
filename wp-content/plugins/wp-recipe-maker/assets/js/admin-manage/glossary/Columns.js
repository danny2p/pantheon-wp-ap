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
                width: 105,
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
                                    let newName = prompt( `${ __wprm( 'What do you want to be the new name for' ) } "${row.original.name}"? ${ __wprm( 'This will update the term anywhere you are using it. Take note that terms are case insensitive (t and T will be seen as the same unit and both get replaced).' ) }`, row.original.name );
                                    if( newName && newName.trim() ) {
                                        Api.manage.renameTerm(datatable.props.options.id, row.original.term_id, newName).then(() => datatable.refreshData());
                                    }
                                }}
                            />
                            <Icon
                                type="duplicate"
                                title={ `${ __wprm( 'Clone' ) } ${ datatable.props.options.label.singular }` }
                                onClick={() => {
                                    let name;

                                    while ( true ) {
                                        name = prompt( __wprm( 'What do you want to be the new term to be?' ) + ' ' + __wprm( 'Make sure the name is different.' ), row.original.name );

                                        if ( name === null || name !== row.original.name ) {
                                            break;
                                        }
                                    }

                                    if( name && name.trim() && name !== row.original.name ) {
                                        Api.manage.cloneTerm( 'glossary_term', row.original.term_id, name ).then((data) => {
                                            if ( ! data ) {
                                                alert( __wprm( 'We were not able to create this term. Make sure it does not exist yet.' ) );
                                            } else {
                                                datatable.refreshData();
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
                Header: __wprm( 'Term' ),
                id: 'name',
                accessor: 'name',
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => row.value ? he.decode(row.value) : null,
            },{
                Header: __wprm( 'Tooltip' ),
                id: 'tooltip',
                accessor: 'tooltip',
                width: 500,
                Filter: (props) => (<TextFilter {...props}/>),
                Cell: row => {
                    return (
                        <div className="wprm-manage-glossary-tooltip-container">
                            <Icon
                                type="pencil"
                                title={ __wprm( 'Change Tooltip' ) }
                                onClick={() => {
                                    const newTooltip = prompt( `${ __wprm( 'What do you want to be the new tooltip for' ) } "${row.original.name}"?`, row.value );
                                    if( null !== newTooltip ) {
                                        Api.manage.changeTermDescription(datatable.props.options.id, row.original.term_id, newTooltip).then(() => datatable.refreshData());
                                    }
                                }}
                            />
                            {
                                row.value
                                ?
                                <span dangerouslySetInnerHTML={ { __html: row.value } } />
                                :
                                null
                            }
                        </div>
                    )
                },
            }
        ];

        return columns;
    }
};