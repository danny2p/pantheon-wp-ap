import React, { Fragment } from 'react';

import '../../../../css/admin/modal/recipe/fields/times.scss';

import { __wprm } from 'Shared/Translations';
import FieldContainer from '../../fields/FieldContainer';
import FieldText from '../../fields/FieldText';
import FieldTime from '../../fields/FieldTime';
 
const RecipeTimes = (props) => {
    const calculatedTotal = Math.max( 0, parseInt( props.prep.time ) ) + Math.max( 0, parseInt( props.cook.time ) ) + Math.max( 0, parseInt( props.custom.time ) );

    return (
        <Fragment>
            <FieldContainer id="prep-time" label={ __wprm( 'Prep Time' ) }>
                <FieldTime
                    id="prep"
                    value={ props.prep }
                    onChange={ (prep_time) => {
                        props.onRecipeChange( { prep_time } );
                    }}
                    onChangeZero={ (prep_time_zero) => {
                        props.onRecipeChange( { prep_time_zero } );
                    }}
                />
            </FieldContainer>
            <FieldContainer id="cook-time" label={ 'howto' === props.type ? __wprm( 'Active Time' ) : __wprm( 'Cook Time' ) }>
                <FieldTime
                    id="cook"
                    value={ props.cook }
                    onChange={ (cook_time) => {
                        props.onRecipeChange( { cook_time } );
                    }}
                    onChangeZero={ (cook_time_zero) => {
                        props.onRecipeChange( { cook_time_zero } );
                    }}
                />
            </FieldContainer>
            <FieldContainer id="custom-time-label" label={ __wprm( 'Custom Time Label' ) } help={ __wprm( 'Optional extra time field that you can label yourself. Examples: Resting Time, Baking Time' ) }>
                <FieldText
                    name="custom-time-label"
                    placeholder={ __wprm( 'Resting Time' ) }
                    value={ props.customLabel }
                    onChange={ (custom_time_label) => {
                        props.onRecipeChange( { custom_time_label } );
                    }}
                />
            </FieldContainer>
            <FieldContainer id="custom-time" label={ __wprm( 'Custom Time' ) }>
                <FieldTime
                    id="custom"
                    value={ props.custom }
                    onChange={ (custom_time) => {
                        props.onRecipeChange( { custom_time } );
                    }}
                    onChangeZero={ (custom_time_zero) => {
                        props.onRecipeChange( { custom_time_zero } );
                    }}
                />
            </FieldContainer>
            <FieldContainer id="total-time" label={ __wprm( 'Total Time' ) }>
                <FieldTime
                    id="total"
                    value={ props.total }
                    onChange={ (total_time) => {
                        props.onRecipeChange( { total_time } );
                    }}
                />
                {
                    calculatedTotal !== parseInt( props.total.time )
                    &&
                    <div>
                        <a
                            href="#"
                            onClick={(e) => {
                                e.preventDefault();
                                props.onRecipeChange({
                                    total_time: calculatedTotal,
                                });
                            }}
                        >{ __wprm( 'Recalculate Total Time' ) }</a>
                    </div>
                }
            </FieldContainer>
        </Fragment>
    );
}
export default RecipeTimes;