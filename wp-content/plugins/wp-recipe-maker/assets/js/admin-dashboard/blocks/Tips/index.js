import React, { Component, Fragment } from 'react';

import '../../../../css/admin/dashboard/tips.scss';

import { __wprm } from 'Shared/Translations';

import Block from '../../layout/Block';
import Tips from './Tips';

export default class TipsBlock extends Component {
    constructor(props) {
        super(props);

        // Random integer between 0 and the number of tips.
        const activeTip = Math.floor( Math.random() * ( Tips.length ) );

        // Automatically change tip every 10 seconds.
        this.changeTip = this.changeTip.bind( this );
        const interval = setInterval( this.changeTip, 15000 );

        this.state = {
            activeTip,
            interval,
            bar: 'odd',
        }
    }

    changeTip( change = 1 ) {
        let newActiveTip = this.state.activeTip + change;

        if ( newActiveTip < 0 ) {
            newActiveTip = newActiveTip + Tips.length;
        } else {
            newActiveTip = newActiveTip % Tips.length;
        }

        // Reset interval.
        clearInterval( this.state.interval );

        this.setState({
            activeTip: newActiveTip,
            interval: setInterval( this.changeTip, 15000 ),
            bar: 'even' === this.state.bar ? 'odd' : 'even',
        });
    }

    render() {
        return (
            <Block
                title={ __wprm( 'Quick Tips' ) }
            >
                <div className="wprm-admin-dashboard-tips-container">
                    <div
                        className="wprm-admin-dashboard-tips-prev"
                        onClick={() => { this.changeTip( -1 ) } }
                    >&lt;</div>
                    <div className="wprm-admin-dashboard-tips">
                    {
                        Tips.map( ( tip, index ) => {
                            let classes = [
                                'wprm-admin-dashboard-tip-container',
                            ];

                            if ( index === this.state.activeTip ) {
                                classes.push( 'wprm-admin-dashboard-tip-container-active' );
                            }

                            return (
                                <div
                                    className={ classes.join( ' ' ) }
                                    key={ index }
                                >{ tip }</div>
                            )
                        } )
                    }
                    </div>
                    <div
                        className="wprm-admin-dashboard-tips-next"
                        onClick={() => { this.changeTip( 1 ) } }
                    >&gt;</div>
                </div>
                <div className="wprm-admin-dashboard-tips-progress">
                    <div className={ `wprm-admin-dashboard-tips-progress-bar wprm-admin-dashboard-tips-progress-bar-${ this.state.bar }`}></div>
                </div>
            </Block>
        );
    }
}