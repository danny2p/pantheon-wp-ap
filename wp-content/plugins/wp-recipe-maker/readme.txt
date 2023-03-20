=== WP Recipe Maker ===
Contributors: BrechtVds, BirtheVdm
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QG7KZMGFU325Y
Tags: recipe, recipes, ingredients, food, cooking, seo, schema.org, json-ld
Requires at least: 4.4
Tested up to: 6.2
Requires PHP: 5.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The easy and user-friendly recipe plugin for everyone. Automatic JSON-LD metadata for food AND how-to recipes will improve your SEO!

== Description ==

[WP Recipe Maker](https://bootstrapped.ventures/wp-recipe-maker/) is the easy recipe plugin that everyone can use. An easy workflow allows you to add recipes to any post or page with automatic JSON-LD metadata for your recipes. This metadata will improve your SEO and get you more visitors!

https://www.youtube.com/watch?v=lbt01DL03DU

Would you like to see the plugin in action before installing it? We have a [WP Recipe Maker demo website](https://demo.wprecipemaker.com) showcasing all of the features! 

> <strong>Get the most out of this plugin!</strong><br>
> Join the [WP Recipe Maker Email Course](https://www.getdrip.com/forms/86388969/submissions/new) and we'll help you get started and learn all the tips and trick for using WPRM.

= Features =
An overview of WP Recipe Maker features:

*   Compatible with both the Classic Editor and new **Gutenberg** editor
*   Includes an **Elementor block** and shortcode can be used in other page builders
*   **Easy workflow** to add recipes to any post or page
*   Uses schema.org/Recipe JSON-LD metadata optimised for **Google Recipe search**
*   Uses schema.org/How-to JSON-LD metadata optimised for **non-food recipes and instructions**
*   Supports both regular and **Guided Recipes** for Google metadata
*   Shoppable ingredients with the **Shop with Instacart** button
*   Google **AMP** compatible
*   Integrates recipe metadata with **Yoast SEO and Rank Math schema graph**
*   Option to **disable metadata per recipe** if you want to publish non-food or DIY recipes
*   Compatible with **Pinterest Rich Pins** and a setting to easily opt out
*   Outputs ItemList metadata for **Recipe Roundup** posts
*   **Associate ingredients with instructions** to have them show up exactly where needed
*   **Keyboard accessible and AMP compatible ratings** for comments
*   Interactive **print recipe** page with room for ads and optional credit to your website
*   **Fallback recipe** shows up when the plugin is disabled
*   Include a **recipe video** in the template and metadata
*   Add **photos or videos** to any step of the recipe
*   Print recipe and **jump to recipe** shortcodes
*   Temperature shortcode with **oven symbols**
*   This plugin is **fully responsive**, your recipes will look good on any device
*   Easily change the look and feel to fit your website in the **Template Editor**
*   Structure your ingredients and instructions in **groups** (e.g. icing and cake batter)
*   **Full text search** for your recipes
*   Access your recipes through the WordPress **REST API**
*   Built-in **SEO check** for your recipe metadata
*   Compatible with **RTL** languages
*   **Import your recipes** from other plugins (see below)

= WP Recipe Maker Premium =

Looking for some more advanced functionality? We also have the [WP Recipe Maker Premium](https://bootstrapped.ventures/wp-recipe-maker/) add-on available with the following features:

*   Use **ingredient links** for linking to products or other recipes
*   **Adjustable servings** make it easy for your visitors
*   Display all nutrition data in a **nutrition label**
*   **User Ratings** allow visitors to vote without commenting
*   Add a mobile-friendly **kitchen timer** to your recipes
*   More **Premium templates** for a unique recipe template
*   Create custom **recipe taxonomies** like price level, difficulty, ...
*   Use **checkboxes** for your ingredients and instructions
*   Import recipes from the **Paprika app**

Even more add-ons can add the following functionality:

*   Integration with a **Nutrition API** for automatic nutrition facts
*   **Unit Conversion** to reach an international audience with a different unit system
*   Have your users send in recipes through the **Recipe Submission** form
*   Give your visitors the power of **Recipe Collections** for favourites, meal planning and more

= Import Options =

Currently using another recipe plugin? No problem! You can easily migrate all your existing recipes to WP Recipe Maker if you're using any of the following plugins:

*   Tasty Recipes
*   Create by Mediavine
*   EasyRecipe
*   WP Ultimate Recipe
*   Recipe Card Blocks by WPZOOM
*   Meal Planner Pro
*   BigOven
*   ZipList and Zip Recipes
*   Yummly
*   Yumprint Recipe Card
*   FoodiePress
*   Cooked
*   Cookbook
*   Simple Recipe Pro
*   Purr Recipe Plugin
*   Recipes by Simmer
*   WordPress.com shortcode
*   JSON-LD HTML Script
*   Recipes Generator (JSON-LD embed code only)
*   Multi Rating (ratings only)
*   (Need anything else? Just ask!)

This plugin is in active development. Feel free to contact us with any feature requests or ideas.

== Installation ==

1. Upload the `wp-recipe-maker` directory (directory included) to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add recipes using the "WP Recipe Maker" button when editing posts or pages

== Frequently asked questions ==

= Where can I find a demo and some more documentation? =
Check out the [WP Recipe Maker sales site](https://bootstrapped.ventures/wp-recipe-maker/), [demo website](https://demo.wprecipemaker.com) and [WPRM documentation](https://help.bootstrapped.ventures/collection/1-wp-recipe-maker) for more information on all of the features!

= What's the difference with WP Ultimate Recipe? =

[WP Ultimate Recipe](http://www.wpultimaterecipe.com/) is the popular recipe plugin that we released in 2013 and have been working on ever since. This gave us a great idea of what most food bloggers are looking for.

Why the new plugin? A few structural choices we made early on have caused WP Ultimate Recipe to be quite complex and not 100% compatible with all themes. With WP Recipe Maker we're building the perfect recipe plugin from scratch, without all the baggage of years of development.

WP Ultimate Recipe is still in active development and will be maintained alongside this new alternative.

= Do you offer any support? =

Yes! We pride ourselves on offering awesome support and almost always answer support questions within 24 hours. Send us an email at [support@bootstrapped.ventures](mailto:support@bootstrapped.ventures) whenever you have a question or suggestion!

== Screenshots ==

1. Example recipe using the default Chic template.
2. Our Template Editor allows you to completely customize the look and feel.
3. A powerful manage pages gives you full control over your recipes.
4. The recipe input form.
5. Fully compatible with the Gutenberg Block Editor.
6. Fully compatible with the Classic Editor.

== Changelog ==
= 8.7.5 =
* Fix: Only affect Yoast metadata on recipe pages
* Fix: Prevent PHP warning with Rank Math integration

= 8.7.4 =
* Improvement: Better method for detecting presence of Rank Math plugin
* Fix: Prevent rare PHP error in some specific themes

= 8.7.3 =
* Fix: Metadata not showing up correctly with some combinations of SEO plugins

= 8.7.2 =
* Fix: PHP error when rounding numbers in some cases

= 8.7.1 =
* Fix: Transparant stars setting off by default

= 8.7.0 =
* Feature: Inline ingredients in instruction steps
* Feature: Integrate with the Rank Math JSON-LD Schema Graph
* Feature: Different size options when printing
* Feature: Quick access for ℉ and ℃ symbols (better for accessibility)
* Feature: Setting to hide recipes on the manage page that the current user cannot edit
* Feature: Easily change size and padding of stars in template editor
* Improvement: Do not show edit icon on manage page if a user is not allowed to edit
* Improvement: Import equipment from WP Tasty
* Improvement: Integration with the SEO schema graph for Yoast 20+
* Improvement: Subsections for properties in the template editor
* Improvement: Initialize features when dynamically loading recipes in WP Ultimate Post Grid
* Improvement: Better interpretation of ranges used in ingredient quantities
* Fix: FlyingPress 4 compatibility
* Fix: Comment rating star color in Firefox
* Fix: Leftover quotes when using HTML in shortcode text

= 8.6.2 =
* Improvement: Allow some HTML tags in shortcode output while still being secure

= 8.6.1 =
* Feature: Author schema integration with Yoast SEO
* Improvement: FlyingPress compatibility for comment ratings
* Fix: Sanitizing shortcode input correctly
* Fix: Deprecation warning because of add_submenu_page

= 8.6.0 =
* Feature: Add Recipe Roundup Item widgets in Elementor
* Improvement: Match exact ID when searching for recipes
* Improvement: Setting to only display roundup items for published recipes
* Improvement: Better consistency for print URLs
* Fix: Creating template with some special characters breaking the CSS styling
* Fix: URL in metadata for some Mediavine videos not working
* Fix: Ignore caption when putting snippets after first image
* Fix: Better matching for range keyword during import
* Fix: Use correct text-domain for block text translations

= 8.5.0 =
* Feature: Tastemakers program for the Instacart Shoppable Recipe button
* Feature: Grouped ingredients style
* Feature: Recipe URL block for the template editor
* Improvement: Ability to set custom class for recipe shortcodes
* Improvement: Setting to force Mediavine video metadata output
* Improvement: Import method and diet from Tasty Recipes
* Improvement: Same style options for equipment notes as for ingredient notes
* Improvement: Better support for oEmbed video metadata
* Fix: Prevent import issue with negative time values
* Fix: Prevent PHP warning when video embed code is empty
* Fix: Support for m4v recipe videos
* Fix: Problem deleting templates with a + sign in the slug

= 8.4.0 =
* Feature: Temperature shortcode with oven symbols
* Improvement: Prevent same image from getting imported multiple times
* Improvement: Add aria-label if no text has been set for a button
* Fix: Use decimal character from settings for average rating

= 8.3.0 =
* Feature: Shop Ingredients with Instacart button
* Improvement: Quick insert icon for group names
* Improvement: Ability to password protect when using recipes as a public post type
* Improvement: Automatic alt tag for external roundup images when not saved locally
* Improvement: New shopping cart icons for the template editor
* Fix: Snippets showing up for password protected posts
* Fix: Template editor names with non-latin characters
* Fix: Smooth scroll in Jump to Comments button
* Fix: Nutrition fields not instantly updating when using comma as decimal character
* Fix: Gutenberg deprecations
* Fix: Elementor deprecation warning
* Fix: Make sure shortcodes work inside of template conditions

= 8.2.0 =
* Feature: Condition shortcode for Template Editor
* Feature: Search list of recipes to import when importing from MV Create or Tasty Recipes
* Improvement: Support video metadata when using youtube-nocookie.com in embed code
* Improvement: Setting to split instructions on punctuation when importing from text
* Fix: Prevent rating caching issues
* Fix: Yoast Video SEO compatibility
* Fix: Flatsome theme UX Builder breaking
* Fix: Don't change ID when importing from JSON in the edit modal
* Fix: Same author for every recipe image setting
* Fix: Don't use wprm- prefix in the slug when saving as a public post type
* Fix: Action filter on the Analytics manage page
* Fix: Check for keywords in SEO checker
* Fix: Searching and displaying special characters in ingredient suggestions
* Fix: SEO Checker not updating after the first rating
* Fix: Prevent Elementor deprecated notice
* Fix: Russian translation errors in PHP 8.0
* Fix: Broken HTML in Pin button

= 8.1.1 =
* Fix: Fatal error related to settings page

= 8.1.0 =
* Feature: Ability to manually set post author
* Feature: Bulk edit post author on manage page
* Feature: Set author image when using "Same author for every recipe" setting
* Feature: Specify different print template to use in print button shortcode
* Feature: Toggle notes on print page
* Improvement: Setting to choose pin method for button
* Improvement: Edit recipe button in block editor sidebar for easy access
* Improvement: Placeholder for modified date
* Improvement: Allow placeholders in link, text and jump to comments blocks
* Improvement: Prevent recipes from getting cached in REST API
* Improvement: Recognize metadata for YouTube shorts embed links
* Fix: Make sure SEO metadata checker updates after receiving a rating
* Fix: Spacing of Grow.me button on mobile in Chic template
* Fix: Toggle buttons border issue for RTL languages
* Fix: Allow dash in taxonomy archive slug
* Fix: Rich text "HTML & Code" field for multi-line HTML
* Fix: Prevent PHP notice on print page
* Fix: Prevent deprecation notice in WordPress 5.9

= 8.0.2 =
* Fix: PHP notice in instructions

= 8.0.1 =
* Fix: Not showing space between associated ingredients
* Fix: Not replacing Tasty Recipes correctly in backend during "Find Parents”
* Fix: A few missing translations

= 8.0.0 =
* Feature: Health Check section on the dashboard page
* Feature: Setting for automatic ItemList metadata for archive pages
* Feature: Allow sorting and filtering by SEO on manage page
* Feature: Rich text editor for the roundup item summary
* Feature: Set custom name and summary for internal roundup item
* Feature: Set custom button text for roundup item link
* Feature: Import Recipes Generator embed code
* Improvement: Further rate limiting of ingredient and equipment suggestions to prevent errors
* Improvement: Setting to use HTML code for print credit
* Improvement: Show warning in block editor preview if recipe block has an incorrect ID
* Improvement: Recognizing JSON-LD HTML for import
* Improvement: Better metadata for video clip IDs
* Improvement: Ability to dismiss marketing notice on dashboard page
* Improvement: Faster loading of manage page with 10,000+ recipes
* Improvement: Ability to remove fixed filter on the manage page
* Improvement: WP Extended Search compatibility when using WP Ultimate Post Grid
* Fix: Prevent recipes from taking over custom post statusses
* Fix: Problem with + in URL of external roundup links
* Fix: Have separator inside span for ingredients associated with instructions
* Fix: Prevent huge stars on comment overview page
* Fix: Delete comment rating when associated comment gets permanently deleted
* Fix: Bulk deleting comment ratings not removing stars from comment
* Fix: PHP notice on comment overview page
* Fix: Prevent error message in Visual Composer editor
* Fix: Not always replacing Tasty Recipes block after importing
* Fix: Elementor widget icon

= 7.7.2 =
* Improvement: Allow changing of post author when having recipes as a public post type
* Improvement: Change preview width in the template editor preview
* Fix: Recipe block not working in older versions of WordPress
* Fix: Make sure editing multiple times through the dashboard page loads the latest version

= 7.7.1 =
* Fix: Suggestions in toolbar at bottom of modal not clickable

= 7.7.0 =
* Feature: Set WPML language when using recipes as a public post type
* Improvement: Smaller comment ratings column
* Improvement: Only show quick edit comment rating for posts with recipes
* Improvement: Bulk delete ingredient units on the manage page
* Fix: Revisioned and scheduled posts losing parent post with Yoast Duplicate Post
* Fix: Import from Create not always replacing block in post correctly
* Fix: Comment rating compatibility with Lazy Load for Comments on non-recipe posts
* Fix: Print URL when using index.php permalinks
* Fix: Deprecation notice in block editor
* Fix: Print page redirect when using non-standard HTTP port
* Fix: Prevent cursor location problem in Firefox when clicking in rich text field

= 7.6.1 =
* Fix: Prevent PHP warning on manage page
* Fix: Broken manage page in some cases with PHP 8
* Fix: Change recipe post status to published when parent post is scheduled

= 7.6.0 =
* Feature: Set sponsored attribute when adding links
* Feature: Show and edit ingredient units on the manage page
* Feature: Order by and quick edit comment ratings on comments overview page
* Improvement: Allow full control over ratings again
* Improvement: Date block for the template editor
* Improvement: Include spaces when filtering on the manage page
* Improvement: Move cursor to end after TAB when editing
* Fix: TAB key problem in Safari when editing
* Fix: Prevent associated ingredients disappearing after dragging around
* Fix: Remove parent post association when saving post
* Fix: Display comment ratings in correct order for RTL languages
* Fix: Buttons on print page for RTL languages

= 7.5.1 =
* Fix: Extra spacing above stars because of half star feature
* Fix: Half stars for RTL languages
* Fix: Convert Create reviews tool not working in some cases
* Fix: Remove instruction image shortcodes during import from Create

= 7.5.0 =
* Feature: Third and half filled stars to better represent the rating
* Feature: Custom hash for the jump to video button
* Feature: Import MV Create Reviews to comments with ratings
* Feature: Enable link for counter block in the template editor
* Feature: Show number of comments in jump to comments block
* Improvement: Allow comments for public recipe post type
* Fix: Prevent HTML from showing up in associated ingredients

= 7.4.1 =
* Fix: Prevent ingredient or instruction dragging issues in some edge cases
* Fix: Allow different star rating colors on the same page

= 7.4.0 =
* Feature: Associate ingredients with specific instruction steps
* Feature: Dashboard page with latest news
* Feature: Import from Recipe Card Blocks by WPZOOM
* Feature: Import ratings from Multi Rating plugin
* Improvement: Prevent font size too small on mobile for meta unit text
* Improvement: Remove analytics actions after x days
* Improvement: Compatibility with Tasty Pins hidden images for automatic snippet placement
* Improvement: Aria label for better roundup link accessibility
* Fix: Prevent comment rating manipulation
* Fix: Prevent Yoast Duplicate Post rewrite feature from breaking parent post
* Fix: Custom hash not working in all themes/page builders
* Fix: PHP notice after Ziplist import
* Fix: Prevent issues with custom SVG icons and some PHP configurations
* Fix: Template Editor dropdown showing below image

= 7.3.0 =
* Feature: Create and edit recipes directly in Elementor
* Feature: Set custom hash for the jump to recipe link
* Feature: Import ratings from WPSSO
* Feature: Setting to add snippets after first div container
* Feature: General image block for the template editor
* Feature: Ability to add your own custom SVG icons for the Template Editor
* Improvement: Compatibility with Grow Social Pro hidden images for automatic snippet placement
* Improvement: Use shortcodes inside of roundup item summary
* Improvement: Optional header for text block in Template Editor
* Improvement: Alignment options for text block in Template Editor
* Improvement: Some additional icons for the Template Editor
* Improvement: Elementor Custom Skin compatibility for our shortcodes
* Improvement: Import video embed from Tasty Recipes
* Improvement: Import diet from MediaVine Create
* Improvement: Import inactive time from Cookbook
* Improvement: Converting blocks when importing from Zip Recipes
* Improvement: Allow unfiltered HTML for users with that capability
* Improvement: Make sure other plugins see post type as public when enabled
* Improvement: Setting for public post type permalink priority
* Improvement: Display diet terms correctly in WP Ultimate Post Grid
* Improvement: Print page redirect when coming from other sites
* Fix: Prevent image border problem with some optimization plugins
* Fix: Prevent image displaying at size 1 by 1 pixels using some image formats
* Fix: Metadata URL problem with video parts in some specific cases
* Fix: Breaking Yoast readability score with snippet block in block editor
* Fix: Prevent error during MPP import
* Fix: Prevent Polylang/WPML related error on the manage page

= 7.2.0 =
* Feature: Change text shown when there are 0, 1 or more votes
* Feature: Optionally automatically replace fractions in amounts with their symbol
* Feature: Setting to have snippet show after first paragraph or first image
* Feature: Yoast Video SEO sitemap compatibility
* Feature: Show recipes as menu item options when post type is public
* Feature: Show taxonomies as menu item options when archive is enabled
* Feature: Optionally enable classic WordPress UI for any taxonomy with archive page
* Improvement: Import recommended products when using the Mediavine Create import
* Improvement: URL property in clip metadata for Mediavine videos
* Fix: Roundup link button for demo recipe in template editor
* Fix: Use correct template on single recipe pages when using public post type
* Fix: Metadata output when using public post type
* Fix: Show Slickstream button in template editor
* Fix: Filter issue when clicking through on number of recipes on manage page
* Fix: Prevent text share issue if another plugin uses Mobile_Detect 
* Fix: Prevent translations from breaking import page

= 7.1.2 =
* Improvement: Mark custom template editor icons as decorative for accessibility
* Improvement: Better recognition of ingredients when importing from Tasty
* Improvement: Reduce JS size by reducing support for legacy browsers
* Fix: Show values when 0 for nutrition facts setting not applying
* Fix: Behaviour of fixed filter on manage page
* Fix: Compatibility issues saving post meta in Gutenberg

= 7.1.1 =
* Fix: External roundup item links

= 7.1.0 =
* Feature: Optionally make the recipe a public post type
* Feature: Enable automatic archive pages for taxonomies
* Feature: Enable links to automatic archive pages
* Feature: Change term slug on manage page
* Feature: Setting to default recipe name to post title
* Feature: Setting to not include ingredient notes in metadata
* Feature: Smooth scroll option for Jump to Comments
* Feature: Use placeholders in template editor headers
* Improvement: Tags and equipment in fallback recipe
* Improvement: More quick access fractions in modal toolbar
* Improvement: View and edit icons for the parent post on the manage page
* Improvement: Include plural in ingredient suggestions
* Improvement: Hide Slickstream button if their JS isn't loaded
* Improvement: Better look of ingredient table layout style on mobile
* Improvement: Better ZipList import
* Improvement: Respect Do Not Track header for optional analytics feature
* Improvement: Ability to exclude IPs from optional analytics feature
* Improvement: Apply "Force Video Rating" setting to separate video shortcode as well
* Fix: Remove onclick from comment rating input on AMP pages
* Fix: Delete from trash when using plain permalinks
* Fix: Problem with 2-column table layout for ingredients
* Fix: Import from JSON in modal not rendering all fields correctly
* Fix: Import of a few nutrients from WPURP incorrect
* Fix: Checkbox alignment for RTL languages in some browsers

= 7.0.0 =
* Feature: Slickstream Favorites integration
* Feature: My Emissions Carbon Label integration
* Feature: New Blend-in Template
* Feature: Table style layout option for ingredients
* Feature: Twitter share button
* Feature: Allow different ingredient order (names before amounts)
* Feature: Click on number of recipes to filter on manage page
* Improvement: Allow decimals for serving size value
* Improvement: Setting to open print links in same tab
* Improvement: Column on manage page to easily copy shortcode
* Fix: Fast Velocity Minify HTML Minification compatibility
* Fix: Prevent lazy image loading problems on print page
* Fix: Some issues with importing instruction images
* Fix: Printing problem with some multilingual setups
* Fix: Prevent duplicate ID when using wpDiscuz

= 6.9.0 =
* Feature: Count block for recipe roundup items template
* Feature: Grow.me button integration
* Feature: Setting to redirect when accessing print page directly
* Feature: Recipe Part block for displaying individual parts in Gutenberg Block Editor
* Feature: Setting to load styling in head to prevent CLS issues
* Feature: Show and filter by parent post language on manage page for WPML and Polylang
* Fix: Special characters in Text Share feature
* Fix: Youtube video duration missing metadata warning

= 6.8.0 =
* Feature: Thrive Comments compatibility for comment ratings
* Improvement: Reduce admin JavaScript file sizes
* Fix: Servings problem when bulk printing recipes through the manage page

= 6.7.0 =
* Feature: Share recipe by email button
* Feature: Optional "Force video ratio" setting
* Improvement: Pin Image column on the Manage page
* Improvement: Bulk remove recipes from the trash
* Improvement: Only load assets on admin screens where needed
* Improvement: Use noopener for share buttons
* Fix: Prevent WPML (and others) from breaking our REST URL endpoints
* Fix: Import recipe notes from Purr

= 6.6.0 =
* Feature: Share by text button on mobile
* Feature: Facebook Share button for template editor
* Feature: Export and import for the Template Editor
* Improvement: Reduce JavaScript file sizes
* Improvement: More styling options for link block in template editor
* Improvement: Import video from Zip Recipes
* Improvement: Import comment ratings from Zip Recipes
* Improvement: Bulk delete for recipe revisions on manage page
* Improvement: Show video on manage page
* Improvement: Show summary on the manage page
* Fix: Prevent double ingredient when copying from checkboxes
* Fix: Instruction image toggle on print pages when using Legacy template mode
* Fix: Deleting revisions through the manage page
* Fix: Wishlist Member compatibility problem

= 6.5.1 =
* Fix: Prevent screen reader text issue for ingredients in some themes

= 6.5.0 =
* Feature: Optional analytics on plugin usage
* Feature: Bulk edit servings through the manage page
* Improvement: Better "Finding Parents" tool
* Fix: Problem with approving recipe ratings in WordPress 5.5
* Fix: Some taxonomy sorting and filtering not working on the Manage page
* Fix: Prevent PHP notice on print page

= 6.4.0 =
* Feature: Copy recipe to clipboard when saving times out
* Feature: Bulk edit option to recalculate total time
* Feature: WP Ultimate Post Grid taxonomy grid links and images integration
* Improvement: Ignore print permission setting for administrators
* Fix: Empty print page in Safari
* Fix: Number formatting with thousands
* Fix: Prevent PHP notices in WordPress 5.5

= 6.3.0 =
* Feature: Setting to change starting state of Instruction Media Toggle
* Feature: Prevent printing of membership-restricted recipes
* Improvement: Accessibility warnings for comment ratings form
* Improvement: Friendly link display in recipe print footer
* Improvement: Show "approved" column on ratings manage page
* Fix: Update comment ratings when bulk editing comments
* Fix: Comment rating display in backend
* Fix: Make sure star SVG IDs are unique
* Fix: Link to direct Youtube timestamp for youtu.be URLs
* Fix: Prevent theme styling from breaking comment ratings
* Fix: Bookly plugin compatibility
* Fix: Manage page not refreshing after deleting a rating

= 6.2.2 =
* Fix: Prevent some rich text errors when using Grammarly

= 6.2.1 =
* Fix: Problem with comment ratings showing up on pages without a recipe

= 6.2.0 =
* Feature: Keyboard accessible comment ratings
* Feature: Setting to autoplay or loop uploaded videos
* Feature: Block editor "Align" option for Recipe and Roundup Block
* Improvement: Accessibility of Instruction Media Toggle buttons
* Improvement: Better default template color contrast for accessibility
* Improvement: Better recognize ingredient and instruction group headers during import
* Improvement: Better compatibility with revision plugins
* Improvement: Setting to change size of comment rating stars
* Fix: Print credit should not be on its own page when setting enabled
* Fix: Show header actions on same line when line decoration is not selected

= 6.1.2 =
* Fix: Prevent "Headers already sent" error on print page

= 6.1.1 =
* Fix: Prevent PHP warning

= 6.1.0 =
* Feature: Set "name" metadata for instruction steps
* Feature: Interactive print page with room for ads
* Feature: Email Link button on print page
* Feature: Bulk print recipes from the Manage page
* Feature: Print icon on the Manage page
* Improvement: Ability to change print slug
* Improvement: Prevent "Invalid string length" metadata warning
* Improvement: Use post author in metadata if custom author is not set
* Improvement: Setting to open internal roundup links in a new tab
* Improvement: Permissions setting for FAQ & Support page
* Fix: PHP Notice when using instruction videos

= 6.0.4 =
* Improvement: Metadata for new "Guided Recipes" in Google Search Console

= 6.0.3 =
* Fix: Prevent incorrect automatic updates from causing a fatal error
* Fix: Prevent meta container from overflowing on mobile

= 6.0.2 =
* Fix: Prevent PHP notice when using deprecated shortcode without attributes

= 6.0.1 =
* Fix: Prevent PHP notices

= 6.0.0 =
* Feature: New "Chic" recipe template
* Feature: Toggle for instruction images and videos
* Feature: New Meta Container block for the Template Editor
* Feature: Icon block for Template Editor with new icons
* Feature: Tool to refresh video metadata
* Improvement: Ability to set own YouTube Data API key
* Improvement: Adjust comment rating scroll when using lazy loading
* Improvement: Add diameter icon to toolbar
* Improvement: Removed jQuery dependency for frontend
* Improvement: Rich Editor browser compatibility
* Fix: Some existing roundup blocks broken in block editor
* Fix: Toolbar suggestions not being saved correctly in some cases
* Fix: Problem with code in rich editor disappearing
* Fix: Prevent images from overflowing in Block Editor preview
* Fix: Hustle plugin compatibility
* Fix: Prevent JS regex warning in Firefox
* Fix: Make sure instruction video gets output in all cases

= 5.11.1 =
* Fix: Prevent PHP notice from showing up

= 5.11.0 =
* Feature: Embed or upload instruction videos with metadata
* Feature: Load information from external site in roundup item
* Feature: Integrate Easy Affiliate Links in rich text editor
* Feature: Setting to remove links on print page
* Feature: Set default roundup template per recipe type
* Improvement: Ability to use external site image in roundup item
* Improvement: Instruction images in HowToStep metadata
* Improvement: Direct link to instruction step in food recipe metadata
* Improvement: Add heart icons to the Template Editor
* Fix: Compatibility with Newsletter plugin

= 5.10.5 =
* Improvement: Alert instead of break when a rich text field can't be loaded
* Fix: Yoast SEO compatibility when using WPRM blocks
* Fix: Spaces after ingredient suggestion breaking editor

= 5.10.4 =
* Improvement: Better compatibility for legacy browsers
* Fix: Non-breaking spaces in import from text

= 5.10.3 =
* Fix: Problem when converting integer to string

= 5.10.2 =
* Fix: Instructions issue with import from text feature
* Fix: Better Microsoft Edge compatibility for rich text editor

= 5.10.1 =
* Fix: Prevent rich text editor from breaking when there are empty HTML elements

= 5.10.0 =
* Feature: General text and link block for the Template Editor
* Improvement: Better support for non-latin characters in the rich editor fields
* Improvement: Better styling on AMP pages
* Improvement: Early refresh embedded video metadata if incomplete
* Improvement: Change smooth scroll duration
* Improvement: Better compatbility for non-recipe shortcodes in the Template Editor
* Improvement: Remove recipe from Yoast SEO readibility in block editor
* Improvement: Better automatic excerpt for recipes
* Improvement: Allow clicking in block editor preview
* Fix: Compatibility with new Mediavine video embed for metadata
* Fix: Ignore leftover revisions when assigning parent post
* Fix: Prevent PHP error during nutrition calculation
* Fix: Make sure recipes in pending posts show up on manage page
* Fix: Prevent double text style fading in template editor
* Fix: Encoded HTML in fallback recipe
* Fix: Use RSS Feed template when using Classic Editor
* Fix: Yoast SEO integration if there are non-metadata recipes before

= 5.9.0 =
* Feature: Ability to add suitableForDiet metadata
* Feature: Full compatibility with the WP Ultimate Post Grid template editor
* Improvement: Tool to remove ratings associated to deleted comments
* Fix: Make sure to remove comment rating when spammed
* Fix: Breaking WP personal data exporter

= 5.8.4 =
* Improvement: WP Ultimate Post Grid 3.0.0 compatibility
* Improvement: Import ratings from Cooked Pro
* Improvement: Render all shortcodes in Template Editor
* Fix: Cookbook import with more than 1 recipe on a page
* Fix: Divi classic builder compatibility
* Fix: Prevent broken taxonomy from breaking manage page
* Fix: Prevent WPML PHP warning
* Fix: Color of comment stars in form after selecting
* Fix: WPRM Recipe block in Elementor

= 5.8.3 =
* Improvement: Setting to add extra Mediavine ad unit in the recipe card
* Improvement: Better IE11 compatibility
* Fix: Prevent deprecated notice for video embed in WordPress 5.3
* Fix: Demo recipe setting not correct

= 5.8.2 =
* Fix: Don't accidentally enable Jetpack Photon feature

= 5.8.1 =
* Improvement: Warning if recipe image is too small
* Improvement: Block editor previews
* Improvement: Opt-out of rich pins for recipe pages only
* Improvement: Prevent Jetpack Photon feature for images in metadata
* Fix: Icons in new Twenty Twenty theme
* Fix: Match multiple digits in list for Import from Text feature

= 5.8.0 =
* Feature: Onboarding for new users
* Feature: Optional smooth scroll for jump buttons
* Feature: Set different snippet templates per recipe type
* Feature: Include aggregateRating for how-to instructions
* Feature: Importer for JSON-LD HTML script
* Improvement: Add servings column to manage page
* Improvement: Change rating average rounding decimals
* Improvement: Simplified settings page
* Improvement: Filter hook to add your own importer directory
* Improvement: Better FAQ page
* Fix: Thumbnail sizes not loading in Template Editor in some themes
* Fix: Divi 4 Builder compatibility problem

= 5.7.0 =
* Feature: hasParts metadata for how-to instruction videos
* Feature: Add after icon for ingredients/instructions
* Feature: Video metadata when using Brid plugin
* Improvement: Prevent MV video from showing metadata twice
* Improvement: Print template in multilingual plugins
* Improvement: Better import for EasyRecipe ingredients
* Improvement: Rich text toolbar on mobile
* Fix: Double paste in rich text fields in Chrome
* Fix: Recipe block loads in Gutenberg for all custom post types
* Fix: Prevent Avada theme from lazy loading images on print page
* Fix: Use correct template when accessing posts through REST API

= 5.6.1 =
* Fix: Pasting text in rich text fields in Firefox

= 5.6.0 =
* Feature: Setting to not output ItemList metadata when there is already recipe metadata
* Feature: Disable pinning of external roundup item images
* Improvement: Import courses and cuisines from ziplist
* Improvement: Use oEmbed for Mediavine video metadata
* Fix: thumbnailUrl issue in video metadata for how-to instructions
* Fix: Compatibility with Lazy Load for Comments plugin
* Fix: Problem with parentheses in import from text
* Fix: Correct template styles for Elementor block
* Fix: Don't output automatic snippets for the excerpt

= 5.5.3 =
* Fix: Make sure comment rating stars only show up when there is a recipe in the post
* Fix: Jetpack warning showing up on settings page incorrectly

= 5.5.2 =
* Fix: Rich text field issues in Firefox

= 5.5.1 =
* Fix: Make sure JS gets localized in correct order

= 5.5.0 =
* Feature: Option to open external roundup link in same tab
* Feature: Unit separator option for nutrition blocks
* Improvement: Only load WPRM assets when needed
* Improvement: Alt tag for comment star rating
* Improvement: Use summary in custom pin description
* Fix: Issue when saving numbers as taxonomy terms
* Fix: Elementor Recipe Block not saving correctly
* Fix: Prevent HTML issue from breaking Template Editor preview
* Fix: Safari 10 compatibility

= 5.4.3 =
* Fix: Make sure recipe metadata shows up in all situations
* Fix: BPS Security plugin compatibility

= 5.4.2 =
* Improvement: Open name and image links in new tab for external roundup items
* Fix: Nutrition Shortcode not showing unit
* Fix: Make sure comment ratings for trashed comments don't show

= 5.4.1 =
* Improvement: Check if Yoast graph is using article metadata or not
* Fix: Prevent scripts from loading on AMP pages

= 5.4.0 =
* Feature: Import for Create by Mediavine
* Feature: Revision Manager TMC compatibility
* Improvement: Yoast SEO graph compatibility
* Improvement: Make sure contentUrl is set in oEmbed video metadata
* Improvement: Show equipment in classic editor preview
* Fix: Prevent wpautop bug from breaking recipe layout
* Fix: Incorrectly thinking changes were made in some cases

= 5.3.3 =
* Fix: Problem with Find Ratings tool

= 5.3.2 =
* Fix: Shortcode in block problem not resolving

= 5.3.1 =
* Fix: Shortcode problem

= 5.3.0 =
* Feature: Show number of ratings with shortcode
* Improvement: Parent Post ID on the ratings manage page
* Improvement: More fraction symbols in rich editor
* Fix: TAB key problem in MS Edge
* Fix: How-to Instructions metadata problem with unnamed sections
* Fix: Don't count ratings of unapproved comments
* Fix: Make sure snippet template CSS gets loaded when not adding automatically
* Fix: Double recipe metadata on AMP pages when using Yoast SEO
* Fix: Paragraph spacing issues when using alignment
* Fix: Prevent PHP notice in admin
* Fix: Divi adding extra bracket to shortcode output
* Fix: Bulk Edit terms dropdown not changing

= 5.2.1 =
* Feature: Set different template per recipe type
* Improvement: Make sure metadata gets output but only once
* Improvement: Speed up comment ratings query
* Improvement: Menu for the manage page
* Improvement: Equipment in import from text feature
* Improvement: Range parsing for ingredient amounts
* Improvement: Setting to change clickable image size
* Improvement: Option to show instruction image before the text
* Fix: Settings not showing up correctly in some cases
* Fix: Bloom plugin compatibility

= 5.2.0 =
* Feature: Use external links for Roundup Items
* Feature: Output How-to metadata for DIY and craft recipes
* Feature: Estimated cost field
* Feature: Recipe equipment section
* Feature: View trash on manage page
* Improvement: Edit roundup items in Gutenberg editor
* Improvement: Clean up HTML in import from text feature
* Improvement: Cache ingredient suggestions in modal
* Improvement: Use global ingredient links as default when switching to custom
* Improvement: Don't shrink image in Snippet Summary template
* Improvement: Working WPURP shortcode for converted recipes after import
* Fix: Make sure REST API always gets latest version
* Fix: Time shortcode selection in Template Editor
* Fix: Yoast metadata integration for the "other" recipe type

= 5.1.1 =
* Fix: Elementor PHP warning

= 5.1.0 =
* Feature: Integration with Yoast SEO 11 schema graph for the recipe metadata
* Feature: "WPRM Recipe" block in Elementor page builder
* Feature: Show and bulk edit recipe type on manage page
* Improvement: Add HTML or shortcode in rich text fields
* Improvement: Allow browser autocomplete for input fields
* Improvement: Try to prevent REST API nonce issues
* Improvement: Clean up cut and copy from rich text fields
* Improvement: Show "Saved successfully" in modal
* Fix: Alert message when clicking on some buttons
* Fix: Not recognizing all amounts during import in some PHP environments
* Fix: Keep serving size field after nutrition calculation
* Fix: Order of time input fields for RTL languages
* Fix: Prevent video embed code PHP warnings

= 5.0.4 =
* Fix: ID undefined when creating new recipe in Classic Editor
* Fix: Do not show a time of 0 in the metadata

= 5.0.3 =
* Fix: Performance issues with typing in new modal
* Fix: Emulate PUT and DELETE API calls for better compatibility
* Fix: Comet Cache compatibility problem when saving a recipe
* Fix: Prevent error when opening ingredient links tab in free version

= 5.0.2 =
* Improvement: Include debug information when API call fails
* Fix: Video embed not working in some cases
* Fix: Dismissing of notices would not work for next one

= 5.0.1 =
* Improvement: Show error that occurs on Manage page to send to support
* Fix: Prevent missing fields from breaking manage page

= 5.0.0 =
* Feature: Brand new recipe modal
* Feature: Brand new manage page
* Feature: Save revisions for recipes
* Feature: Copy to JSON when saving fails
* Feature: Autosuggest existing ingredients
* Feature: Ratings on the Manage page
* Feature: Output 0 for cook and prep time in metadata
* Feature: Show "0" in template for cook, prep or custom times
* Feature: Link image and name block to recipe URL (for roundup)
* Improvement: Allow links in ingredient and instruction group headers
* Improvement: Reduce rating queries
* Improvement: Support more formats for custom icon
* Improvement: Keywords and total time in WP Tasty import
* Improvement: Replace WP Tasty recipes in Gutenberg after import
* Improvement: Use recipe name placeholder in roundup link
* Improvement: Adjustable servings in recipe roundup
* Fix: Prevent closing bracket from breaking shortcode in Template Editor
* Fix: Double block properties when editing roundup template

= 4.3.4 =
* Feature: Set same author name for every recipe
* Fix: Prevent incorrect ID from breaking recipe roundup shortcode
* Fix: Print link when using plain permalinks

= 4.3.3 =
* Improvement: Setting to output metadata in either head or body
* Improvement: Only automatically output snippets on posts, not pages
* Improvement: Legacy mode setting for comment rating placement
* Fix: Specificy VideoObject type for video metadata
* Fix: Prevent WP_Error when getting YouTube metadata
* Fix: Revisionize compatibility for parent post

= 4.3.2 =
* Fix: Deprecated notice related to oEmbed cache

= 4.3.1 =
* Improvement: Consistent image size in Roundup Summary template
* Improvement: Clear oEmbed cache when no video metadata is found
* Fix: Issue with 2 recipes in 1 post on archive page
* Fix: Visual Composer compatibility
* Fix: PHP warning with nutrition label on print page

= 4.3.0 =
* Feature: Recipe Roundup for ItemList metadata
* Feature: Setting to prevent printing of non-published recipes
* Fix: Image position in print version for some browsers
* Fix: Overlap when selecting icons in Template Editor
* Fix: Update post status when parent post gets deleted

= 4.2.1 =
* Feature: Change link for Jump to Comments button
* Feature: Change position of rating field in comment form
* Improvement: Better default image sizes
* Improvement: Prevent WP Rocket lazy image loading on print page
* Improvement: Try to prevent conflict with generic [recipe] shortcode
* Improvement: Change print template for multilingual sites
* Fix: Color of rating stars in comment form
* Fix: Print button with query parameters

= 4.2.0 =
* Feature: Searchable and fallback recipe for Gutenberg blocks
* Feature: Setting to only show metadata for first recipe on page
* Feature: Polylang compatibility for changing recipe template in different languages
* Feature: Option to disable recipe and instruction image pinning
* Feature: Set instruction margin in Template Editor
* Feature: Set different template for RSS Feed
* Feature: Use recipe shortcodes in WP Ultimate Post Grid template
* Feature: Import Recipes by Simmer
* Feature: Import WordPress.com recipe shortcode
* Improvement: Restrict access to print version for non-published recipes
* Improvement: Add metadata to head instead of body
* Improvement: Drastically reduce DOM nodes for comment stars
* Improvement: Labels in template easier to translate
* Improvement: Classes for rating stars on hover
* Improvement: Date placeholder for print credit
* Fix: Changing instruction images size not working in some cases
* Fix: Use correct recipe default for shortcode on archive pages
* Fix: Bug in Elementor removing the admin menu
* Fix: PHP 7.3 compatibility for EasyRecipe import
* Fix: Print not always matching template

= 4.1.1 =
* Fix: Styling for automatic snippets in Legacy Mode

= 4.1.0 =
* Feature: Shortcode to display recipe snippets
* Improvement: Prevent other plugin notices from breaking the Template Editor layout
* Fix: Jump to Video button in Legacy templates
* Fix: Call to Action link color when using custom link action


= 4.0.8 =
* Improvement: Recipe block preview in Gutenberg editor
* Improvement: Filter by "No terms set" on the Manage page
* Fix: Make sure snippets only show up for posts containing a recipe
* Fix: EasyRecipe import not finding all recipes when encountering error

= 4.0.7 =
* Fix: Gutenberg 4.5.1 compatibility
* Fix: Convert to Gutenberg blocks for recipe shortcodes
* Fix: Translations in "Simple" nutrition label

= 4.0.6 =
* Fix: Prevent z-index problems in legacy templates
* Fix: Preview of some shortcodes in the Template Editor
* Fix: Prevent Template Editor from breaking in "Edit HTML" mode
* Fix: Don't show dateModified in metadata anymore to prevent rich snippet issues

= 4.0.5 =
* Fix: Prevent variable problem from breaking custom template

= 4.0.4 =
* Feature: Property to open CTA custom links in same or new tab and as nofollow
* Improvement: Separate stylesheet for modern mode to prevent legacy template conflicts
* Fix: Don't add inline styling to recipe snippet buttons in legacy mode
* Fix: Only include default.css for non-custom templates
* Fix: Make sure recipe metadata shows up on AMP pages

= 4.0.3 =
* Improvement: Speed and resource usage of Template Editor preview
* Improvement: Print page HTML code
* Fix: Line breaks in summary, instructions and notes
* Fix: Print button not clickable in some Legacy templates
* Fix: Ingredient links in clean legacy template

= 4.0.2 =
* Fix: Template Editor error when creating your own template in the theme folder
* Fix: Star ratings in legacy clean template

= 4.0.1 =
* Improvement: Prevent caching issue from breaking the backend
* Fix: Red stars in compact template
* Fix: Boxes and Columns container with large images
* Fix: User rating hovering problem in legacy mode
* Fix: Internet Explorer 11 compatibility

= 4.0.0 =
* Feature: Template Editor
* Feature: Set specific template for recipe block in Gutenberg
* Feature: Set different template for recipe archive pages
* Feature: Pin Recipe button
* Feature: Jump to comment form shortcode
* Feature: Recognize VideoObject meta tags in video embed code
* Feature: Include dateModified in metadata
* Improvement: Get rid of paragraphs in summary and instructions to prevent styling issues
* Improvement: Add slight delay to print dialog to make sure page is fully loaded
* Improvement: Match multiple URLs for MediaVine embed videos
* Improvement: Add Cookbook instruction images to previous instruction instead of separate line
* Improvement: Default options for the recipe dropdowns
* Fix: Import problem with multiple images on the same line
* Fix: Import instruction groups from Simple Recipe Pro correctly
* Fix: Yoast readability highlighter with recipe in post
* Fix: Loading incorrect ingredient links
* Fix: Prevent recursion issue when a recipe is added to its own notes
* Fix: NaN problem in Unit Conversion
* Fix: Prevent unit conversion issues when using numbers in the ingredient name
* Fix: Should not mix noindex and canonical on print page
* Fix: Remove blank lines in HTML fields when saving
* Fix: Translation JS errors in Gutenberg

= 3.2.1 =
* Fix: Allow shortcodes in new recipe shortcodes

= 3.2.0 =
* Feature: Shortcodes for different recipe parts
* Feature: Setting to show recipe and/or instruction images on the print page
* Feature: Jump to Recipe Video shortcode and block
* Improvement: Ignore recipe shortcode in Yoast SEO readability test
* Improvement: Show ingredient links when using one of the print templates
* Improvement: Own category for WP Recipe Maker blocks in Gutenberg
* Improvement: Import Meal Planner Pro instruction images
* Improvement: Import ingredient links for various plugins
* Improvement: Import ingredient links from various plugins
* Improvement: Import instruction links and styling from various plugins
* Improvement: Register settings and labels for translation in WPML
* Fix: Import ingredient and instruction groups in Cookbook
* Fix: Update recipe rating when comment gets trashed
* Fix: Update recipe rating when (un)approving comments
* Fix: Only update post content in find parents tool when necessary
* Fix: Scheduled recipes showing in recipe grid
* Fix: First letter of paragraph not displaying correctly on print page for iOS
* Fix: PHP Error caused by fallback recipe
* Fix: Using > sign in Custom Styling

= 3.1.2 =
* Feature: Gutenberg blocks and compatibility
* Improvement: Plugin hooks to change star icon

= 3.1.1 =
* Fix: Fatal error when embedding unpublished Youtube videos

= 3.1.0 =
* Feature: Full video metadata when using Youtube
* Feature: Use WordPress embed shortcode when using a URL for the video
* Feature: Import from Purr Recipe plugin
* Feature: Video metadata for WP YouTube Lyte plugin
* Improvement: Cache video metadata for 7 days
* Improvement: Cache recipe rating to improve performance
* Improvement: Cache comment ratings in meta to reduce database queries
* Fix: Only show recipe snippets in main query
* Fix: Prevent theme compatibility issue with first letter in paragraph

= 3.0.3 =
* Fix: Make sure settings JS is loaded after admin JS
* Fix: Don't cache settings structure to prevent issues with some settings not showing up
* Improvement: Hide notices on settings page

= 3.0.2 =
* Fix: Another settings problem in some environments

= 3.0.1 =
* Fix: Settings problem in some environments

= 3.0.0 =
* Feature: Brand new settings page
* Feature: Add video metadata for the Adthrive shortcode
* Feature: Support video metadata for oEmbed (YouTube, Vimeo, ...)
* Feature: Import ratings from Comment Rating Field plugin
* Improvement: Cache video metadata

= 2.5.3 =
* Improvement: Keep ingredient styling in EasyRecipe import
* Fix: Make sure print page is always noindex
* Fix: Prevent datatables conflicts with other plugins

= 2.5.2 =
* Improvement: Privacy policy suggestions
* Improvement: Don't use Google CDN
* Improvement: Support author queries through REST API
* Fix: Mediavine embed in Tastefully Simple template
* Fix: GLOB_BRACE warning when not defined on system
* Fix: Nutrition label warning

= 2.5.1 =
* Feature: Ability to embed instead of upload the recipe video
* Feature: Automatically add embedded Mediavine video metadata
* Improvement: Ability to save when getting logged out during editing

= 2.5.0 =
* Feature: Recipe video with metadata
* Feature: Ability to disable pinning to Pinterest on the print page
* Feature: Setting to hide keywords from recipe template
* Improvement: Change keyword label from settings
* Improvement: Use correct format for date in metadata
* Improvement: Noindex print page if parent post is not set

= 2.4.1 =
* Feature: Support for new Google Assistant keywords metadata
* Feature: Use new HowToStep and HowToSection for recipeInstructions metadata
* Improvement: Canonical URL on Print page

= 2.4.0 =
* Feature: Ability to set recipe type as "Non-Food" and not output metadata
* Feature: User and comment ratings accessible via REST API
* Feature: Compatible with Classic block in Gutenberg
* Improvement: Find Simple Recipe Pro ratings with the "Find Ratings" tool
* Fix: Ability to remove comment rating
* Fix: Prevent ratings migration from showing up on first install

= 2.3.0 =
* Feature: Import support for new Simple Recipe Pro update
* Feature: Allow HTML to be used as text for the print and jump shortcodes
* Improvement: Nofollow for print links in recipe template
* Improvement: Adjust height of tooltip on manage page to fit content
* Improvement: wpautop for recipe notes
* Improvement: Wider input fields for numbers
* Improvement: Allow more HTML tags in recipe text

= 2.2.2 =
* Fix: Get rid of ratings warning

= 2.2.1 =
* Fix: Showing all ratings when there were no ratings yet

= 2.2.0 =
* Feature: Optional custom time field for resting, cooling, ...
* Feature: Setting for default custom author name
* Improvement: Tool to find missing recipe ratings from settings page
* Improvement: Site icon on print page
* Improvement: Separate instructions steps better in Simple Recipe Pro import
* Fix: Times not showing in Tastyfully Simple template when Prep Time was not filled in
* Fix: Not all information showing in nutrition label when only some nutrients were filled in
* Fix: HTML Entities when editing ingredient fields

= 2.1.1 =
* Fix: Remove !important flags from AMP CSS output
* Fix: Don't show 0 values in nutrition label after importing

= 2.1.0 =
* Feature: Recipe styling on AMP pages
* Feature: Set instruction image alignment on settings page
* Feature: Tool to automatically find parent posts
* Feature: Import user ratings from Zip Recipes Premium
* Feature: Import comment ratings from Meal Planner Pro
* Feature: Import recipes from Cookbook recipe plugin
* Feature: Import recipes from Simple Recipe Pro
* Improvement: Use actual link for print button
* Improvement: Don't apply custom CSS to print page when feature is disabled
* Improvement: Use svg for TinyMCE button icon
* Improvement: Set standard text alignment for recipe template
* Fix: Not finding MealPlannerPro recipes to import
* Fix: Filter manage table when clicking through from taxonomy

= 2.0.0 =
* Plugin restructure for performance improvements and Premium bundle system

= 1.27.0 =
* Feature: Create and update recipes via REST API
* Improvement: Only show automatically added snippets on singular pages
* Improvement: Only show warning when changes have actually been made
* Improvement: Introduction video on the welcome page
* Fix: Status of recipes in scheduled posts
* Fix: Show correct plural or singular form for hours
* Fix: Prevent PHP notice caused by metadata

= 1.26.1 =
* Fix: Modal overlay problem

= 1.26.0 =
* Feature: Setting to automatically add recipe snippets like Jump to Recipe
* Feature: Create recipes on the Manage page
* Feature: Import from Cooked recipe plugin
* Improvement: Ability to reset settings to defaults
* Fix: Prevent issue with image metadata in different thumbnail sizes
* Fix: Prevent trashing parent post from trashing recipe
* Fix: Recipe image not loading when using Fly Dynamic Image Resizer

= 1.25.0 =
* Feature: Show recipe rating on manage page
* Feature: Remove inline metadata for recipes, using JSON-LD metadata only now
* Feature: Present multiple image sizes to Google in the metadata
* Feature: Setting to opt out of Pinterest Rich Pins
* Feature: Import from FoodiePress
* Improvement: Top margin for nutrition label inside recipe box
* Improvement: Don't fill in serving unit automatically when empty
* Fix: Correct font from settings applied to Tastefully Simple template

= 1.24.0 =
* Improvement: Only show ratings given by an enabled feature
* Improvement: Import title in older versions of EasyRecipe recipes
* Improvement: Prevent instruction list style icon position problem
* Fix: Default author setting not working
* Fix: Prevent PHP warning when saving posts

= 1.23.1 =
* Fix: Make sure recipe rating is updated
* Fix: Inline rating metadata when there are no votes yet

= 1.23.0 =
* Feature: Import WP Tasty Recipes
* Improvement: Better handling of shortcodes in JSON-LD metadata
* Fix: Problem with double recipes in categories
* Fix: Jetpack Contact Form compatibility problem on manage page

= 1.22.0 =
* Feature: RTL support for recipe templates and print
* Improvement: Print and unit conversion functionality when using the print templates
* Improvement: Combined public JS files
* Improvement: Apply appearance settings to print template as well
* Improvement: Recipe rating saved as post meta
* Improvement: Migrate WP Ultimate Recipe Premium user ratings
* Fix: Heading style dropdown in Recipe Notes

= 1.21.0 =
* Feature: change ingredients and instructions list style from settings page
* Feature: Rename terms on the manage page
* Feature: Associate same categories as parent post with recipes
* Improvement: Correctly apply font size setting to Tastefully Simple template
* Improvement: Ability to override the comment rating templates
* Fix: Prevent Firefox list style position issue
* Fix: Make sure Premium version loads with all directory names
* Fix: Prevent empty nutrition label from showing up

= 1.20.0 =
* Feature: Intermediate save when creating or editing recipes
* Feature: Settings to personalize the ingredient text import
* Improvement: Abbreviations in ingredient text import
* Improvement: Immediately update parent post when importing
* Improvement: Try to fix serialize issues when getting ingredients or instructions
* Improvement: Prevent styling differences in instructions
* Fix: EasyRecipe recipes stuck in to import list
* Fix: Remove leftover debug code

= 1.19.1 =
* Fix: PHP Notice when saving recipes

= 1.19.0 =
* Feature: Sub- and superscript in summary and instructions
* Feature: Allow for basic HTML in ingredients
* Feature: Setting to align nutrition label left, center or right
* Improvement: Streamlined import process using AJAX
* Fix: Ingredients not importing when going too fast in the text import
* Fix: Prevent wpautop from breaking the icons
* Fix: Prevent compatibilty bugs when saving posts

= 1.18.0 =
* Feature: Add custom styling to the recipe print page
* Feature: Bulk delete ingredients
* Improvement: Easy edit and view links for imported recipes
* Fix: Prevent jumping to the top on the manage page
* Fix: Print URL without trailing slash

= 1.17.1 =
* Fix: Ingredient import problem

= 1.17.0 =
* Feature: Setting to disable the output of inline CSS
* Improvement: Better import of ingredient notes
* Improvement: Prevent themes from messing up the recipe template
* Fix: Duplicate slug problem

= 1.16.0 =
* Feature: Change image sizes in settings
* Feature: Recipe placeholders for print credit
* Feature: Change position of the comment rating stars
* Improvement: Easier access to next recipes on import page
* Improvement: change comment rating label
* Improvement: Support unicode units when parsing ingredients
* Improvement: Ability to save empty ingredient and  instruction groups
* Improvement: Print shortcode works without JS as well (AMP pages)
* Improvement: Click on SEO indicator to edit recipe
* Fix: Search filter in WP Ultimate Post Grid
* Fix: Saving links for new ingredients in Premium plugin

= 1.15.0 =
* Feature: SEO check on manage page
* Feature: Change recipe template fonts from settings
* Feature: Show latest or random recipe with the shortcode
* Improvement: Show preview of links in recipe summary and instructions
* Improvement: Import Recipe Card adapted field to recipe notes
* Improvement: More information in shortcode preview
* Fix: Disappearing characters in text import
* Fix: Don't replace encoded characters in ingredient links
* Fix: Query issues on import recipes page

= 1.14.1 =
* Feature: Setting to change access to import recipes page
* Improvement: Better parsing of ingredients during import
* Improvement: Stay on correct page after reloading datatable
* Fix: Ability to remove ingredient links again

= 1.14.0 =
* Feature: Edit Recipe button for easy access
* Feature: Setting to set capability required for the manage page
* Improvement: Shortcode preview shows entire recipe
* Improvement: Taxonomies in REST API
* Improvement: AggregateRating details in inline metadata even when not shown
* Improvement: Better parsing of ingredient notes
* Fix: Clearfix for recipe container
* Fix: Manage page filters appearing over modal
* Fix: Pagination on taxonomy manage pages

= 1.13.0 =
* Feature: Filter recipes on manage page by ingredients and tags
* Feature: Change comment rating stars color in the settings
* Improvement: Mobile template of Tastefully Simple
* Fix: Problem with unwanted redirections by the Redirection plugin
* Fix: Problem with unwanted redirections by the Yoast SEO Premium plugin
* Fix: Prevent warnings on settings page

= 1.12.1 =
* Fix: Recipe Card import of instructions

= 1.12.0 =
* Feature: Print Credit message
* Feature: Add existing recipe through modal
* Feature: Import Yummly recipes
* Feature: Import Yumprint Recipe Card recipes
* Fix: Issue with shortcode preview displaying on the front-end
* Fix: Prevent importing empty lines
* Fix: Prevent datatable from outputting errors as alerts
* Fix: Empty ingredient groups in text import
* Fix: Text import unit issue in some languages

= 1.11.0 =
* Improvement: Set default value for Author Display field
* Improvement: Noindex the print page
* Improvement: Better margins for recipe image in Tastefully Simple
* Fix: Make sure correct nutrition label is shown with multiple recipes on a page
* Fix: Tastefully Simple template image on mobile

= 1.10.1 =
* Fix: Activation issue for hosts using an old version of PHP

= 1.10.0 =
* Feature: Change recipe template colors in the settings
* Feature: Change recipe template labels in the settings
* Feature: Manage courses and cuisines
* Improvement: Show days in recipe times
* Improvement: Recipe import performance
* Improvement: Prevent accidental closing of the modal
* Improvement: Setting to use featured image of parent post
* Improvement: Prevent recipe getting overwritten by the other shortcodes
* Fix: Prevent issue with post content replacing recipe notes
* Fix: Only import numbers for nutrition facts
* Fix: WordFence Compatibility

= 1.9.1 =
* Feature: WP Recipe Maker icon in TinyMCE editor
* Fix: Ingredients settings page

= 1.9.0 =
* Feature: Manage recipes and ingredients in a central place
* Feature: Edit recipes through the WP Recipe Maker button
* Fix: Prevent Divi Builder bug

= 1.8.0 =
* Feature: Import from ZipList and Zip Recipes
* Improvement: Increased performance of recipe dropdowns
* Improvement: Don't output JSON-LD metadata in RSS feed
* Improvement: Use fallback recipe in RSS feed
* Improvement: Easier to select multiple recipes for import
* Improvement: Indicate recipes without parent post in import
* Fix: Use correct default feature settings

= 1.7.1 =
* Fix: Associate all ingredient terms with recipes

= 1.7.0 =
* Feature: Import recipe from text
* Feature: Add nofollow links in summary and instructions
* Feature: Import recipes from Meal Planner Pro
* Feature: Import recipes from BigOven
* Improvement: Setting to disable comment ratings
* Improvement: Recognize unicode fractions when importing ingredients
* Improvement: Import nutrition facts from WP Ultimate Recipe
* Fix: Only show nutritional metadata if present
* Fix: Consistent behaviour for automatic time calculations

= 1.6.1 =
* Improvement: Show warning if EasyRecipe is breaking things

= 1.6.0 =
* Feature: Show hours for longer recipe times
* Improvement: Prevent font size inconsistencies in template
* Fix: Don't associate recipes with revisions
* Fix: Capital letters in template names

= 1.5.0 =
* Feature: Set recipe author
* Improvement: Sanitize metadata before outputting
* Fix: Warning when adding comments as a subscriber
* Fix: Compatibility issue with Jetpack
* Fix: Prevent infinite shortcode loop

= 1.4.0 =
* Feature: Access recipes though REST API
* Feature: Choose specific recipe template in shortcode
* Improvement: Check for leftover ER comment ratings when importing from WP Ultimate Recipe
* Improvement: Execute shortcodes in the recipe template
* Fix: Include correct stylesheet when using recipe templates from theme
* Fix: Show all recipes to be checked instead of just 8 recipes
* Fix: Use correct print URL if WordPress is in a subdirectory
* Fix: Linebreak accumulation when updating recipes
* Fix: Prevent Post Type Switcher plugin bug from breaking recipes

= 1.3.0 =
* Feature: Import from WP Ultimate Recipe
* Feature: wpDiscuz support for comment ratings
* Improvement: Use photo from Photo tab when importing from EasyRecipe
* Improvement: Check for custom templates in both parent and child theme
* Improvement: Different print system for better browser compatibility
* Fix: Round average comment rating to 2 decimals

= 1.2.0 =
* Feature: New "Tastefully Simple" template, similar to EasyRecipe
* Feature: New "Clean Print with Image" recipe template
* Feature: Print recipe shortcode
* Feature: Jump to recipe shortcode
* Improvement: Shortcode preview includes image and summary
* Fix: use ratingCount instead of reviewCount for JSON-LD metadata
* Fix: Trailing slash issue in asset URLs

= 1.1.0 =
* Feature: Comment ratings with metadata
* Feature: Inline metadata for Pinterest rich pins
* Feature: Calories field for nutrition metadata
* Improvement: FAQ pages
* Improvement: Strip HTML from JSON-LD metadata

= 1.0.0 =
* Feature: JSON-LD Metadata
* Feature: Intuitive workflow using regular posts or pages
* Feature: Import from EasyRecipe
* Feature: Clean printing of recipes
* Feature: Fallback recipe when the plugin is disabled

== Upgrade notice ==
= 8.7.5 =
Update recommended when using Yoast SEO

= 8.7.4 =
Update can fix some rare theme compatibility problems

= 8.7.3 =
Update highly recommended to prevent recipe metadata issues

= 8.7.2 =
Update to prevent a PHP error in some specific cases

= 8.7.1 =
Only changes the default of one of the new settings

= 8.7.0 =
Lots of great new features, improvements and fixes

= 8.6.2 =
Fix for some HTML problems in template

= 8.6.1 =
Security fix update

= 8.6.0 =
Some quality of life improvements and better Elementor compatibility

= 8.5.0 =
Ability to monetize your recipes with Instacart Tastmakers and some other improvements

= 8.4.0 =
New temperature shortcode with oven symbols

= 8.3.0 =
Instacart integration, some improvements and fixes

= 8.2.0 =
Several fixes and some improvements

= 8.1.1 =
Update to prevent fatal error from happening in 8.1.0

= 8.1.0 =
Some great new features, improvements, fixes and better compatibility with WordPress 5.9

= 8.0.2 =
Prevent a PHP notice from showing up in the instructions

= 8.0.1 =
Fix a few small issues introduced in 8.0.0

= 8.0.0 =
Major update with plenty of new features, improvements and fixes

= 7.7.2 =
A few smaller fixes and improvements

= 7.7.1 =
Make sure the ingredient and equipment suggestions still work

= 7.7.0 =
Some great new improvements and fixes

= 7.6.1 =
Prevent PHP warnings or errors in some specific cases

= 7.6.0 =
Great new features and some quality of life improvements

= 7.5.1 =
Update for some star rating and import related fixes

= 7.5.0 =
A few smaller features and improvements

= 7.4.1 =
Two small fixes

= 7.4.0 =
Some great new features and improvements

= 7.3.0 =
Great list of new features, improvements and fixes

= 7.2.0 =
Some fun new features opening up more possibilities

= 7.1.2 =
Update to prevent plugin compatibility problems and improve pagespeed results

= 7.1.1 =
Update to prevent issues with external roundup links

= 7.1.0 =
Lots of new advanced options, improvements and fixes

= 7.0.0 =
Lots of new integrations, features, improvements and fixes

= 6.9.0 =
Some great new features and small fixes

= 6.8.0 =
Update for a few smaller fixes and improvements

= 6.7.0 =
Update for a few smaller fixes and improvements

= 6.6.0 =
Update for various performance and quality of life improvements

= 6.5.1 =
Update to prevent ingredient display issues in some themes

= 6.5.0 =
Update to prevent comment rating issues

= 6.4.0 =
Update to ensure WordPress 5.5 compatibility

= 6.3.0 =
Further accessiblilty improvements and fixes

= 6.2.2 =
Update to prevent some errors in the rich text editors

= 6.2.1 =
Update to prevent comment ratings in wrong places

= 6.2.0 =
Update for accessibility improvements

= 6.1.2 =
Update to prevent errors on the print page

= 6.1.1 =
Update to prevent a PHP warning

= 6.1.0 =
Update to fully optimize for the new Google Guided Recipes and an advanced print feature

= 6.0.4 =
Update to prevent Google Search Console warnings

= 6.0.3 =
Update to prevent incorrect automatic updates from causing a fatal error

= 6.0.2 =
Update to prevent a PHP notice from showing up

= 6.0.1 =
Update to prevent a PHP notice from showing up

= 6.0.0 =
Various fixes and improvements with a brand new recipe template

= 5.11.1 =
Update to prevent a PHP notice from showing up

= 5.11.0 =
Various metadata and video related improvements

= 5.10.5 =
Update to fix Yoast SEO analysis in the block editor

= 5.10.4 =
Update for better compatibility in legacy browsers

= 5.10.3 =
Update recommended to prevent further rich text editor issues

= 5.10.2 =
Update recommended to prevent further rich text editor issues

= 5.10.1 =
Update recommended to prevent the rich text editor from breaking

= 5.10.0 =
Plenty of improvements and fixes

= 5.9.0 =
Update recommend when using in combination with our WP Ultimate Post Grid plugin

= 5.8.4 =
Various improvements and fixes

= 5.8.3 =
A few smaller fixes and improvements

= 5.8.2 =
Update when using the Jetpack plugin

= 5.8.1 =
Small update with various improvements and compatibility with the new Twenty Twenty theme

= 5.8.0 =
Some great new features and improvements

= 5.7.0 =
Various fixes and improvements

= 5.6.1 =
Update to prevent issues when using Firefox

= 5.6.0 = 
Various fixes and improvements

= 5.5.3 =
Small fixes related to comment ratings

= 5.5.2 =
Update highly recommended when using Firefox

= 5.5.1 =
Update if you're experiencing issues with not all features loading correctly

= 5.5.0 =
Various fixes and improvements

= 5.4.3 =
Update highly recommended to make sure the metadata shows up in all situations

= 5.4.2 =
A few smaller fixes

= 5.4.1 =
Update recommended when using AMP the Yoast SEO

= 5.4.0 =
A few smaller features and fixes

= 5.3.3 =
Update when getting incorrect comment rating totals

= 5.3.2 =
Update when using the Gutenberg Editor

= 5.3.1 =
Update when using the Classic Editor

= 5.3.0 =
Update for a variety of fixes

= 5.2.1 =
Various fixes and improvements

= 5.2.0 =
Update when using the plugin for How-to instructions

= 5.1.1 =
Upgrade when using Elementor

= 5.1.0 =
Some integrations, improvements and fixes

= 5.0.4 =
Update when using the Classic Editor

= 5.0.3 =
Better compatibility for the new modal

= 5.0.2 =
Update to make sure video embeds work

= 5.0.1 =
Update to prevent manage page from breaking

= 5.0.0 =
Major update with brand new modal and manage page

= 4.3.4 =
Update for a few minor fixes and a new author option

= 4.3.3 =
Update for a few minor improvements and fixes

= 4.3.2 =
Update to prevent PHP notice from showing up

= 4.3.1 =
A few smaller bug and compatibility fixes

= 4.3.0 =
New ItemList metadata feature and a few fixes

= 4.2.1 =
A few smaller fixes and improvements

= 4.2.0 =
Update with lots of smaller improvements and features

= 4.1.1 =
Update if you're using legacy mode

= 4.1.0 =
Update required to use the new premium Recipe Collections feature

= 4.0.8 =
Update recommend to ensure WordPress 5.0 and Gutenberg compatibility

= 4.0.7 =
Update required when using Gutenberg

= 4.0.6 =
Fix some template issues

= 4.0.5 =
Update when using a legacy template

= 4.0.4 =
Update for improved Legacy Mode compatibility

= 4.0.3 =
Various fixes for legacy and modern templates

= 4.0.2 =
Update if you want to create your own modern template

= 4.0.1 =
Update if you're experiencing issues editing recipes

= 4.0.0 =
Major update with the brand new Template Editor feature as the highlight

= 3.2.1 =
Update if you're using shortcodes in the summary, video or notes section

= 3.2.0 =
Update for various improvements and new features

= 3.1.2 =
Update if you want to use the new Gutenberg editor

= 3.1.1 =
Update to prevent a fatal error when embedding videos

= 3.1.0 =
Update for some metadata and performance improvements

= 3.0.3 =
Update if you're experiencing issues with the new settings page

= 3.0.2 =
Update to make sure all the settings work

= 3.0.1 =
Update to prevent a fatal error in some environments

= 3.0.0 =
Update for a brand new settings page and better video metadata

= 2.5.3 =
Update to prevent the print page from getting indexed

= 2.5.2 =
Update for privacy policy suggestions in compliance with GDPR

= 2.5.1 =
Ability to embed a video and prevent save problems

= 2.5.0 =
Various metadata improvements and new video feature

= 2.4.1 =
Update for the latest changes in Google's structured data

= 2.4.0 =
A few new features and improvements, including basic Gutenberg compatibility (blocks still coming up!)

= 2.3.0 =
A few new features and improvements

= 2.2.2 =
Update to prevent a rating related warning from showing up

= 2.2.1 =
Update to prevent incorrect ratings from showing up

= 2.2.0 =
A few new features and improvements

= 2.1.1 =
Update to prevent issues on AMP pages

= 2.1.0 =
Update for more import options, AMP improvements and a few smaller fixes

= 2.0.0 =
Update for performance improvements due to restructured assets

= 1.27.0 =
Update for a few bug fixes and improvements

= 1.26.1 =
Update to prevent the modal overlay from blocking TinyMCE functionality

= 1.26.0 =
Update to prevent issues with the recipe metadata

= 1.25.0 =
Update to use the latest metadata structure

= 1.24.0 =
Update to fix a few bugs

= 1.23.1 =
Update if you're experiencing issues with the ratings

= 1.23.0 =
Update to prevent a Jetpack compatibility problem

= 1.22.0 =
Update required if you want to use the latest version of the Premium version

= 1.21.0 =
Update for the latest version of this plugin

= 1.20.0 =
Update for a few great tweaks

= 1.19.1 =
Update to get rid of a PHP notice

= 1.19.0 =
Update to ensure WordPress 4.8 compatibility

= 1.18.0 =
Update to easily customize the print page

= 1.17.1 =
Update if you're importing from other plugins

= 1.17.0 =
Update if you're experiencing problemns with the post slug

= 1.16.0 =
Update for even more easy template customization options

= 1.15.0 =
Some fun new features and improvements

= 1.14.1 =
Upgrade required to use the latest Premium add-ons

= 1.14.0 =
Update for various improvements and bug fixes

= 1.13.0 =
Update to prevent warning notices on the settings page

= 1.12.1 =
Update if you want to use the Recipe Card import

= 1.12.0 =
New import and other features + some bug fixes

= 1.11.0 =
Some improvements to the template

= 1.10.1 =
Update only needed if you're on a very old version of PHP

= 1.10.0 =
Make your template unique in this new update

= 1.9.1 =
Fix for settings issue introduced in previous update

= 1.9.0 =
Easier recipe management in this new update

= 1.8.0 =
A bunch of general improvements and the possibility to import from ZipList and Zip Recipes

= 1.7.1 =
Update to fix the ingredient term relations

= 1.7.0 =
Lots of great improvements

= 1.6.1 =
Warning message for EasyRecipe users

= 1.6.0 =
Some minor updates and the release of the Premium plugin

= 1.5.0 =
Fixed a few issues and added the author field.

= 1.4.0 =
A few important bug fixes and improvements

= 1.3.0 =
Another week, another update!

= 1.2.0 =
Update for some new SEO improvements and templates.

= 1.1.0 =
Update highly recommended for SEO purposes.

= 1.0.0 =
First version of this plugin, no upgrades needed.
