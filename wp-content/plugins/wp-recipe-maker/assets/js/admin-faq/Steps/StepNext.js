import React from 'react';
import Faq from '../Faq';

const StepNext = (props) => {
    return (
        <div className="wprm-admin-onboarding-step-next">
            <p>
                You made it to the end of the onboarding! There is a lot left to explore, but we recommend just starting by creating a recipe now. And don't forget to <strong>sign up for the email course</strong> below to get the most out of this plugin. You won't regret it!
            </p>
            <p>
                No need to worry about leaving this page either. The information below will be available on the <em>WP Recipe Maker > FAQ & Support</em> page at any time.
            </p>
            <Faq />
            <div className="footer-buttons">
                    <a
                        href={ wprm_admin.manage_url + '&skip_onboarding=1' }
                        className="button button-primary"
                    >Continue to the Manage page</a>
                </div>
        </div>
    );
}
export default StepNext;