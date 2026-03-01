import React, { Fragment } from 'react';

import { __wprm } from 'Shared/Translations';
import FieldContainer from '../../fields/FieldContainer';
import FieldDropdown from '../../fields/FieldDropdown';
import FieldDateTime from '../../fields/FieldDateTime';
import FieldText from '../../fields/FieldText';

const RecipePostType = (props) => {
    const showAll = 'public' === wprm_admin.settings.post_type_structure;

    let languageOptions = [
        { value: false, label: __wprm( 'No language set' ) }
    ];
    if ( 'wpml' === wprm_admin_modal.multilingual.plugin ) {
        languageOptions = languageOptions.concat( Object.values( wprm_admin_modal.multilingual.languages ) );
    }

    return (
        <Fragment>
            {
                showAll
                &&
                <Fragment>
                    <FieldContainer id="slug" label={ __wprm( 'Slug' ) }>
                        <FieldText
                            name="recipe-slug"
                            placeholder={ __wprm( 'recipe-slug' ) }
                            value={ props.slug }
                            onChange={ (slug) => {
                                props.onRecipeChange( { slug }, {
                                    historyMode: 'debounced',
                                    historyKey: 'post_type:slug',
                                } );
                            }}
                            onBlur={ (slug) => {
                                props.onRecipeChange( { slug }, {
                                    historyMode: 'debounced',
                                    historyBoundary: true,
                                    historyKey: 'post_type:slug',
                                } );
                            }}
                        />
                    </FieldContainer>
                    <FieldContainer id="post_status" label={ __wprm( 'Status' ) }>
                        <FieldDropdown
                            options={ wprm_admin_modal.options.post_status }
                            value={ props.post_status }
                            onChange={ (post_status) => {
                                props.onRecipeChange( { post_status }, {
                                    historyMode: 'immediate',
                                    historyBoundary: true,
                                    historyKey: 'post_type:post_status',
                                } );
                            }}
                            width={ 300 }
                        />
                    </FieldContainer>
                    <FieldContainer id="post_date" label={ __wprm( 'Date' ) }>
                        <FieldDateTime
                            value={ props.date }
                            onChange={ (date) => {
                                props.onRecipeChange( { date }, {
                                    historyMode: 'immediate',
                                    historyBoundary: true,
                                    historyKey: 'post_type:date',
                                } );
                            }}
                        />
                    </FieldContainer>
                    <FieldContainer id="post_password" label={ __wprm( 'Password' ) } help={ __wprm( `Optionally set a password to restrict access to the recipe post.` ) }>
                        <FieldText
                            name="post-password"
                            value={ props.post_password }
                            onChange={ (post_password) => {
                                props.onRecipeChange( { post_password }, {
                                    historyMode: 'debounced',
                                    historyKey: 'post_type:post_password',
                                } );
                            }}
                            onBlur={ (post_password) => {
                                props.onRecipeChange( { post_password }, {
                                    historyMode: 'debounced',
                                    historyBoundary: true,
                                    historyKey: 'post_type:post_password',
                                } );
                            }}
                        />
                    </FieldContainer>
                </Fragment>
            }
            <FieldContainer id="post_author" label={ __wprm( 'Post Author' ) }>
                <FieldDropdown
                    options={ wprm_admin_modal.options.post_author }
                    value={ parseInt( props.post_author ) }
                    onChange={ (post_author) => {
                        props.onRecipeChange( { post_author }, {
                            historyMode: 'immediate',
                            historyBoundary: true,
                            historyKey: 'post_type:post_author',
                        } );
                    }}
                    width={ 300 }
                />
            </FieldContainer>
            {
                showAll
                && 1 < languageOptions.length
                &&
                <FieldContainer id="language" label={ __wprm( 'Language' ) }>
                    <FieldDropdown
                        options={ languageOptions }
                        value={ props.language }
                        onChange={ (language) => {
                            props.onRecipeChange( { language }, {
                                historyMode: 'immediate',
                                historyBoundary: true,
                                historyKey: 'post_type:language',
                            } );
                        }}
                        width={ 300 }
                    />
                </FieldContainer>
            }
        </Fragment>
    );
}
export default RecipePostType;
