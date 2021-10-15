const analyticsEndpoint = wprm_admin.endpoints.analytics;

import ApiWrapper from '../ApiWrapper';

export default {
    delete(id) {
        return ApiWrapper.call( `${analyticsEndpoint}/${id}`, 'DELETE' );
    },
};
