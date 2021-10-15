import React, { Component, Fragment } from 'react';

import Api from 'Shared/Api';
import FieldContainer from '../../fields/FieldContainer';
import FieldText from '../../fields/FieldText';
import Button from 'Shared/Button';
import { __wprm } from 'Shared/Translations';
import Loader from 'Shared/Loader';

import '../../../../css/admin/modal/recipe/fields/nutrition.scss';

const needsCalculation = Object.values(wprm_admin_modal.nutrition).findIndex((options) => 'calculated' === options.type) !== -1;

export default class RecipeNutrition extends Component {
    constructor(props) {
        super(props);
    
        this.state = {
            calculating: false,
        }
    }

    shouldComponentUpdate(nextProps, nextState) {
        return this.state.calculing !== nextState.calculating
               || JSON.stringify( this.props.servings ) !== JSON.stringify( nextProps.servings )
               || JSON.stringify( this.props.nutrition ) !== JSON.stringify( nextProps.nutrition );
    }

    componentDidMount() {
        this.calculateNutrients();
    }

    componentDidUpdate(prevProps) {
        if ( JSON.stringify( this.props.nutrition ) !== JSON.stringify( prevProps.nutrition ) ) {
            this.calculateNutrients();
        }
    }

    calculateNutrients() {
        if ( needsCalculation && wprm_admin.addons.pro ) {
            this.setState({
                calculating: true,
            }, () => {
                Api.nutrition.getCalculated(this.props.nutrition).then((data) => {
                    if ( data ) {
                        if ( Object.keys( data.calculated ).length > 0 ) {
                            this.props.onRecipeChange( {
                                nutrition: {
                                    ...this.props.nutrition,
                                    ...data.calculated,
                                }
                            } );
                        }
                    }
    
                    this.setState({
                        calculating: false,
                    });
                });
            });
        }
    }

    render() {
        const props = this.props;
        const serving_size = props.nutrition.hasOwnProperty('serving_size') && props.nutrition['serving_size'] ? props.nutrition['serving_size'] : '';
        const serving_unit = props.nutrition.hasOwnProperty('serving_unit') && props.nutrition['serving_unit'] ? props.nutrition['serving_unit'] : '';

        return (
            <Fragment>
                <p>
                    { __wprm( 'These should be the nutrition facts for 1 serving of your recipe.' ) }<br/>
                    {
                        props.servings.amount
                        ?
                        <Fragment>{ __wprm( 'Total servings for this recipe:' ) } { `${props.servings.amount} ${props.servings.unit}`}</Fragment>
                        :
                        <Fragment>{ __wprm( `You don't have the servings field set for your recipe under "General".` ) }</Fragment>
                    }
                </p>
                <div className="wprm-admin-modal-field-nutrition-container">
                    {
                        wprm_admin.addons.premium
                        ?
                        <FieldContainer id="nutrition_serving_size" label={ __wprm( 'Serving Size' ) } help={ __wprm( 'The weight of 1 serving. Does not affect the calculation.' ) }>
                            <FieldText
                                type="number"
                                value={ serving_size }
                                onChange={ (serving_size) => {
                                    const nutrition = {
                                        ...props.nutrition,
                                        serving_size,
                                    };

                                    props.onRecipeChange( { nutrition } );
                                }}
                            />
                            <FieldText
                                name="serving-unit"
                                placeholder={ wprm_admin.settings.nutrition_default_serving_unit }
                                value={ serving_unit }
                                onChange={ (serving_unit) => {
                                    const nutrition = {
                                        ...props.nutrition,
                                        serving_unit,
                                    };

                                    props.onRecipeChange( { nutrition } );
                                }}
                            />
                        </FieldContainer>
                        :
                        null
                    }
                    {
                        Object.keys(wprm_admin_modal.nutrition).map((nutrient, index ) => {
                            const options = wprm_admin_modal.nutrition[nutrient];
                            const value = props.nutrition.hasOwnProperty(nutrient) ? props.nutrition[nutrient] : '';

                            if ( 'serving_size' === nutrient ) {
                                return null;
                            }

                            if ( 'calories' !== nutrient && ! wprm_admin.addons.premium ) {
                                return null;
                            }

                            return (
                                <FieldContainer id={ `nutrition_${nutrient}` } label={ options.label } key={ index }>
                                    {
                                        'calculated' === options.type
                                        && this.state.calculating
                                        ?
                                        <Loader />
                                        :
                                        <Fragment>
                                            <FieldText
                                                type="number"
                                                value={ value }
                                                onChange={ (value) => {
                                                    const nutrition = {
                                                        ...props.nutrition,
                                                        [nutrient]: value,
                                                    };

                                                    props.onRecipeChange( { nutrition } );
                                                }}
                                                disabled={ 'calculated' === options.type }
                                            /><span className="wprm-admin-modal-field-nutrition-unit">{ options.unit }</span>
                                        </Fragment>
                                    }
                                </FieldContainer>
                            )
                        })
                    }
                </div>
                {
                    wprm_admin.addons.premium
                    ?
                    null
                    :
                    <p>{ __wprm( 'More nutrients are available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium</a>.</p>
                }
                <Button
                    isPrimary
                    required="pro"
                    onClick={() => {
                        props.onModeChange('nutrition-calculation');
                    }}
                >{ __wprm( 'Calculate Nutrition Facts' ) }</Button>
            </Fragment>
        );
    }
}
