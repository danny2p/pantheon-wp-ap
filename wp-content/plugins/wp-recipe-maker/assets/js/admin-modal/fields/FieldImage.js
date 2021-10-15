import React from 'react';

import Media from '../general/Media';
import Button from 'Shared/Button';
import { __wprm } from 'Shared/Translations';
 
const FieldImage = (props) => {
    const hasImage = props.id > 0;

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
                <Button
                    required={ props.required }
                    disableTab={ props.disableTab }
                    onClick={ selectImage }
                    
                >{ __wprm( 'Select Image' ) }</Button>
            }
        </div>
    );
}
export default FieldImage;