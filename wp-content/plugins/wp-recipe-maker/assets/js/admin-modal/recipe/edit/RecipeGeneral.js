import React, { Fragment } from 'react';

import '../../../../css/admin/modal/recipe/fields/general.scss';

import { __wprm } from 'Shared/Translations';
import { isProblemBrowser } from 'Shared/Browser';
import FieldCheckbox from '../../fields/FieldCheckbox';
import FieldContainer from '../../fields/FieldContainer';
import FieldDropdown from '../../fields/FieldDropdown';
import FieldText from '../../fields/FieldText';
import FieldRadio from '../../fields/FieldRadio';
import FieldRichText from '../../fields/FieldRichText';
import FieldAdvancedServings from '../../fields/FieldAdvancedServings';

const RecipeGeneral = (props) => {
    const author = wprm_admin_modal.options.author.find((option) => option.value === props.author.display );

    return (
        <Fragment>
            {
                isProblemBrowser()
                &&
                <FieldContainer id="warning" label={ __wprm( 'Warning!' ) }>
                    <p>Looks like you're using an older browser like <strong>Legacy Microsoft Edge</strong> or <strong>Internet Explorer</strong> which does not support all of our advanced features.</p>
                    <p>We highly recommend using <a href="https://www.google.com/chrome/" target="_blank">Google Chrome</a>, <a href="https://www.mozilla.org/en-US/firefox/new/" target="_blank">Firefox</a>, <a href="https://support.apple.com/downloads/safari" target="_blank">Safari</a> or <a href="https://www.microsoft.com/en-us/edge" target="_blank">Microsoft Edge</a>.</p>
                </FieldContainer>
            }
            <FieldContainer id="type" label={ __wprm( 'Recipe Type' ) } help={ __wprm( `Make sure to pick the right recipe type to ensure we include the correct metadata.` ) }>
                <FieldRadio
                    id="type"
                    options={[
                        { value: 'food', label: __wprm( 'Food Recipe' ) },
                        { value: 'howto', label: __wprm( 'How-to Instructions' ) },
                        { value: 'other', label: __wprm( 'Other (no metadata)' ) },
                    ]}
                    value={ props.type }
                    onChange={ (type) => {
                        props.onRecipeChange( { type } );
                    }}
                />
            </FieldContainer>
            <FieldContainer id="name" label={ __wprm( 'Name' ) }>
                <FieldText
                    name="recipe-name"
                    placeholder={ __wprm( 'Recipe Name' ) }
                    value={ props.name }
                    onChange={ (name) => {
                        props.onRecipeChange( { name } );
                    }}
                />
            </FieldContainer>
            <FieldContainer id="summary" label={ 'howto' === props.type ? __wprm( 'Description' ) : __wprm( 'Summary' ) }>
                <FieldRichText
                    placeholder={ __wprm( 'Short description of this recipe...' ) }
                    value={ props.summary }
                    onChange={ (summary) => {
                        props.onRecipeChange( { summary } );
                    }}
                />
            </FieldContainer>
            {
                author && 'same' === author.actual
                ?
                null // Don't display when set to "Same author for every recipe".
                :
                <FieldContainer id="author" label={ __wprm( 'Author' ) }>
                    <FieldDropdown
                        options={ wprm_admin_modal.options.author.filter( ( author ) => 'same' !== author.actual ) }
                        value={ props.author.display }
                        onChange={ (author_display) => {
                            props.onRecipeChange( { author_display } );
                        }}
                        width={ 300 }
                    />
                </FieldContainer>
            }
            {
                author && 'custom' === author.actual
                &&
                <Fragment>
                    <FieldContainer id="author-name" label={ __wprm( 'Name' ) }>
                        <FieldText
                            name="author-name"
                            placeholder={ __wprm( 'Author Name' ) }
                            value={ props.author.name }
                            onChange={ (author_name) => {
                                props.onRecipeChange( { author_name } );
                            }}
                        />
                    </FieldContainer>
                    <FieldContainer id="author-link" label={ __wprm( 'Link' ) }>
                        <FieldText
                            name="author-link"
                            placeholder="https://bootstrapped.ventures"
                            type="url"
                            value={ props.author.link }
                            onChange={ (author_link) => {
                                props.onRecipeChange( { author_link } );
                            }}
                        />
                    </FieldContainer>
                </Fragment>
            }
            <FieldContainer id="servings" label={ 'howto' === props.type ? __wprm( 'Yield' ) : __wprm( 'Servings' ) }>
                <FieldText
                    placeholder="4"
                    type="number"
                    min="0"
                    step="any"
                    value={ 0 != props.servings.amount ? props.servings.amount : '' }
                    onChange={ (servings) => {
                        props.onRecipeChange( { servings } );
                    }}
                />
                <FieldText
                    name="servings-unit"
                    placeholder={ 'howto' === props.type ? __wprm( 'candles' ) : __wprm( 'people' ) }
                    value={ props.servings.unit }
                    onChange={ (servings_unit) => {
                        props.onRecipeChange( { servings_unit } );
                    }}
                />
            </FieldContainer>
            <FieldContainer
                    id="advanced-servings"
                    label={ __wprm( 'Advanced Servings' ) }
                    help={ __wprm( `Enable to have an advanced servings calculator, useful for different baking forms` ) }
                >
                    <FieldAdvancedServings
                        enabled={ props.servings_advanced_enabled }
                        onChangeEnabled={ (servings_advanced_enabled) => {
                            props.onRecipeChange( { servings_advanced_enabled } );
                        }}
                        servings={ props.servings_advanced }
                        onChangeServings={ (servings_advanced) => {
                            props.onRecipeChange( { servings_advanced } );
                        }}
                    />
                </FieldContainer>
            <FieldContainer
                id="cost"
                label={ __wprm( 'Estimated Cost' ) }
                help={ 'howto' === props.type ? __wprm( `The estimated cost of the materials consumed when performing instructions. Used in the metadata.` ) : null }
            >
                <FieldText
                    name="cost"
                    placeholder={ '$5' }
                    value={ props.cost }
                    onChange={ (cost) => {
                        props.onRecipeChange( { cost } );
                    }}
                />
            </FieldContainer>
            {
                wprm_admin_modal.integrations.my_emissions_checkbox
                &&
                <FieldContainer
                    id="my-emissions"
                    label={ __wprm( 'My Emissions Label' ) }
                >
                    <FieldCheckbox
                        name="my-emissions"
                        value={ props.my_emissions }
                        onChange={ (my_emissions) => {
                            props.onRecipeChange( { my_emissions } );
                        }}
                    />
                </FieldContainer>
            }
        </Fragment>
    );
}
export default RecipeGeneral;