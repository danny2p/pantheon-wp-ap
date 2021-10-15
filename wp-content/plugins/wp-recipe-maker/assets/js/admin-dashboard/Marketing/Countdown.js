import React, { Component, Fragment } from 'react';

import { __wprm } from 'Shared/Translations';

import Flip from './Flip';

export default class Countdown extends Component {
    constructor(props) {
        super(props);

        let total = 0;
        total += props.countdown.seconds;
        total += props.countdown.minutes * 60;
        total += props.countdown.hours * 60 * 60;
        total += props.countdown.days * 24 * 60 * 60;

        // Timing.
        const interval = setInterval( this.updateCountdown.bind( this ), 1000 );

        this.state = {
            start: Date.now(),
            interval,
            total,
            timeLeft: total,
        }
    }

    updateCountdown() {
        const now = Date.now();
        const diff = Math.round( ( now - this.state.start ) / 1000 );

        this.setState({
            timeLeft: this.state.total - diff,
        });
    }

    render() {
        const { timeLeft } = this.state;
        const finished = timeLeft <= 0;

        let days = 0;
        let hours = 0;
        let minutes = 0;
        let seconds = 0;

        if ( ! finished ) {
            days = Math.floor( timeLeft / ( 3600*24 ) );
            hours = Math.floor( timeLeft % (3600*24) / 3600 );
            minutes = Math.floor( timeLeft % 3600 / 60 );
            seconds = Math.floor( timeLeft % 60 );
        }

        return (
            <Fragment>
                {
                    finished
                    ?
                    <p style={ { color: 'darkred' } }>{ __wprm( 'You just missed the deadline!' ) }</p>
                    :
                    <div className="wprm-admin-dashboard-marketing-countdown">
                        <div className="wprm-admin-dashboard-marketing-countdown-unit"><Flip value={ days } /> { 1 === days ? __wprm( 'day' ) : __wprm( 'days' ) }</div>
                        <div className="wprm-admin-dashboard-marketing-countdown-unit"><Flip value={ String( hours ).padStart( 2, '0' ) } /> { 1 === hours ? __wprm( 'hour' ) : __wprm( 'hours' ) }</div>
                        <div className="wprm-admin-dashboard-marketing-countdown-unit"><Flip value={ String( minutes ).padStart( 2, '0' ) } /> { 1 === minutes ? __wprm( 'minute' ) : __wprm( 'minutes' ) }</div>
                        <div className="wprm-admin-dashboard-marketing-countdown-unit"><Flip value={ String( seconds ).padStart( 2, '0' ) } /> { 1 === seconds ? __wprm( 'second' ) : __wprm( 'seconds' ) }</div>
                        <div className="wprm-admin-dashboard-marketing-countdown-label">{ __wprm( 'left to grab the discount!' ) }</div>
                    </div>
                }
            </Fragment>
        );
    }
}