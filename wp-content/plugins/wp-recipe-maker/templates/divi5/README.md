# WP Recipe Maker Divi 5 integration

This folder contains the new Divi 5 module assets and build output for the `WPRM Recipe` placeholder module.  The TypeScript/SCSS sources live in `src/` and are bundled into `scripts/bundle.js` plus the paired styles in `styles/` via the main project `webpack.config.js` entry named `divi5`.

## Local development

1. Install JS dependencies (requires Node 18+):
   ```bash
   npm install --legacy-peer-deps
   ```
2. Rebuild all bundles, including Divi 5 assets:
   ```bash
   npm run build
   ```
3. When Divi 5 is active inside Divi, the plugin enqueues `scripts/bundle.js`, the builder CSS (`styles/vb-bundle.css`) and exposes the generated `modules-json` metadata to Divi's Module Library.

The implementation details follow Elegant Themes' [d5-extension-example-modules](https://github.com/elegantthemes/d5-extension-example-modules) reference.

## Divi 4 migration

The module metadata declares the legacy shortcode slug (`divi_wprm_recipe`) and includes a conversion outline so Divi 5 automatically upgrades existing Divi 4 layouts the first time they are opened in the new builder. No manual migration is required—just open the page in Divi 5 and save it.
