import React, { Component, Fragment } from 'react';

import '../../../../css/admin/dashboard/analytics.scss';

import { __wprm } from 'Shared/Translations';
import Api from 'Shared/Api';

import Block from '../../layout/Block';
import Footer from '../../layout/Footer';
import EnableAnalytics from './EnableAnalytics';
import Charts from './Charts';

export default class Analytics extends Component {
    constructor(props) {
        super(props);

        this.state = {
            analyticsEnabled: wprm_admin_dashboard.settings.analytics_enabled,
        }
    }

    render() {
        return (
            <Block
                title={ __wprm( 'Analytics' ) }
            >
                {                
                    this.state.analyticsEnabled
                    ?
                    <Fragment>
                        <Charts />
                        {/* {
                            wprm_admin_dashboard.settings.honey_home_integration
                            ?
                            <Footer
                                title={ __wprm( 'Learn more' ) }
                            >
                                Go to your <a href="https://dailygrub.com/dashboard" target="_blank">DailyGrub Dashboard</a> for more insights!
                            </Footer>
                            :
                            <Footer
                                title={ `📈 ${ __wprm( 'Interested in more data?' )}` }
                            >
                                WP Recipe Maker partners with <a href="https://dailygrub.com" target="_blank">DailyGrub</a> to offer a full suite of recipe-specific analytics!
                            </Footer>
                        } */}
                    </Fragment>
                    :
                    <EnableAnalytics
                        onEnable={() => {
                            // Save setting.
                            Api.settings.save({
                                analytics_enabled: true,
                            });

                            // Update component.
                            this.setState({
                                analyticsEnabled: true,
                            });
                        }}
                    />
                }
            </Block>
        );
    }
}