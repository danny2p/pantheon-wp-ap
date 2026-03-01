import React, { Fragment } from 'react';
import { __wprm } from 'Shared/Translations';
import Accordion from '../General/Accordion';

const imgUrl = wprm_admin.wprm_url + 'assets/images/faq/creating/';

const Editors = (props) => {
    return (
        <Accordion
            items={[
                {
                    header: __wprm( 'Gutenberg Block Editor (WordPress default)' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'This is the default editor for WordPress and the one we recommend. To add a recipe, you' ) } <strong>{ __wprm( 'add a WPRM Recipe block' ) }</strong> { __wprm( 'to the post content.' ) }
                        </p>
                        <img src={ imgUrl + 'gutenberg-block.png' } alt={ __wprm( 'WPRM block in the Gutenberg block inserter' ) } />
                        <p>
                            { __wprm( 'After adding a WPRM Recipe block, you can click a button to' ) } <strong>{ __wprm( 'create a new recipe or insert an existing one' ) }</strong>. { __wprm( 'The "Create new from Existing Recipe" button can duplicate an existing recipe to use as a starting point.' ) }
                        </p>
                        <img src={ imgUrl + 'gutenberg-block-buttons.png' } alt={ __wprm( 'Buttons to create or insert recipes in the Gutenberg block' ) } />
                        <p>
                            { __wprm( 'Clicking a button opens the recipe modal for you to fill in.' ) }
                        </p>
                    </Fragment>
                },{
                    header: __wprm( 'Classic Editor' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'You will not get a live recipe preview, but we still fully support the Classic Editor. To add a recipe,' ) } <strong>{ __wprm( 'click the WP Recipe Maker button or icon' ) }</strong> { __wprm( 'in the visual editor.' ) }
                        </p>
                        <img src={ imgUrl + 'classic-editor-buttons.png' } alt={ __wprm( 'WP Recipe Maker button in the Classic Editor toolbar' ) } />
                        <p>
                            { __wprm( 'After clicking, a modal appears with everything WP Recipe Maker can insert for you.' ) }
                            </p>
                        <img src={ imgUrl + 'classic-editor-modal.png' } alt={ __wprm( 'Classic Editor WP Recipe Maker modal' ) } />
                        <p>
                            { __wprm( 'Click the button to' ) } <strong>{ __wprm( 'create a new recipe or insert an existing one' ) }</strong>. { __wprm( 'The "Create new from Existing Recipe" button can duplicate an existing recipe to use as a starting point.' ) }
                        </p>
                    </Fragment>
                },{
                    header: __wprm( 'Elementor Page Builder' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'We integrate with Elementor so you can simply' ) } <strong>{ __wprm( 'add a WPRM Recipe widget to your post' ) }</strong>.
                        </p>
                        <img src={ imgUrl + 'elementor-widget.png' } alt={ __wprm( 'WPRM recipe widget in Elementor' ) } />
                        <p>
                            { __wprm( 'When you click "Create or edit Recipe," it takes you to the' ) } <strong>{ __wprm( 'WP Recipe Maker Manage page' ) }</strong> { __wprm( 'because recipes cannot be created or edited directly in the Elementor interface.' ) }
                        </p>
                        <p>
                            { __wprm( 'Once you have created a recipe, you can' ) } <strong>{ __wprm( 'search for its name' ) }</strong> { __wprm( 'to insert it.' ) }
                        </p>
                        <img src={ imgUrl + 'elementor-select-recipe.png' } alt={ __wprm( 'Selecting a recipe in Elementor by searching recipe name' ) } />
                    </Fragment>
                },{
                    header: __wprm( 'Divi Page Builder' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'We integrate with Divi so you can simply' ) } <strong>{ __wprm( 'add a WPRM Recipe module to your post' ) }</strong>.
                        </p>
                        <img src={ imgUrl + 'divi-insert.png' } alt={ __wprm( 'WPRM recipe module in Divi builder' ) } />
                        <p>
                            { __wprm( 'Set the' ) } <strong>{ __wprm( 'recipe ID' ) }</strong> { __wprm( 'to display the recipe.' ) }
                        </p>
                        <img src={ imgUrl + 'divi-module.png' } alt={ __wprm( 'Divi module settings showing recipe ID field' ) } />
                    </Fragment>
                },{
                    header: __wprm( 'Other Page Builder' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'If you use a page builder that we do not integrate with, you can still use WP Recipe Maker. You will' ) } <strong>{ __wprm( 'create a recipe on the WP Recipe Maker Manage page' ) }</strong>.
                        </p>
                        <p>
                            { __wprm( 'After creating a recipe, you' ) } <strong>{ __wprm( 'type the recipe shortcode' ) }</strong> { __wprm( 'where you want the recipe to appear.' ) }
                        </p>
                        <img src={ imgUrl + 'page-builder.png' } alt={ __wprm( 'Recipe shortcode inserted in a page builder content area' ) } />
                    </Fragment>
                },{
                    header: __wprm( 'WordPress.com Editor' ),
                    content: <Fragment>
                        <p>
                            { __wprm( 'If your interface' ) } <strong>{ __wprm( 'looks like the classic editor but does not have the WP Recipe Maker button' ) }</strong> { __wprm( 'you might be using the WordPress.com interface.' ) }
                        </p>
                        <img src={ imgUrl + 'wordpress-com-interface.png' } alt={ __wprm( 'WordPress.com editor interface without WPRM button' ) } />
                        <p>
                            { __wprm( 'One option is to' ) } <strong>{ __wprm( 'type the recipe shortcode as shown under "Other Page Builder"' ) }</strong>.
                        </p>
                        <p>
                            { __wprm( 'Or you can switch back to the' ) } <strong>{ __wprm( 'classic WP Admin interface' ) }</strong> { __wprm( 'through the menu link.' ) }
                        </p>
                        <img src={ imgUrl + 'wordpress-com-admin-link.png' } alt={ __wprm( 'Menu link to switch back to classic WP Admin' ) } />
                        <p>
                            { __wprm( 'Once in the classic interface, follow the' ) } <strong>{ __wprm( 'Classic Editor' ) }</strong> { __wprm( 'instructions above.' ) }
                        </p>
                    </Fragment>
                }
            ]}
        />
    );
}
export default Editors;
