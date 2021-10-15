const customTaxonomiesEndpoint = wprm_admin.endpoints.custom_taxonomies;

import ApiWrapper from 'Shared/ApiWrapper';

export default {
    save( editing, taxonomy ) {
        const data = {
            ...taxonomy,
        };

        const method = editing ? 'PUT' : 'POST';

        return ApiWrapper.call( customTaxonomiesEndpoint, method, data );
    },
};
