const ideaEndpoint = wprm_admin.endpoints.idea;
const ideaImportEndpoint = wprm_admin.endpoints.idea_import;

import ApiWrapper from '../ApiWrapper';

export default {
    get(id) {
        return ApiWrapper.call( `${ideaEndpoint}/${id}?t=${ Date.now() }` );
    },
    save(idea) {
        const data = {
            idea,
        };

        let url = ideaEndpoint;
        let method = 'POST';

        const ideaId = idea.id ? parseInt( idea.id ) : false;
        if ( ideaId ) {
            url += `/${ideaId}`;
            method = 'PUT';
        }

        return ApiWrapper.call( url, method, data );
    },
    updateStatus(ideaId, status) {
        const data = {
            idea: {
                status,
            },
        };

        return ApiWrapper.call( `${ideaEndpoint}/${ideaId}`, 'PUT', data );
    },
    delete(id, permanently = false) {
        let endpoint = `${ideaEndpoint}/${id}`;

        if ( permanently ) {
            endpoint += '?force=true';
        }

        return ApiWrapper.call( endpoint, 'DELETE' );
    },
    import(ideas) {
        return ApiWrapper.call( ideaImportEndpoint, 'POST', {
            ideas,
        } );
    },
};
