import React, { Fragment } from 'react';

import Media from '../general/Media';
import FieldTextarea from './FieldTextarea';

import { __wprm } from 'Shared/Translations';
 
const FieldVideo = (props) => {
    const hasUpload = props.id > 0;
    const hasEmbed = ! hasUpload && ( -1 == props.id || props.embed );
    const hasVideo = hasUpload || hasEmbed;

    const selectVideo = (e) => {
        e.preventDefault();

        Media.selectVideo((attachment) => {
            props.onChange( attachment.attributes.id, attachment.attributes.thumb.src );
        });
    }

    return (
        <div className="wprm-admin-modal-field-video">
            {
                hasVideo
                ?
                <Fragment>
                    {
                        hasUpload
                        ?
                        <div className="wprm-admin-modal-field-video-preview">
                            <img
                                onClick={ selectVideo }
                                src={ props.thumb || wprm_admin_modal.images.video }
                            />
                            <a
                                href="#"
                                onClick={ (e) => {
                                    e.preventDefault();
                                    props.onChange( 0, '' );
                                } }
                            >{ __wprm( 'Remove Video' ) }</a>
                        </div>
                        :
                        <Fragment>
                            <FieldTextarea
                                value={ props.embed }
                                onChange={(embed) => {
                                    props.onChange( -1, '', embed );
                                }}
                                placeholder={ __wprm( 'Use URL to the video (e.g. https://www.youtube.com/watch?v=dQw4w9WgXcQ) or the full embed code.' ) }
                            />
                            <a
                                href="#"
                                onClick={ (e) => {
                                    e.preventDefault();
                                    props.onChange( 0, '', '' );
                                } }
                            >{ __wprm( 'Remove Video' ) }</a>
                        </Fragment>
                    }
                </Fragment>
                :
                <Fragment>
                    <button
                        className="button"
                        onClick={ selectVideo }
                    >{ __wprm( 'Upload Video' ) }</button>
                    <button
                        className="button"
                        onClick={ (e) => {
                            e.preventDefault();
                            props.onChange( -1, '' );
                        } }
                    >{ __wprm( 'Embed Video' ) }</button>
                </Fragment>
            }
        </div>
    );
}
export default FieldVideo;