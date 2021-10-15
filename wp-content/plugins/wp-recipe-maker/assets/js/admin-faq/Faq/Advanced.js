import React, { Fragment } from 'react';
import Accordion from '../General/Accordion';

const Advanced = (props) => {
    return (
        <Accordion
            items={[
                {
                    header: 'Earn affiliate income with ingredient and equipment links',
                    content: <Fragment>
                        {
                            ! wprm_admin.addons.premium
                            &&
                            <p style={{ color: 'darkred' }}>
                                This feature is available in <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium</a>.
                            </p>
                        }
                        <p>
                            Ingredient and equipment links are perfect for affiliate marketing: you set the link once and it will automatically get displayed whenever you use that ingredient/equipment in a recipe.
                        </p>
                        <p>
                            For equipment you even have the ability to add an image to increase the changes of having visitors click on the link.
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/29-ingredient-links" target="_blank">Learn about ingredient links</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/193-equipment-links" target="_blank">Learn about equipment links</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/203-equipment-images" target="_blank">Adding equipment images</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: 'Calculating and adding nutrition facts to your recipes',
                    content: <Fragment>
                        {
                            ! wprm_admin.addons.pro
                            &&
                            <p style={{ color: 'darkred' }}>
                                This feature is available in <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium - Pro Bundle</a>.
                            </p>
                        }
                        <p>
                            Provide your visitors with the complete recipe details by including a full nutrition label. With the Pro Bundle we can even <strong>help calculated these nutrition facts for you</strong>.
                        </p>
                        <p>
                            You have full control over the values that get displayed and can even <strong>create your own custom and calculated nutrients</strong>. This can be used for fields like Net Carbs, for example.
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/22-nutrition-label" target="_blank">Nutrition Label</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/21-nutrition-facts-calculation" target="_blank">Calculating Nutrition Facts</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/199-custom-and-calculated-nutrients" target="_blank">Custom and Calculated Nutrients</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: 'Reach an international audience with US and Metric units',
                    content: <Fragment>
                        {
                            ! wprm_admin.addons.pro
                            &&
                            <p style={{ color: 'darkred' }}>
                                This feature is available in <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium - Pro Bundle</a>.
                            </p>
                        }
                        <p>
                            Some of your readers might have a hard time making your recipes because they simply don't use the units you write them in. Not everyone is familiar with cups or grams, for example.
                        </p>
                        <p>
                            Our unit conversion allows you to <strong>offer both unit system</strong> to your visitors and have them switch back and forth. To get these values we integrate with an API that helps calculate them for you!
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/18-unit-conversion" target="_blank">Setting up the Unit Conversion feature</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: 'Set up Meal Planning with the recipe collections feature',
                    content: <Fragment>
                        {
                            ! wprm_admin.addons.elite
                            &&
                            <p style={{ color: 'darkred' }}>
                                This feature is available in <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium - Elite Bundle</a>.
                            </p>
                        }
                        <p>
                            Recipe Collections allow your visitors to <strong>save the recipes on your website in their own collections and then generate a shopping list</strong> for them. Can be used for collecting favorites, meal planning and much more!
                        </p>
                        <p>
                            As the site owner you can also <strong>create your own saved collections to present to your users</strong>. This can include as many recipes (and individual ingredients) as you want and you can even total the nutrition facts for those recipes.
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/148-recipe-collections" target="_blank">Learn more about Recipe Collections</a></li>
                            <li><a href="https://demo.wprecipemaker.com/saved-recipe-collection/" target="_blank">See a Saved Recipe Collection in action</a></li>
                        </ul>
                    </Fragment>
                }
            ]}
        />
    );
}
export default Advanced;