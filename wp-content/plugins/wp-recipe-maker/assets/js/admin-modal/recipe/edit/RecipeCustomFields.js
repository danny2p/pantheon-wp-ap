import React, { Fragment } from 'react';

import '../../../../css/admin/modal/recipe/fields/custom-fields.scss';

import { __wprm } from 'Shared/Translations';
import FieldContainer from '../../fields/FieldContainer';
import FieldImage from '../../fields/FieldImage';
import FieldText from '../../fields/FieldText';
import FieldRichText from '../../fields/FieldRichText';
import FieldTinymce from '../../fields/FieldTinymce';

const customFields = wprm_admin_modal.custom_fields && wprm_admin_modal.custom_fields.fields ? Object.values( wprm_admin_modal.custom_fields.fields ) : [];

const RecipeCustomFields = (props) => {
    return (
        <Fragment>
            {
                customFields.map((field, index) => {
                    const value = props.fields.hasOwnProperty( field.key ) ? props.fields[ field.key ] : false;

                    switch( field.type ) {
                        case 'text':
                        case 'link':
                        case 'email':
                            const type = 'link' === field.type ? 'url' : field.type;

                            return (
                                <FieldContainer label={ field.name } key={ index }>
                                    <FieldText
                                        type={ type }
                                        name={ `recipe-custom-${ field.key }` }
                                        value={ value ? value : '' }
                                        onChange={ (value) => {
                                            props.onFieldChange( field.key, value );
                                        }}
                                    />
                                </FieldContainer>
                            );
                        case 'textarea':
                            return (
                                <FieldContainer label={ field.name } key={ index }>
                                    <FieldRichText
                                        value={ value ? value : '' }
                                        onChange={ (value) => {
                                            props.onFieldChange( field.key, value );
                                        }}
                                    />
                                </FieldContainer>
                            );
                        case 'classic':
                            return (
                                <FieldContainer label={ field.name } key={ index }>
                                    <FieldTinymce
                                        value={ value ? value : '' }
                                        onChange={ (value) => {
                                            props.onFieldChange( field.key, value );
                                        }}
                                    />
                                </FieldContainer>
                            );
                        case 'image':
                            return (
                                <FieldContainer label={ field.name } key={ index }>
                                    <FieldImage
                                        id={ value ? value.id : 0 }
                                        url={ value ? value.url : '' }
                                        onChange={ ( id, url ) => {
                                            props.onFieldChange( field.key, {
                                                id,
                                                url,
                                            } );
                                        }}
                                    />
                                </FieldContainer>
                            );
                    }
                })
            }
        </Fragment>
    );
}
export default RecipeCustomFields;