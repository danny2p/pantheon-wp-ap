import React from 'react';
import Masonry from 'react-masonry-css';

const breakpointColumns = {
    default: 2,
    // 1550: 2,
    1150: 1,
};
 
const Grid = (props) => {
    return (
        <Masonry
            breakpointCols={ breakpointColumns }
            className="wprm-admin-dashboard-blocks-grid"
            columnClassName="wprm-admin-dashboard-blocks-grid-column"
        >
            {
                props.blocks.map( ( block ) => {
                    const BlockElement = block.block;

                    return (
                        <BlockElement
                            key={ block.id }
                        />
                    )
                })
            }
        </Masonry>
    );
}
export default Grid;