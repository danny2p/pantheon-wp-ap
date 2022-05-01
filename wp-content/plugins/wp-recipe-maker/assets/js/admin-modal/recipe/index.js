import React, { Component } from 'react';
import { scroller } from 'react-scroll';

import '../../../css/admin/modal/recipe.scss';

import Api from 'Shared/Api';
import { __wprm } from 'Shared/Translations';
const { hooks } = WPRecipeMaker['wp-recipe-maker/dist/shared'];

import EditRecipe from './edit';
import TextImport from './text-import';

const modalContent = {
    'text-import': TextImport,
    recipe: EditRecipe,
};
export default class Recipe extends Component {
    constructor(props) {
        super(props);

        let recipe = JSON.parse( JSON.stringify( wprm_admin_modal.recipe ) );
        let loadingRecipe = false;

        if ( props.args.hasOwnProperty( 'recipe' ) ) {
            recipe = JSON.parse( JSON.stringify( props.args.recipe ) );
        } else if ( props.args.hasOwnProperty( 'recipeId' ) ) {
            loadingRecipe = true;
            Api.recipe.get(props.args.recipeId).then((data) => {
                if ( data ) {
                    const recipe = JSON.parse( JSON.stringify( data.recipe ) );

                    if ( props.args.cloneRecipe ) {
                        delete recipe.id;
                    }

                    this.setState({
                        recipe,
                        originalRecipe: props.args.cloneRecipe || props.args.restoreRevision ? {} : JSON.parse( JSON.stringify( recipe ) ),
                        loadingRecipe: false,
                        mode: 'recipe',
                    });

                    this.scrollToGroup();
                } else {
                    // Loading recipe failed.
                    this.setState({
                        loadingRecipe: false,
                    });
                }
            });
        }

        this.state = {
            recipe,
            originalRecipe: props.args.cloneRecipe || props.args.restoreRevision ? {} : JSON.parse( JSON.stringify( recipe ) ),
            savingChanges: false,
            saveResult: false,
            loadingRecipe,
            forceRerender: 0,
            mode: 'recipe',
        };

        // Bind functions.
        this.scrollToGroup = this.scrollToGroup.bind(this);
        this.onModeChange = this.onModeChange.bind(this);
        this.onRecipeChange = this.onRecipeChange.bind(this);
        this.onImportJSON = this.onImportJSON.bind(this);
        this.saveRecipe = this.saveRecipe.bind(this);
        this.allowCloseModal = this.allowCloseModal.bind(this);
        this.changesMade = this.changesMade.bind(this);
    }

    componentDidMount() {
        if ( 'recipe' === this.state.mode && ! this.state.loadingRecipe ) {
            this.scrollToGroup();
        }
    }

    onModeChange( mode, args = false ) {
        let newState = {
            mode,
        };

        if ( 'text-import' === mode ) {
            newState['textImportText'] = args;
        }
        
        this.setState(newState, () => {
            if ( 'recipe' === mode ) {
                args = args ? args : 'media';
                this.scrollToGroup( args );
            }
        });
    }

    scrollToGroup( group = 'media' ) {
        scroller.scrollTo( `wprm-admin-modal-fields-group-${ group }`, {
            containerId: 'wprm-admin-modal-recipe-content',
            offset: -10,
        } );
    }

    onRecipeChange(fields) {
        this.setState((prevState) => ({
            recipe: {
                ...prevState.recipe,
                ...fields,
            }
        }));
    }

    onImportJSON(fields) {
        // Ignore ID.
        delete fields.id;

        this.setState((prevState) => ({
            recipe: {
                ...prevState.recipe,
                ...fields,
            },
            forceRerender: prevState.forceRerender + 1,
        }));
    }

    saveRecipe( closeAfter = false ) {
        if ( ! this.state.savingChanges ) {
            const savingTimeout = setTimeout(() => {
                this.setState({
                    saveResult: 'waiting',
                });
            }, 5000 );

            this.setState({
                savingChanges: true,
                saveResult: false,
            }, () => {    
                Api.recipe.save(this.state.recipe).then((data) => {
                    clearTimeout( savingTimeout );

                    if ( data && data.recipe ) {
                        const recipe = JSON.parse( JSON.stringify( data.recipe ) );
                        this.setState((prevState) => ({
                            recipe,
                            originalRecipe: JSON.parse( JSON.stringify( recipe ) ),
                            savingChanges: false,
                            saveResult: 'ok',
                            forceRerender: prevState.forceRerender + 1,
                        }), () => {
                            if ( 'function' === typeof this.props.args.saveCallback ) {
                                this.props.args.saveCallback( recipe );
                            }
                            if ( closeAfter ) {
                                this.props.maybeCloseModal();
                            }
                            
                            // Show save OK message for 3 seconds.
                            setTimeout(() => {
                                if ( 'ok' === this.state.saveResult ) {
                                    this.setState({
                                        saveResult: false,
                                    });
                                }
                            }, 3000);
                        });
                    } else {
                        this.setState({
                            savingChanges: false,
                            saveResult: 'failed',
                        });
                    }
                });
            });
        }
    }

    allowCloseModal() {
        switch ( this.state.mode ) {
            case 'nutrition-calculation':
                if ( confirm( __wprm( 'Are you sure you want to stop calculating the nutrition facts?' ) ) ) {
                    this.onModeChange( 'recipe', 'nutrition' );
                }
                return false;
            case 'equipment-affiliate':
                this.onModeChange( 'recipe', 'equipment' );
                return false;
            case 'ingredient-links':
                this.onModeChange( 'recipe', 'ingredients' );
                return false;
            case 'text-import':
                this.onModeChange( 'recipe' );
                return false;
        }

        // Closing recipe itself.
        return ! this.state.savingChanges && ( ! this.changesMade() || confirm( __wprm( 'Are you sure you want to close without saving changes?' ) ) );
    }

    changesMade() {
        if ( typeof window.lodash !== 'undefined' ) {
            return ! window.lodash.isEqual( this.state.recipe, this.state.originalRecipe );
        } else {
            return JSON.stringify( this.state.recipe ) !== JSON.stringify( this.state.originalRecipe );
        }
    }

    render() {
        const allModalContent = hooks.applyFilters( 'modalRecipe', modalContent );
        const Content = allModalContent.hasOwnProperty(this.state.mode) ? allModalContent[this.state.mode] : false;

        if ( ! Content ) {
            return null;
        }

        switch ( this.state.mode ) {
            case 'nutrition-calculation':
                return (
                    <Content
                        onCloseModal={ this.props.maybeCloseModal }
                        name={ this.state.recipe.name }
                        servings={ this.state.recipe.servings }
                        ingredients={ this.state.recipe.ingredients_flat }
                        onCancel={() => {
                            this.onModeChange( 'recipe', 'nutrition' );
                        }}
                        onNutritionChange={ (calculated) => {
                            let nutrition = {};

                            Object.keys(wprm_admin_modal.nutrition).map((nutrient, index ) => {
                                if ( calculated.hasOwnProperty( nutrient ) ) {
                                    nutrition[ nutrient ] = calculated[ nutrient ];
                                } else {
                                    nutrition[ nutrient ] = false;
                                }
                            });

                            // Keep serving size and unit.
                            nutrition['serving_size'] = this.state.recipe.hasOwnProperty( 'nutrition' ) && this.state.recipe.nutrition.hasOwnProperty( 'serving_size' ) ? this.state.recipe.nutrition.serving_size : false;
                            nutrition['serving_unit'] = this.state.recipe.hasOwnProperty( 'nutrition' ) && this.state.recipe.nutrition.hasOwnProperty( 'serving_unit' ) ? this.state.recipe.nutrition.serving_unit : false;

                            // Overwrite recipe nutrition.
                            this.onRecipeChange({
                                nutrition,
                            });
                            this.onModeChange( 'recipe', 'nutrition' );
                        }}
                    />
                );
            case 'equipment-affiliate':
                return (
                    <Content
                        onCloseModal={ this.props.maybeCloseModal }
                        onCancel={() => {
                            this.onModeChange( 'recipe', 'equipment' );
                        }}
                        equipment={ this.state.recipe.equipment }
                        onEquipmentChange={ (equipment) => {
                            this.onRecipeChange({
                                equipment,
                            });
                            this.onModeChange( 'recipe', 'equipment' );
                        }}
                    />
                );
            case 'ingredient-links':
                return (
                    <Content
                        onCloseModal={ this.props.maybeCloseModal }
                        onCancel={() => {
                            this.onModeChange( 'recipe', 'ingredients' );
                        }}
                        ingredients={ this.state.recipe.ingredients_flat }
                        onIngredientsChange={ (ingredients_flat) => {
                            this.onRecipeChange({
                                ingredients_flat,
                            });
                            this.onModeChange( 'recipe', 'ingredients' );
                        }}
                    />
                );
            case 'text-import':
                return (
                    <Content
                        onCloseModal={ this.props.maybeCloseModal }
                        onCancel={() => {
                            this.onModeChange( 'recipe' );
                        }}
                        text={ this.state.textImportText }
                        recipe={ this.state.recipe }
                        onImportValues={ ( newRecipe ) => {
                            this.onRecipeChange( newRecipe );
                            this.onModeChange( 'recipe' );
                        } }
                    />
                );
            default:
                return (
                    <Content
                        onCloseModal={ this.props.maybeCloseModal }
                        changesMade={ this.changesMade() }
                        savingChanges={ this.state.savingChanges }
                        saveResult={ this.state.saveResult }
                        loadingRecipe={ this.state.loadingRecipe }
                        recipe={ this.state.recipe }
                        onRecipeChange={ this.onRecipeChange }
                        onImportJSON={ this.onImportJSON }
                        saveRecipe={ this.saveRecipe }
                        forceRerender={ this.state.forceRerender }
                        onModeChange={this.onModeChange}
                    />
                );
        }
    }
}