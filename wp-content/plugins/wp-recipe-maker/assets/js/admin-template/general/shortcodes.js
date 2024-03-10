import Elements from './elements';

// Shortcodes that include content.
const contentShortcodes = [
    'wprm-expandable',
];

// Shortcodes that still exist but should not get added to the "Add Blocks" section.
const ignoreShortcodes = [
    'wprm-recipe-chicory',
    'wprm-recipe-my-emissions-label',
];

// Sort shortcodes for "Add Blocks" section.
const shortcodeGroups = {
    layout: {
        group: 'Layout',
        shortcodes: [
            'wprm-spacer',
            'wprm-expandable',
            ...Elements.layoutElements,
        ],
    },
    general: {
        group: 'General',
        shortcodes: [
            'wprm-text',
            'wprm-link',
            'wprm-image',
            'wprm-call-to-action',
            'wprm-icon',
            'wprm-prevent-sleep',
        ],
    },
    recipe: {
        group: 'Recipe Fields',
        shortcodes: [
            'wprm-recipe-name',
            'wprm-recipe-image',
            'wprm-recipe-rating',
            'wprm-recipe-date',
            'wprm-recipe-author',
            'wprm-recipe-summary',
            'wprm-recipe-meta-container',
            'wprm-recipe-tag',
            'wprm-recipe-time',
            'wprm-recipe-cost',
            'wprm-recipe-servings',
            'wprm-recipe-servings-unit',
            'wprm-recipe-equipment',
            'wprm-recipe-ingredients',
            'wprm-recipe-instructions',
            'wprm-recipe-video',
            'wprm-recipe-notes',
            'wprm-nutrition-label',
            'wprm-recipe-nutrition',
            'wprm-recipe-url',
            'wprm-recipe-custom-field',
        ],
    },
    roundup: {
        group: 'Recipe Roundup Fields',
        shortcodes: [
            'wprm-recipe-counter',
            'wprm-recipe-roundup-link',
            'wprm-recipe-roundup-credit',
        ],
    },
    snippet: {
        group: 'Recipe Snippet Fields',
        shortcodes: [
            'wprm-recipe-jump',
            'wprm-recipe-jump-to-comments',
            'wprm-recipe-jump-video',
        ],
    },
    interaction: {
        group: 'Recipe Interactions',
        shortcodes: [
            'wprm-recipe-add-to-collection',
            'wprm-recipe-add-to-shopping-list',
            'wprm-recipe-adjustable-servings',
            'wprm-recipe-advanced-adjustable-servings',
            'wprm-recipe-unit-conversion',
            'wprm-recipe-media-toggle',
            'wprm-recipe-print',
            'wprm-private-notes',
        ],
    },
    sharing: {
        group: 'Recipe Sharing',
        shortcodes: [
            'wprm-recipe-pin',
            'wprm-recipe-email-share',
            'wprm-recipe-facebook-share',
            'wprm-recipe-twitter-share',
            'wprm-recipe-text-share',
        ],
    },
    integration: {
        group: 'Integrations',
        shortcodes: [
            'wprm-recipe-emeals',
            'wprm-recipe-grow.me',
            'wprm-recipe-shop-instacart',
            'wprm-recipe-slickstream-favorites',
            'wprm-recipe-smart-with-food',
        ],
    },
};

const generalShortcodeKeys = Object.values( shortcodeGroups ).flatMap( ( { shortcodes = [] } ) => shortcodes );
const shortcodeKeysAlphebetically = Object.keys( wprm_admin_template.shortcodes ).sort();

for ( let shortcode of shortcodeKeysAlphebetically ) {
    if ( ! generalShortcodeKeys.includes( shortcode ) && ! ignoreShortcodes.includes( shortcode ) ) {
        shortcodeGroups.recipe.shortcodes.push( shortcode );
    }
}

export default {
    contentShortcodes,
    shortcodeGroups,
    shortcodeKeysAlphebetically,
};