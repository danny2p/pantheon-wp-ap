import React, { Component, Fragment } from 'react';

import '../../../../css/admin/dashboard/health-check.scss';

import { __wprm } from 'Shared/Translations';

import Block from '../../layout/Block';
import ItemCompatibility from './ItemCompatibility';
import ItemDuplicateNames from './ItemDuplicateNames';
import ItemMissingThumbnails from './ItemMissingThumbnails';
import ItemMultipleParents from './ItemMultipleParents';
import ItemSeoTypes from './ItemSeoTypes';

const itemBlocks = {
    compatibility: ItemCompatibility,
    duplicate_names: ItemDuplicateNames,
    missing_thumbnails: ItemMissingThumbnails,
    multiple_parents: ItemMultipleParents,
    seo_types: ItemSeoTypes,
}
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
                    window.location = wprm_admin_dashboard.health.tool;
                }}
            >
                <div className="wprm-admin-dashboard-health-check-container">
                    <div
                        className={ `wprm-admin-dashboard-health-check-last-update wprm-admin-dashboard-health-check-last-update-${ wprm_admin_dashboard.health.urgency }` }
                        title={ wprm_admin_dashboard.health.date ? wprm_admin_dashboard.health.date_formatted_full : null }
                    >
                        { __wprm( 'Last check:' ) } { wprm_admin_dashboard.health.date_formatted }{ wprm_admin_dashboard.health.updated && ` (${ __wprm( 'outdated version' ) })`}
                    </div>
                    <div className="wprm-admin-dashboard-health-check-description">
                        {
                            __wprm( 'Use the Health Check feature to search for any WPRM-related issues and improve your recipes.' )
                        } {
                            'never' === wprm_admin_dashboard.health.urgency
                            ?
                            __wprm( 'Recommended to run occassionally by clicking on the blue button.' )
                            :
                            __wprm( 'Most recent results:' )
                        }
                    </div>
                    {
                        Object.keys( this.state.items ).map( ( type, index ) => {
                            const item = this.state.items[ type ];
                            let ItemBlock = itemBlocks.hasOwnProperty( type ) ? itemBlocks[ type ] : false;

                            if ( ! ItemBlock ) {
                                return null;
                            }

                            return (
                                <ItemBlock
                                    item={ item }
                                    key={ index }
                                />
                            )
                        } )
                    }
                    <div className="wprm-admin-dashboard-health-check-learn-more">
                        <a href="https://help.bootstrapped.ventures/article/306-health-check" target="_blank">{ __wprm( 'Learn more about the Health Check feature' ) }</a>
                    </div>
                </div>
            </Block>
        );
    }
}