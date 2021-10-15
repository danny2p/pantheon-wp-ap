const noticeEndpoint = wprm_admin.endpoints.notices;
import ApiWrapper from '../ApiWrapper';

export default {
    dismissNotice(id) {
        const data = {
            id,
        };

        return ApiWrapper.call( noticeEndpoint, 'DELETE', data );
    },
};
