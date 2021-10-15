import React from 'react';

import '../../../../css/admin/modal/recipe/fields/notes.scss';

import { __wprm } from 'Shared/Translations';
import FieldContainer from '../../fields/FieldContainer';
import FieldTinymce from '../../fields/FieldTinymce';
 
const RecipeNotes = (props) => {
    return (
        <FieldContainer label={ __wprm( 'Recipe Notes' ) }>
            <FieldTinymce
                id="recipe-notes"
                value={ props.notes }
                onChange={ ( notes ) => {
                    props.onRecipeChange( { notes } );
                }}
            />
        </FieldContainer>
    );
}
export default RecipeNotes;