import React, {Component, Fragment } from 'react';

import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';

export default class Item extends Component {
    constructor(props) {
        super(props);

        this.state = {
            open: false,
        }
    }

    render() {
        const { item } = props;

        return (
            <div className="wprm-admin-dashboard-health-check-item">
                This is it!
            </div>
        );
    }
}