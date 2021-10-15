import React, { Component, Fragment } from 'react';

import '../../css/admin/shared/error-boundary.scss';

export default class ErrorBoundary extends Component {

    constructor(props) {
        super(props);
        
        this.state = {
            hasError: false,
            error: false,
            info: false,
        }
    }

    componentDidCatch(error, info) {
        this.setState({
            hasError: true,
            error,
            info,
        });
    }

    render() {
        return (
            <Fragment>
                {
                    this.state.hasError
                    ?
                    <div className="wprm-error-boundary">
                        <p>
                            <strong>Something went wrong</strong><br/>
                            Please contact <a href="mailto:support@bootstrapped.ventures">support@bootstrapped.ventures</a> and send along the following information:</p>
                        <pre>
                            { this.props.module ? `Module: ${ this.props.module }\n` : null }
                            { this.state.error ? `Error: ${ this.state.error.toString() }\n` : null }
                            { this.state.info ? `Stack: ${ this.state.info.componentStack }` : null }
                        </pre>
                    </div>
                    :
                    this.props.children
                }
            </Fragment>
        );
    }
}
