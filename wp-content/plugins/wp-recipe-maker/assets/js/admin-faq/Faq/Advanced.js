import React, { Fragment } from 'react';
import { __wprm } from 'Shared/Translations';
import Accordion from '../General/Accordion';

const Advanced = () => {
    return (
        <Accordion
            items={[
                {
                    header: __wprm( 'Increase engagement with interactive recipe actions' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'WP Recipe Maker supports several engagement-focused features such as ratings, print buttons, share options, and save-to-collection actions.' ) }
                        </p>
                        <p>
                            { __wprm( 'A quick way to discover these opportunities is the Feature Explorer in the Template Editor.' ) }
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/docs/wp-recipe-maker/user-ratings/" target="_blank" rel="noopener noreferrer">{ __wprm( 'User Ratings' ) }</a></li>
                            <li><a href="https://help.bootstrapped.ventures/docs/wp-recipe-maker/print-recipes/" target="_blank" rel="noopener noreferrer">{ __wprm( 'Print Recipes' ) }</a></li>
                            <li><a href="https://help.bootstrapped.ventures/docs/wp-recipe-maker/share-options-popup/" target="_blank" rel="noopener noreferrer">{ __wprm( 'Share Options Popup' ) }</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: __wprm( 'Earn affiliate income with ingredient and equipment links' ),
                    content: <Fragment>
                        {
                            ! wprm_admin.addons.premium
                            &&
                            <p style={{ color: 'darkred' }}>
                                { __wprm( 'This feature is available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank" rel="noopener noreferrer">{ __wprm( 'WP Recipe Maker Premium' ) }</a>.
                            </p>
                        }
                        <p>
                            { __wprm( 'Ingredient and equipment links are perfect for affiliate marketing: set the link once and it will automatically be displayed whenever you use that ingredient or equipment item in a recipe.' ) }
                        </p>
                        <p>
                            { __wprm( 'For equipment, you can also add an image to improve click-through rates.' ) }
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/29-ingredient-links" target="_blank" rel="noopener noreferrer">{ __wprm( 'Learn about ingredient links' ) }</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/193-equipment-links" target="_blank" rel="noopener noreferrer">{ __wprm( 'Learn about equipment links' ) }</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/203-equipment-images" target="_blank" rel="noopener noreferrer">{ __wprm( 'Adding equipment images' ) }</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: __wprm( 'Calculating and adding nutrition facts to your recipes' ),
                    content: <Fragment>
                        {
                            ! wprm_admin.addons.pro
                            &&
                            <p style={{ color: 'darkred' }}>
                                { __wprm( 'This feature is available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank" rel="noopener noreferrer">{ __wprm( 'WP Recipe Maker Premium - Pro Bundle' ) }</a>.
                            </p>
                        }
                        <p>
                            { __wprm( 'Provide visitors with complete recipe details by including a full nutrition label. With the Pro Bundle, we can even' ) } <strong>{ __wprm( 'help calculate these nutrition facts for you' ) }</strong>.
                        </p>
                        <p>
                            { __wprm( 'You have full control over displayed values and can even' ) } <strong>{ __wprm( 'create custom and calculated nutrients' ) }</strong> { __wprm( 'for fields like Net Carbs.' ) }
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/22-nutrition-label" target="_blank" rel="noopener noreferrer">{ __wprm( 'Nutrition Label' ) }</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/21-nutrition-facts-calculation" target="_blank" rel="noopener noreferrer">{ __wprm( 'Calculating Nutrition Facts' ) }</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/199-custom-and-calculated-nutrients" target="_blank" rel="noopener noreferrer">{ __wprm( 'Custom and Calculated Nutrients' ) }</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: __wprm( 'Reach an international audience with US and Metric units' ),
                    content: <Fragment>
                        {
                            ! wprm_admin.addons.pro
                            &&
                            <p style={{ color: 'darkred' }}>
                                { __wprm( 'This feature is available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank" rel="noopener noreferrer">{ __wprm( 'WP Recipe Maker Premium - Pro Bundle' ) }</a>.
                            </p>
                        }
                        <p>
                            { __wprm( 'Some visitors might struggle with recipes because they do not use the units you write in. Not everyone is familiar with cups or grams, for example.' ) }
                        </p>
                        <p>
                            { __wprm( 'Unit conversion allows you to' ) } <strong>{ __wprm( 'offer both unit systems' ) }</strong> { __wprm( 'and let visitors switch between them. We integrate with an API that helps calculate conversion values.' ) }
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/18-unit-conversion" target="_blank" rel="noopener noreferrer">{ __wprm( 'Setting up the Unit Conversion feature' ) }</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: __wprm( 'Set up meal planning with Recipe Collections' ),
                    content: <Fragment>
                        {
                            ! wprm_admin.addons.elite
                            &&
                            <p style={{ color: 'darkred' }}>
                                { __wprm( 'This feature is available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank" rel="noopener noreferrer">{ __wprm( 'WP Recipe Maker Premium - Elite Bundle' ) }</a>.
                            </p>
                        }
                        <p>
                            { __wprm( 'Recipe Collections allow visitors to' ) } <strong>{ __wprm( 'save recipes on your site in their own collections and generate shopping lists' ) }</strong>. { __wprm( 'This can be used for favorites, meal planning, and more.' ) }
                        </p>
                        <p>
                            { __wprm( 'As the site owner, you can also' ) } <strong>{ __wprm( 'create your own saved collections to present to users' ) }</strong>. { __wprm( 'These can include recipes and individual ingredients, and you can even total nutrition facts across recipes.' ) }
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/148-recipe-collections" target="_blank" rel="noopener noreferrer">{ __wprm( 'Learn more about Recipe Collections' ) }</a></li>
                            <li><a href="https://demo.wprecipemaker.com/saved-recipe-collection/" target="_blank" rel="noopener noreferrer">{ __wprm( 'See a Saved Recipe Collection in action' ) }</a></li>
                        </ul>
                    </Fragment>
                }
            ]}
        />
    );
}
export default Advanced;
