import React, { Component, Fragment } from 'react';

import StepZilla from 'react-stepzilla';
import steps from './Steps';

import Faq from './Faq';
import AjaxWrapper from 'Shared/AjaxWrapper';

import '../../css/admin/onboarding/app.scss';

export default class App extends Component {
    render() {
        if ( wprm_faq.onboarded ) {
            return (
                <Faq context="faq_page" />
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
                                    AjaxWrapper.call('wprm_finished_onboarding');
                                }
                            }}
                        />
                    </div>
                </Fragment>
            );   
        }
    }
}
