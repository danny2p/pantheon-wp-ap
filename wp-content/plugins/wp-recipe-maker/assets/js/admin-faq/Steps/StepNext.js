import React from 'react';
import { __wprm } from 'Shared/Translations';
import Faq from '../Faq';

const StepNext = (props) => {
    return (
        <div className="wprm-admin-onboarding-step-next">
            <p>
                { __wprm( 'You made it to the end of onboarding. There is still a lot to explore, but we recommend starting by creating a recipe now. And do not forget to' ) } <strong>{ __wprm( 'sign up for the email course' ) }</strong> { __wprm( 'below to get the most out of this plugin.' ) }
            </p>
            <p>
                { __wprm( 'No need to worry about leaving this page. The information below is always available on' ) } <em>{ __wprm( 'WP Recipe Maker > FAQ & Support' ) }</em>.
            </p>
            <Faq context="onboarding" />
            <div className="footer-buttons">
                    <a
                        href={ wprm_admin.manage_url + '&skip_onboarding=1' }
                        className="button button-primary"
                    >{ __wprm( 'Continue to the Manage page' ) }</a>
                </div>
        </div>
    );
}
export default StepNext;
