import React from 'react';

import Advanced from './Advanced';
import DripForm from './DripForm';
import Editors from './Editors';
import GettingStarted from './GettingStarted';

const Faq = (props) => {
    return (
        <div id="wprm-admin-faq-container">
            <h1>Get the most out of WP Recipe Maker</h1>
            <DripForm />
            <h1>Explainer Videos</h1>
            <p>
                Are you a visual learner? Make sure to check out the <a href="https://bootstrapped.ventures/wp-recipe-maker/videos/" target="_blank">WP Recipe Maker Explainer Videos</a> we have on several topics. "Introduction to WP Recipe Maker" is a good one to start with:
            </p>
            <iframe width="640" height="433" src="https://www.loom.com/embed/9f268e92cc064be9a45580a46fc84084" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
            <h1>Documentation & Support</h1>
            <p>
                We've listed some frequently asked questions below. If you need more help we recommend checking out the <a href="https://help.bootstrapped.ventures/collection/1-wp-recipe-maker" target="_blank">WP Recipe Maker Knowledge Base</a> and the <a href="https://demo.wprecipemaker.com" target="_blank">WPRM Demo Site</a> that shows all features in action.
            </p>
            <p>
                If you have any other questions or suggestions at all, <strong>contact us using the blue question mark in the bottom right</strong> of this page or by emailing <a href="mailto:support@bootstrapped.ventures">support@bootstrapped.ventures</a> directly. We answer all tickets within 24 hours, and usually a lot faster.
            </p>
            <h1>Frequently Asked Questions</h1>
            <p>Just click on the different sections to learn more!</p>
            <h2>Getting started with WP Recipe Maker</h2>
            <GettingStarted />
            <h2>Adding recipes in different editors</h2>
            <Editors />
            <h2>Advanced WPRM Usage</h2>
            <Advanced />
            <p>
                Need more? Go to the <a href="https://help.bootstrapped.ventures/collection/1-wp-recipe-maker" target="_blank">WP Recipe Maker Knowledge Base</a>.
            </p>
        </div>
    );
}
export default Faq;