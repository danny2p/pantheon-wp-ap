import React, { Component, Fragment } from 'react';
import he from 'he';

import '../../../../css/admin/modal/bulk-add.scss';

import Header from '../../general/Header';
import Footer from '../../general/Footer';
import { __wprm } from 'Shared/Translations';

import FieldContainer from '../../fields/FieldContainer';
import FieldText from '../../fields/FieldText';
import FieldTextarea from '../../fields/FieldTextarea';

import Api from 'Shared/Api';
import SelectGroups from '../text-import/SelectGroups';

export default class BulkAdd extends Component {
    constructor(props) {
        super(props);

        this.textInput = React.createRef();

        this.state = {
            text: '',
            value: false,
            isParsing: false,
        };

        this.cleanUpText = this.cleanUpText.bind(this);
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

    getSeperateFields( content ) {
        if ( false === content ) {
            return false;
        }

        // Splitting on punctuation as well?
        if ( 'instructions' === this.props.field && 'punctuation' === wprm_admin_modal.settings.import_instructions_split ) {
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
        // Instructions.
        if ( 'instructions' === this.props.field ) {
            let instructions_flat = [];

            this.state.value.map( ( instruction, index ) => {
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

            this.props.onBulkAdd( instructions_flat );
            return;
        }

        // Ingredients.
        if ( 'ingredients' === this.props.field ) {
            let ingredients_flat = [];
            let ingredientsToParse = {};

            this.state.value.map((ingredient, index) => {
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

            // Parse ingredients?
            if ( 0 < Object.keys( ingredientsToParse ).length ) {
                this.setState({
                    isParsing: true,
                }, () => {
                    Api.import.parseIngredients(ingredientsToParse).then((data) => {
                        if (data) {
                            for ( let index in data.parsed ) {
                                const parsedIngredient = data.parsed[ index ];
        
                                ingredients_flat[ index ] = {
                                    ...ingredients_flat[ index ],
                                    ...parsedIngredient,
                                }
                            }
        
                            this.props.onBulkAdd( ingredients_flat );
                        } else {
                            this.setState({
                                isParsing: false,
                            });
                        }
                    });
                });
            } else {
                this.props.onBulkAdd( ingredients_flat );
            }
        }
    }

    render() {
        const changesMade = false !== this.state.value;

        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.onCloseModal }
                >
                    {
                        'ingredients' === this.props.field ? __wprm( 'Bulk Add Ingredients' ) : __wprm( 'Bulk Add Instructions' )
                    }
                </Header>
                <div
                    className={ `wprm-admin-modal-bulk-add-container wprm-admin-modal-bulk-add-${ this.props.field }-container` }
                >
                    <h2>1. { __wprm( 'Paste in text' ) }</h2>
                    <div className="wprm-admin-modal-bulk-add-input">
                        <textarea
                            ref={this.textInput}
                            value={this.state.text}
                            placeholder={ __wprm( 'Paste or type recipe' ) }
                            onChange={(e) => {
                                const text = this.cleanUpText( e.target.value );
                                const value = text ? this.getSeperateFields( text ) : false;

                                this.setState({
                                    text,
                                    value,
                                });
                            }}
                        />
                    </div>
                    <h2>2. { __wprm( 'Fine-tune' ) }</h2>
                    <div className="wprm-admin-modal-bulk-add-input-finetune">
                        {
                            ! this.state.text
                            ?
                            <p>{ __wprm( 'Paste in text first.' ) }</p>
                            :
                            <FieldContainer label={ 'ingredients' === this.props.field ? __wprm( 'Ingredients' ) : __wprm( 'Instructions' ) } help={__wprm( 'Use the checkboxes to indicate group headers (like Frosting and Cake)' ) }>
                                <SelectGroups
                                    value={ this.state.value }
                                    onChange={ (value) => {
                                        this.setState({ value });
                                    }}
                                />
                            </FieldContainer>
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
                        { __wprm( 'Add' ) }
                    </button>
                </Footer>
            </Fragment>
        );
    }
}