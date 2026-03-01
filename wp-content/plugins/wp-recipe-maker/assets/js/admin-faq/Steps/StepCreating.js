import React from 'react';
import { __wprm } from 'Shared/Translations';
import Editors from '../Faq/Editors';

const imgUrl = wprm_admin.wprm_url + 'assets/images/faq/creating/';

const StepCreating = (props) => {
    return (
        <div className="wprm-admin-onboarding-step-creating">
            <p>
                { __wprm( 'An important thing about WP Recipe Maker is that' ) } <strong>{ __wprm( 'recipes do not exist on their own' ) }</strong>. { __wprm( 'You create a recipe and then' ) } <strong>{ __wprm( 'add it to a regular post' ) }</strong> { __wprm( 'on your website.' ) }
            </p>
            <p>
                { __wprm( 'The way to add a recipe to a post depends on the editor you use.' ) }
            </p>
            <h2>{ __wprm( 'What editor are you using?' ) }</h2>
            <p>{ __wprm( 'Click the editor you use on your website to get instructions on how to add a recipe.' ) }</p>
            <Editors />
            <h2>{ __wprm( 'Using the WP Recipe Maker Manage page' ) }</h2>
            <p>
                { __wprm( 'Whatever editor you are using, an easy way to' ) } <strong>{ __wprm( 'create, edit, and manage' ) }</strong> { __wprm( 'your recipes is through' ) } <em>{ __wprm( 'WP Recipe Maker > Manage' ) }</em> { __wprm( 'which will be available after these onboarding steps.' ) }
            </p>
            <p>
                { __wprm( 'On the Manage page, you will find an' ) } <strong>{ __wprm( 'overview of all recipes on your website' ) }</strong>.
            </p>
            <img src={ imgUrl + 'manage-overview.png' } alt={ __wprm( 'WP Recipe Maker Manage page overview' ) } />
            <p>
                { __wprm( 'There is a lot to explore on the Manage page, but for now focus on the' ) } <strong>{ __wprm( 'blue "Create Recipe" button' ) }</strong> { __wprm( 'in the top right. Clicking this creates a new recipe.' ) }
            </p>
            <p>
                { __wprm( 'It is worth repeating that' ) } <strong>{ __wprm( 'this new recipe will not be displayed anywhere automatically' ) }</strong>. { __wprm( 'It must be added to a post using one of the methods shown above. That post then becomes' ) } <strong>{ __wprm( 'the parent post for the recipe' ) }</strong>.
            </p>
            <p>
                { __wprm( 'Now that you know how to create recipes, it is time to have a look at templates.' ) }
            </p>
        </div>
    );
}
export default StepCreating;
