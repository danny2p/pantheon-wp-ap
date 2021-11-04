import React, { Component, Fragment } from 'react';

import '../../../../css/admin/dashboard/health-check.scss';

import { __wprm } from 'Shared/Translations';

import Block from '../../layout/Block';
import Item from './Item';

export default class Recipes extends Component {
    constructor(props) {
        super(props);

        this.state = {
            items: wprm_admin_dashboard.health.items,
        }
    }

    render() {


        return (
            <Block
                title={ __wprm( 'Health Check' ) }
                button={ __wprm( 'Run Check' ) }
                buttonAction={ () => {
                    alert( 'Checking now' );
                }}
            >
                <div className="wprm-admin-dashboard-health-check-container">
                    <div className={ `wprm-admin-dashboard-health-check-last-update wprm-admin-dashboard-health-check-last-update-${ wprm_admin_dashboard.health.urgency }` }>{ __wprm( 'Last check:' ) } { wprm_admin_dashboard.health.date_formatted }</div>
                    <div className="wprm-admin-dashboard-health-check-description">
                        { __wprm( 'Use the Health Check feature to search for any WPRM-related issues and improve your recipes.' ) } { __wprm( 'Recommended to run occassionally by clicking on the blue button.' ) }
                    </div>
                    {
                        this.state.items.map( ( item, index ) => {
                            return (
                                <Item
                                    item={ item }
                                    key={ index }
                                />
                            )
                        } )
                    }
                </div>
            </Block>
        );
    }
}