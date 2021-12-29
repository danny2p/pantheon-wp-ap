import React, { Fragment } from 'react';

import Media from '../general/Media';
import Button from 'Shared/Button';
import { __wprm } from 'Shared/Translations';

import FieldText from './FieldText';
 
const FieldPinImage = (props) => {
    const hasUpload = props.id > 0;
    const hasRepinId = ! hasUpload && ( -1 == props.id || props.repin );
    const hasImage = hasUpload || hasRepinId;

    const selectImage = (e) => {
        e.preventDefault();

        Media.selectImage((attachment) => {
            if ( props.hasOwnProperty( 'requirements' ) ) {
                let warnings = [];

                if ( props.requirements.hasOwnProperty( 'width' ) && attachment.width && attachment.width < props.requirements.width ) {
                    warnings.push( `${ __wprm( 'The image should have at least this width:' ) } ${ props.requirements.width }px` );
                }
                if ( props.requirements.hasOwnProperty( 'height' ) && attachment.height && attachment.height < props.requirements.height ) {
                    warnings.push( `${ __wprm( 'The image should have at least this height:' ) } ${ props.requirements.width }px` );
                }

                if ( warnings.length ) {
                    alert( `${ __wprm( 'Warning! We recommend making sure the image meets the following requirements:' ) }\n\n${ warnings.join( '\n' ) }` );
                }
            }

            props.onChange( attachment.id, attachment.url );
        });
    }

    return (
        <div className="wprm-admin-modal-field-image">
            {
                hasImage
                ?
                <Fragment>
                    {
                        hasUpload
                        ?
                        <div className="wprm-admin-modal-field-image-preview">
                            <img
                                onClick={ selectImage }
                                src={ props.url }
                            />
                            <a
                                href="#"
                                tabIndex={ props.disableTab ? '-1' : null }
                                onClick={ (e) => {
                                    e.preventDefault();
                                    props.onChange( 0, '' );
                                } }
                            >{ __wprm( 'Remove Image' ) }</a>
                        </div>
                        :
                        <Fragment>
                            <FieldText
                                value={ props.repin }
                                onChange={(repin) => {
                                    props.onChange( -1, '', repin );
                                }}
                                placeholder="123455679"
                            />
                            <a
                                href="#"
                                onClick={ (e) => {
                                    e.preventDefault();
                                    props.onChange( 0, '', '' );
                                } }
                            >{ __wprm( 'Remove Repin ID' ) }</a>
                        </Fragment>
                    }
                </Fragment>
                :
                <Fragment>
                    <Button
                        required={ props.required }
                        disableTab={ props.disableTab }
                        onClick={ selectImage }
                        
                    >{ __wprm( 'Select Image' ) }</Button>
                    <Button
                        required={ props.required }
                        disableTab={ props.disableTab }
                        onClick={ (e) => {
                            e.preventDefault();
                            props.onChange( -1, '' );
                        } }
                    >{ __wprm( 'Use Repin ID' ) }</Button>
                </Fragment>
            }
        </div>
    );
}
export default FieldPinImage;