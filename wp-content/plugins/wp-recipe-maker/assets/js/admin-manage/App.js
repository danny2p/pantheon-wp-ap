import React, { Component } from 'react';
import { Route, useHistory } from 'react-router-dom';

import Menu from './Menu';
import Notices from './Notices';
import DataTable from './DataTable';
import ErrorBoundary from 'Shared/ErrorBoundary';

import '../../css/admin/manage/app.scss';
import defaultDatatables from './DataTableConfig';
const { hooks } = WPRecipeMaker['wp-recipe-maker/dist/shared'];

export default class App extends Component {
    render() {
        let datatables = hooks.applyFilters( 'datatables', defaultDatatables );

        return (
            <ErrorBoundary module="Manage">
                <div id="wprm-admin-manage-header">
                    <Menu
                        datatables={ datatables }
                    />
                    <Notices />
                </div>
                <div id="wprm-admin-manage-content">
                    <Route path="/:type?/:filter?" render={( {match} ) => {
                        let type = 'recipe';
                        if ( match.params.type && Object.keys(datatables).includes( match.params.type ) ) {
                            type = match.params.type;
                        }

                        let filter = false;
                        if ( match.params.filter ) {
                            const filterParts = match.params.filter.split( '=' );

                            if ( 2 === filterParts.length ) {
                                filter = filterParts;
                            }
                        }

                        if ( ! datatables.hasOwnProperty( type ) ) {
                            return null;
                        }
                        
                        return (
                            <DataTable
                                type={ type }
                                filter={ filter }
                                onRemoveFilter={ () => {
                                    const history = useHistory();
                                    history.push( `/${type}` );
                                }}
                                options={ datatables[ type ] }
                            />
                        )
                    }} />
                </div>
            </ErrorBoundary>
        );
    }
}
