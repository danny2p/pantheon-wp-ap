// External Dependencies
import React, { Component, Fragment } from 'react';

export default class Recipe extends Component {
    static slug = 'divi_wprm_recipe';

    constructor( props ) {
        super( props );

        const id = props.hasOwnProperty( 'recipe_id' ) ? parseInt( props.recipe_id ) : false;

        this.state = {
            id,
            html: {},
        }
    }

    componentDidMount() {
        if ( this.state.id && ! this.state.html.hasOwnProperty( `recipe-${ this.state.id }` ) ) {
            this.getRecipeHTML();
        }
    }

    componentDidUpdate( prevProps, prevState ) {
        // Check if we need to update the ID.
        if ( prevProps.recipe_id !== this.props.recipe_id ) {
            const id = parseInt( this.props.recipe_id );

            this.setState({
                id,
            });
        }
    
        // Check if we need to load the HTML for a new recipe ID.
        if ( prevState.id !== this.state.id ) {
            if ( this.state.id && ! this.state.html.hasOwnProperty( `recipe-${ this.state.id }` ) ) {
                this.getRecipeHTML();
            }
        }
    }

    getRecipeHTML() {
        const recipeId = this.state.id;

        return fetch(`${ DiviWpRecipeMakerBuilderData.endpoints.utilities }/preview/${ recipeId }?t=${ Date.now() }`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': DiviWpRecipeMakerBuilderData.nonce,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                // Don't cache API calls.
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': 0,
            },
            // body: JSON.stringify({
            //     template,
            // }),
            credentials: 'same-origin',
        }).then(response => {
            return response.json().then(json => {
                return response.ok ? json : false;
            });
        }).then(data => {
            let html = data;

            if ( ! html ) {
                html = '<p>Could not load WPRM Recipe #' + recipeId + '</p>';
            }

            this.setState({
                html: {
                    ...this.state.html,
                    [ `recipe-${ recipeId }` ]: html,
                },
            });
        });
    }

    render() {
        const preview = this.state.id && this.state.html.hasOwnProperty( `recipe-${ this.state.id }` ) ? this.state.html[ `recipe-${ this.state.id }` ] : false;

        return (
            <div className="wprm-divi-preview-container">
                {
                    ! this.state.id
                    ?
                    <p>Make sure to select a WP Recipe Maker Recipe to display.</p>
                    :
                    <Fragment>
                        {
                            false === preview
                            ?
                            <p>Loading WPRM Recipe #{ this.state.id }</p>
                            :
                            <div
                                className="wprm-divi-preview"
                                dangerouslySetInnerHTML={ { __html: preview } }
                            />
                        }
                    </Fragment>
                }
            </div>
        );
    }
}
