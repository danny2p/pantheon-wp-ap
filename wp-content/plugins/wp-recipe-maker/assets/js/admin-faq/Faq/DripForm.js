
import React, { Fragment } from 'react';

const DripForm = (props) => {
    return (
        <Fragment>
            <p>
                We built an email course full of <strong>tips and tricks</strong> to help you get the most out of WP Recipe Maker.
            </p>
            <p>
                During the course you'll get introduced to a <strong>private Facebook group</strong> full of WP Recipe Maker Food Bloggers to learn from and we'll even <strong>help promote your recipes on social media</strong> for free.
            </p>
            <form action="https://www.getdrip.com/forms/917801565/submissions" method="post" className="wprm-drip-form" data-drip-embedded-form="917801565" target="_blank">
                <div>
                    <div>
                        <label htmlFor="drip-email">Email Address</label><br />
                        <input type="email" id="drip-email" name="fields[email]" defaultValue={ wprm_faq.user.email } />
                        <input type="hidden" id="drip-customer-website" name="fields[customer_website]" value={ wprm_faq.user.website } />
                    </div>
                    <div>
                        <input type="hidden" name="fields[eu_consent]" id="drip-eu-consent-denied" value="denied" />
                        <input type="checkbox" name="fields[eu_consent]" id="drip-eu-consent" value="granted" />
                        <label htmlFor="drip-eu-consent">I understand and agree to the <a href="https://www.iubenda.com/privacy-policy/82708778">privacy policy</a></label>
                    </div>
                    <div>
                        <input type="hidden" name="fields[eu_consent_message]" value="I understand and agree to the privacy policy (https://www.iubenda.com/privacy-policy/82708778)" />
                    </div>
                </div>
                <div>
                    <input type="submit" name="submit" value="Help me get the most out of WP Recipe Maker!" className="button button-primary" data-drip-attribute="sign-up-button" />
                </div>
            </form>
        </Fragment>
    );
}
export default DripForm;
