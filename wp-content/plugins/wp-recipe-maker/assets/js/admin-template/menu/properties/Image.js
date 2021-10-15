import React from 'react';
import Media from 'Modal/general/Media';

const PropertyImage = (props) => {
    const hasImage = 0 < parseInt( props.value );

    return (
        <a href="#" onClick={(e) => {
            e.preventDefault();
            
            Media.selectImage((attachment) => {
                props.onValueChange( '' + attachment.id ); // Needs to be a string.
            });
        }}>{ hasImage ? 'Change...' : 'Select...' }</a>
    );
}

export default PropertyImage;