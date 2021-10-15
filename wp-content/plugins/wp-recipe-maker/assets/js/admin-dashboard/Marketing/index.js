import React, { Fragment } from 'react';

import '../../../css/admin/dashboard/marketing.scss';

import { __wprm } from 'Shared/Translations';
import Block from '../layout/Block';

import Countdown from './Countdown';
 
const Marketing = (props) => {
    const { campaign } = props;
    console.log( campaign );

    return (
        <div className="wprm-admin-dashboard-marketing">
            <Block
                title={ campaign.page_title }
            >
                <p dangerouslySetInnerHTML={ { __html: campaign.page_text } } />
                <Countdown
                    countdown={ campaign.countdown }
                />
                <a
                    className="button button-primary"
                    href={ campaign.url }
                    target="blank"
                >{ __wprm( 'Learn more about the sale' ) } 🎉</a>
            </Block>
        </div>
    );   
}
export default Marketing;