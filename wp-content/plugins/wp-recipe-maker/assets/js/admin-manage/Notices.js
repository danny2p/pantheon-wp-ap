import React, { Component } from 'react';

import '../../css/admin/manage/notices.scss';

import Api from 'Shared/Api';
import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';

export default class Notices extends Component {
    render() {
        if ( ! wprm_admin_modal.notices || ! wprm_admin_modal.notices.length ) {
            return null;
        }

        return (
            <div className="wprm-admin-manage-notices">
                {
                    wprm_admin_modal.notices.map((notice, index) => {
                        if ( notice.dismissed ) {
                            return null;
                        }

                        return (
                            <div className="wprm-admin-notice" key={ index }>
                                <div className="wprm-admin-notice-content">
                                    {
                                        notice.title
                                        ?
                                        <div className="wprm-admin-notice-title">{ notice.title }</div>
                                        :
                                        null
                                    }
                                    <div
                                        className="wprm-admin-notice-text"
                                        dangerouslySetInnerHTML={ { __html: notice.text } }
                                    />
                                </div>
                                <div className="wprm-admin-notice-dismiss">
                                    <Icon
                                        title={ __wprm( 'Remove Notice' ) }
                                        type="close"
                                        onClick={() => {
                                            Api.general.dismissNotice( notice.id );
                                            notice.dismissed = true;
                                            this.forceUpdate();
                                        }}
                                    />
                                </div>
                            </div>
                        )
                    })
                }
            </div>
        );
    }
}