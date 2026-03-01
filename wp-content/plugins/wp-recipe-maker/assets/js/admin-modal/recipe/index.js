import React, { Component } from 'react';
import { scroller } from 'react-scroll';

import '../../../css/admin/modal/recipe.scss';

import Api from 'Shared/Api';
import { __wprm } from 'Shared/Translations';

import EditRecipe from './edit';

const isEnabledSetting = ( value, defaultValue = true ) => {
    if ( 'undefined' === typeof value || null === value ) {
        return defaultValue;
    }

    if ( 'boolean' === typeof value ) {
        return value;
    }

    if ( 'number' === typeof value ) {
        return 0 !== value;
    }

    if ( 'string' === typeof value ) {
        const normalized = value.trim().toLowerCase();

        if ( [ '0', 'false', 'off', 'no', '' ].includes( normalized ) ) {
            return false;
        }

        if ( [ '1', 'true', 'on', 'yes' ].includes( normalized ) ) {
            return true;
        }
    }

    return !! value;
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
                        historyPast: [],
                        historyFuture: [],
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
            historyPast: [],
            historyFuture: [],
            historyLimitNotice: false,
        };
        this.historyEnabled = isEnabledSetting( wprm_admin.settings.recipe_modal_undo_redo_history, true );

        this.historyDebounceTimer = null;
        this.historyDebounceOpen = false;
        this.historyDebounceKey = false;
        this.historyPendingSnapshot = false;
        this.historyPendingKey = false;
        this.historyMaxSteps = 20;
        this.historyLimitNoticeTimer = null;
        this.historyLimitReachedInUpdate = false;

        // Bind functions.
        this.scrollToGroup = this.scrollToGroup.bind(this);
        this.onRecipeChange = this.onRecipeChange.bind(this);
        this.onImportJSON = this.onImportJSON.bind(this);
        this.saveRecipe = this.saveRecipe.bind(this);
        this.setUids = this.setUids.bind(this);
        this.allowCloseModal = this.allowCloseModal.bind(this);
        this.changesMade = this.changesMade.bind(this);
        this.onUndo = this.onUndo.bind(this);
        this.onRedo = this.onRedo.bind(this);
        this.resetHistory = this.resetHistory.bind(this);
        this.showHistoryLimitNotice = this.showHistoryLimitNotice.bind(this);
    }

    componentDidMount() {
        if ( ! this.state.loadingRecipe ) {
            this.scrollToGroup();
        }
    }

    componentWillUnmount() {
        this.closeHistoryDebounceWindow();

        if ( this.historyLimitNoticeTimer ) {
            clearTimeout( this.historyLimitNoticeTimer );
            this.historyLimitNoticeTimer = null;
        }
    }

    cloneRecipe(recipe) {
        return JSON.parse(JSON.stringify(recipe));
    }

    isRecipeEqual(a, b) {
        if ( typeof window.lodash !== 'undefined' ) {
            return window.lodash.isEqual(a, b);
        }

        return JSON.stringify(a) === JSON.stringify(b);
    }

    scheduleHistoryDebounceClose() {
        // Typing history windows are closed explicitly on blur/boundary.
    }

    closeHistoryDebounceWindow() {
        if ( this.historyDebounceTimer ) {
            clearTimeout(this.historyDebounceTimer);
            this.historyDebounceTimer = null;
        }

        this.historyDebounceOpen = false;
        this.historyDebounceKey = false;
        this.historyPendingSnapshot = false;
        this.historyPendingKey = false;
    }

    resetHistory() {
        this.closeHistoryDebounceWindow();

        this.setState({
            historyPast: [],
            historyFuture: [],
        });
    }

    normalizeOnRecipeChangeOptions(optionsOrForceRerender = false) {
        if ( 'boolean' === typeof optionsOrForceRerender ) {
            return {
                forceRerender: optionsOrForceRerender,
                historyMode: 'immediate',
                historyBoundary: false,
                historyKey: false,
                resetHistory: false,
            };
        }

        if ( ! optionsOrForceRerender || 'object' !== typeof optionsOrForceRerender ) {
            return {
                forceRerender: false,
                historyMode: 'immediate',
                historyBoundary: false,
                historyKey: false,
                resetHistory: false,
            };
        }

        return {
            forceRerender: !! optionsOrForceRerender.forceRerender,
            historyMode: 'debounced' === optionsOrForceRerender.historyMode ? 'debounced' : 'immediate',
            historyBoundary: !! optionsOrForceRerender.historyBoundary,
            historyKey: optionsOrForceRerender.historyKey || false,
            resetHistory: !! optionsOrForceRerender.resetHistory,
        };
    }

    trimHistoryPast(historyPast) {
        if ( historyPast.length > this.historyMaxSteps ) {
            this.historyLimitReachedInUpdate = true;
            return historyPast.slice( historyPast.length - this.historyMaxSteps );
        }

        return historyPast;
    }

    showHistoryLimitNotice() {
        if ( this.historyLimitNoticeTimer ) {
            clearTimeout( this.historyLimitNoticeTimer );
            this.historyLimitNoticeTimer = null;
        }

        this.setState({
            historyLimitNotice: `${ __wprm( 'Undo history limit reached' ) } (${ this.historyMaxSteps }). ${ __wprm( 'Oldest step removed.' ) }`,
        });

        this.historyLimitNoticeTimer = setTimeout(() => {
            this.setState({
                historyLimitNotice: false,
            });
            this.historyLimitNoticeTimer = null;
        }, 4000 );
    }

    pushHistorySnapshot(historyPast, snapshot) {
        if ( ! snapshot ) {
            return historyPast;
        }

        const lastHistoryRecipe = historyPast.length ? historyPast[ historyPast.length - 1 ] : false;
        if ( ! lastHistoryRecipe || ! this.isRecipeEqual(lastHistoryRecipe, snapshot) ) {
            historyPast = historyPast.concat([this.cloneRecipe(snapshot)]);
        }

        return this.trimHistoryPast(historyPast);
    }

    commitPendingSnapshot(historyPast) {
        if ( this.historyPendingSnapshot ) {
            historyPast = this.pushHistorySnapshot(historyPast, this.historyPendingSnapshot);
        }

        this.historyPendingSnapshot = false;
        this.historyPendingKey = false;
        this.historyDebounceOpen = false;
        this.historyDebounceKey = false;

        return historyPast;
    }

    recordHistoryBeforeChange(prevState, { mode = 'immediate', historyKey = false, historyBoundary = false, recipeChanged = false } = {}) {
        if ( ! prevState.recipe ) {
            return {
                historyPast: prevState.historyPast,
                historyFuture: prevState.historyFuture,
            };
        }

        let historyPast = prevState.historyPast;
        const currentRecipeSnapshot = this.cloneRecipe(prevState.recipe);

        if ( 'debounced' === mode ) {
            const pendingKey = historyKey || '__default__';

            if ( historyBoundary ) {
                if ( this.historyPendingSnapshot ) {
                    historyPast = this.commitPendingSnapshot(historyPast);
                } else if ( recipeChanged ) {
                    historyPast = this.pushHistorySnapshot(historyPast, currentRecipeSnapshot);
                }
            } else {
                const hasPendingSnapshot = !! this.historyPendingSnapshot;
                const samePendingKey = pendingKey === this.historyPendingKey;

                if ( hasPendingSnapshot && ! samePendingKey ) {
                    historyPast = this.commitPendingSnapshot(historyPast);
                }

                if ( ! this.historyPendingSnapshot ) {
                    this.historyPendingSnapshot = currentRecipeSnapshot;
                    this.historyPendingKey = pendingKey;
                    this.historyDebounceOpen = true;
                    this.historyDebounceKey = pendingKey;
                }
            }
        } else {
            historyPast = this.commitPendingSnapshot(historyPast);
            historyPast = this.pushHistorySnapshot(historyPast, currentRecipeSnapshot);
        }

        return {
            historyPast: this.trimHistoryPast(historyPast),
            historyFuture: [],
        };
    }

    blurActiveElement() {
        if ( document && document.activeElement && 'function' === typeof document.activeElement.blur ) {
            document.activeElement.blur();
        }
    }

    onUndo() {
        if ( ! this.historyEnabled ) {
            return;
        }

        this.blurActiveElement();
        this.closeHistoryDebounceWindow();
        this.historyLimitReachedInUpdate = false;

        this.setState((prevState) => {
            if ( ! prevState.recipe || ! prevState.historyPast.length ) {
                return null;
            }

            const restoredRecipe = prevState.historyPast[ prevState.historyPast.length - 1 ];

            return {
                recipe: this.cloneRecipe(restoredRecipe),
                historyPast: prevState.historyPast.slice(0, -1),
                historyFuture: this.trimHistoryPast(prevState.historyFuture.concat([ this.cloneRecipe(prevState.recipe) ])),
                forceRerender: prevState.forceRerender + 1,
            };
        }, () => {
            if ( this.historyLimitReachedInUpdate ) {
                this.showHistoryLimitNotice();
            }
        });
    }

    onRedo() {
        if ( ! this.historyEnabled ) {
            return;
        }

        this.blurActiveElement();
        this.closeHistoryDebounceWindow();
        this.historyLimitReachedInUpdate = false;

        this.setState((prevState) => {
            if ( ! prevState.recipe || ! prevState.historyFuture.length ) {
                return null;
            }

            const restoredRecipe = prevState.historyFuture[ prevState.historyFuture.length - 1 ];

            return {
                recipe: this.cloneRecipe(restoredRecipe),
                historyPast: this.trimHistoryPast(prevState.historyPast.concat([ this.cloneRecipe(prevState.recipe) ])),
                historyFuture: prevState.historyFuture.slice(0, -1),
                forceRerender: prevState.forceRerender + 1,
            };
        }, () => {
            if ( this.historyLimitReachedInUpdate ) {
                this.showHistoryLimitNotice();
            }
        });
    }


    scrollToGroup( group = 'media' ) {
        scroller.scrollTo( `wprm-admin-modal-fields-group-${ group }`, {
            containerId: 'wprm-admin-modal-recipe-content',
            offset: -10,
        } );
    }

    onRecipeChange(fields, optionsOrForceRerender = false) {
        const options = this.normalizeOnRecipeChangeOptions(optionsOrForceRerender);
        this.historyLimitReachedInUpdate = false;

        if ( options.resetHistory ) {
            this.closeHistoryDebounceWindow();
        }

        this.setState((prevState) => {
            const resolvedFields = 'function' === typeof fields ? fields(prevState.recipe) : fields;

            if ( ! resolvedFields || 'object' !== typeof resolvedFields ) {
                if ( this.historyEnabled && options.historyBoundary && 'debounced' === options.historyMode ) {
                    return {
                        ...this.recordHistoryBeforeChange(prevState, {
                            mode: options.historyMode,
                            historyKey: options.historyKey,
                            historyBoundary: true,
                            recipeChanged: false,
                        }),
                    };
                }

                if ( options.forceRerender ) {
                    return {
                        forceRerender: prevState.forceRerender + 1,
                    };
                }

                return null;
            }

            const nextRecipe = {
                ...prevState.recipe,
                ...resolvedFields,
            };
            const recipeChanged = ! this.isRecipeEqual(prevState.recipe, nextRecipe);
            const shouldCommitDebouncedBoundary = this.historyEnabled
                && ! recipeChanged
                && options.historyBoundary
                && 'debounced' === options.historyMode;

            if ( ! recipeChanged && ! options.forceRerender && ! options.resetHistory && ! shouldCommitDebouncedBoundary ) {
                return null;
            }

            const historyState = ! this.historyEnabled
                ? {
                    historyPast: [],
                    historyFuture: [],
                    historyLimitNotice: false,
                }
                : options.resetHistory
                ? {
                    historyPast: [],
                    historyFuture: [],
                }
                : recipeChanged || shouldCommitDebouncedBoundary
                    ? this.recordHistoryBeforeChange(prevState, {
                        mode: options.historyMode,
                        historyKey: options.historyKey,
                        historyBoundary: options.historyBoundary,
                        recipeChanged,
                    })
                    : {
                        historyPast: prevState.historyPast,
                        historyFuture: prevState.historyFuture,
                    };

            return {
                recipe: nextRecipe,
                ...historyState,
                ...(options.forceRerender && { forceRerender: prevState.forceRerender + 1 }),
            };
        }, () => {
            if ( options.historyBoundary ) {
                this.closeHistoryDebounceWindow();
            }

            if ( this.historyLimitReachedInUpdate ) {
                this.showHistoryLimitNotice();
            }
        });
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

        this.onRecipeChange(fields, {
            forceRerender: true,
            historyMode: 'immediate',
            historyBoundary: true,
            historyKey: 'import:json',
        });
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
                historyEnabled={ this.historyEnabled }
                canUndo={ this.state.historyPast.length > 0 }
                canRedo={ this.state.historyFuture.length > 0 }
                undoCount={ this.state.historyPast.length }
                redoCount={ this.state.historyFuture.length }
                onUndo={ this.onUndo }
                onRedo={ this.onRedo }
                historyLimitNotice={ this.state.historyLimitNotice }
            />
        );
    }
}
