import React, { Fragment } from 'react';

import { __wprm } from 'Shared/Translations';
import FieldContainer from '../../fields/FieldContainer';
import FieldCheckbox from '../../fields/FieldCheckbox';
import FieldText from '../../fields/FieldText';

const ListMetadata = (props) => {
    return (
        <Fragment>
            <FieldContainer id="metadata_output" label={ __wprm( 'Output ItemList Metadata' ) }>
                <FieldCheckbox
                    value={ props.metadata_output }
                    onChange={ (metadata_output) => {
                        props.onListChange( { metadata_output } );
                    }}
                />
            </FieldContainer>
            {
                props.metadata_output
                &&
                <Fragment>
                    <FieldContainer label={ __wprm( 'Name' ) }>
                        <FieldText
                            placeholder={ __wprm( '10 Birthday Party Recipes' ) }
                            value={ props.metadata_name }
                            onChange={ (metadata_name) => {
                                props.onListChange( { metadata_name } );
                            }}
                        />
                    </FieldContainer>
                    <FieldContainer label={ __wprm( 'Description' ) }>
                        <FieldText
                            value={ props.metadata_description }
                            onChange={ (metadata_description) => {
                                props.onListChange( { metadata_description } );
                            }}
                            placeholder={ __wprm( 'These recipes are great for a kids birthday party.' ) }
                        />
                    </FieldContainer>
                </Fragment>
            }
        </Fragment>
    );
}
export default ListMetadata;