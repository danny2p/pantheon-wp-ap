import React from 'react';
import Editors from '../Faq/Editors';

const imgUrl = wprm_admin.wprm_url + 'assets/images/faq/creating/';

const StepCreating = (props) => {
    return (
        <div className="wprm-admin-onboarding-step-creating">
            <p>
                An important thing about WP Recipe Maker is that <strong>recipes do not exist on their own</strong>. You create a recipe and then <strong>add it to a regular post</strong> on your website.
            </p>
            <p>
                The way to add a recipe to a post depends on the editor you're using.
            </p>
            <h2>What editor are you using?</h2>
            <p>Click on the editor you use on your website to get instructions on how to add a recipe.</p>
            <Editors />
            <h2>Using the WP Recipe Maker Manage page</h2>
            <p>
                Whatever editor you're using, an easy way to <strong>create, edit, and manage</strong> your recipes is through the <em>WP Recipe Maker > Manage</em> page that will be available after going through these onboarding steps.
            </p>
            <p>
                On the Manage page you will find an <strong>overview of all the recipes on your website</strong>.
            </p>
            <img src={ imgUrl + 'manage-overview.png' } />
            <p>
                There is a LOT to explore on the manage page, but for now just focus on the <strong>blue "Create Recipe" button</strong> in the top right. Simply clicking this will create a new recipe for you.
            </p>
            <p>
                It's worth repeating that <strong>this new recipe won't get displayed anywhere automatically</strong>. It has to be added to a post using one of the methods shown above. This will then become <strong>the parent post for the recipe</strong>, the place on your website where the recipe is displayed.
            </p>
            <p>
                Now that you know how to create recipes it's time to have a look at them!
            </p>
        </div>
    );
}
export default StepCreating;