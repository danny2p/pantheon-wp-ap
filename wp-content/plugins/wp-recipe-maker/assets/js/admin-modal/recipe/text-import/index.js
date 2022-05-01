import React, { Component, Fragment } from 'react';
import he from 'he';

import '../../../../css/admin/modal/text-import.scss';

import Header from '../../general/Header';
import Footer from '../../general/Footer';
import { __wprm } from 'Shared/Translations';

import FieldContainer from '../../fields/FieldContainer';
import FieldText from '../../fields/FieldText';
import FieldTextarea from '../../fields/FieldTextarea';

import Api from 'Shared/Api';
import SelectGroups from './SelectGroups';

export default class TextImport extends Component {
    constructor(props) {
        super(props);

        this.textInput = React.createRef();

        let text = '';
        if ( props.text ) {
            text = this.cleanUpText( props.text );
        }

        this.state = {
            text,
            name: false,
            summary: false,
            equipment: false,
            ingredients: false,
            instructions: false,
            notes: false,
            isParsing: false,
        };

        this.cleanUpText = this.cleanUpText.bind(this);
        this.setSelection = this.setSelection.bind(this);
        this.useValues = this.useValues.bind(this);
    }

    componentDidMount() {
        this.textInput.current.focus();
    }

    cleanUpText( text ) {
        text = text.replace( /(<([^>]+)>)/ig, '' );
        text = he.decode( text );

        return text;
    }

    setSelection( field ) {
        const textArea = this.textInput.current;
        let selection = textArea.value.substring( textArea.selectionStart, textArea.selectionEnd );

        selection = selection ? selection : false;

        if ( 'equipment' === field || 'ingredients' === field || 'instructions' === field ) {
            selection = this.getSeperateFields( selection, field );
        }

        if ( selection !== this.state[ field ] ) {
            let newState = {};
            newState[ field ] = selection;
            this.setState(newState);
        }
    }

    getSeperateFields( content, field ) {
        if ( false === content ) {
            return false;
        }

        // Splitting on punctuation as well?
        if ( 'instructions' === field && 'punctuation' === wprm_admin_modal.settings.import_instructions_split ) {
            content = content.replace(/([!\.\?]+)/gm, '$1\n');
        }

        // Split into seperate lines.
        let fields = [];
        let lines = content.split(/[\r\n]+/);

        // Loop over all lines in selection.
        for ( let line of lines ) {
            // Trim and remove bullet points.
            line = line.trim();
            line = line.replace(/^(\d+\)\s+|\d+\.\s+|[a-z]+\)\s+|•\s+|[A-Z]+\.\s+|[IVX]+\.\s+)/, '');

            if ( line ) {
                fields.push({
                    group: false,
                    text: line,
                });
            }
        }

        // Return false if there weren't any non-empty lines.
        if ( ! fields.length ) {
            return false;
        }

        return fields;
    }

    useValues() {
        let newRecipe = {};

        // Simple matching.
        if ( false !== this.state.name )    { newRecipe.name = this.state.name }
        if ( false !== this.state.summary ) { newRecipe.summary = this.state.summary }
        if ( false !== this.state.notes )   { newRecipe.notes = this.state.notes }

        // Equipment.
        if ( false !== this.state.equipment ) {
            let equipment = [];

            this.state.equipment.map( ( equipmentItem, index ) => {
                equipment.push({
                    uid: index,
                    name: equipmentItem.text,
                });
            });

            newRecipe.equipment = equipment;
        }

        // Instructions.
        if ( false !== this.state.instructions ) {
            let instructions_flat = [];

            this.state.instructions.map( ( instruction, index ) => {
                if ( instruction.group ) {
                    instructions_flat.push({
                        uid: index,
                        type: 'group',
                        name: instruction.text,
                    });
                } else {
                    instructions_flat.push({
                        uid: index,
                        type: 'instruction',
                        text: instruction.text,
                        image: 0,
                        image_url: '',
                    });
                }
            });

            newRecipe.instructions_flat = instructions_flat;
        }

        // Ingredients.
        let ingredients_flat = [];
        let ingredientsToParse = {};

        if ( false !== this.state.ingredients ) {
            this.state.ingredients.map((ingredient, index) => {
                if ( ingredient.group ) {
                    ingredients_flat.push({
                        uid: index,
                        type: 'group',
                        name: ingredient.text,
                    });
                } else {
                    ingredients_flat.push({
                        uid: index,
                        type: 'ingredient',
                        amount: '',
                        unit: '',
                        name: '',
                        notes: '',
                    });

                    ingredientsToParse[ index ] = ingredient.text;
                }
            })

            newRecipe.ingredients_flat = ingredients_flat;
        }

        // Parse ingredients?
        if ( 0 < Object.keys( ingredientsToParse ).length ) {
            this.setState({
                isParsing: true,
            }, () => {
                Api.import.parseIngredients(ingredientsToParse).then((data) => {
                    if (data) {
                        for ( let index in data.parsed ) {
                            const parsedIngredient = data.parsed[ index ];
    
                            newRecipe.ingredients_flat[ index ] = {
                                ...newRecipe.ingredients_flat[ index ],
                                ...parsedIngredient,
                            }
                        }
    
                        this.props.onImportValues( newRecipe );
                    } else {
                        this.setState({
                            isParsing: false,
                        });
                    }
                });
            });
        } else {
            this.props.onImportValues( newRecipe );
        }
    }

    render() {
        const changesMade = false !== this.state.name
                            || false !== this.state.summary
                            || false !== this.state.equipment
                            || false !== this.state.ingredients
                            || false !== this.state.instructions
                            || false !== this.state.notes;

        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.onCloseModal }
                >
                    {
                        this.props.recipe.name
                        ?
                        `${this.props.recipe.name} - ${ __wprm( 'Import from Text' ) }`
                        :
                        `${ __wprm( 'Recipe' ) } - ${ __wprm( 'Import from Text' ) }`
                    }
                </Header>
                <div className="wprm-admin-modal-field-text-import-container">
                    <h2>{ __wprm( '1. Highlight text and click the corresponding button' ) }</h2>
                    <div className="wprm-admin-modal-field-text-import-selection">
                        <div className="wprm-admin-modal-field-text-import-buttons">
                            <button
                                className={ false !== this.state.name ? 'button wprm-selection-made' : 'button' }
                                onClick={() => this.setSelection( 'name' ) }
                            >{ __wprm( 'Name' ) }</button>
                            <button
                                className={ false !== this.state.summary ? 'button wprm-selection-made' : 'button' }
                                onClick={() => this.setSelection( 'summary' ) }
                            >{ __wprm( 'Summary' ) }</button>
                            <button
                                className={ false !== this.state.equipment ? 'button wprm-selection-made' : 'button' }
                                onClick={() => this.setSelection( 'equipment' ) }
                            >{ __wprm( 'Equipment' ) }</button>
                            <button
                                className={ false !== this.state.ingredients ? 'button wprm-selection-made' : 'button' }
                                onClick={() => this.setSelection( 'ingredients' ) }
                            >{ __wprm( 'Ingredients' ) }</button>
                            <button
                                className={ false !== this.state.instructions ? 'button wprm-selection-made' : 'button' }
                                onClick={() => this.setSelection( 'instructions' ) }
                            >{ __wprm( 'Instructions' ) }</button>
                            <button
                                className={ false !== this.state.notes ? 'button wprm-selection-made' : 'button' }
                                onClick={() => this.setSelection( 'notes' ) }
                            >{ __wprm( 'Notes' ) }</button>
                        </div>
                        <textarea
                            ref={this.textInput}
                            value={this.state.text}
                            placeholder={ __wprm( 'Paste or type recipe' ) }
                            onChange={(e) => {
                                this.setState({
                                    text: this.cleanUpText( e.target.value ),
                                });
                            }}
                        />
                    </div>
                    <h2>{ __wprm( '2. Fine-tune selections' ) }</h2>
                    <div className="wprm-admin-modal-field-text-import-finetune">
                        {
                            ! changesMade
                            ?
                            <p>{ __wprm( 'Make a selection using the buttons above first.' ) }</p>
                            :
                            <Fragment>
                                {
                                    false !== this.state.name
                                    &&
                                    <FieldContainer label={ __wprm( 'Name' ) }>
                                        <FieldText
                                            name="recipe-name"
                                            value={ this.state.name }
                                            onChange={ (name) => {
                                                this.setState({ name });
                                            }}
                                        />
                                    </FieldContainer>
                                }
                                {
                                    false !== this.state.summary
                                    &&
                                    <FieldContainer label={ __wprm( 'Summary' ) }>
                                        <FieldTextarea
                                            value={ this.state.summary }
                                            onChange={ (summary) => {
                                                this.setState({ summary });
                                            }}
                                        />
                                    </FieldContainer>
                                }
                                {
                                    false !== this.state.equipment
                                    &&
                                    <FieldContainer label={ __wprm( 'Equipment' ) }>
                                        {
                                            this.state.equipment.map((equipment, index) => (
                                                <div className="wprm-admin-modal-field-text-import-equipment-field" key={ index }>
                                                    <FieldText
                                                        value={ equipment.text }
                                                        onChange={ (name) => {
                                                            let newEquipment = JSON.parse( JSON.stringify( this.state.equipment ) );
                                                            newEquipment[ index ].text = name;
                                                            this.setState({ equipment: newEquipment });
                                                        }}
                                                    />
                                                </div>
                                            ))
                                        }
                                    </FieldContainer>
                                }
                                {
                                    false !== this.state.ingredients
                                    &&
                                    <FieldContainer label={ __wprm( 'Ingredients' ) } help={ __wprm( 'Use the checkboxes to indicate group headers (like Frosting and Cake)' ) }>
                                        <SelectGroups
                                            value={ this.state.ingredients }
                                            onChange={ (ingredients) => {
                                                this.setState({ ingredients });
                                            }}
                                        />
                                    </FieldContainer>
                                }
                                {
                                    false !== this.state.instructions
                                    &&
                                    <FieldContainer label={ __wprm( 'Instructions' ) } help={__wprm( 'Use the checkboxes to indicate group headers (like Frosting and Cake)' ) }>
                                        <SelectGroups
                                            value={ this.state.instructions }
                                            onChange={ (instructions) => {
                                                this.setState({ instructions });
                                            }}
                                        />
                                    </FieldContainer>
                                }
                                {
                                    false !== this.state.notes
                                    &&
                                    <FieldContainer label={ __wprm( 'Notes' ) }>
                                        <FieldTextarea
                                            value={ this.state.notes }
                                            onChange={ (notes) => {
                                                this.setState({ notes });
                                            }}
                                        />
                                    </FieldContainer>
                                }
                            </Fragment>
                        }
                    </div>
                </div>
                <Footer
                    savingChanges={ this.state.isParsing }
                >
                    <button
                        className="button"
                        onClick={ this.props.onCancel }
                    >
                        { __wprm( 'Cancel' ) }
                    </button>
                    <button
                        className="button button-primary"
                        onClick={ this.useValues }
                        disabled={ ! changesMade }
                    >
                        { __wprm( 'Use these Values' ) }
                    </button>
                </Footer>
            </Fragment>
        );
    }
}