import React from 'react';
import { __wprm } from 'Shared/Translations';

import Advanced from './Advanced';
import DripForm from './DripForm';
import Editors from './Editors';
import GettingStarted from './GettingStarted';
import Troubleshooting from './Troubleshooting';

const Faq = () => {
    const showFeatureExplorer = !! wprm_faq.can_access_template_editor;
    const formAnchorId = 'wprm-faq-email-signup';

    const SignupReminder = () => (
        <div className="wprm-faq-signup-reminder">
            <h3>{ __wprm( 'Want more WP Recipe Maker tips?' ) }</h3>
            <p>
                { __wprm( 'Join our email course for practical setup tips, feature walkthroughs, and proven ways to get more out of WP Recipe Maker.' ) }
            </p>
            <a href={ `#${formAnchorId}` } className="button button-primary">
                { __wprm( 'Sign up for the email course' ) }
            </a>
        </div>
    );

    return (
        <div id="wprm-admin-faq-container">
            <h1>{ __wprm( 'Get the most out of WP Recipe Maker' ) }</h1>
            <p>
                { __wprm( 'This page helps you get results faster with guided setup tips, feature walkthroughs, and direct support resources.' ) }
            </p>
            <DripForm anchorId={ formAnchorId } />
            <h2>{ __wprm( 'Demo, Documentation & Support' ) }</h2>
            {
                showFeatureExplorer
                ?
                <p>
                    { __wprm( 'See how WP Recipe Maker features look and work together by browsing the demo site or using the built-in Feature Explorer, then jump to the docs for anything you want to enable next.' ) }
                </p>
                :
                <p>
                    { __wprm( 'See how WP Recipe Maker features look and work together by browsing the demo site, then jump to the docs for anything you want to enable next.' ) }
                </p>
            }
            <div className="wprm-faq-feature-explorer-buttons">
                {
                    showFeatureExplorer
                    &&
                    <a href={ wprm_faq.template_editor_feature_explorer_url } className="button button-primary">
                        { __wprm( 'Open Feature Explorer' ) }
                    </a>
                }
                <a href="https://demo.wprecipemaker.com" target="_blank" rel="noopener noreferrer" className="button">
                    { __wprm( 'Visit the demo site' ) }
                </a>
                <a href="https://help.bootstrapped.ventures/docs/wp-recipe-maker/" target="_blank" rel="noopener noreferrer" className="button">
                    { __wprm( 'Browse documentation' ) }
                </a>
            </div>
            <p>
                { __wprm( 'Need more help? Use the blue question mark in the bottom-right corner or email support@bootstrapped.ventures directly. We answer all tickets within 24 hours, usually much faster.' ) }
            </p>
            <h3>{ __wprm( 'Explainer Videos' ) }</h3>
            <p>
                { __wprm( 'Prefer video tutorials? Start with our introduction and then explore videos on specific topics.' ) } { ' ' }
                <a href="https://bootstrapped.ventures/wp-recipe-maker/videos/" target="_blank" rel="noopener noreferrer">{ __wprm( 'WP Recipe Maker Explainer Videos' ) }</a>.
            </p>
            <div className="wprm-faq-video-embed">
                <iframe
                    src="https://www.loom.com/embed/9f268e92cc064be9a45580a46fc84084"
                    title={ __wprm( 'Introduction to WP Recipe Maker video' ) }
                    allow="fullscreen"
                    allowFullScreen
                ></iframe>
            </div>
            <h2>{ __wprm( 'Frequently Asked Questions' ) }</h2>
            <p>{ __wprm( 'Click a section below to expand detailed guidance.' ) }</p>
            <h3>{ __wprm( 'Getting started with WP Recipe Maker' ) }</h3>
            <GettingStarted />
            <h3>{ __wprm( 'Adding recipes in different editors' ) }</h3>
            <Editors />
            <h3>{ __wprm( 'Advanced WPRM usage' ) }</h3>
            <Advanced />
            <h3>{ __wprm( 'Troubleshooting' ) }</h3>
            <Troubleshooting />
            <p>
                { __wprm( 'Need more? Visit the' ) } { ' ' }
                <a href="https://help.bootstrapped.ventures/collection/1-wp-recipe-maker" target="_blank" rel="noopener noreferrer">{ __wprm( 'WP Recipe Maker Knowledge Base' ) }</a>.
            </p>
            <SignupReminder />
        </div>
    );
}
export default Faq;
