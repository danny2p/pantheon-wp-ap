// Internal Dependencies
import modules from './modules';

jQuery(window).on('et_builder_api_ready', (event, API) => {
    API.registerModules(modules);
});