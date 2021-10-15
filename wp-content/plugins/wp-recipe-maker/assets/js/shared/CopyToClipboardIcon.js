
import React, { Component } from 'react';
import CopyToClipboard from 'react-copy-to-clipboard';

import Icon from 'Shared/Icon';
import Tooltip from 'Shared/Tooltip';
import { __wprm } from 'Shared/Translations';

import '../../css/admin/shared/copy-clipboard.scss';

export default class CopyToClipboardIcon extends Component {
    constructor(props) {
        super(props);

        this.state = {
            copied: false,
        }
    }

    onCopy() {
        this.setState({
            copied: true,
        }, () => {
            setTimeout(() => {
                this.setState({
                    copied: false,
                });
            }, 2000);
        });
    }

    render() {
        return (
            <CopyToClipboard
                text={this.props.text}
                onCopy={this.onCopy.bind(this)}
            >
                <span
                    className="wprm-admin-table-container-copy"
                    style={{
                        opacity: this.state.copied ? 0.2 : 1
                    }}
                >
                    {
                        this.props.hasOwnProperty( 'type' )
                        && 'text' === this.props.type
                        ?
                        <Tooltip content={ this.state.copied ? __wprm( 'Copied!' ) : __wprm( 'Copy to clipboard' ) }>
                            <span>{ this.props.text }</span>
                        </Tooltip>
                        :
                        <Icon
                            type="clipboard"
                            title={ this.state.copied ? __wprm( 'Copied!' ) : __wprm( 'Copy to clipboard' ) }
                        />
                    }
                </span>
            </CopyToClipboard>
        );
    }
}