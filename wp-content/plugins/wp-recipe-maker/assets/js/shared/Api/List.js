const listEndpoint = wprm_admin.endpoints.list;
import ApiWrapper from '../ApiWrapper';

export default {
    get(id) {
        return ApiWrapper.call( `${listEndpoint}/${id}?t=${ Date.now() }` );
    },
    save(list) {
        const data = {
            list,
        };

        // Default to create new list.
        let url = listEndpoint;
        let method = 'POST';

        // List ID set? Update an existing one.
        const listId = list.id ? parseInt(list.id) : false;
        if ( listId ) {
            url += `/${listId}`
            method = 'PUT';
        }

        return ApiWrapper.call( url, method, data );
    },
    updateStatus(listId, status) {
        const data = {
            status,
        };

        return ApiWrapper.call( `${listEndpoint}/${listId}`, 'PUT', data );
    },
    delete(id, permanently = false) {
        let endpoint = `${listEndpoint}/${id}`;
        
        if ( permanently ) {
            endpoint += '?force=true';
        }

        return ApiWrapper.call( endpoint, 'DELETE' );
    },
};
