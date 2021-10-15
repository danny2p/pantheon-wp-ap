import React, { Component, Fragment } from 'react';

export default class ErrorBoundary extends Component {

    constructor(props) {
        super(props);
        
        this.state = {
            hasError: false,
        }
    }

    componentDidCatch(error, info) {
        this.setState({
            hasError: true,
        });
    }

    render() {
        return (
            <Fragment>
                {
                    this.state.hasError
                    ?
                    <div className="wprm-settings-error">Something went wrong with this setting. Please contact support.</div>
                    :
                    this.props.children
                }
            </Fragment>
        );
    }
}
