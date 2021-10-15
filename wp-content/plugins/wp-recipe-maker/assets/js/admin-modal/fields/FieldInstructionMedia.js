import React, { Fragment } from 'react';

import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';

import FieldTextarea from './FieldTextarea';
import Media from '../general/Media';

const FieldInstructionMedia = (props) => {
    const { video } = props;
    const hasImage = props.image > 0;

    return (
        <div className="wprm-admin-modal-field-instruction-after-container-media">
            <div className="wprm-admin-modal-field-instruction-after-container-media-icons">
                <Icon
                    type="photo"
                    title={ hasImage ? __wprm( 'Remove Image' ) : __wprm( 'Add Instruction Image' ) }
                    onClick={ () => {
                        if ( hasImage ) {
                            props.onChangeImage(0, '');
                        } else {
                            Media.selectImage((attachment) => {
                                props.onChangeImage(attachment.id, attachment.url);
                            });
                        }
                    } }
                    hidden={ 'none' !== video.type && 'part' !== video.type }
                />
                <div className="wprm-icon-spacer"/>
                <Icon
                    type="movie"
                    title={ 'upload' === video.type ? __wprm( 'Remove Video' ) : __wprm( 'Upload Instruction Video' ) }
                    onClick={ () => {
                        if ( 'upload' === video.type ) {
                            props.onChangeVideo({
                                ...video,
                                type: 'none',
                                id: 0,
                                thumb: '',
                            });
                        } else {
                            Media.selectVideo((attachment) => {
                                props.onChangeVideo({
                                    ...video,
                                    type: 'upload',
                                    id: attachment.attributes.id,
                                    thumb: attachment.attributes.thumb.src,
                                });
                            });
                        }
                    } }
                    hidden={ hasImage || ( 'none' !== video.type && 'upload' !== video.type ) }
                />
                <Icon
                    type="code"
                    title={ 'embed' === video.type ? __wprm( 'Remove Video' ) : __wprm( 'Embed Instruction Video' ) }
                    onClick={ () => {
                        if ( 'embed' === video.type ) {
                            props.onChangeVideo({
                                ...video,
                                type: 'none',
                                embed: '',
                            });
                        } else {
                            props.onChangeVideo({
                                ...video,
                                type: 'embed',
                            });
                        }
                    } }
                    hidden={ hasImage || ( 'none' !== video.type && 'embed' !== video.type ) }
                />
                <Icon
                    type="videoplayer"
                    title={ 'part' === video.type ? __wprm( 'Remove Video Part' ) : __wprm( 'Instruction is part of the main video' ) }
                    onClick={ () => {
                        props.onChangeVideo({
                            ...video,
                            type: 'part' === video.type ? 'none' : 'part',
                            start: '',
                            end: '',
                            name: '',
                        });
                    } }
                    hidden={ ! props.allowVideo || ( 'none' !== video.type && 'part' !== video.type ) }
                />
            </div>
            {
                ( hasImage || 'upload' === video.type || 'embed' === video.type )
                &&
                <div className="wprm-admin-modal-field-instruction-after-container-media-preview">
                    {
                        hasImage
                        ?
                        <div className="wprm-admin-modal-field-image-preview">
                            <img
                                src={ props.image_url }
                                onClick={ () => {
                                    Media.selectImage((attachment) => {
                                        props.onChangeImage(attachment.id, attachment.url);
                                    });
                                } }
                            />
                        </div>
                        :
                        <Fragment>
                            {
                                'upload' === video.type
                                &&
                                <div className="wprm-admin-modal-field-video-preview">
                                    <img
                                        src={ video.thumb }
                                        onClick={ () => {
                                            Media.selectVideo((attachment) => {
                                                props.onChangeVideo({
                                                    ...video,
                                                    id: attachment.attributes.id,
                                                    thumb: attachment.attributes.thumb.src,
                                                });
                                            });
                                        } }
                                    />
                                </div>
                            }
                            {
                                'embed' === video.type
                                &&
                                <FieldTextarea
                                    value={ video.embed }
                                    onChange={(embed) => {
                                        props.onChangeVideo({
                                            ...video,
                                            embed,
                                        });
                                    }}
                                    placeholder={ __wprm( 'Instruction video URL or embed code' ) }
                                />
                            }
                        </Fragment>
                    }
                </div>
            }
        </div>
    );
}
export default FieldInstructionMedia;