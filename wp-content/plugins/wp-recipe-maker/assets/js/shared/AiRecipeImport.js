import React, { useEffect, useState } from 'react';

import '../../css/admin/modal/ai-text-import.scss';

import Api from './Api';
import Loader from './Loader';
import { __wprm } from './Translations';
import { convertTermNamesToObjects } from './CategoryTerms';

import FieldContainer from '../admin-modal/fields/FieldContainer';
import FieldTextarea from '../admin-modal/fields/FieldTextarea';

const formatTime = ( minutes ) => {
    if ( ! minutes ) {
        return '';
    }

    const mins = parseInt( minutes );

    if ( mins < 60 ) {
        return `${ mins } min`;
    }

    const hours = Math.floor( mins / 60 );
    const remainingMins = mins % 60;

    return remainingMins > 0 ? `${ hours } hr ${ remainingMins } min` : `${ hours } hr`;
};

const getTagName = ( tag ) => {
    if ( 'string' === typeof tag ) {
        return tag.trim();
    }

    if ( tag && 'object' === typeof tag ) {
        if ( 'string' === typeof tag.name ) {
            return tag.name.trim();
        }

        if ( 'string' === typeof tag.term_id ) {
            return tag.term_id.trim();
        }
    }

    return '';
};

const normalizeRecipeTags = ( recipe ) => {
    if ( ! recipe || ! recipe.tags || 'object' !== typeof recipe.tags ) {
        return recipe;
    }

    const normalizedTags = Object.keys( recipe.tags ).reduce( ( tags, key ) => {
        const values = Array.isArray( recipe.tags[ key ] ) ? recipe.tags[ key ] : [];
        const normalizedValues = [];
        const namesToConvert = [];

        values.forEach( ( value ) => {
            if ( value && 'object' === typeof value && value.name ) {
                normalizedValues.push( value );
                return;
            }

            const tagName = getTagName( value );

            if ( tagName ) {
                namesToConvert.push( tagName );
            }
        } );

        if ( namesToConvert.length ) {
            const convertedValues = convertTermNamesToObjects( key, namesToConvert );

            if ( convertedValues.length ) {
                normalizedValues.push( ...convertedValues );
            } else {
                normalizedValues.push( ...namesToConvert.map( ( name ) => ( {
                    term_id: name,
                    name,
                } ) ) );
            }
        }

        tags[ key ] = normalizedValues;
        return tags;
    }, {} );

    return {
        ...recipe,
        tags: normalizedTags,
    };
};

const getTagItems = ( recipe ) => {
    if ( ! recipe || ! recipe.tags || 'object' !== typeof recipe.tags ) {
        return [];
    }

    const labels = {
        course: __wprm( 'Courses' ),
        cuisine: __wprm( 'Cuisines' ),
        keyword: __wprm( 'Keywords' ),
        difficulty: __wprm( 'Difficulty' ),
    };

    return Object.keys( labels ).map( ( key ) => {
        const value = Array.isArray( recipe.tags[ key ] )
            ? recipe.tags[ key ].map( getTagName ).filter( Boolean )
            : [];

        if ( ! value.length ) {
            return false;
        }

        return {
            key,
            label: labels[ key ],
            value: value.join( ', ' ),
        };
    }).filter( Boolean );
};

const IngredientPreview = ( props ) => {
    const ingredients = props.ingredients || [];

    if ( ! ingredients.length ) {
        return null;
    }

    return (
        <div className="wprm-ai-preview-section">
            <h4>{ __wprm( 'Ingredients' ) }</h4>
            <ul className="wprm-ai-preview-ingredients">
                { ingredients.map( ( ingredient, index ) => {
                    if ( 'group' === ingredient.type ) {
                        return (
                            <li key={ index }>
                                <strong>{ ingredient.name }</strong>
                            </li>
                        );
                    }

                    return (
                        <li key={ index }>
                            { ( ingredient.amount || ingredient.unit ) && (
                                <span className="wprm-ai-preview-ingredient-amount">
                                    { [ ingredient.amount, ingredient.unit ].filter( Boolean ).join( ' ' ) }
                                </span>
                            ) }
                            <span className="wprm-ai-preview-ingredient-name">{ ingredient.name }</span>
                            { ingredient.notes && (
                                <span className="wprm-ai-preview-ingredient-notes">({ ingredient.notes })</span>
                            ) }
                        </li>
                    );
                } ) }
            </ul>
        </div>
    );
};

const InstructionPreview = ( props ) => {
    const instructions = props.instructions || [];

    if ( ! instructions.length ) {
        return null;
    }

    return (
        <div className="wprm-ai-preview-section">
            <h4>{ __wprm( 'Instructions' ) }</h4>
            <ol className="wprm-ai-preview-instructions">
                { instructions.map( ( instruction, index ) => {
                    if ( 'group' === instruction.type ) {
                        return (
                            <li key={ index }>
                                <strong>{ instruction.name }</strong>
                            </li>
                        );
                    }

                    if ( 'tip' === instruction.type ) {
                        return (
                            <li key={ index }>
                                <strong>{ __wprm( 'Tip' ) }:</strong> { instruction.text }
                            </li>
                        );
                    }

                    return <li key={ index }>{ instruction.text }</li>;
                } ) }
            </ol>
        </div>
    );
};

const EquipmentPreview = ( props ) => {
    const equipment = props.equipment || [];

    if ( ! equipment.length ) {
        return null;
    }

    return (
        <div className="wprm-ai-preview-section">
            <h4>{ __wprm( 'Equipment' ) }</h4>
            <ul className="wprm-ai-preview-ingredients">
                { equipment.map( ( item, index ) => (
                    <li key={ index }>
                        { item.amount && <span className="wprm-ai-preview-ingredient-amount">{ item.amount }</span> }
                        <span className="wprm-ai-preview-ingredient-name">{ item.name }</span>
                        { item.notes && <span className="wprm-ai-preview-ingredient-notes">({ item.notes })</span> }
                    </li>
                ) ) }
            </ul>
        </div>
    );
};

export const AIRecipeImportPreview = ( props ) => {
    const recipe = props.recipe;

    if ( ! recipe ) {
        return null;
    }

    const ingredients = Array.isArray( recipe.ingredients_flat ) ? recipe.ingredients_flat : [];
    const instructions = Array.isArray( recipe.instructions_flat ) ? recipe.instructions_flat : [];
    const equipment = Array.isArray( recipe.equipment ) ? recipe.equipment.filter( ( item ) => item && item.name ) : [];
    const summary = recipe.summary ? recipe.summary : '';
    const notes = recipe.notes ? recipe.notes : '';
    const servings = recipe.servings ? `${ recipe.servings }${ recipe.servings_unit ? ` ${ recipe.servings_unit }` : '' }` : '';
    const tagItems = getTagItems( recipe );
    const timeItems = [
        recipe.prep_time ? {
            label: __wprm( 'Prep Time' ),
            value: formatTime( recipe.prep_time ),
        } : false,
        recipe.cook_time ? {
            label: __wprm( 'Cook Time' ),
            value: formatTime( recipe.cook_time ),
        } : false,
        recipe.total_time ? {
            label: __wprm( 'Total Time' ),
            value: formatTime( recipe.total_time ),
        } : false,
        recipe.custom_time ? {
            label: recipe.custom_time_label || __wprm( 'Custom Time' ),
            value: formatTime( recipe.custom_time ),
        } : false,
    ].filter( Boolean );

    return (
        <div className="wprm-admin-modal-ai-text-import-preview">
            <div className="wprm-ai-preview-header">
                <h3>{ recipe.name }</h3>
                { summary && <p className="wprm-ai-preview-summary">{ summary }</p> }
            </div>
            { ( timeItems.length > 0 || servings ) && (
                <div className="wprm-ai-preview-meta-grid">
                    { timeItems.map( ( time, index ) => (
                        <div key={ index } className="wprm-ai-preview-meta-item">
                            <span className="wprm-ai-preview-meta-label">{ time.label }</span>
                            <span className="wprm-ai-preview-meta-value">{ time.value }</span>
                        </div>
                    ) ) }
                    { servings && (
                        <div className="wprm-ai-preview-meta-item">
                            <span className="wprm-ai-preview-meta-label">{ __wprm( 'Servings' ) }</span>
                            <span className="wprm-ai-preview-meta-value">{ servings }</span>
                        </div>
                    ) }
                </div>
            ) }
            { tagItems.length > 0 && (
                <div className="wprm-ai-preview-section">
                    <h4>{ __wprm( 'Tags' ) }</h4>
                    <ul className="wprm-ai-preview-ingredients">
                        { tagItems.map( ( tag ) => (
                            <li key={ tag.key }>
                                <strong>{ tag.label }:</strong> { tag.value }
                            </li>
                        ) ) }
                    </ul>
                </div>
            ) }
            <EquipmentPreview equipment={ equipment } />
            <IngredientPreview ingredients={ ingredients } />
            <InstructionPreview instructions={ instructions } />
            { notes && (
                <div className="wprm-ai-preview-section">
                    <h4>{ __wprm( 'Notes' ) }</h4>
                    <p className="wprm-ai-preview-summary">{ notes }</p>
                </div>
            ) }
        </div>
    );
};

export const AIRecipeImportContent = ( props ) => {
    const hasImportedRecipe = !! props.importedRecipe;

    return (
        <div className={ `wprm-admin-modal-ai-text-import-container${ hasImportedRecipe ? ' wprm-has-preview' : '' }` }>
            <FieldContainer label={ __wprm( 'Recipe Text' ) }>
                <FieldTextarea
                    value={ props.text }
                    placeholder={ __wprm( 'Paste or type recipe and click the import button' ) }
                    onChange={ props.onTextChange }
                />
            </FieldContainer>
            { props.importing && (
                <div className="wprm-admin-modal-ai-text-import-loading">
                    <Loader />
                    <p>{ __wprm( 'Importing recipe with AI...' ) }</p>
                </div>
            ) }
            { props.error && (
                <div className="wprm-admin-modal-ai-text-import-error">
                    <p><strong>{ __wprm( 'Error' ) }</strong></p>
                    <p>{ props.error }</p>
                </div>
            ) }
            { hasImportedRecipe && <AIRecipeImportPreview recipe={ props.importedRecipe } /> }
            { ! props.importing && ! hasImportedRecipe && ! props.error && (
                <p className="wprm-admin-modal-ai-text-import-help">
                    { __wprm( 'Paste recipe text and let AI extract the recipe fields for you. You can review the imported values before applying them.' ) }
                </p>
            ) }
        </div>
    );
};

export const useAIRecipeImport = ( options = {} ) => {
    const initialText = options.initialText || '';
    const autoImport = !! options.autoImport;

    const [ text, setText ] = useState( initialText );
    const [ importing, setImporting ] = useState( false );
    const [ error, setError ] = useState( '' );
    const [ importedRecipe, setImportedRecipe ] = useState( false );

    const onTextChange = ( value ) => {
        setText( value );
        setError( '' );
        setImportedRecipe( false );
    };

    const editImportedRecipe = () => {
        setError( '' );
        setImportedRecipe( false );
    };

    const importRecipe = ( textToImport = text ) => {
        const trimmedText = textToImport.trim();

        if ( ! trimmedText ) {
            setError( __wprm( 'Text is required' ) );
            setImportedRecipe( false );

            return Promise.resolve( false );
        }

        setImporting( true );
        setError( '' );
        setImportedRecipe( false );

        return Api.import.aiImportRecipe( trimmedText ).then( ( response ) => {
            if ( response && response.success && response.recipe ) {
                const importedRecipe = normalizeRecipeTags( response.recipe );

                setImporting( false );
                setError( '' );
                setImportedRecipe( importedRecipe );

                return importedRecipe;
            }

            setImporting( false );
            setError( response?.error || __wprm( 'Failed to import recipe with AI. Please try again.' ) );
            setImportedRecipe( false );

            return false;
        } ).catch( ( importError ) => {
            console.error( 'Error importing recipe with AI:', importError );

            setImporting( false );
            setError( __wprm( 'An error occurred while importing with AI. Please try again.' ) );
            setImportedRecipe( false );

            return false;
        } );
    };

    useEffect( () => {
        if ( autoImport && initialText ) {
            importRecipe( initialText );
        }
        // Only run once on mount for optional auto-import behavior.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [] );

    return {
        text,
        importing,
        error,
        importedRecipe,
        onTextChange,
        editImportedRecipe,
        importRecipe,
        setError,
    };
};
