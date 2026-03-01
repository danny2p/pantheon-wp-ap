import React, { Component, Fragment } from 'react';

import { __wprm } from 'Shared/Translations';
import Header from '../../admin-modal/general/Header';
import Footer from '../../admin-modal/general/Footer';
import '../../../css/admin/modal/manage-export-csv.scss';

const normalizeCount = ( value ) => {
    const parsedValue = parseInt( value );

    if ( isNaN( parsedValue ) || parsedValue < 0 ) {
        return 0;
    }

    return parsedValue;
};

export default class Modal extends Component {
    constructor(props) {
        super(props);

        this.state = {
            scope: 'current_view',
            exporting: false,
        };

        this.handleExport = this.handleExport.bind(this);
    }

    allowCloseModal() {
        return ! this.state.exporting;
    }

    async handleExport() {
        if ( this.state.exporting ) {
            return;
        }

        this.setState({
            exporting: true,
        });

        let success = true;

        try {
            if ( this.props.args && 'function' === typeof this.props.args.onConfirm ) {
                const result = await this.props.args.onConfirm( this.state.scope );
                success = false !== result;
            }
        } catch ( error ) {
            success = false;
            console.error( 'WPRM manage export modal failed', error );
        }

        this.setState({
            exporting: false,
        }, () => {
            if ( success ) {
                this.props.maybeCloseModal();
            }
        });
    }

    getOptions() {
        const args = this.props.args || {};
        const currentRows = normalizeCount( args.currentRows );
        const filteredRows = normalizeCount( args.filteredRows );
        const totalRows = normalizeCount( args.totalRows );
        const showFilteredOption = !! args.showFilteredOption;

        const options = [
            {
                value: 'current_view',
                label: `${ __wprm( 'Current view' ) } (${ currentRows } ${ __wprm( 'rows' ) })`,
            },
        ];

        if ( showFilteredOption ) {
            options.push({
                value: 'all_filtered',
                label: `${ __wprm( 'All filtered rows' ) } (${ filteredRows } ${ __wprm( 'rows' ) })`,
            });
        }

        options.push({
            value: 'all_rows',
            label: `${ __wprm( 'All rows' ) } (${ totalRows } ${ __wprm( 'rows' ) })`,
        });

        return options;
    }

    render() {
        const options = this.getOptions();

        return (
            <Fragment>
                <Header onCloseModal={ this.props.maybeCloseModal }>
                    { __wprm( 'Export to CSV' ) }
                </Header>
                <div className="wprm-admin-modal-input-fields-container">
                    <div className="wprm-admin-modal-bulk-edit-label">{ __wprm( 'Choose what you want to export:' ) }</div>
                    <div className="wprm-admin-modal-bulk-edit-actions">
                        {
                            options.map( ( option ) => (
                                <div className="wprm-admin-modal-bulk-edit-action" key={ option.value }>
                                    <input
                                        type="radio"
                                        value={ option.value }
                                        name="wprm-admin-radio-export-csv-scope"
                                        id={ `wprm-admin-radio-export-csv-scope-${ option.value }` }
                                        checked={ this.state.scope === option.value }
                                        disabled={ this.state.exporting }
                                        onChange={ () => {
                                            this.setState({
                                                scope: option.value,
                                            });
                                        }}
                                    /><label htmlFor={ `wprm-admin-radio-export-csv-scope-${ option.value }` }>{ option.label }</label>
                                </div>
                            ) )
                        }
                    </div>
                    <div className="wprm-admin-modal-export-csv-description">{ __wprm( 'This particular CSV export is only for record keeping or analysing your data in a different tool. The information cannot be imported back into WP Recipe Maker afterwards.' ) }</div>
                </div>
                <Footer
                    savingChanges={ this.state.exporting }
                    alwaysShow={ () => {
                        if ( this.state.exporting ) {
                            return <span>{ __wprm( 'Generating CSV...' ) }</span>;
                        }

                        return null;
                    }}
                >
                    <button
                        className="button button-primary"
                        onClick={ this.handleExport }
                    >
                        { __wprm( 'Export to CSV' ) }
                    </button>
                </Footer>
            </Fragment>
        );
    }
}
