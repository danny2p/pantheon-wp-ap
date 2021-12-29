import React, { Component } from 'react';

export default class Item extends Component {
    constructor(props) {
        super(props);

        this.state = {
            open: false,
        }
    }

    render() {
        return (
            <div className="wprm-admin-dashboard-health-check-item">
                <div
                    className="wprm-admin-dashboard-health-check-header"
                    onClick={ () => {
                        this.setState({
                            open: ! this.state.open,
                        });
                    } }
                >
                    { this.props.header }
                </div>
                {
                    this.state.open
                    &&
                    <div className="wprm-admin-dashboard-health-check-content">
                        { this.props.children }
                    </div>
                }
            </div>
        );
    }
}