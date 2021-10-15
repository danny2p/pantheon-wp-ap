import React, { Component, Fragment } from 'react';

import '../../../css/admin/modal/input-fields.scss';

import { __wprm } from 'Shared/Translations';
import Header from '../general/Header';
import Footer from '../general/Footer';

import FieldText from '../fields/FieldText';
import FieldTextarea from '../fields/FieldTextarea';

export default class InputFields extends Component {
    constructor(props) {
        super(props);

        this.state = {
            fields: props.args.fields,
        };
    }

    render() {
        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.maybeCloseModal }
                >
                    { this.props.args.header }
                </Header>
                <div className="wprm-admin-modal-input-fields-container">
                    {
                        this.state.fields.map( (field, index) => {
                            let FieldComponent = FieldText;
                            const type = field.hasOwnProperty( 'type' ) ? field.type : 'text';

                            switch ( type ) {
                                case 'textarea':
                                    FieldComponent = FieldTextarea;
                                    break;
                            }

                            return (
                                <Fragment key={ index }>
                                    {
                                        field.hasOwnProperty( 'label' )
                                        && <div className="wprm-admin-modal-input-fields-field-label">{ field.label }</div>
                                    }
                                    <FieldComponent
                                        value={ field.value }
                                        onChange={ (value) => {
                                            let newFields = [ ...this.state.fields ];

                                            newFields[ index ].value = value;

                                            this.setState({
                                                fields: newFields,
                                            });
                                        }}
                                    />
                                </Fragment>
                            )
                        })
                    }
                </div>
                <Footer
                    savingChanges={ false }
                >
                    <button
                        className="button button-primary"
                        onClick={ () => {
                            if ( 'function' === typeof this.props.args.insertCallback ) {
                                this.props.args.insertCallback( this.state );
                            }
                            this.props.maybeCloseModal();
                        } }
                    >
                        { __wprm( 'Change' ) }
                    </button>
                </Footer>
            </Fragment>
        );
    }
}