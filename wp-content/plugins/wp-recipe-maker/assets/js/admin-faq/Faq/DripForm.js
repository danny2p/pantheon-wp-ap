import React from 'react';
import { __wprm } from 'Shared/Translations';

const DripForm = (props) => {
    const anchorId = props.anchorId ? props.anchorId : 'wprm-faq-email-signup';

    return (
        <div id={ anchorId } className="wprm-faq-signup-form">
            <p>
                { __wprm( 'We built an email course full of' ) } <strong>{ __wprm( 'tips and tricks' ) }</strong> { __wprm( 'to help you get the most out of WP Recipe Maker.' ) }
            </p>
            <p>
                { __wprm( 'During the course, you will get introduced to a' ) } <strong>{ __wprm( 'private Facebook group' ) }</strong> { __wprm( 'with fellow food bloggers and we will even' ) } <strong>{ __wprm( 'help promote your recipes on social media' ) }</strong> { __wprm( 'for free.' ) }
            </p>
            <form action="https://www.getdrip.com/forms/917801565/submissions" method="post" className="wprm-drip-form" data-drip-embedded-form="917801565" target="_blank">
                <div>
                    <div>
                        <label htmlFor="drip-email">{ __wprm( 'Email Address' ) }</label><br />
                        <input type="email" id="drip-email" name="fields[email]" defaultValue={ wprm_faq.user.email } />
                        <input type="hidden" id="drip-customer-website" name="fields[customer_website]" value={ wprm_faq.user.website } />
                    </div>
                    <div>
                        <input type="hidden" name="fields[eu_consent]" id="drip-eu-consent-denied" value="denied" />
                        <input type="checkbox" name="fields[eu_consent]" id="drip-eu-consent" value="granted" />
                        <label htmlFor="drip-eu-consent">{ __wprm( 'I understand and agree to the' ) } <a href="https://www.iubenda.com/privacy-policy/82708778" target="_blank" rel="noopener noreferrer">{ __wprm( 'privacy policy' ) }</a></label>
                    </div>
                    <div>
                        <input type="hidden" name="fields[eu_consent_message]" value={ __wprm( 'I understand and agree to the privacy policy (https://www.iubenda.com/privacy-policy/82708778)' ) } />
                    </div>
                </div>
                <div>
                    <input type="submit" name="submit" value={ __wprm( 'Help me get the most out of WP Recipe Maker!' ) } className="button button-primary" data-drip-attribute="sign-up-button" />
                </div>
            </form>
        </div>
    );
}
export default DripForm;
