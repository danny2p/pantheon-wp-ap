import { __wprm } from './Translations';

export const ideaStatusOptions = [
    { value: 'idea', label: __wprm( 'Idea' ) },
    { value: 'planned', label: __wprm( 'Planned' ) },
    { value: 'in-progress', label: __wprm( 'In Progress' ) },
    { value: 'published', label: __wprm( 'Published' ) },
    { value: 'dismissed', label: __wprm( 'Dismissed' ) },
];

export const ideaTypeOptions = [
    { value: 'recipe', label: __wprm( 'Recipe' ) },
    { value: 'list', label: __wprm( 'List' ) },
    { value: 'other', label: __wprm( 'Other' ) },
];

export const ideaStatusFilterOptions = [
    { value: 'all', label: __wprm( 'All' ) },
    { value: 'not-started', label: __wprm( 'Not Started' ) },
    ...ideaStatusOptions,
];

export const ideaSourceOptions = [
    { value: 'manual', label: __wprm( 'Manual' ) },
    { value: 'ai', label: __wprm( 'AI' ) },
];

export const getIdeaOptionLabel = ( options, value ) => {
    const option = options.find( ( item ) => item.value === value );
    return option ? option.label : value;
};

export const getIdeaLabel = ( type, value ) => {
    switch ( type ) {
        case 'status':
            return getIdeaOptionLabel( ideaStatusOptions, value );
        case 'type':
            return getIdeaOptionLabel( ideaTypeOptions, value );
        case 'source':
            return getIdeaOptionLabel( ideaSourceOptions, value );
        default:
            return value;
    }
};
