import React, { Fragment } from 'react';
import Accordion from '../General/Accordion';

const imgUrl = wprm_admin.wprm_url + 'assets/images/faq/getting-started/';

const GettingStarted = (props) => {
    return (
        <Accordion
            items={[
                {
                    header: 'Using WPRM in a different language (or multilingual site)',
                    content: <Fragment>
                        <p>
                            We follow WordPress standards to make sure all text in WP Recipe Maker can be translated to fit your needs. Learn more here:
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/128-translating-text-in-the-plugin" target="_blank">Translating any text in WP Recipe Maker</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/132-how-to-use-this-for-a-multilingual-blog" target="_blank">Using WPRM on a multilingual website</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: 'Importing recipes from another plugin',
                    content: <Fragment>
                        <p>
                            Already have recipes on your website that were created in a different plugin? There's a good chance we can import them for you! If there are recipes we can import, you will find them on the <em>WP Recipe Maker > Import Recipes</em>.
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/69-importing-recipes-from-other-plugins" target="_blank">All the plugins we can import from</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/86-custom-recipe-importer" target="_blank">Develop your own recipe importer</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: 'Adding recipes from Word, Google Docs, ...',
                    content: <Fragment>
                        <p>
                            If you already have your recipes in another document, filling in all the individual fields can be a bit tedious. Use our <strong>import recipe from text feature</strong> to paste in that recipe entirely and speed up the process.
                        </p>
                        <p>
                            The field to paste in the recipe can be found after scrolling up all the way in the recipe modal:
                        </p>
                        <img src={ imgUrl + 'import-from-text.png' } />
                        <p>
                            This will open up a new modal where you can follow the steps to import.
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/70-import-recipe-from-text" target="_blank">Learn more about the import recipe from text feature</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: 'Recipe metadata and SEO',
                    content: <Fragment>
                        <p>
                            An important reason for using a recipe plugin is to have it <strong>automatically add the recipe metadata that Google wants to see</strong>. 
                        </p>
                        <p>
                            But WP Recipe Maker can only add that metadata if you actually fill in all the fields. To find out if you've done that, have a look at the SEO column on the <em>WP Recipe Maker > Manage</em> page and make sure you <strong>get a green light there</strong>.
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/51-recipe-metadata-for-seo" target="_blank">Learn more about Recipe Metadata for SEO</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/74-recipe-metadata-checker" target="_blank">Using the Recipe Metadata Checker</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: 'Using the Template Editor',
                    content: <Fragment>
                        <p>
                            Everyone is unique so we want you to be able to <strong>completely change the recipe template to your liking</strong>. Not everyone will have the budget for a completely custom-coded template though, so that's what we built the Template Editor for!
                        </p>
                        <p>
                            With a little bit of a learning curve everyone should be able to add or remove specific parts of the recipe box, change labels and colors or add custom text. The Template Editor can be accessed through the <em>WP Recipe Maker > Settings</em> page.
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/118-template-editor-101" target="_blank">Go through the Template Editor 101 documentation first</a></li>
                            <li><a href="https://help.bootstrapped.ventures/category/25-template-editor-faq" target="_blank">Learn more in these Template Editor FAQs</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: 'WPRM for recipe roundup posts',
                    content: <Fragment>
                        <p>
                            WP Recipe Maker can also be used for recipe roundup posts (think "Easy Valentine's Day Menu" or "10 Scary Halloween Recipes"), <strong>linking to both recipes on your own website and others</strong>.
                        </p>
                        <p>
                            A good reason for using WPRM for these kind of posts is that we'll automatically include the <strong>ItemList metadata that Google needs to display your recipes in a Carousel</strong>. That should definitely get you some extra visits!
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/182-itemlist-metadata-for-recipe-roundup-posts" target="_blank">Learn about using recipe roundup feature</a></li>
                        </ul>
                    </Fragment>
                }
            ]}
        />
    );
}
export default GettingStarted;