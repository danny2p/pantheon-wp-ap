import React, { Component, Fragment } from 'react';

import { __wprm } from 'Shared/Translations';
import Loader from 'Shared/Loader';
import Api from 'Shared/Api';

import ChartDays from './ChartDays';
import ChartTable from './ChartTable';

export default class Charts extends Component {
    constructor(props) {
        super(props);

        this.state = {
            loading: true,
            data: false,
        }
    }

    componentDidMount() {
		this.checkForUpdate();
	}

	checkForUpdate() {
		this.setState({
            loading: true,
        }, () => {
            Api.dashboard.getAnalytics().then( (apiData) => {
                let data = false
                if ( apiData ) {
                    data = apiData.data;
                }

                this.setState({
                    loading: false,
                    data,
                });
            });
        });
	}

    render() {
        const { data } = this.state;
        return (
            <Fragment>
                {
                    this.state.loading
                    ?
                    <Loader />
                    :
                    <Fragment>
                        {
                            0 === data.total
                            ?
                            <span>{ __wprm( 'No actions found for the last 7 days. If you just enabled analytics they should start showing up soon.' ) }</span>
                            :
                            <Fragment>
                                <div className="wprm-admin-dashboard-block-chart"><ChartDays data={ data.per_day } /></div>
                                <div className="wprm-admin-dashboard-block-chart">
                                    <ChartTable
                                        title={ `🏆 ${ __wprm( 'Top Recipe Interactions (last 7 days)' ) }` }
                                        label={ __wprm( 'Recipe' ) }
                                        data={ data.per_recipe }
                                    />
                                </div>
                                <div className="wprm-admin-dashboard-block-chart">
                                    <ChartTable
                                        title={ `👍 ${ __wprm( 'Interactions (last 7 days)' ) }` }
                                        label={ __wprm( 'Type' ) }
                                        data={ data.per_type }
                                    />
                                </div>
                            </Fragment>
                        }
                    </Fragment>
                }
            </Fragment>
        );
    }
}