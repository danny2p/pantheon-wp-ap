import React from 'react';

import Header from '../../general/Header';
import Footer from '../../general/Footer';

import { __wprm } from 'Shared/Translations';
import { AIRecipeImportContent, useAIRecipeImport } from 'Shared/AiRecipeImport';

const AITextImport = ( props ) => {
    const recipe = props.recipe || {};
    const hasRecipeName = !! recipe.name;

    const {
        text,
        importing,
        error,
        importedRecipe,
        onTextChange,
        editImportedRecipe,
        importRecipe,
    } = useAIRecipeImport( {
        initialText: props.text || '',
        autoImport: !! props.text,
    } );

    const useImportedValues = () => {
        if ( importedRecipe && props.onImportValues ) {
            props.onImportValues( importedRecipe );
            props.maybeCloseModal();
        }
    };

    return (
        <>
            <Header onCloseModal={ props.maybeCloseModal }>
                { hasRecipeName
                    ? `${ recipe.name } - ${ __wprm( 'Import with AI' ) }`
                    : `${ __wprm( 'Recipe' ) } - ${ __wprm( 'Import with AI' ) }`
                }
            </Header>
            <AIRecipeImportContent
                text={ text }
                importing={ importing }
                error={ error }
                importedRecipe={ importedRecipe }
                onTextChange={ onTextChange }
            />
            <Footer savingChanges={ importing }>
                <button className="button button-secondary button-compact" onClick={ props.maybeCloseModal }>
                    { __wprm( 'Cancel' ) }
                </button>
                <button
                    className="button button-secondary button-compact"
                    onClick={ ( e ) => {
                        e.preventDefault();
                        if ( importedRecipe ) {
                            editImportedRecipe();
                        } else {
                            importRecipe();
                        }
                    } }
                    style={ {
                        marginLeft: '10px',
                    } }
                >
                    { importedRecipe ? __wprm( 'Import Again' ) : __wprm( 'Import with AI' ) }
                </button>
                <button
                    className="button button-primary button-compact"
                    onClick={ ( e ) => {
                        e.preventDefault();
                        useImportedValues();
                    } }
                    disabled={ ! importedRecipe }
                    style={ {
                        marginLeft: '10px',
                    } }
                >
                    { __wprm( 'Use these Values' ) }
                </button>
            </Footer>
        </>
    );
};

export default AITextImport;
