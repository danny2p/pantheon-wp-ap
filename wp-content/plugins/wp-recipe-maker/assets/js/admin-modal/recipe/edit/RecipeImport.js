import React, { Fragment, useState } from 'react';

import '../../../../css/admin/modal/recipe/fields/import.scss';

import { __wprm } from 'Shared/Translations';
import Button from 'Shared/Button';
import FieldContainer from '../../fields/FieldContainer';
import FieldTextarea from '../../fields/FieldTextarea';
 
const RecipeImport = (props) => {
    const [text, setText] = useState('');

    const openImportModal = (mode) => {
        const trimmedText = text.trim();

        if ( ! trimmedText ) {
            alert( __wprm( 'Paste or type recipe and click the import button' ) );
            return;
        }

        props.openSecondaryModal( mode, {
            text: trimmedText,
            recipe: props.recipe,
            onImportValues: (newRecipe) => {
                props.onRecipeChange(newRecipe, {
                    forceRerender: true,
                    historyMode: 'immediate',
                    historyBoundary: true,
                    historyKey: `import:${ 'ai-text-import' === mode ? 'ai' : 'text' }`,
                });
                props.scrollToGroup('general');
            }
        });
    };

    return (
        <Fragment>
            <FieldContainer label={ __wprm( 'Import from Text' ) }>
                <div className="wprm-admin-modal-recipe-import-field">
                    <FieldTextarea
                        placeholder={ __wprm( 'Paste or type recipe and click the import button' ) }
                        value={ text }
                        onChange={ (value) => {
                            setText( value );
                        }}
                    />
                    <div className="wprm-admin-modal-recipe-import-actions">
                        <Button
                            onClick={ (e) => {
                                e.preventDefault();
                                openImportModal( 'text-import' );
                            } }
                        >{ __wprm( 'Import from Text' ) }</Button>
                        <Button
                            ai
                            onClick={ (e) => {
                                e.preventDefault();
                                openImportModal( 'ai-text-import' );
                            } }
                        >{ __wprm( 'Import with AI' ) }</Button>
                    </div>
                </div>
            </FieldContainer>
            <FieldContainer label={ __wprm( 'Restore Backup' ) } help={ __wprm( `If something goes wrong during saving, the plugin allows you to copy the recipe to your clipboard. Paste that modal backup here to restore the recipe.` ) }>
                <FieldTextarea
                    placeholder={ __wprm( 'Paste the recipe modal backup to restore the recipe' ) }
                    value={''}
                    onChange={ (value) => {
                        if ( value ) {
                            try {
                                const importedRecipe = JSON.parse(value);
                                props.onImportJSON( importedRecipe );
                                alert( __wprm( 'The recipe has been imported.' ) );
                            } catch (e) {
                                alert( __wprm( 'No valid recipe found.' ) );
                            }
                        }
                    }}
                />
            </FieldContainer>
        </Fragment>
    );
}
export default RecipeImport;
