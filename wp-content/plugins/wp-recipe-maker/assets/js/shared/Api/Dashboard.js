const dashboardEndpoint = wprm_admin.endpoints.dashboard;

import ApiWrapper from '../ApiWrapper';

export default {
    getAnalytics() {
        return ApiWrapper.call( `${dashboardEndpoint}/analytics` );
    },
};
