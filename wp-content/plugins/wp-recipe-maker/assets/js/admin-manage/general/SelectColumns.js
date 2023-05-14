import React, { Component, Fragment } from 'react';

import { __wprm } from 'Shared/Translations';

import '../../../css/admin/manage/select-columns.scss';

export default class SelectColumns extends Component {
    constructor(props) {
        super(props);

        this.state = {
            show: false,
        };
    }
    
    render() {
        const { props } = this;

        if ( false === props.selectedColumns ) {
            return (
                <div></div>
            );
        }

        let currentGroupHeader = '';

        return (
            <div className="wprm-admin-manage-select-columns-container">
                <div className="wprm-admin-manage-select-columns">
                    <a
                        href="#"
                        onClick={(e) => {
                            e.preventDefault();

                            this.setState((prevState) => ({
                                show: ! prevState.show,
                            }));
                        }}
                    >{ this.state.show ? __wprm( '← Columns' ) : __wprm( '→ Change Columns' ) }</a>
                    {
                        this.state.show
                        &&
                        props.columns.map( (column, index) => {
                            if ( 'actions' === column.id ) {
                                return null;
                            }

                            const selected = props.selectedColumns.includes(column.id);
                            const filtered = props.filteredColumns.includes(column.id);

                            let classNames = ['wprm-admin-manage-select-columns-column'];
                            if ( selected ) { classNames.push( 'wprm-admin-manage-select-columns-column-selected' ) }
                            if ( filtered ) { classNames.push( 'wprm-admin-manage-select-columns-column-filtered' ) }

                            // Maybe add group header in between.
                            let GroupHeaderOutput = false;
                            const groupHeader = column.hasOwnProperty( 'groupHeader' ) ? column.groupHeader : '';

                            if ( currentGroupHeader !== groupHeader ) {
                                currentGroupHeader = groupHeader;
                                GroupHeaderOutput = () => (
                                    <Fragment>
                                        <div className="wprm-admin-manage-select-columns-group-break"></div>
                                        <span className="wprm-admin-manage-select-columns-group">{ groupHeader }</span>
                                    </Fragment>
                                );
                            }

                            return (
                                <Fragment key={index}>
                                    {
                                        false !== GroupHeaderOutput
                                        && <GroupHeaderOutput/>
                                    }
                                    <span
                                        className={ classNames.join( ' ' ) }
                                        onClick={(e) => {
                                            e.preventDefault();
                                            if ( ! filtered ) {
                                                props.onColumnsChange( column.id, ! selected );
                                            }
                                        }}
                                    >{ column.Header }</span>
                                </Fragment>
                            );
                        })
                    }
                </div>
            </div>
        );
    }
}