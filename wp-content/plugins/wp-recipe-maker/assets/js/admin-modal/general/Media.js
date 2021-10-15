import { __wprm } from 'Shared/Translations';

const Media = {
    selectImage( callback ) {
        this.select( 'image', callback );
    },
    selectVideo( callback ) {
        this.select( 'video', callback );
    },
    select( type, callback ) {
        let media_arguments = {
            title: __wprm( 'Select Media' ),
            button: {
                text: __wprm( 'Select' ),
            },
            multiple: false,
        };
    
        // Check what media type we're getting.
        if ( 'video' === type ) {
            media_arguments.frame = 'video';
            media_arguments.state = 'video-details';
        } else {
            // Default to image.
            media_arguments.library = {
                type: 'image',
            };
        }
    
        // Create a new media frame (don't reuse because we have multiple different inputs)
        let frame = wp.media(media_arguments);
    
        // Handle image selection
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            callback( attachment );
        });
    
        // Handle video selection
        frame.on('update', function() {
            let attachment = frame.state().media.attachment;
    
            if ( attachment ) {
                callback( attachment );
            }
        });
    
        // Finally, open the modal on click
        frame.open();
    }
}
export default Media;