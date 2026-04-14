# WP Recipe Maker Divi 5 integration

This folder contains the Divi 5 `WPRM Recipe` module source plus the bundled Visual Builder assets. The TypeScript/SCSS sources live in `src/` and are bundled into `scripts/bundle.js` plus the paired styles in `styles/` via the main project `webpack.config.js` entry named `divi5`.

## Local development

1. Install JS dependencies (requires Node 18+):
   ```bash
   npm install --legacy-peer-deps
   ```
2. Rebuild all bundles, including Divi 5 assets:
   ```bash
   npm run build
   ```
3. When Divi 5 is active, the plugin enqueues `scripts/bundle.js`, the builder CSS (`styles/vb-bundle.css`) and registers the server-side module directly from `src/components/wprm-recipe/`, where `module.json` and `conversion-outline.json` live.

The implementation details follow Elegant Themes' [d5-extension-example-modules](https://github.com/elegantthemes/d5-extension-example-modules) reference.

## Divi 4 migration

The module metadata declares the legacy shortcode slug (`divi_wprm_recipe`) and includes a JSON conversion outline so Divi 5 can migrate supported WPRM Recipe modules to the native `wprm/recipe` module during Divi 5 layout migration.

For existing posts that were already migrated into Divi 5 backward compatibility mode before this module registration was in place, WPRM normalizes the legacy `divi_wprm_recipe` module to the native Divi 5 module through the Divi Visual Builder post-content migration hook.
