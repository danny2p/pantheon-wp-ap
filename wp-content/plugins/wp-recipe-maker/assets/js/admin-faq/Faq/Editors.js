import React, { Fragment } from 'react';
import Accordion from '../General/Accordion';

const imgUrl = wprm_admin.wprm_url + 'assets/images/faq/creating/';

const Editors = (props) => {
    return (
        <Accordion
            items={[
                {
                    header: 'Gutenberg Block Editor (WordPress default)',
                    content: <Fragment>
                        <p>
                            This is the default editor for WordPress and the one we recommend. To add a recipe you <strong>add a WPRM Recipe block</strong> to the post content.
                        </p>
                        <img src={ imgUrl + 'gutenberg-block.png' } />
                        <p>
                            After adding a WPRM Recipe block you can click a button to <strong>create a new recipe or insert an existing one</strong>. The "Create new from Existing Recipe" button can be used to duplicate an existing recipe to start your new recipe from.
                        </p>
                        <img src={ imgUrl + 'gutenberg-block-buttons.png' } />
                        <p>
                            Clicking on a button will open up the recipe modal for you to fill in.
                        </p>
                    </Fragment>
                },{
                    header: 'Classic Editor',
                    content: <Fragment>
                        <p>
                            You won't get a nice preview of the recipe, but we still fully support the Classic Editor. To add a recipe, <strong>click on the WP Recipe Maker button or icon</strong> in the visual editor.
                        </p>
                        <img src={ imgUrl + 'classic-editor-buttons.png' } />
                        <p>
                            After clicking a modal will show up with all things that WP Recipe Maker can insert for you.
                            </p>
                        <img src={ imgUrl + 'classic-editor-modal.png' } />
                        <p>
                            Click the button to <strong>create a new recipe or insert an existing one</strong>. The "Create new from Existing Recipe" button can be used to duplicate an existing recipe to start your new recipe from.
                        </p>
                    </Fragment>
                },{
                    header: 'Elementor Page Builder',
                    content: <Fragment>
                        <p>
                            We integrate with the Elementor Page Builder so you can simply <strong>add a WPRM Recipe widget to your post</strong>.
                        </p>
                        <img src={ imgUrl + 'elementor-widget.png' } />
                        <p>
                            When you click on "Create or edit Recipe" it will take you to the <strong>WP Recipe Maker Manage page explained below</strong>, as recipes can't be created or edited in the Elementor interface.
                        </p>
                        <p>
                            Once you've create a recipe you can just <strong>search for its name</strong> to insert it.
                        </p>
                        <img src={ imgUrl + 'elementor-select-recipe.png' } />
                    </Fragment>
                },{
                    header: 'Other Page Builder',
                    content: <Fragment>
                        <p>
                            If you're using a page builder that we don't integrate with you can still use WP Recipe Maker. You'll <strong>create a recipe on the WP Recipe Maker Manage page explained below</strong>.
                        </p>
                        <p>
                            After creating a recipe you <strong>type the recipe shortcode</strong> where you want the recipe to appear.
                        </p>
                        <img src={ imgUrl + 'page-builder.png' } />
                    </Fragment>
                },{
                    header: 'WordPress.com Editor',
                    content: <Fragment>
                        <p>
                            If your interface <strong>looks like the classic editor but doesn't have the WP Recipe Maker button</strong> you might be using the WordPress.com interface.
                        </p>
                        <img src={ imgUrl + 'wordpress-com-interface.png' } />
                        <p>
                            One option you have is to <strong>type the recipe shortcode as shown under "Other Page Builder"</strong> above.
                        </p>
                        <p>
                            Or you could revert to the <strong>classic WP Admin interface</strong> through the menu link.
                        </p>
                        <img src={ imgUrl + 'wordpress-com-admin-link.png' } />
                        <p>
                            Once in the classic interface you can follow the <strong>Classic Editor</strong> instructions above.
                        </p>
                    </Fragment>
                }
            ]}
        />
    );
}
export default Editors;