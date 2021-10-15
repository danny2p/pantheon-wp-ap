import React from 'react';

const StepWelcome = (props) => {
    return (
        <div className="wprm-admin-onboarding-step-welcome">
            <p>
                Welcome to WP Recipe Maker!
            </p>
            <p>
                These onboarding steps will get you up and running in no time by <strong>choosing the correct options for your situation</strong> and showing you how to get the most out of this plugin.
            </p>
            <div className="wprm-admin-onboarding-step-welcome-buttons">
                <button
                    className="button button-primary"
                    onClick={() => {
                        props.jumpToStep(1);
                    }}
                >Start the onboarding!</button>
                <a href={ wprm_admin.manage_url + '&skip_onboarding=1' }>or click here to skip</a>
            </div>
        </div>
    );
}
export default StepWelcome;