const settingEndPoint = wprm_admin.endpoints.setting;
import ApiWrapper from '../ApiWrapper';

export default {
    save(settings) {
        let data = {
            settings
        };

        return ApiWrapper.call( settingEndPoint, 'POST', data );
    },
};
