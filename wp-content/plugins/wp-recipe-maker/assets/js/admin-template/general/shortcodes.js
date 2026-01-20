import Elements from './elements';

// Shortcodes that include content.
const contentShortcodes = [
    'wprm-expandable',
    'wprm-condition',
];

// Shortcodes that still exist but should not get added to the "Add Blocks" section.
const ignoreShortcodes = [
    'wprm-recipe-my-emissions-label',
    'wprm-recipe-my-shopping-help',
];

// Helper function to normalize shortcode entries (handles both string IDs and objects)
const normalizeShortcode = (entry) => {
    if (typeof entry === 'string') {
        return { id: entry };
    }
    return entry;
};

// Helper function to get shortcode ID from entry (handles both string IDs and objects)
const getShortcodeId = (entry) => {
    return typeof entry === 'string' ? entry : entry.id;
};

// Shortcodes that should be marked as "new" in the Add Blocks view
const newShortcodes = [
    'wprm-recipe-add-products-to-cart',
    'wprm-recipe-cook-mode',
    'wprm-recipe-share-options-popup',
    'wprm-recipe-mastodon-share',
    'wprm-recipe-tumblr-share',
];

// Sort shortcodes for "Add Blocks" section.
// Each shortcode can be a string (ID) or an object with { id, description }
const shortcodeGroups = {
    layout: {
        group: 'Layout',
        description: 'Layout blocks help structure and organize your template with spacing and containers.',
        shortcodes: [
            { id: 'wprm-spacer', description: 'Add vertical spacing between blocks' },
            { id: 'wprm-expandable', description: 'Create a collapsible section that can be expanded/collapsed' },
            ...Elements.layoutElements.map(id => ({
                id,
                description: Elements.layoutElementDescriptions[id] || '',
            })),
            { id: 'wprm-recipe-meta-container', description: 'A container that displays multiple recipe fields (cooking times, servings, tags, ...) in different styles' },
        ],
    },
    general: {
        group: 'General',
        description: 'General purpose blocks for adding custom content, links, images, and other elements. These are fixed blocks that will look the same on all recipes and do not have any recipe-specific content.',
        shortcodes: [
            { id: 'wprm-text', description: 'Display custom text content' },
            { id: 'wprm-link', description: 'Add a clickable link' },
            { id: 'wprm-qr-code', name: 'QR Code', description: 'Display a QR code for the recipe' },
            { id: 'wprm-image', description: 'Display a custom image' },
            { id: 'wprm-recipe-jump-to-section', description: 'Add a link that jumps to a specific section' },
            { id: 'wprm-call-to-action', description: 'Display a call-to-action button or link' },
            { id: 'wprm-icon', description: 'Display an icon' },
        ],
    },
    recipe: {
        group: 'Recipe Fields',
        description: 'Display specific recipe information like name, ingredients, instructions, and metadata.',
        shortcodes: [
            { id: 'wprm-recipe-name', description: 'Display the recipe name/title' },
            { id: 'wprm-recipe-image', description: 'Display the recipe featured image' },
            { id: 'wprm-recipe-rating', description: 'Display the recipe rating (stars)' },
            { id: 'wprm-recipe-date', description: 'Display when the recipe was published' },
            { id: 'wprm-recipe-author', description: 'Display the recipe author name' },
            { id: 'wprm-recipe-author-bio', description: 'Display the recipe author biography' },
            { id: 'wprm-recipe-summary', description: 'Display the recipe summary/description' },
            { id: 'wprm-recipe-tag', description: 'Display recipe tags like course, cuisine, diet, ...' },
            { id: 'wprm-recipe-time', description: 'Display prep, cook, or total time' },
            { id: 'wprm-recipe-cost', description: 'Display the recipe cost' },
            { id: 'wprm-recipe-servings', description: 'Display the number of servings' },
            { id: 'wprm-recipe-servings-unit', description: 'Display the servings unit' },
            { id: 'wprm-recipe-equipment', description: 'Display required equipment' },
            { id: 'wprm-recipe-ingredients', description: 'Display the ingredients list' },
            { id: 'wprm-recipe-instructions', description: 'Display the cooking instructions' },
            { id: 'wprm-recipe-video', description: 'Display the recipe video' },
            { id: 'wprm-recipe-notes', description: 'Display recipe notes' },
            { id: 'wprm-nutrition-label', description: 'Display nutrition information label' },
            { id: 'wprm-recipe-nutrition', description: 'Display nutrition information' },
            { id: 'wprm-recipe-url', description: 'Display the recipe URL' },
            { id: 'wprm-recipe-custom-field', description: 'Display the value of one of your custom fields' },
        ],
    },
    roundup: {
        group: 'Recipe Roundup Fields',
        description: 'Blocks designed specifically for recipe roundup templates, used in roundup posts that list multiple recipes.',
        shortcodes: [
            { id: 'wprm-recipe-counter', description: 'Counter that displays the current recipe number in a roundup list' },
            { id: 'wprm-recipe-roundup-link', description: 'Link to the full recipe in a roundup' },
            { id: 'wprm-recipe-roundup-credit', description: 'Display the recipe credit as set for an external roundup recipe item' },
        ],
    },
    snippet: {
        group: 'Recipe Snippet Fields',
        description: 'Blocks designed specifically for recipe snippet templates, used for the snippet at the start of the post content that usually allow visitors to quickly jump to the recipe card.',
        shortcodes: [
            { id: 'wprm-recipe-jump', description: 'A button to jump to the recipe cart' },
            { id: 'wprm-recipe-jump-to-comments', description: 'A button to jump to the comments section' },
            { id: 'wprm-recipe-jump-video', description: 'A button to jump to the recipe video' },
        ],
    },
    interaction: {
        group: 'Recipe Interactions',
        description: 'Interactive elements that allow users to adjust servings, convert units, and more.',
        shortcodes: [
            { id: 'wprm-recipe-add-to-collection', description: 'Button to add recipe to the Recipe Collections feature' },
            { id: 'wprm-recipe-add-to-shopping-list', description: 'Button to add ingredients to the Quick Access Shopping List feature' },
            { id: 'wprm-recipe-add-products-to-cart', description: 'Button to add products to a shopping cart, when running an eCommerce store on your own site' },
            { id: 'wprm-recipe-adjustable-servings', description: 'Allow users to adjust serving size' },
            { id: 'wprm-recipe-advanced-adjustable-servings', description: 'Advanced serving size adjustment for baking' },
            { id: 'wprm-recipe-unit-conversion', description: 'Convert between different ingredient unit systems' },
            { id: 'wprm-recipe-media-toggle', description: 'Toggle to show or hide images and videos in the instructions' },
            { id: 'wprm-recipe-cook-mode', description: 'Open a cook mode popup modal with step-by-step instructions' },
            { id: 'wprm-prevent-sleep', description: 'Prevent device from sleeping while viewing recipe' },
            { id: 'wprm-recipe-print', description: 'Print button for the recipe' },
            { id: 'wprm-recipe-user-ratings-modal', description: 'Modal for users to rate the recipe' },
            { id: 'wprm-private-notes', description: 'Allow visitors to add their own private notes about the recipe' },
        ],
    },
    sharing: {
        group: 'Recipe Sharing',
        description: 'Social sharing buttons and options for users to share recipes.',
        shortcodes: [
            { id: 'wprm-recipe-share-options-popup', description: 'Popup that can show multiple different sharing options' },
            { id: 'wprm-recipe-email-share', name: 'Email', description: 'Share recipe via email' },
            { id: 'wprm-recipe-text-share', name: 'Text Message', description: 'Share recipe via text message' },
            { id: 'wprm-recipe-pin', name: 'Pinterest', description: 'Pin recipe to Pinterest' },
            { id: 'wprm-recipe-facebook-share', name: 'Facebook', description: 'Share recipe on Facebook' },
            { id: 'wprm-recipe-whatsapp-share', name: 'WhatsApp', description: 'Share recipe via WhatsApp' },
            { id: 'wprm-recipe-messenger-share', name: 'Messenger', description: 'Share recipe via Messenger' },
            { id: 'wprm-recipe-twitter-share', name: 'Twitter/X', description: 'Share recipe on Twitter/X' },
            { id: 'wprm-recipe-bluesky-share', name: 'Bluesky', description: 'Share recipe on Bluesky' },
            { id: 'wprm-recipe-mastodon-share', name: 'Mastodon', description: 'Share recipe on Mastodon' },
            { id: 'wprm-recipe-tumblr-share', name: 'Tumblr', description: 'Share recipe on Tumblr' },
        ],
    },
    integration: {
        group: 'Integrations',
        description: 'Third-party integrations and services for enhanced recipe functionality.',
        shortcodes: [
            { id: 'wprm-recipe-grow.me', name: 'Grow.me', description: 'A button to add the recipe to Grow.me' },
            { id: 'wprm-recipe-shop-instacart', name: 'Instacart', description: 'A button to shop ingredients on Instacart' },
            { id: 'wprm-recipe-emeals', name: 'eMeals', description: 'A button to shop ingredients on eMeals' },
            { id: 'wprm-recipe-chicory', name: 'Chicory', description: 'A button to shop ingredients on Chicory' },
            { id: 'wprm-recipe-slickstream-favorites', name: 'Slickstream', description: 'A button to add the recipe to Slickstream favorites' },
            { id: 'wprm-recipe-smart-with-food', name: 'Smart with Food', description: 'A button to shop ingredients through Smart with Food' },
            { id: 'wprm-hubbub-save-this', name: 'Hubbub', description: 'Display the Hubbub "Save This" form' },
        ],
    },
};

const generalShortcodeKeys = Object.values( shortcodeGroups ).flatMap( ( { shortcodes = [] } ) => 
    shortcodes.map( getShortcodeId )
);
const shortcodeKeysAlphebetically = Object.keys( wprm_admin_template.shortcodes ).sort();

for ( let shortcode of shortcodeKeysAlphebetically ) {
    if ( ! generalShortcodeKeys.includes( shortcode ) && ! ignoreShortcodes.includes( shortcode ) ) {
        shortcodeGroups.recipe.shortcodes.push( { id: shortcode } );
    }
}

export default {
    contentShortcodes,
    shortcodeGroups,
    shortcodeKeysAlphebetically,
    getShortcodeId,
    normalizeShortcode,
    newShortcodes,
};