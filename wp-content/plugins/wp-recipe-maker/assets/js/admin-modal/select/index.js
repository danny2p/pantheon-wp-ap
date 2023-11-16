import React, { Component, Fragment } from 'react';

import '../../../css/admin/modal/select.scss';

import { __wprm } from 'Shared/Translations';
import Header from '../general/Header';
import Footer from '../general/Footer';

import SelectRecipe from './SelectRecipe';
import SelectList from './SelectList';

const firstRecipeOnPage = {
    id: 0,
    text: __wprm( 'First recipe on page' ),
};

export default class Select extends Component {
    constructor(props) {
        super(props);

        let type = 'recipe';
        if ( props.args.hasOwnProperty( 'type' ) ) {
            type = props.args.type;
        }

        let selection = false;
        if ( 'recipe' === type && props.args.fields.recipe.showFirst ) {
            selection = firstRecipeOnPage;
        }
    
        this.state = {
            type,
            selection,
        };
    }

    selectionsMade() {
        return false !== this.state.selection;
    }

    render() {
        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.maybeCloseModal }
                >
                    {
                        this.props.args.title
                        ?
                        this.props.args.title
                        :
                        'WP Recipe Maker'
                    }
                </Header>
                <div className="wprm-admin-modal-select-container">
                    {
                        'recipe' === this.state.type
                        &&
                        <Fragment>
                            {
                                this.props.args.fields.recipe
                                ?
                                <SelectRecipe
                                    options={
                                        this.props.args.fields.recipe.showFirst
                                        ?
                                        [firstRecipeOnPage]
                                        :
                                        []
                                    }
                                    value={ this.state.selection }
                                    onValueChange={(selection) => {
                                        this.setState({ selection });
                                    }}
                                />
                                :
                                null
                            }
                        </Fragment>
                    }
                    {
                        'list' === this.state.type
                        &&
                        <Fragment>
                            <SelectList
                                options={ [] }
                                value={ this.state.selection }
                                onValueChange={(selection) => {
                                    this.setState({ selection });
                                }}
                            />
                        </Fragment>
                    }
                </div>
                <Footer
                    savingChanges={ false }
                >
                    <button
                        className="button button-primary"
                        onClick={ () => {
                            let data = {};

                            switch ( this.state.type ) {
                                case 'list':
                                    data.list = this.state.selection;
                                    break;
                                default:
                                    data.recipe = this.state.selection;
                            }

                            if ( 'function' === typeof this.props.args.nextStepCallback ) {
                                this.props.args.nextStepCallback( data );
                            } else {
                                if ( 'function' === typeof this.props.args.insertCallback ) {
                                    this.props.args.insertCallback( data );
                                }
                                this.props.maybeCloseModal();
                            }
                        } }
                        disabled={ ! this.selectionsMade() }
                    >
                        {
                            this.props.args.button
                            ?
                            this.props.args.button
                            :
                            __wprm( 'Select' )
                        }
                    </button>
                </Footer>
            </Fragment>
        );
    }
}