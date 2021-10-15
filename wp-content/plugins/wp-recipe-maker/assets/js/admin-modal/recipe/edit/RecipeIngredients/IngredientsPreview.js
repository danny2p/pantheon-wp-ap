import React from 'react';

const IngredientsPreview = (props) => {
    const { ingredients } = props;

    return (
        <div className="wprm-admin-modal-field-ingredient-preview">
            {
                ingredients.map((field, index) => {
                    if ( 'group' === field.type ) {
                        if ( field.name ) {
                            return (
                                <h3 key={index}>{ field.name }</h3>
                            );
                        }
                    } else {
                        if ( field.amount || field.unit || field.name || field.notes ) {
                            let ingredient = '';

                            if ( field.amount ) {
                                ingredient += `<span className="wprm-admin-modal-field-ingredient-preview-ingredient-amount">${ field.amount }</span>`;
                            }
                            if ( field.unit ) {
                                ingredient += `<span className="wprm-admin-modal-field-ingredient-preview-ingredient-unit">${ field.unit }</span>`;
                            }
                            if ( field.name ) {
                                ingredient += `<span className="wprm-admin-modal-field-ingredient-preview-ingredient-name">${ field.name }</span>`;
                            }
                            if ( field.notes ) {
                                ingredient += `<span className="wprm-admin-modal-field-ingredient-preview-ingredient-notes">${ field.notes }</span>`;
                            }

                            if ( ingredient ) {
                                return (
                                    <div
                                        className="wprm-admin-modal-field-ingredient-preview-ingredient"
                                        dangerouslySetInnerHTML={ { __html: ingredient } }
                                        key={index}/
                                    >
                                );
                            }
                        }
                    }

                    return null;
                })
            }
        </div>
    );
}
export default IngredientsPreview;