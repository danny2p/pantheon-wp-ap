import React, { Component, Fragment } from 'react';

import StepZilla from 'react-stepzilla';
import steps from './Steps';

import Faq from './Faq';

import '../../css/admin/onboarding/app.scss';

export default class App extends Component {
    render() {
        if ( wprm_faq.onboarded ) {
            return (
                <Faq />
            );
        } else {
            return (
                <Fragment>
                    <h1>WP Recipe Maker</h1>
                    <div id="wprm-admin-onboarding-steps">
                        <StepZilla
                            steps={ steps }
                            stepsNavigation={ false }
                            prevBtnOnLastStep={ false }
                            backButtonCls="button"
                            nextButtonCls="button button-primary"
                            // startAtStep={ 4 }
                            onStepChange={ (step) => {
                                if ( step === steps.length - 1 ) {
                                    // Finished last step, set onboarding done.
                                    fetch(wprm_admin.ajax_url, {
                                        method: 'POST',
                                        credentials: 'same-origin',
                                        body: 'action=wprm_finished_onboarding&security=' + wprm_admin.nonce,
                                        headers: {
                                            'Accept': 'application/json, text/plain, */*',
                                            'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
                                        },
                                    });
                                }
                            }}
                        />
                    </div>
                </Fragment>
            );   
        }
    }
}
