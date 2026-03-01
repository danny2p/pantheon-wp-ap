import React, { Fragment } from 'react';
import { __wprm } from 'Shared/Translations';
import Accordion from '../General/Accordion';

const Troubleshooting = () => {
    return (
        <Accordion
            items={[
                {
                    header: __wprm( 'My recipe is not visible on the post' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'Recipes need to be inserted into post content (or via shortcode/module in your page builder). Creating a recipe in Manage does not display it automatically.' ) }
                        </p>
                        <p>
                            { __wprm( 'If the recipe still does not appear, clear your cache (plugin/server/CDN) and verify that the post actually contains the recipe block or shortcode.' ) }
                        </p>
                    </Fragment>,
                },{
                    header: __wprm( 'The SEO/metadata indicator is not green' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'Use the SEO column on the Manage page to spot missing metadata fields. Most issues are caused by incomplete required recipe fields.' ) }
                        </p>
                        <p>
                            <a href="https://help.bootstrapped.ventures/article/74-recipe-metadata-checker" target="_blank" rel="noopener noreferrer">
                                { __wprm( 'Open the Recipe Metadata Checker guide' ) }
                            </a>
                        </p>
                    </Fragment>,
                },{
                    header: __wprm( 'Editor and insertion options do not match what I see in the docs' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'WP Recipe Maker supports different workflows for Block Editor, Classic Editor, Elementor, Divi, and shortcode-based builders. Make sure you are following the instructions for your exact editor.' ) }
                        </p>
                        <p>
                            { __wprm( 'If needed, open the Feature Explorer and demo site to compare expected output with your setup.' ) }
                        </p>
                    </Fragment>,
                },{
                    header: __wprm( 'Cache or optimization plugins cause stale output' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'After template or settings changes, clear all caches: page cache, object cache, CDN cache, and any optimization plugin cache (including minified JS/CSS bundles).' ) }
                        </p>
                        <p>
                            { __wprm( 'If behavior differs between logged-in and logged-out users, this is often a cache layer issue.' ) }
                        </p>
                    </Fragment>,
                },{
                    header: __wprm( 'How to contact support effectively' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'Use the blue question mark in the bottom-right corner or email support@bootstrapped.ventures.' ) }
                        </p>
                        <p>
                            { __wprm( 'To speed things up, include your site URL, screenshots, exact steps to reproduce, and links to affected posts.' ) }
                        </p>
                    </Fragment>,
                },
            ]}
        />
    );
}
export default Troubleshooting;
