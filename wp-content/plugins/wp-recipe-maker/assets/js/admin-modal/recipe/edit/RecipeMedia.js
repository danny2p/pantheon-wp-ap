import React, { Fragment } from 'react';

import '../../../../css/admin/modal/recipe/fields/media.scss';

import { __wprm } from 'Shared/Translations';
import FieldContainer from '../../fields/FieldContainer';
import FieldImage from '../../fields/FieldImage';
import FieldPinImage from '../../fields/FieldPinImage';
import FieldVideo from '../../fields/FieldVideo';
 
const RecipeMedia = (props) => {
    return (
        <Fragment>
            <FieldContainer label={ __wprm( 'Image' ) } help={ __wprm( 'Used in the recipe metadata. Should be an image of the completed dish.' ) }>
                <FieldImage
                    requirements={ {
                        width: 500,
                        height: 500,
                    } }
                    id={ props.image.id }
                    url={ props.image.url }
                    onChange={ ( image_id, image_url ) => {
                        props.onRecipeChange( {
                            image_id,
                            image_url,
                        } );
                    }}
                />
            </FieldContainer>
            {
                'recipe_image' !== wprm_admin.settings.pinterest_use_for_image
                ?
                <FieldContainer label={ __wprm( 'Pin Image' ) } help={ __wprm( 'Optionally set a different image to use for the "Pin Recipe" button.' ) }>
                    <FieldPinImage
                        required="premium"
                        id={ props.pinImage.id }
                        url={ props.pinImage.url }
                        repin={ props.pinImage.repin }
                        onChange={ ( pin_image_id, pin_image_url, pin_image_repin_id = false ) => {
                            let image = {
                                pin_image_id,
                                pin_image_url,
                            };

                            if ( pin_image_repin_id !== false ) {
                                image.pin_image_repin_id = pin_image_repin_id;
                            }

                            props.onRecipeChange( image );
                        }}
                    />
                    
                </FieldContainer>
                :
                null
            }
            <FieldContainer label={ __wprm( 'Video' ) } help={ __wprm( 'Used in the recipe metadata. Can be displayed elsewhere on the page using the [wprm-recipe-video] shortcode.' ) }>
                <FieldVideo
                    id={ props.video.id }
                    thumb={ props.video.thumb }
                    embed={ props.video.embed }
                    onChange={ ( video_id, video_thumb_url, video_embed = false ) => {
                        let video = {
                            video_id,
                            video_thumb_url,
                        }

                        if ( video_embed !== false ) {
                            video.video_embed = video_embed;
                        }

                        props.onRecipeChange( video );
                    }}
                />
            </FieldContainer>
        </Fragment>
    );
}
export default RecipeMedia;