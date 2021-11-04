import { __wprm } from 'Shared/Translations';
import Api from 'Shared/Api';

import ColumnsCustomTaxonomies from './custom-taxonomies/Columns';
import ColumnsRatings from './ratings/Columns';
import ColumnsRecipe from './recipes/Columns';
import ColumnsRevision from './revisions/Columns';
import ColumnsTaxonomies from './taxonomies/Columns';
import ColumnsTrash from './trash/Columns';
import ColumnsUnits from './units/Columns';
import ColumnsAnalytics from './analytics/Columns';

let datatables = {
    'recipe': {
        parent: __wprm( 'Recipes' ),
        title: __wprm( 'Overview' ),
        id: 'recipe',
        route: 'recipe',
        label: {
            singular: __wprm( 'Recipe' ),
            plural: __wprm( 'Recipes' ),
        },
        bulkEdit: {
            route: 'recipe',
            type: 'recipe',
        },
        createButton: (datatable) => {
            WPRM_Modal.open( 'recipe', {
                saveCallback: () => datatable.refreshData(),
            } );
        },
        selectedColumns: ['seo','id','date','name','parent_post', 'rating'],
        columns: ColumnsRecipe,
    }
}

if ( wprm_admin_manage.revisions ) {
    datatables.revision = {
        parent: __wprm( 'Recipes' ),
        id: 'revision',
        route: 'revision',
        label: {
            singular: __wprm( 'Revision' ),
            plural: __wprm( 'Revisions' ),
        },
        bulkEdit: {
            route: 'revision',
            type: 'revision',
        },
        createButton: false,
        selectedColumns: false,
        columns: ColumnsRevision,
    };
}

datatables.trash = {
    parent: __wprm( 'Recipes' ),
    title: `${__wprm( 'Trash' )} (${wprm_admin_manage.trash})`,
    id: 'trash',
    route: 'trash',
    label: {
        singular: __wprm( 'Recipe' ),
        plural: __wprm( 'Recipes' ),
    },
    bulkEdit: {
        route: 'trash',
        type: 'trash',
    },
    createButton: false,
    selectedColumns: false,
    columns: ColumnsTrash,
};
    
datatables.ingredient = {
    parent: __wprm( 'Recipe Fields' ),
    id: 'ingredient',
    route: 'taxonomy',
    label: {
        singular: __wprm( 'Ingredient' ),
        plural: __wprm( 'Ingredients' ),
    },
    bulkEdit: {
        route: 'taxonomy',
        type: 'ingredient',
    },
    createButton: (datatable) => {
        let name = prompt( __wprm( 'What do you want to be the name of this new ingredient?' ) );
        if( name && name.trim() ) {
            Api.manage.createTerm('ingredient', name).then((data) => {
                if ( ! data ) {
                    alert( __wprm( 'We were not able to create this ingredient. Make sure it does not exist yet.' ) );
                } else {
                    datatable.refreshData();
                }
            });
        }
    },
    selectedColumns: false,
    columns: ColumnsTaxonomies,
};

datatables.ingredientUnit = {
    parent: __wprm( 'Recipe Fields' ),
    id: 'ingredient_unit',
    route: 'taxonomy',
    label: {
        singular: __wprm( 'Ingredient Unit' ),
        plural: __wprm( 'Ingredient Units' ),
    },
    bulkEdit: {
        route: 'taxonomy',
        type: 'ingredient_unit',
    },
    createButton: (datatable) => {
        let name = prompt( __wprm( 'What do you want to be the name of this new unit?' ) );
        if( name && name.trim() ) {
            Api.manage.createTerm('ingredient_unit', name).then((data) => {
                if ( ! data ) {
                    alert( __wprm( 'We were not able to create this unit. Make sure it does not exist yet.' ) );
                } else {
                    datatable.refreshData();
                }
            });
        }
    },
    selectedColumns: false,
    columns: ColumnsUnits,
};

datatables.equipment = {
    parent: __wprm( 'Recipe Fields' ),
    id: 'equipment',
    route: 'taxonomy',
    label: {
        singular: __wprm( 'Equipment' ),
        plural: __wprm( 'Equipment' ),
    },
    bulkEdit: {
        route: 'taxonomy',
        type: 'equipment',
    },
    createButton: (datatable) => {
        let name = prompt( __wprm( 'What do you want to be the name of this new equipment?' ) );
        if( name && name.trim() ) {
            Api.manage.createTerm('equipment', name).then((data) => {
                if ( ! data ) {
                    alert( __wprm( 'We were not able to create this equipment. Make sure it does not exist yet.' ) );
                } else {
                    datatable.refreshData();
                }
            });
        }
    },
    selectedColumns: false,
    columns: ColumnsTaxonomies,
};

// Taxonomies.
Object.keys(wprm_admin_manage.taxonomies).map((taxonomy) => {
    const labels = wprm_admin_manage.taxonomies[ taxonomy ];
    const id = taxonomy.substr(5);

    if ( 'suitablefordiet' === id ) {
        datatables[ 'tag_' + id ] = {
            parent: __wprm( 'Recipe Fields' ),
            id,
            route: 'taxonomy',
            label: {
                singular: labels.singular_name,
                plural: labels.name,
            },
            bulkEdit: false,
            createButton: false,
            selectedColumns: ['id','name','label','count'],
            columns: ColumnsTaxonomies,
        }
    } else {
        datatables[ 'tag_' + id ] = {
            parent: __wprm( 'Recipe Fields' ),
            id,
            route: 'taxonomy',
            label: {
                singular: labels.singular_name,
                plural: labels.name,
            },
            bulkEdit: {
                route: 'taxonomy',
                type: id,
            },
            createButton: (datatable) => {
                let name = prompt( __wprm( 'What do you want to be the name of this new term?' ) );
                if( name && name.trim() ) {
                    Api.manage.createTerm(id, name).then((data) => {
                        if ( ! data ) {
                            alert( __wprm( 'We were not able to create this term. Make sure it does not exist yet.' ) );
                        } else {
                            datatable.refreshData();
                            wprm_admin_modal.categories[ id ].terms.push({
                                term_id: data.id,
                                name: data.name,
                                count: 0,
                            });
                        }
                    });
                }
            },
            selectedColumns: ['id','name','count'],
            columns: ColumnsTaxonomies,
        }
    }
});

datatables.taxonomies = {
    parent: __wprm( 'Your Custom Fields' ),
    id: 'taxonomies',
    route: 'taxonomies',
    label: {
        singular: __wprm( 'Recipe Taxonomy' ),
        plural: __wprm( 'Recipe Taxonomies' ),
    },
    bulkEdit: false,
    selectedColumns: false,
    columns: ColumnsCustomTaxonomies,
};

if ( wprm_admin.addons.premium ) {
    datatables.taxonomies.createButton = (datatable) => {
        WPRM_Modal.open( 'taxonomy', {
            saveCallback: () => datatable.refreshData(),
        } );
    };
}

datatables['custom-fields'] = {
    required: 'pro',
    parent: __wprm( 'Your Custom Fields' ),
    id: 'custom-fields',
    label: {
        singular: __wprm( 'Custom Field' ),
        plural: __wprm( 'Custom Fields' ),
    },
};

datatables.nutrition = {
    required: 'pro',
    parent: __wprm( 'Your Custom Fields' ),
    id: 'nutrition_ingredient',
    label: {
        singular: __wprm( 'Custom Nutrition Ingredient' ),
        plural: __wprm( 'Custom Nutrition' ),
    },
};

datatables.nutrients = {
    required: 'premium',
    parent: __wprm( 'Your Custom Fields' ),
    id: 'nutrition_ingredient',
    label: {
        singular: __wprm( 'Custom Nutrient' ),
        plural: __wprm( 'Custom Nutrients' ),
    },
};

datatables.rating = {
    parent: __wprm( 'Features' ),
    id: 'rating',
    route: 'rating',
    label: {
        singular: __wprm( 'Rating' ),
        plural: __wprm( 'Ratings' ),
    },
    bulkEdit: {
        route: 'rating',
        type: 'rating',
    },
    createButton: false,
    selectedColumns: ['date','rating','type', 'user_id','ip'],
    columns: ColumnsRatings,
}

datatables.collections = {
    required: 'elite',
    parent: __wprm( 'Features' ),
    id: 'collections',
    label: {
        singular: __wprm( 'Saved Collection' ),
        plural: __wprm( 'Saved Collections' ),
    },
};

datatables['user-collections'] = {
    required: 'elite',
    parent: __wprm( 'Features' ),
    id: 'user-collections',
    label: {
        singular: __wprm( 'User Collection' ),
        plural: __wprm( 'User Collections' ),
    },
};

datatables['recipe-submission'] = {
    required: 'elite',
    parent: __wprm( 'Features' ),
    title: __wprm( 'Recipe Submissions' ),
    id: 'recipe-submission',
    label: {
        singular: __wprm( 'Recipe Submission' ),
        plural: __wprm( 'Recipe Submissions' ),
    },
};

datatables.analytics = {
    parent: __wprm( 'Analytics' ),
    title: __wprm( 'Overview' ),
    id: 'analytics',
    route: 'analytics',
    label: {
        singular: __wprm( 'Action' ),
        plural: __wprm( 'Actions' ),
    },
    bulkEdit: {
        route: 'analytics',
        type: 'analytics',
    },
    createButton: false,
    selectedColumns: ['created_at','type','recipe_id','post_id','user_id'],
    columns: ColumnsAnalytics,
};

export default datatables;
