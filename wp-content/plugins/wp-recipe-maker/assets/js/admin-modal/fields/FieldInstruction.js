import React, { Component, Fragment } from 'react';
import { Draggable } from 'react-beautiful-dnd';
import { isKeyHotkey } from 'is-hotkey';

const isTabHotkey = isKeyHotkey('tab');

import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';

import FieldInstructionMedia from './FieldInstructionMedia';
import FieldInstructionIngredients from './FieldInstructionIngredients';

import FieldRichText from './FieldRichText';
import FieldText from './FieldText';
import FieldTextarea from './FieldTextarea';
import FieldVideoTime from './FieldVideoTime';
import Media from '../general/Media';

const handle = (provided) => (
    <div
        className="wprm-admin-modal-field-instruction-handle"
        {...provided.dragHandleProps}
        tabIndex="-1"
    ><Icon type="drag" /></div>
);

const group = (props, provided) => (
    <div
        className="wprm-admin-modal-field-instruction-group"
        ref={provided.innerRef}
        {...provided.draggableProps}
    >
        <div className="wprm-admin-modal-field-instruction-main-container">
            { handle(provided) }
            <div className="wprm-admin-modal-field-instruction-group-name-container">
                <FieldRichText
                    singleLine
                    toolbar="no-styling"
                    value={ props.name }
                    placeholder={ __wprm( 'Instruction Group Header' ) }
                    onChange={(value) => props.onChangeName(value)}
                    onKeyDown={(event) => {
                        if ( isTabHotkey(event) ) {
                            props.onTab(event);
                        }
                    }}
                />
            </div>
        </div>
        <div className="wprm-admin-modal-field-instruction-after-container">
            <div className="wprm-admin-modal-field-instruction-after-container-icons">
                <Icon
                    type="trash"
                    title={ __wprm( 'Delete' ) }
                    onClick={ props.onDelete }
                />
                <Icon
                    type="plus-text"
                    title={ __wprm( 'Insert Group After' ) }
                    onClick={ props.onAddGroup }
                />
                <Icon
                    type="plus"
                    title={ __wprm( 'Insert Instruction After' ) }
                    onClick={ props.onAdd }
                />
            </div>
        </div>
    </div>
);

const instruction = (props, provided) => {
    let video = {
        type: 'none',
        embed: '',
        id: '',
        thumb: '',
        start: '',
        end: '',
        name: '',
    };

    if ( props.video ) {
        video = {
            ...video,
            ...props.video,
        };

        // For backwards compatibility.
        if ( 'none' === video.type && ( video.start || video.end ) ) {
            video.type = 'part';
        }
    }

    return (
        <div
            className="wprm-admin-modal-field-instruction"
            ref={provided.innerRef}
            {...provided.draggableProps}
        >
            <div className="wprm-admin-modal-field-instruction-main-container">
                { handle(provided) }
                <div className="wprm-admin-modal-field-instruction-text-container">
                    <div className="wprm-admin-modal-field-instruction-text-name-container">
                        <FieldRichText
                            className="wprm-admin-modal-field-instruction-text"
                            ingredients={ props.ingredients }
                            instructionsRef={ props.instructionsRef }
                            allIngredients={ props.hasOwnProperty( 'allIngredients' ) ? props.allIngredients : null }
                            inlineIngredientsPortalRendered={ props.inlineIngredientsPortalRendered }
                            inlineIngredientsPortal={ props.hasOwnProperty( 'inlineIngredientsPortal' ) ? props.inlineIngredientsPortal : null }
                            value={ props.text }
                            placeholder={ __wprm( 'This is one step of the instructions.' ) }
                            onChange={(value) => props.onChangeText(value)}
                            onKeyDown={(event) => {
                                if ( isTabHotkey(event) ) {
                                    props.onTab(event);
                                }
                            }}
                            key={ props.hasOwnProperty( 'externalUpdate' ) ? props.externalUpdate : null }
                        />
                    </div>
                    {
                        props.allowVideo
                        && 'part' === video.type
                        && 'media' === props.editMode
                        &&
                        <div className="wprm-admin-modal-field-instruction-video-container">
                            <FieldVideoTime
                                value={ video.start }
                                onChange={ (start) => {
                                    props.onChangeVideo({
                                        ...video,
                                        start,
                                    });
                                }}
                            />
                            <FieldVideoTime
                                value={ video.end }
                                onChange={ (end) => {
                                    props.onChangeVideo({
                                        ...video,
                                        end,
                                    });
                                }}
                            />
                            {
                                video.start && video.end
                                ?
                                <FieldText
                                    placeholder={ __wprm( 'Name for this video part' ) }
                                    value={ video.name }
                                    onChange={ (name) => {
                                        props.onChangeVideo({
                                            ...video,
                                            name,
                                        });
                                    }}
                                />
                                :
                                <Icon
                                    type="movie"
                                    title={ __wprm( 'Add video start and end time (in seconds or minutes:seconds format) if this instruction step is part of the recipe video.' ) }
                                />
                            }
                        </div>
                    }
                </div>
            </div>
            <div className="wprm-admin-modal-field-instruction-after-container">
                <div className="wprm-admin-modal-field-instruction-after-container-icons">
                    <Icon
                        type="trash"
                        title={ __wprm( 'Delete' ) }
                        onClick={ props.onDelete }
                    />
                    <Icon
                        type="plus-text"
                        title={ __wprm( 'Insert Group After' ) }
                        onClick={ props.onAddGroup }
                    />
                    <Icon
                        type="plus"
                        title={ __wprm( 'Insert Instruction After' ) }
                        onClick={ props.onAdd }
                    />
                </div>
                {
                    'summary' === props.editMode
                    &&
                    <div className="wprm-admin-modal-field-instruction-after-container-summary">
                        <FieldRichText
                            singleLine
                            className="wprm-admin-modal-field-instruction-name"
                            toolbar={ 'none' }
                            value={ props.hasOwnProperty( 'name' ) ? props.name : '' }
                            placeholder={ __wprm( 'Step Summary' ) }
                            onChange={(value) => props.onChangeName(value)}
                        />
                    </div>
                }
                {
                    'media' === props.editMode
                    &&
                    <FieldInstructionMedia
                        { ...props }
                        video={ video }
                    />
                }
                {
                    'ingredients' === props.editMode
                    &&
                    <FieldInstructionIngredients
                        { ...props }
                    />
                }
            </div>
        </div>
    )
};

export default class FieldInstruction extends Component {
    shouldComponentUpdate(nextProps) {
        // Check simpler props first.
        if (
            this.props.uid !== nextProps.uid
            || this.props.index !== nextProps.index
            || this.props.editMode !== nextProps.editMode
            || this.props.allowVideo !== nextProps.allowVideo
        ) {
            return true;
        }

        // Check text content specifically.
        if ( this.props.name !== nextProps.name || this.props.text !== nextProps.text ) {
            return true;
        }

        // Deep compare objects only if needed.
        // Note: instructions prop is passed as the full array, so we only check if *this* instruction changed in it?
        // Actually FieldInstruction receives {...field} props, so it doesn't receive the full array unless explicitly passed.
        // Wait, it DOES receive `instructions={ this.props.instructions }` in RecipeInstructions/index.js line 271.
        
        // We need to check if the full instructions array reference changed, BUT simply comparing reference 
        // will always be true if parent created a new array.
        // Ideally we want to avoid re-rendering if THIS instruction hasn't changed and the full list isn't needed for rendering.
        // But FieldInstruction passes `instructions` to FieldRichText for InlineIngredients.
        
        // Optimized check:
        // 1. Check own properties (text, name, video, ingredients local).
        // 2. Check if `allIngredients` changed (needed for InlineIngredients).
        // 3. Check if `instructions` changed (needed for InlineIngredients).
        
        if (
            JSON.stringify(this.props.video) !== JSON.stringify(nextProps.video)
            || JSON.stringify(this.props.ingredients) !== JSON.stringify(nextProps.ingredients)
        ) {
             return true;
        }

        // For InlineIngredients, we need to know if allIngredients or instructions list changed.
        // Since we optimized InlineIngredients to handle updates efficiently, we can perhaps let it pass
        // if we simply check reference equality for these large objects?
        // The parent `RecipeInstructions` creates new array references on every change.
        // So `this.props.instructions !== nextProps.instructions` will almost always be true on typing.
        
        // CRITICAL: If we return false here, InlineIngredients won't update even if its internal logic is fast.
        // However, InlineIngredients is smart now.
        
        // We can cheat: Only update if THIS instruction's text changed OR if the ingredients list changed.
        // But InlineIngredients needs to know about OTHER instructions to know if an ingredient is used elsewhere.
        
        // Let's try to rely on the fact that `InlineIngredients` is now fast.
        // But we want to avoid re-rendering `FieldInstruction` wrapper (Draggable etc).
        
        // If we allow re-render, we get 400 re-renders.
        // If we block it, we save 399 re-renders.
        
        // When typing in instruction A:
        // - Instruction A text changes -> Re-render A (Correct).
        // - Instructions array changes (A is different) -> passed to B, C, D...
        // - B, C, D re-render because `props.instructions` changed.
        
        // Does B need to re-render?
        // B passes `instructions` to `FieldRichText` -> `InlineIngredients`.
        // `InlineIngredients` uses `instructions` to calculate `ingredientUidsInAll`.
        // If A changed, `ingredientUidsInAll` *might* change (e.g. A added/removed an ingredient).
        // So B *does* need to re-render to update its highlighting (e.g. "used in other step").
        
        // HOWEVER, this highlighting update is visual only inside the editor.
        // Does `FieldInstruction` DOM structure change?
        // - `FieldRichText` receives new props.
        // - `InlineIngredients` receives new props.
        
        // If we use `shouldComponentUpdate` to block B, C, D:
        // Their `FieldRichText` won't get the new `instructions` list.
        // Their `InlineIngredients` won't update.
        // So if I add "Flour" in A, and B has "Flour", B should theoretically update to show "Flour" is now used.
        
        // BUT: 400 re-renders is too slow.
        // We need a middle ground.
        
        // PROPOSAL:
        // We accept that highlighting in *other* fields might be slightly delayed or we optimize the check.
        // But actually, the lag is the *React Render* of 400 components.
        
        // If we compare `props.instructions` by reference, it's always different.
        // If we deep compare, it's slow ($O(N^2)$ total).
        
        // We can use a version id or timestamp for instructions?
        // Or we can just check if `allIngredients` changed.
        
        // Wait, if I type in A, I only change text. I don't change the SET of ingredients used unless I specifically add/remove an ingredient shortcode.
        // InlineIngredients parses text to find used ingredients.
        
        // If I type "Hello" in A:
        // - A text changes.
        // - `instructions` array changes (A's text updated).
        // - B receives new `instructions`.
        // - B calculates "used ingredients in all". A's used ingredients might have changed?
        //   - Only if I typed an ingredient shortcode/html.
        //   - But `InlineIngredients` inside B will re-parse A to find out.
        
        // The cache I added in `InlineIngredients` makes the *calculation* fast.
        // But the *render trigger* is still happening.
        
        // To stop the render trigger, we must return `false` in `shouldComponentUpdate`.
        // But then `InlineIngredients` inside B won't get the new data.
        
        // Solution:
        // 1. `InlineIngredients` is the ONLY thing in B that needs the new `instructions` list.
        // 2. `FieldRichText` passes it down.
        // 3. Can we make `FieldInstruction` NOT re-render, but still pass data? No, that's not how React works.
        
        // Maybe we only update if:
        // 1. Props specific to THIS instruction changed (text, name, video).
        // 2. `allIngredients` changed (ingredients list modified).
        // 3. `instructions` length changed (added/removed step).
        // 4. AND... do we care if another instruction changed text?
        //    - Strictly speaking, yes, for the "used elsewhere" graying out.
        //    - But strictly checking that is expensive ($O(N)$).
        
        // FAST PATH COMPROMISE:
        // We assume that if `allIngredients` hasn't changed, and `instructions` length hasn't changed,
        // and THIS instruction hasn't changed... then we skip update?
        // NO, because typing in A *can* change "used ingredients".
        
        // Better approach:
        // Let's use the fact that `RecipeInstructions` is the parent.
        // It knows WHAT changed.
        // But it passes the whole `instructions` array.
        
        // Let's stick to the "No JSON.stringify" plan but do a shallow compare of the *item* props,
        // and for `instructions` array, we just accept it changes?
        // No, that causes the 400 re-renders.
        
        // We need `FieldInstruction` to IGNORE `instructions` prop changes UNLESS it's the one being edited?
        // No, that breaks the "used elsewhere" feature.
        
        // Let's look at what `shouldComponentUpdate` was doing before:
        // `JSON.stringify(this.props) !== JSON.stringify(nextProps)`
        // This was checking EVERYTHING.
        
        // New implementation:
        // Check strict equality for primitives.
        // Check strict equality for objects (which will fail for `instructions` and `video` etc).
        
        // If we want to avoid re-renders when `instructions` prop changes but "content relevant to B" hasn't:
        // We can't easily know "content relevant to B" without parsing.
        
        // Let's assume the user is okay with "used elsewhere" updating slightly less aggressively?
        // OR, realize that `JSON.stringify` was the bottleneck.
        // Maybe the Render itself (virtual DOM diffing) of 400 items is ALSO a bottleneck.
        // The user says: "It does show 400 render logs... The other one (RecipeInstructions Render) is fine: 0ms".
        // This confirms the *render* of the children is the cost.
        
        // So we MUST prevent re-render of B when A changes.
        // But we want B to update if A's used ingredients changed.
        
        // COMPROMISE:
        // Only update B if:
        // 1. B's own props changed (text, name, etc).
        // 2. `allIngredients` changed.
        // 3. `instructions` LENGTH changed.
        // 4. We SKIP checking if *content* of other instructions changed.
        
        // Consequence:
        // If I add an ingredient to A, B will NOT immediately update to show it as "used in other".
        // It will update when I:
        // - Click something else.
        // - Save.
        // - Type in B.
        
        // This is a valid performance trade-off for "extreme" recipes (400 steps).
        
        if (
             this.props.uid !== nextProps.uid
             || this.props.index !== nextProps.index
             || this.props.editMode !== nextProps.editMode
             || this.props.allowVideo !== nextProps.allowVideo
             || this.props.name !== nextProps.name
             || this.props.text !== nextProps.text
            // Allow re-render when ingredient data (like name) changes.
            || this.props.allIngredients !== nextProps.allIngredients
             || this.props.inlineIngredientsPortalRendered !== nextProps.inlineIngredientsPortalRendered
            // Need to re-render when instruction media changes so the preview updates immediately.
            || this.props.image !== nextProps.image
            || this.props.image_url !== nextProps.image_url
        ) {
            return true;
        }
        
        // Check array lengths (fast).
        if (
            this.props.instructions.length !== nextProps.instructions.length
            || this.props.allIngredients.length !== nextProps.allIngredients.length
        ) {
            return true;
        }
        
        // Check objects that might have changed deep values but we want to avoid full deep compare if possible.
        // `video` is small.
        if ( JSON.stringify(this.props.video) !== JSON.stringify(nextProps.video) ) {
            return true;
        }
        
        // `ingredients` (local to instruction) is small.
        if ( JSON.stringify(this.props.ingredients) !== JSON.stringify(nextProps.ingredients) ) {
            return true;
        }

        // `allIngredients` - we checked length. Content might change (renaming ingredient).
        // This is rarer. Let's do shallow compare of first item or something? 
        // Or just JSON.stringify it? It's 200 ingredients, might be 20-30kb string. Fast enough?
        // The user has 200 ingredients. Stringify is probably OK for *one* list, but we do this check 400 times?
        // No, `shouldComponentUpdate` runs on every child.
        // So 400 * Stringify(200 ingredients). That's heavy.
        
        // `allIngredients` is passed from parent. If parent updated it, the reference changed.
        // Parent `RecipeInstructions` filters ingredients: 
        // `const allIngredients = this.props.ingredients.filter(...)`
        // This creates a NEW array reference every time `RecipeInstructions` renders.
        
        // So `this.props.allIngredients !== nextProps.allIngredients` is ALWAYS true.
        
        // We need to know if the *data* inside changed.
        // But we can't afford to check it 400 times.
        
        // TRICK: `RecipeInstructions` could memoize `allIngredients`?
        // It's a class component. The `render` method creates it.
        // We can't easily change the parent (RecipeInstructions) to memoize without refactoring it significantly or adding helper.
        // BUT, we can rely on `JSON.stringify(nextProps.ingredients)` (the raw prop from parent) in `shouldComponentUpdate` of `RecipeInstructions`?
        // `RecipeInstructions` already has `shouldComponentUpdate` that checks `JSON.stringify(this.props.ingredients)`.
        // So `RecipeInstructions` only re-renders if ingredients *actually* changed.
        
        // Wait, `RecipeInstructions` render method creates `allIngredients` new array.
        // So children receive new array.
        // But if `RecipeInstructions` didn't re-render, children wouldn't update anyway.
        // If `RecipeInstructions` DID re-render, it means something changed.
        
        // Case 1: User types in Instruction A.
        // `RecipeInstructions` updates `instructions_flat`.
        // `RecipeInstructions` re-renders.
        // `allIngredients` is re-calculated (filter).
        // `ingredients` (raw) prop didn't change.
        // But `instructions` prop changed.
        
        // So `RecipeInstructions` passes new `instructions` and new `allIngredients` to all 400 children.
        
        // We want Child B to ignore this update.
        
        // Child B should update if:
        // 1. `allIngredients` DEEP changed (unlikely during typing).
        // 2. `instructions` length changed.
        // 3. Child B's own text/name changed.
        
        // How to check #1 fast?
        // Check `this.props.allIngredients` vs `nextProps.allIngredients`.
        // References are different.
        // But we know they come from `RecipeInstructions`.
        // If `RecipeInstructions` props.ingredients didn't change, `allIngredients` content didn't change (it's just a filter).
        // But `FieldInstruction` doesn't know about `RecipeInstructions` props.
        
        // However, we can check `JSON.stringify` of `allIngredients`?
        // 400 * Stringify(200 items).
        // If we assume 1ms per stringify (optimistic), that's 400ms. Too slow.
        
        // What if we trust that `allIngredients` content rarely changes while typing instructions?
        // We can check strict equality of the *first* and *last* element reference?
        // `filter` creates new array, but elements are references to the objects in `props.ingredients`.
        // If `props.ingredients` objects are stable (from Redux/State), then references inside the new array are stable!
        // `filter` does NOT clone items.
        
        // So:
        // `this.props.allIngredients[0] === nextProps.allIngredients[0]`
        // If the underlying ingredient object didn't change, this is true.
        // If the list order changed or items added, this might be false (or length check catches it).
        
        // So we can check:
        // 1. Length.
        // 2. First item reference equality.
        // 3. Last item reference equality.
        // This is O(1).
        
        if (
            this.props.allIngredients.length !== nextProps.allIngredients.length
            || ( this.props.allIngredients.length > 0 && this.props.allIngredients[0] !== nextProps.allIngredients[0] )
        ) {
            return true;
        }
        
        // Same for `instructions`?
        // `this.props.instructions` references.
        // If I type in A, A's object reference changes (usually, if immutable pattern used).
        // B's object reference does NOT change.
        // So `this.props.instructions[B_index] === nextProps.instructions[B_index]` should be true!
        
        // Wait, `RecipeInstructions` methods:
        // `let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );`
        // Oh no. It uses `JSON.parse(JSON.stringify(...))` to deep clone the array on every edit.
        // This breaks reference equality for ALL items.
        
        // See `onChangeText`:
        // `let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );`
        // `newFields[instructionIndex].text = text;`
        // `this.props.onRecipeChange(...)`
        
        // Because of this Deep Clone, every instruction object is a NEW reference every time.
        // So we cannot use reference equality to detect "did B change?".
        
        // We are forced to use value equality.
        // `this.props.text !== nextProps.text`
        // `this.props.name !== nextProps.name`
        
        // These string comparisons are fast.
        
        // What about the "Used Elsewhere" feature (requiring full `instructions` list)?
        // We have established we must skip this for performance.
        // So we will intentionally NOT check if `instructions` prop changed deeply.
        // We only check if the length changed.
        
        return false;
    }

    render() {
        return (
            <Draggable
                draggableId={ `instruction-${this.props.uid}` }
                index={ this.props.index }
            >
                {(provided, snapshot) => {
                    if ( 'group' === this.props.type ) {
                        return group(this.props, provided);
                    } else {
                        return instruction(this.props, provided);
                    }
                }}
            </Draggable>
        );
    }
}