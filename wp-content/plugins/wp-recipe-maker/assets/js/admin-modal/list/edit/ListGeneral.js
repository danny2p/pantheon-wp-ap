import React, { Fragment } from 'react';

import { __wprm } from 'Shared/Translations';
import FieldContainer from '../../fields/FieldContainer';
import FieldText from '../../fields/FieldText';
import FieldDropdownTemplate from '../../fields/FieldDropdownTemplate';

const ListGeneral = (props) => {
    return (
        <Fragment>
            <FieldContainer label={ __wprm( 'Internal Name' ) }>
                <FieldText
                    placeholder={ __wprm( 'List Name' ) }
                    value={ props.name }
                    onChange={ (name) => {
                        props.onListChange( { name } );
                    }}
                />
            </FieldContainer>
            <FieldContainer label={ __wprm( 'Internal Note' ) }>
                <FieldText
                    value={ props.note }
                    onChange={ (note) => {
                        props.onListChange( { note } );
                    }}
                    placeholder={ __wprm( 'Optional note about this list' ) }
                />
            </FieldContainer>
            <FieldContainer label={ __wprm( 'List Template' ) }>
                <FieldDropdownTemplate
                    priority="roundup"
                    options={
                        [{
                            label: __wprm( 'General' ),
                            options: [
                                {
                                    value: 'default',
                                    label: __wprm( 'Use default from settings' ),
                                }
                            ],
                        }]
                    }
                    value={ props.template }
                    onChange={ (template) => {
                        props.onListChange( { template } );
                    }}
                    width={ 300 }
                />
            </FieldContainer>
        </Fragment>
    );
}
export default ListGeneral;