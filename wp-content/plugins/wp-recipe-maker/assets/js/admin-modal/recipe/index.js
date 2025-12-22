import React, { Component } from 'react';
import { scroller } from 'react-scroll';

import '../../../css/admin/modal/recipe.scss';

import Api from 'Shared/Api';
import { __wprm } from 'Shared/Translations';
const { hooks } = WPRecipeMakerAdmin['wp-recipe-maker/dist/shared'];

import EditRecipe from './edit';
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
        };

        // Bind functions.
        this.scrollToGroup = this.scrollToGroup.bind(this);
        this.onRecipeChange = this.onRecipeChange.bind(this);
        this.onImportJSON = this.onImportJSON.bind(this);
        this.saveRecipe = this.saveRecipe.bind(this);
        this.setUids = this.setUids.bind(this);
        this.allowCloseModal = this.allowCloseModal.bind(this);
        this.changesMade = this.changesMade.bind(this);
    }

    componentDidMount() {
        if ( ! this.state.loadingRecipe ) {
            this.scrollToGroup();
        }
    }


    scrollToGroup( group = 'media' ) {
        scroller.scrollTo( `wprm-admin-modal-fields-group-${ group }`, {
            containerId: 'wprm-admin-modal-recipe-content',
            offset: -10,
        } );
    }

    onRecipeChange(fields, forceRerender = false) {
        this.setState((prevState) => ({
            recipe: {
                ...prevState.recipe,
                ...fields,
            },
            ...(forceRerender && { forceRerender: prevState.forceRerender + 1 })
        }));
    }

    onImportJSON(fields) {
        // If fields is an array, use the the first object.
        if ( Array.isArray( fields ) ) {
            fields = fields[0];
        }

        // Check if we now have a single recipe object.
        if ( typeof fields !== 'object' || fields === null || Array.isArray( fields ) ) {
            // Throw error if not an object.
            throw new Error('Invalid recipe object');
        }

        // Ignore ID and fields that might be coming from the JSON export feature.
        delete fields.id;
        delete fields.parent;
        delete fields.user_ratings;

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

    setUids( currentValues, valuesToAdd ) {
        // Give unique UID.
        let maxUid = Math.max.apply( Math, currentValues.map( function(field) { return field.uid; } ) );
        maxUid = maxUid < 0 ? -1 : maxUid;

        let valuesWithUid = [];
        for ( let valueToAdd of valuesToAdd ) {
            maxUid++;
            valueToAdd.uid = maxUid;
            valuesWithUid.push( valueToAdd );
        }

        return valuesWithUid;
    }

    allowCloseModal() {
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
        return (
            <EditRecipe
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
                openSecondaryModal={ this.props.openSecondaryModal }
                setUids={ this.setUids }
                scrollToGroup={ this.scrollToGroup }
            />
        );
    }
}