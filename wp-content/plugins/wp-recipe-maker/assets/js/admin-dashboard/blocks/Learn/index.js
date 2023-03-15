import React, { Fragment } from 'react';

import '../../../../css/admin/dashboard/learn.scss';

import { __wprm } from 'Shared/Translations';

import Block from '../../layout/Block';

import Section from './Section';
import Item from './Item';

const Learn = (props) => {
    return (
        <Block
            title={ __wprm( 'Get the most out of WPRM' ) }
        >
            <div className="wprm-admin-dashboard-learn-container">
                <Section
                    title={ `🧑‍🎓 ${ __wprm( 'Learn More' ) }` }
                >
                    <Item url="https://help.bootstrapped.ventures/collection/1-wp-recipe-maker">{ __wprm( 'Documentation in our knowledge base' ) }</Item>
                    <Item url="https://demo.wprecipemaker.com/all-features/">{ __wprm( 'All features in action on our demo site' ) }</Item>
                    <Item url="https://bootstrapped.ventures/wp-recipe-maker/videos/">{ __wprm( 'Video tutorials and walkthroughs' ) }</Item>
                </Section>
                <Section
                    title={ `🤝 ${ __wprm( 'Our Partners' ) }` }
                >
                    <Item url="https://help.bootstrapped.ventures/article/323-shop-with-instacart-button">{ __wprm( 'Shoppable Recipes with Instacart' ) }</Item>
                </Section>
                <Section
                    title={ `🙋 ${ __wprm( 'Get Help' ) }` }
                >
                    <Item url="https://www.facebook.com/groups/1741126932849712">{ __wprm( 'WP Recipe Maker Facebook Group' ) }</Item>
                    <Item url="mailto:support@bootstrapped.ventures">{ __wprm( 'Email our support team' ) }</Item>
                </Section>
            </div>
        </Block>
    );
}
export default Learn;