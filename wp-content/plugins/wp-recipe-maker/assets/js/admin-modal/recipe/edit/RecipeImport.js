import React, { Fragment } from 'react';

import '../../../../css/admin/modal/recipe/fields/import.scss';

import { __wprm } from 'Shared/Translations';
import FieldContainer from '../../fields/FieldContainer';
import FieldTextarea from '../../fields/FieldTextarea';
 
const RecipeImport = (props) => {
    return (
        <Fragment>
            <FieldContainer label={ __wprm( 'Import from Text' ) }>
                <FieldTextarea
                    placeholder={ __wprm( 'Paste or type recipe to start...' ) }
                    value={''}
                    onChange={ (value) => {
                        if ( value ) {
                            props.openSecondaryModal( 'text-import', {
                                text: value,
                                recipe: props.recipe,
                                onImportValues: (newRecipe) => {
                                    // Use onRecipeChange with forceRerender to refresh rich text fields
                                    props.onRecipeChange(newRecipe, {
                                        forceRerender: true,
                                        historyMode: 'immediate',
                                        historyBoundary: true,
                                        historyKey: 'import:text',
                                    });
                                    // Scroll to General section after import
                                    props.scrollToGroup('general');
                                }
                            });
                        }
                    }}
                />
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
