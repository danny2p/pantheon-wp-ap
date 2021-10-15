const manageEndpoint = wprm_admin.endpoints.manage;
const ratingEndpoint = wprm_admin.endpoints.rating;
const taxonomyEndpoint = wprm_admin.endpoints.taxonomy;

import ApiWrapper from '../ApiWrapper';

let gettingData = false;
let gettingDataNextArgs = false;

export default {
    getData(args) {
        if ( ! gettingData ) {
            return this.getDataDebounced(args);
        } else {
            gettingDataNextArgs = args;
            return new Promise(r => r(false));
        }
    },
    getDataDebounced(args) {
        gettingData = true;

        return ApiWrapper.call( `${manageEndpoint}/${args.route}`, 'POST', args ).then(json => {
            // Check if another request is queued.
            if ( gettingDataNextArgs ) {
                const newArgs = gettingDataNextArgs;
                gettingDataNextArgs = false;

                return this.getDataDebounced(newArgs);
            } else {
                // Return this request.
                gettingData = false;
                return json;
            }
        });
    },
    deleteUserRatings(id) {
        return ApiWrapper.call( `${ratingEndpoint}/recipe/${id}`, 'DELETE' );
    },
    getTerm(type, id) {
        return ApiWrapper.call( `${taxonomyEndpoint}${type}/${id}` );
    },
    createTerm(type, name) {
        const data = {
            name,
        };

        return ApiWrapper.call( `${taxonomyEndpoint}${type}`, 'POST', data );
    },
    deleteTerm(type, id) {
        return ApiWrapper.call( `${taxonomyEndpoint}${type}/${id}?force=true`, 'DELETE' );
    },
    renameTerm(type, id, name) {
        const data = {
            name,
        };

        return ApiWrapper.call( `${taxonomyEndpoint}${type}/${id}`, 'POST', data );
    },
    changeTermSlug(type, id, slug) {
        const data = {
            slug,
        };

        return ApiWrapper.call( `${taxonomyEndpoint}${type}/${id}`, 'POST', data );
    },
    renameTermLabel(type, id, label) {
        const data = {
            type,
            id,
            label,
        };

        return ApiWrapper.call( `${manageEndpoint}/taxonomy/label`, 'POST', data );
    },
    mergeTerm(type, oldId, newId) {
        const data = {
            type,
            oldId,
            newId,
        };

        return ApiWrapper.call( `${manageEndpoint}/taxonomy/merge`, 'POST', data );
    },
    updateTaxonomyMeta(type, id, meta) {
        let data = {};
        data[ type ] = meta;

        return ApiWrapper.call( `${taxonomyEndpoint}${type}/${id}`, 'POST', data );
    },
    bulkEdit(route, type, ids, action) {
        const data = {
            type,
            ids,
            action,
        };

        return ApiWrapper.call( `${manageEndpoint}/${route}/bulk`, 'POST', data );
    },
};
