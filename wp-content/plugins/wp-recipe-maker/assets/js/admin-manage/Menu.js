import React, { Component, Fragment } from 'react';
import { NavLink } from 'react-router-dom';
import { withRouter } from 'react-router';

import '../../css/admin/manage/menu.scss';


class Menu extends Component {
    render() {
        const { datatables } = this.props;
        let parents = {};

        // Get all parents.
        Object.keys(datatables).map((id) => {
            const datatable = datatables[ id ];
            const parent = datatable.parent;
            const link = 'recipe' === id ? '/' : `/${id}`;

            // Output each parent once.
            if ( parent && ! parents.hasOwnProperty( parent ) ) {
                parents[ parent ] = {
                    name: parent,
                    active: false,
                    link,
                };
            }

            // Check if this path is active.
            if ( link === this.props.location.pathname ) {
                parents[ parent ].active = true;
            }
        });

        return (
            <Fragment>
                <div className="wprm-admin-manage-parent-menu">
                    {
                        Object.keys(parents).map((key, index) => {
                            const parent = parents[ key ];
                            
                            return (
                                <NavLink to={ parent.link } className={ `wprm-admin-manage-menu-item${ parent.active ? ' wprm-admin-manage-menu-item-active' : ''}` } key={ index } exact>{ parent.name }</NavLink>
                            )
                        })
                    }
                </div>
                <div className="wprm-admin-manage-child-menu">
                {
                    Object.keys(datatables).map((id, index) => {
                        const datatable = datatables[ id ];
                        const parent = datatable.parent;
                        const link = 'recipe' === id ? '/' : `/${id}`;

                        // Check requirement.
                        let hasAccess = true;
                        if ( datatable.hasOwnProperty( 'required' ) && ( ! wprm_admin.addons.hasOwnProperty( datatable.required ) || true !== wprm_admin.addons[ datatable.required ] ) ) {
                            hasAccess = false;
                        }

                        // Only show children for active parent.
                        if ( parents.hasOwnProperty( parent ) && parents[ parent ].active  ) {
                            let label = datatable.hasOwnProperty('title') ? datatable.title : datatable.label.plural;

                            if ( ! hasAccess ) {
                                label += '*';
                            }

                            return (
                                <NavLink to={ link } className={ `wprm-admin-manage-menu-item${ hasAccess ? '' : ' wprm-admin-manage-menu-item-requirement'}` } activeClassName="wprm-admin-manage-menu-item-active" key={ index } exact>{ label }</NavLink>
                            )
                        } else {
                            return null;
                        }
                    })
                }
                </div>
            </Fragment>
        );
    }
}

export default withRouter(Menu)
