import React, { Component, Fragment } from 'react';

import FieldContainer from '../../fields/FieldContainer';
import FieldCategory from '../../fields/FieldCategory';

export default class RecipeCategories extends Component {
    shouldComponentUpdate(nextProps) {
        return JSON.stringify(this.props.tags) !== JSON.stringify(nextProps.tags);
    }

    render() {
        const categories = Object.keys( wprm_admin_modal.categories );

        return (
            <Fragment>
                {
                    categories.map((category, index) => {
                        const options = wprm_admin_modal.categories[ category ];
                        const value = this.props.tags.hasOwnProperty( category ) ? this.props.tags[ category ] : [];

                        return (
                            <FieldContainer
                                id={ category }
                                label={ options.label }
                                help={ options.hasOwnProperty( 'help' ) ? options.help : null }
                                key={ index }
                            >
                                <FieldCategory
                                    id={ category }
                                    value={ value }
                                    onChange={ (value) => {
                                        const tags = {
                                            ...this.props.tags,
                                        };

                                        tags[ category ] = value;

                                        this.props.onRecipeChange( { tags } );
                                    }}
                                    creatable={ options.creatable }
                                    width="450px"
                                />
                            </FieldContainer>
                        )
                    })
                }
            </Fragment>
        );
    }
}