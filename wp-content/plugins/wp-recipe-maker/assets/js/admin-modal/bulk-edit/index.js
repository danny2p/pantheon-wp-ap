import React, { Component, Fragment } from 'react';

import '../../../css/admin/modal/bulk-edit.scss';

import Api from 'Shared/Api';
import { __wprm } from 'Shared/Translations';
import Button from 'Shared/Button';

import Header from '../general/Header';
import Footer from '../general/Footer';

import ActionsAnalytics from './ActionsAnalytics';
import ActionsCollection from './ActionsCollection';
import ActionsEquipment from './ActionsEquipment';
import ActionsIngredient from './ActionsIngredient';
import ActionsIngredientUnit from './ActionsIngredientUnit';
import ActionsRating from './ActionsRating';
import ActionsRecipe from './ActionsRecipe';
import ActionsRevision from './ActionsRevision';
import ActionsTaxonomy from './ActionsTaxonomy';
import ActionsTrash from './ActionsTrash';

let actions = {
    'analytics': {
        label: __wprm( 'Actions' ),
        elem: ActionsAnalytics,
    },
    'rating': {
        label: __wprm( 'Ratings' ),
        elem: ActionsRating,
    },
    'recipe': {
        label: __wprm( 'Recipes' ),
        elem: ActionsRecipe,
    },
    'revision': {
        label: __wprm( 'Revisions' ),
        elem: ActionsRevision,
    },
    'ingredient': {
        label: __wprm( 'Ingredients' ),
        elem: ActionsIngredient,
    },
    'ingredient_unit': {
        label: __wprm( 'Ingredient Units' ),
        elem: ActionsIngredientUnit,
    },
    'equipment': {
        label: __wprm( 'Equipment' ),
        elem: ActionsEquipment,
    },
    'trash': {
        label: __wprm( 'Recipes' ),
        elem: ActionsTrash,
    },
    'nutrition_ingredient': {
        label: __wprm( 'Custom Nutrition Ingredients' ),
        elem: ActionsTaxonomy,
    },
    'collection': {
        label: __wprm( 'Saved Collections' ),
        elem: ActionsCollection,
    },
};

Object.keys(wprm_admin_modal.categories).map((id) => {
    const taxonomy = wprm_admin_modal.categories[ id ];

    actions[ id ] = { 
        label: taxonomy.label,
        elem: ActionsTaxonomy,
    };
});

export default class BulkEdit extends Component {
    constructor(props) {
        super(props);

        this.state = {
            route: props.args.hasOwnProperty( 'route' ) ? props.args.route : 'recipe', 
            type: props.args.hasOwnProperty( 'type' ) ? props.args.type : 'recipe', 
            ids: props.args.hasOwnProperty( 'ids' ) ? props.args.ids : [],
            action: false,
            savingChanges: false,
            result: false,
        };

        // Bind functions.
        this.onBulkEdit = this.onBulkEdit.bind(this);
        this.allowCloseModal = this.allowCloseModal.bind(this);
    }

    onBulkEdit() {
        if ( this.state.action ) {
            this.setState({
                savingChanges: true,
            }, () => {
                Api.manage.bulkEdit(this.state.route, this.state.type, this.state.ids, this.state.action).then((data) => {
                    let result = false;
                    if ( data.hasOwnProperty('result') )  {
                        result = data.result;
                    }

                    this.setState({
                        savingChanges: false,
                        result,
                    }, () => {
                        if ( 'function' === typeof this.props.args.saveCallback ) {
                            this.props.args.saveCallback();
                        }
                        if ( ! result ) {
                            this.props.maybeCloseModal();
                        }
                    });
                });
            });
        }
    }

    allowCloseModal() {
        return ! this.state.savingChanges;
    }

    changesMade() {
        if ( ! this.state.action || ! this.state.action.type ) {
            return false;
        } else {
            return Array.isArray( this.state.action.options ) && this.state.action.options.length === 0 ? false : true;
        }
    }

    render() {
        const action = actions.hasOwnProperty( this.state.type ) ? actions[ this.state.type ] : false;

        if ( ! action ) {
            return null;
        }

        const Actions = action.elem;
        const bulkEditLabel = `${ __wprm( 'Bulk Edit' ) } ${ this.state.ids.length } ${ action.label }`;

        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.maybeCloseModal }
                >
                    { bulkEditLabel  }
                </Header>
                <div className="wprm-admin-modal-bulk-edit-container">
                    {
                        false === this.state.result
                        ?
                        <Actions
                            action={ this.state.action }
                            onActionChange={ (action) => {
                                this.setState({
                                    action,
                                });
                            } }
                        />
                        :
                        <div dangerouslySetInnerHTML={ { __html: this.state.result } } />
                    }
                </div>
                <Footer
                    savingChanges={ this.state.savingChanges }
                >
                    {
                        false === this.state.result
                        ?
                        <Button
                            isPrimary
                            required={ this.state.action && this.state.action.hasOwnProperty( 'required' ) ? this.state.action.required : null }
                            onClick={this.onBulkEdit}
                            disabled={ ! this.changesMade() }
                        >{ bulkEditLabel }</Button>
                        :
                        <Button
                            isPrimary
                            onClick={ this.props.maybeCloseModal }
                        >{ __wprm( 'Close' ) }</Button>
                    }
                </Footer>
            </Fragment>
        );
    }
}