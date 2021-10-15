import React, { Component, Fragment } from 'react';

import '../../../css/admin/modal/select.scss';

import { __wprm } from 'Shared/Translations';
import Header from '../general/Header';
import Footer from '../general/Footer';

import SelectRecipe from './SelectRecipe';

const firstRecipeOnPage = {
    id: 0,
    text: __wprm( 'First recipe on page' ),
};

export default class Select extends Component {
    constructor(props) {
        super(props);

        let recipe = false;
        if ( props.args.fields.recipe.showFirst ) {
            recipe = firstRecipeOnPage;
        }
    
        this.state = {
            recipe,
        };
    }

    selectionsMade() {
        return false !== this.state.recipe;
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
                            value={ this.state.recipe }
                            onValueChange={(recipe) => {
                                this.setState({ recipe });
                            }}
                        />
                        :
                        null
                    }
                </div>
                <Footer
                    savingChanges={ false }
                >
                    <button
                        className="button button-primary"
                        onClick={ () => {
                            if ( 'function' === typeof this.props.args.nextStepCallback ) {
                                this.props.args.nextStepCallback( this.state );
                            } else {
                                if ( 'function' === typeof this.props.args.insertCallback ) {
                                    this.props.args.insertCallback( this.state );
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