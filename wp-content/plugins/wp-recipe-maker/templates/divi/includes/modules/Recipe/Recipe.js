// External Dependencies
import React, { Component, Fragment } from 'react';

export default class Recipe extends Component {
    static slug = 'divi_wprm_recipe';

    constructor() {
        super();
        this.state = {
            loading: true,
            id: false,
        }
    }

    render() {
        // const Content = this.props.content;

        return (
            <div className="wprm-divi-preview">
                {/* <h1 className="simp-simple-header-heading">{this.props.heading}</h1>
                <p>
                    {this.props.content()}
                </p> */}
                {
                    false === this.state.id
                    ?
                    <p>Add a WP Recipe Maker Recipe</p>
                    :
                    <Fragment>
                        {
                            this.state.loading
                            ?
                            <p>Loading WPRM Recipe #{ this.state.id }</p>
                            :
                            <p>Recipe ID { this.state.id }</p>
                        }
                    </Fragment>
                }
            </div>
        );
    }
}
