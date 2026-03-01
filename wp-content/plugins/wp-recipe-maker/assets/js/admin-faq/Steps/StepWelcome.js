import React from 'react';
import { __wprm } from 'Shared/Translations';

const StepWelcome = (props) => {
    return (
        <div className="wprm-admin-onboarding-step-welcome">
            <p>
                { __wprm( 'Welcome to WP Recipe Maker!' ) }
            </p>
            <p>
                { __wprm( 'These onboarding steps get you up and running quickly by' ) } <strong>{ __wprm( 'choosing the correct options for your situation' ) }</strong> { __wprm( 'and showing you how to get the most out of this plugin.' ) }
            </p>
            <div className="wprm-admin-onboarding-step-welcome-buttons">
                <button
                    className="button button-primary"
                    onClick={() => {
                        props.jumpToStep(1);
                    }}
                >{ __wprm( 'Start the onboarding!' ) }</button>
                <a href={ wprm_admin.manage_url + '&skip_onboarding=1' }>{ __wprm( 'or click here to skip' ) }</a>
            </div>
        </div>
    );
}
export default StepWelcome;
