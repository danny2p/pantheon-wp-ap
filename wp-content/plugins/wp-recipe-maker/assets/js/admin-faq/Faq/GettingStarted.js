import React, { Fragment } from 'react';
import { __wprm } from 'Shared/Translations';
import Accordion from '../General/Accordion';

const imgUrl = wprm_admin.wprm_url + 'assets/images/faq/getting-started/';

const GettingStarted = (props) => {
    return (
        <Accordion
            items={[
                {
                    header: __wprm( 'Using WPRM in a different language (or multilingual site)' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'We follow WordPress standards to make sure all text in WP Recipe Maker can be translated to fit your needs. Learn more here:' ) }
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/128-translating-text-in-the-plugin" target="_blank" rel="noopener noreferrer">{ __wprm( 'Translating any text in WP Recipe Maker' ) }</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/132-how-to-use-this-for-a-multilingual-blog" target="_blank" rel="noopener noreferrer">{ __wprm( 'Using WPRM on a multilingual website' ) }</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: __wprm( 'Importing recipes from another plugin' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'Already have recipes on your website that were created in a different plugin? There is a good chance we can import them for you. If there are recipes we can import, you will find them on' ) } <em>{ __wprm( 'WP Recipe Maker > Import Recipes' ) }</em>.
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/69-importing-recipes-from-other-plugins" target="_blank" rel="noopener noreferrer">{ __wprm( 'All the plugins we can import from' ) }</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/86-custom-recipe-importer" target="_blank" rel="noopener noreferrer">{ __wprm( 'Develop your own recipe importer' ) }</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: __wprm( 'Adding recipes from Word, Google Docs, ...' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'If you already have your recipes in another document, filling in all the individual fields can be a bit tedious. Use our' ) } <strong>{ __wprm( 'import recipe from text feature' ) }</strong> { __wprm( 'to paste in that recipe entirely and speed up the process.' ) }
                        </p>
                        <p>
                            { __wprm( 'The field to paste in the recipe can be found after scrolling all the way up in the recipe modal:' ) }
                        </p>
                        <img src={ imgUrl + 'import-from-text.png' } alt={ __wprm( 'Import from text field in the recipe modal' ) } />
                        <p>
                            { __wprm( 'This will open a new modal where you can follow the steps to import.' ) }
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/70-import-recipe-from-text" target="_blank" rel="noopener noreferrer">{ __wprm( 'Learn more about the import recipe from text feature' ) }</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: __wprm( 'Recipe metadata and SEO' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'An important reason for using a recipe plugin is to have it' ) } <strong>{ __wprm( 'automatically add the recipe metadata that Google wants to see' ) }</strong>.
                        </p>
                        <p>
                            { __wprm( 'WP Recipe Maker can only add that metadata if you actually fill in all the relevant fields. To verify this, check the SEO column on' ) } <em>{ __wprm( 'WP Recipe Maker > Manage' ) }</em> { __wprm( 'and make sure you' ) } <strong>{ __wprm( 'get a green light there' ) }</strong>.
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/51-recipe-metadata-for-seo" target="_blank" rel="noopener noreferrer">{ __wprm( 'Learn more about Recipe Metadata for SEO' ) }</a></li>
                            <li><a href="https://help.bootstrapped.ventures/article/74-recipe-metadata-checker" target="_blank" rel="noopener noreferrer">{ __wprm( 'Using the Recipe Metadata Checker' ) }</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: __wprm( 'Using the Template Editor' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'Everyone is unique, so we want you to be able to' ) } <strong>{ __wprm( 'completely change the recipe template to your liking' ) }</strong>. { __wprm( 'Not everyone has the budget for a completely custom-coded template, so that is what the Template Editor is for.' ) }
                        </p>
                        <p>
                            { __wprm( 'With a bit of learning, most users can add or remove parts of the recipe card, change labels and colors, and add custom text. The Template Editor can be accessed through' ) } <em>{ __wprm( 'WP Recipe Maker > Settings' ) }</em>.
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/118-template-editor-101" target="_blank" rel="noopener noreferrer">{ __wprm( 'Go through the Template Editor 101 documentation first' ) }</a></li>
                            <li><a href="https://help.bootstrapped.ventures/category/25-template-editor-faq" target="_blank" rel="noopener noreferrer">{ __wprm( 'Learn more in these Template Editor FAQs' ) }</a></li>
                        </ul>
                    </Fragment>
                },{
                    header: __wprm( 'WPRM for recipe roundup posts' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'WP Recipe Maker can also be used for recipe roundup posts (for example "Easy Valentine\'s Day Menu" or "10 Scary Halloween Recipes"),' ) } <strong>{ __wprm( 'linking to both recipes on your own website and external websites' ) }</strong>.
                        </p>
                        <p>
                            { __wprm( 'A great reason to use WPRM for these posts is that it automatically includes the' ) } <strong>{ __wprm( 'ItemList metadata Google needs for carousel display' ) }</strong>.
                        </p>
                        <ul>
                            <li><a href="https://help.bootstrapped.ventures/article/182-itemlist-metadata-for-recipe-roundup-posts" target="_blank" rel="noopener noreferrer">{ __wprm( 'Learn about the recipe roundup feature' ) }</a></li>
                        </ul>
                    </Fragment>
                }
            ]}
        />
    );
}
export default GettingStarted;
