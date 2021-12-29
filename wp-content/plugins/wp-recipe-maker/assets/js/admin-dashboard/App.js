const { hooks } = WPRecipeMaker['wp-recipe-maker/dist/shared'];

import React, { Component, Fragment } from 'react';

import '../../css/admin/dashboard/app.scss';
import Grid from './layout/Grid';
import Marketing from './Marketing';

import Analytics from './blocks/Analytics';
import Feedback from './blocks/Feedback';
import HealthCheck from './blocks/HealthCheck';
import Learn from './blocks/Learn';
import News from './blocks/News';
import Recipes from './blocks/Recipes';
import Tips from './blocks/Tips';

let blocks = hooks.applyFilters( 'dashboardBlocks', [
    { id: 'recipes', block: Recipes },
    { id: 'news', block: News },
    { id: 'health', block: HealthCheck },
    { id: 'tips', block: Tips },
    { id: 'learn', block: Learn },
    { id: 'analytics', block: Analytics },
] );

// Only add if showing (to not mess up order).
if ( window.wprm_admin_dashboard.hasOwnProperty( 'feedback' ) && window.wprm_admin_dashboard.feedback ) {
    blocks.unshift( { id: 'feedback', block: Feedback } );
}

export default class App extends Component {
    render() {
        let marketingCampaign = false;
        if ( window.wprm_admin_dashboard.hasOwnProperty( 'marketing' ) && window.wprm_admin_dashboard.marketing ) {
            marketingCampaign = wprm_admin_dashboard.marketing;
        }
        
        return (
            <Fragment>
                <h1>WP Recipe Maker</h1>
                {
                    false !== marketingCampaign
                    &&
                    <Marketing
                        campaign={ marketingCampaign }
                    />
                }
                <Grid
                    blocks={ blocks }
                />
            </Fragment>
        )
    }
}
