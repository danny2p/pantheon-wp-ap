const modalEndpoint = wprm_admin.endpoints.modal;

import ApiWrapper from '../ApiWrapper';

export default {
    parseIngredients(ingredients) {
        const data = {
            ingredients,
        };

        return ApiWrapper.call( `${modalEndpoint}/ingredient/parse`, 'POST', data );
    },
    aiImportRecipe(text) {
        const data = {
            text,
        };

        return ApiWrapper.call( `${modalEndpoint}/ai-recipe-import`, 'POST', data );
    },
};
