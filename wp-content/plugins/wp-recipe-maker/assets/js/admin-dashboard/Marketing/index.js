import React, { Component, Fragment } from 'react';

import '../../../css/admin/dashboard/marketing.scss';

import Api from 'Shared/Api';
import { __wprm } from 'Shared/Translations';
import Block from '../layout/Block';

import Countdown from './Countdown';
 
export default class Marketing extends Component {
    render() {
        const { campaign } = this.props;
        // Hide after dismissing.
        if ( campaign.hasOwnProperty( 'dismissed' ) && campaign.dismissed ) {
            return null;
        }

        return (
            <div className="wprm-admin-dashboard-marketing">
                <Block
                    title={ campaign.page_title }
                >
                    <p dangerouslySetInnerHTML={ { __html: campaign.page_text } } />
                    <Countdown
                        countdown={ campaign.countdown }
                    />
                    <div class="wprm-admin-dashboard-marketing-actions">
                        <a
                            className="button button-primary"
                            href={ campaign.url }
                            target="_blank"
                        >{ __wprm( 'Learn more about the sale' ) } 🎉</a>
                        <a
                            href="#"
                            class="wprm-admin-dashboard-marketing-actions-remove"
                            onClick={() => {
                                Api.general.dismissNotice( `dashboard_${ campaign.id }` );
                                campaign.dismissed = true;
                                this.forceUpdate();
                            }}
                        >{ __wprm( 'Remove Notice' ) }</a>
                    </div>
                </Block>
            </div>
        );
    }
}