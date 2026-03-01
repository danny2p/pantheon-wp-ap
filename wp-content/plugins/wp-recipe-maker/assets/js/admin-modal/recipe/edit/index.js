import React, { Fragment, useState } from 'react';
import { Element, Link } from 'react-scroll';
import CopyToClipboard from 'react-copy-to-clipboard';

import Header from '../../general/Header';
import Footer from '../../general/Footer';

import Loader from 'Shared/Loader';
import { __wprm } from 'Shared/Translations';
import Api from 'Shared/Api';
import Icon from 'Shared/Icon';

import FieldGroup from '../../fields/FieldGroup';

import RecipeImport from './RecipeImport';
import RecipeMedia from './RecipeMedia';
import RecipePostType from './RecipePostType';
import RecipeGeneral from './RecipeGeneral';
import RecipeTimes from './RecipeTimes';
import RecipeCategories from './RecipeCategories';
import RecipeIngredients from './RecipeIngredients';
import RecipeEquipment from './RecipeEquipment';
import RecipeInstructions from './RecipeInstructions';
import RecipeNutrition from './RecipeNutrition';
import RecipeCustomFields from './RecipeCustomFields';
import RecipeNotes from './RecipeNotes';
 
const EditRecipe = (props) => {
    const hasUpload = props.recipe.video_id > 0;
    const hasEmbed = ! hasUpload && ( -1 == props.recipe.video_id || props.recipe.video_embed );
    const hasVideo = hasUpload || hasEmbed;

    const [nutritionWarning, setNutritionWarning] = useState(false);
    const historyEnabled = !! props.historyEnabled;
    const historyDisabled = props.loadingRecipe || props.savingChanges || 'waiting' === props.saveResult;
    const renderHistoryControls = () => (
        historyEnabled
        ?
        <div className="wprm-admin-modal-recipe-history-controls">
            <button
                type="button"
                className="button"
                disabled={ historyDisabled || ! props.canUndo }
                aria-label={ __wprm( 'Undo last recipe change' ) }
                onClick={ () => {
                    if ( props.onUndo ) {
                        props.onUndo();
                    }
                } }
            >{ __wprm( 'Undo' ) } ({ props.undoCount || 0 })</button>
            <button
                type="button"
                className="button"
                disabled={ historyDisabled || ! props.canRedo }
                aria-label={ __wprm( 'Redo recipe change' ) }
                onClick={ () => {
                    if ( props.onRedo ) {
                        props.onRedo();
                    }
                } }
            >{ __wprm( 'Redo' ) } ({ props.redoCount || 0 })</button>
            {
                props.historyLimitNotice
                &&
                <span className="wprm-admin-modal-recipe-history-notice" aria-live="polite">
                    { props.historyLimitNotice }
                </span>
            }
        </div>
        :
        null
    );

    let structure = [
        {
            id: 'import', name: __wprm( 'Import' ),
            elem: (
                <RecipeImport
                    onImportJSON={ props.onImportJSON }
                    openSecondaryModal={ props.openSecondaryModal }
                    onRecipeChange={ props.onRecipeChange }
                    recipe={ props.recipe }
                    scrollToGroup={ props.scrollToGroup }
                />
            )
        },
        {
            id: 'media', name: __wprm( 'Media' ),
            elem: (
                <RecipeMedia
                    image={{
                        id: props.recipe.image_id,
                        url: props.recipe.image_url,
                    }}
                    pinImage={{
                        id: props.recipe.pin_image_id,
                        url: props.recipe.pin_image_url,
                        repin: props.recipe.pin_image_repin_id,
                    }}
                    video={{
                        id: props.recipe.video_id,
                        thumb: props.recipe.video_thumb_url,
                        embed: props.recipe.video_embed,
                    }}
                    onRecipeChange={ props.onRecipeChange }
                />
            )
        }
    ];

    if ( 'public' === wprm_admin.settings.post_type_structure || 'manual' === wprm_admin.settings.recipe_use_author ) {
        structure.push({
            id: 'postType', name: __wprm( 'Post Type' ),
            elem: (
                <RecipePostType
                    slug={ props.recipe.slug }
                    post_status={ props.recipe.post_status }
                    date={ props.recipe.date }
                    post_password={ props.recipe.post_password }
                    post_author={ props.recipe.post_author }
                    language={ props.recipe.language }
                    onRecipeChange={ props.onRecipeChange }
                />
            )
        });
    }

    structure.push({
        id: 'general', name: __wprm( 'General' ),
        elem: (
            <RecipeGeneral
                type={ props.recipe.type }
                name={ props.recipe.name }
                summary={ props.recipe.summary }
                author={{
                    display: props.recipe.author_display,
                    name: props.recipe.author_name,
                    link: props.recipe.author_link,
                    bio: props.recipe.author_bio,
                }}
                servings={{
                    amount: props.recipe.servings,
                    unit: props.recipe.servings_unit,
                }}
                servings_advanced_enabled={ props.recipe.servings_advanced_enabled }
                servings_advanced={ props.recipe.servings_advanced }
                cost={ props.recipe.cost }
                onRecipeChange={ props.onRecipeChange }
            />
        )
    });
    structure.push({
        id: 'times', name: __wprm( 'Times' ),
        elem: (
            <RecipeTimes
                type={ props.recipe.type }
                prep={ {
                    time: props.recipe.prep_time,
                    zero: props.recipe.prep_time_zero,
                } }
                cook={ {
                    time: props.recipe.cook_time,
                    zero: props.recipe.cook_time_zero,
                } }
                custom={ {
                    time: props.recipe.custom_time,
                    zero: props.recipe.custom_time_zero,
                } }
                customLabel={ props.recipe.custom_time_label }
                total={ {
                    time: props.recipe.total_time,
                    zero: false,
                } }
                onRecipeChange={ props.onRecipeChange }
            />
        )
    });
    structure.push({
        id: 'categories', name: __wprm( 'Categories' ),
        elem: (
            <RecipeCategories
                tags={ props.recipe.tags }
                recipe={ props.recipe }
                onRecipeChange={ props.onRecipeChange }
                openSecondaryModal={ props.openSecondaryModal }
            />
        )
    });
    structure.push({
        id: 'equipment', name: __wprm( 'Equipment' ),
        elem: (
            <RecipeEquipment
                type={ props.recipe.type }
                equipment={ props.recipe.equipment }
                onRecipeChange={ props.onRecipeChange }
                openSecondaryModal={ props.openSecondaryModal }
            />
        )
    });
    structure.push({
        id: 'ingredients',
        name: 'howto' === props.recipe.type ? __wprm( 'Materials' ) : __wprm( 'Ingredients' ),
        elem: (
            <RecipeIngredients
                type={ props.recipe.type }
                ingredients={ props.recipe.ingredients_flat }
                instructions={ props.recipe.instructions_flat }
                linkType={ props.recipe.ingredient_links_type }
                system={ props.recipe.unit_system }
                onRecipeChange={ props.onRecipeChange }
                openSecondaryModal={ props.openSecondaryModal }
                setUids={ props.setUids }
            />
        )
    });
    structure.push({
        id: 'instructions', name: __wprm( 'Instructions' ),
        elem: (
            <RecipeInstructions
                type={ props.recipe.type }
                ingredients={ props.recipe.ingredients_flat }
                instructions={ props.recipe.instructions_flat }
                onRecipeChange={ props.onRecipeChange }
                allowVideo={ hasVideo && 'other' !== props.recipe.type }
                openSecondaryModal={ props.openSecondaryModal }
                setUids={ props.setUids }
            />
        )
    });

    // Only show nutrition for food recipes.
    if ( 'howto' !== props.recipe.type ) {
        let nutritionName = __wprm( 'Nutrition' );
        let nutritionClassName = 'wprm-admin-modal-recipe-quicklink';
        
        if ( nutritionWarning ) {
            nutritionClassName += ' wprm-admin-modal-recipe-quicklink-warning';
            nutritionName = (
                <span style={{ display: 'inline-flex', gap: '5px', alignItems: 'baseline' }}>
                    <Icon type="warning" color="#8B0000" />
                    { __wprm( 'Nutrition' ) }
                </span>
            );
        }

        structure.push({
            id: 'nutrition', 
            name: nutritionName,
            className: nutritionClassName,
            elem: (
                <RecipeNutrition
                    nutrition={ props.recipe.nutrition }
                    servings={{
                        amount: props.recipe.servings,
                        unit: props.recipe.servings_unit,
                    }}
                    ingredients={ props.recipe.ingredients_flat }
                    recipe={ props.recipe }
                    onRecipeChange={ props.onRecipeChange }
                    openSecondaryModal={ props.openSecondaryModal }
                    onWarningChange={ setNutritionWarning }
                />
            )
        });
    }

    // Only show custom fields when available and at least 1 is set.
    if ( wprm_admin_modal.custom_fields && wprm_admin_modal.custom_fields.fields && 0 < Object.keys( wprm_admin_modal.custom_fields.fields ).length ) {
        structure.push({
            id: 'custom-fields', name: __wprm( 'Custom Fields' ),
            elem: (
                <RecipeCustomFields
                    fields={ props.recipe.custom_fields }
                    onFieldChange={( field, value, changeOptions = {} ) => {
                        props.onRecipeChange((recipe) => ({
                            custom_fields: {
                                ...( recipe.custom_fields || {} ),
                                [ field ]: value,
                            },
                        }), {
                            historyMode: changeOptions.historyMode ? changeOptions.historyMode : 'debounced',
                            historyBoundary: !! changeOptions.historyBoundary,
                            historyKey: `custom_fields:${ field }`,
                        });
                    }}
                />
            )
        });
    }

    structure.push({
        id: 'notes', name: __wprm( 'Notes' ),
        elem: (
            <RecipeNotes
                notes={ props.recipe.notes }
                onRecipeChange={ props.onRecipeChange }
                openSecondaryModal={ props.openSecondaryModal }
            />
        )
    });

    return (
        <Fragment>
            <Header
                onCloseModal={ props.onCloseModal }
            >
                {
                    props.loadingRecipe
                    ?
                    __wprm( 'Loading Recipe...' )
                    :
                    <Fragment>
                        {
                            props.recipe.id
                            ?
                            `${ __wprm( 'Editing Recipe' ) } #${props.recipe.id}${props.recipe.name ? ` - ${props.recipe.name}` : ''}`
                            :
                            `${ __wprm( 'Creating new Recipe' ) }${props.recipe.name ? ` - ${props.recipe.name}` : ''}`
                        }
                    </Fragment>
                }
            </Header>
            <div className="wprm-admin-modal-recipe-quicklinks">
                {
                    structure.map((group, index) => (
                        <Link
                            to={ `wprm-admin-modal-fields-group-${ group.id }` }
                            containerId="wprm-admin-modal-recipe-content"
                            className={ group.className || 'wprm-admin-modal-recipe-quicklink' }
                            activeClass="active"
                            spy={true}
                            offset={-10}
                            smooth={true}
                            duration={400}
                            key={index}
                        >
                            { group.name }
                        </Link>
                    ))
                }
            </div>
            <Element className="wprm-admin-modal-content" id="wprm-admin-modal-recipe-content">
                {
                    props.loadingRecipe
                    ?
                    <Loader/>
                    :
                    <form className="wprm-admin-modal-recipe-fields">
                        {
                            structure.map((group, index) => (
                                <FieldGroup
                                    header={ group.name }
                                    id={ group.id }
                                    key={ 100 * props.forceRerender + index }
                                >
                                    { group.elem }
                                </FieldGroup>
                            ))
                        }
                    </form>
                }
            </Element>
            <div id="wprm-admin-modal-toolbar-container"></div>
            {
                'waiting' === props.saveResult
                ?
                <Footer
                    savingChanges={ false }
                    leftActions={ historyEnabled ? renderHistoryControls : false }
                >
                    <CopyToClipboard
                        text={JSON.stringify( props.recipe )}
                        onCopy={(text, result) => {
                            if ( result ) {
                                alert( __wprm( 'The recipe has been copied and can be pasted in "Restore Backup" at the top of the modal.' ) );
                            } else {
                                alert( __wprm( 'Something went wrong. Please contact support.' ) );
                            }
                        }}
                    >
                        <a href="#" onClick={ (e) => { e.preventDefault(); } }>
                            { __wprm( 'This is taking a long time. Maybe something went wrong?' ) } { __wprm( 'Click to copy the recipe to your clipboard.' ) }
                        </a>
                    </CopyToClipboard> <Loader />
                </Footer>
                :
                <Footer
                    savingChanges={ props.savingChanges }
                    leftActions={ historyEnabled ? renderHistoryControls : false }
                >
                    {
                        'failed' === props.saveResult
                        &&
                        <CopyToClipboard
                            text={JSON.stringify( props.recipe )}
                            onCopy={(text, result) => {
                                if ( result ) {
                                    alert( __wprm( 'The recipe has been copied and can be pasted in "Restore Backup" at the top of the modal.' ) );
                                } else {
                                    alert( __wprm( 'Something went wrong. Please contact support.' ) );
                                }
                            }}
                        >
                            <a href="#" onClick={ (e) => { e.preventDefault(); } }>
                                { __wprm( 'Something went wrong during saving.' ) } { __wprm( 'Click to copy the recipe to your clipboard.' ) }
                            </a>
                        </CopyToClipboard>
                    }
                    {
                        'ok' === props.saveResult
                        ?
                        <span>{ __wprm( 'Saved successfully' ) }</span>
                        :
                        null
                    }
                    <button
                        className="button"
                        onClick={ () => {
                            Api.utilities.previewRecipe( JSON.stringify( props.recipe ) ).then((previewUrl) => {
                                if ( previewUrl ) {
                                    window.open( previewUrl, '_blank' );
                                } else {
                                    alert( __wprm( 'Something went wrong. The preview could not be loaded.' ) );
                                }
                            });
                        } }
                    >
                        { __wprm( 'Preview' ) }
                    </button>
                    <button
                        className="button button-primary"
                        onClick={ () => {
                            if ( nutritionWarning ) {
                                const confirmed = confirm(
                                    __wprm( 'You have nutrition warnings that indicate changes to ingredients or serving size may require updating the nutrition facts. Are you sure you want to save anyway?' )
                                );
                                if ( ! confirmed ) {
                                    return;
                                }
                            }
                            props.saveRecipe( false );
                        } }
                        disabled={ ! props.changesMade }
                    >
                        { __wprm( 'Save' ) }
                    </button>
                    <button
                        className="button button-primary"
                        onClick={ () => {
                            if ( props.changesMade ) {
                                if ( nutritionWarning ) {
                                    const confirmed = confirm(
                                        __wprm( 'You have nutrition warnings that indicate changes to ingredients or serving size may require updating the nutrition facts. Are you sure you want to save anyway?' )
                                    );
                                    if ( ! confirmed ) {
                                        return;
                                    }
                                }
                                props.saveRecipe( true );
                            } else {
                                props.onCloseModal();
                            }
                        } }
                    >
                        { props.changesMade ? __wprm( 'Save & Close' ) : __wprm( 'Close' ) }
                    </button>
                </Footer>
            }
        </Fragment>
    );
}
export default EditRecipe;
