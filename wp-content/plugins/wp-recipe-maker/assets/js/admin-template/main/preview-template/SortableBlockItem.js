import React, { forwardRef } from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

import Icon from 'Shared/Icon';
import { canHaveChildren } from './treeUtils';

const SortableBlockItem = forwardRef(({
    shortcode,
    depth = 0,
    isHovering = false,
    isDragging = false,
    isAnyDragging = false,
    isOverlay = false,
    childCount = 0,
    onChangeHoveringBlock,
    onChangeEditingBlock,
    onDuplicateBlock,
    onRemoveBlock,
}, ref) => {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging: isSortableDragging,
    } = useSortable({
        id: shortcode.uid,
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        paddingLeft: `${depth * 15}px`,
        opacity: isSortableDragging ? 0 : null,
        height: isSortableDragging ? '0px' : null,
    };

    const containerRef = (node) => {
        setNodeRef(node);
        if (ref) {
            if (typeof ref === 'function') {
                ref(node);
            } else {
                ref.current = node;
            }
        }
    };

    const isContainer = canHaveChildren(shortcode);

    let className = 'wprm-template-menu-block';
    if (isHovering) {
        className += ' wprm-template-menu-block-hover';
    }
    if (isSortableDragging || isDragging) {
        className += ' wprm-template-menu-block-dragging';
    }
    if (isOverlay) {
        className += ' wprm-template-menu-block-overlay';
    }
    if (isContainer) {
        className += ' wprm-template-menu-block-container';
    }

    return (
        <div
            ref={containerRef}
            className={className}
            style={style}
            data-uid={shortcode.uid}
            onMouseEnter={onChangeHoveringBlock && !isAnyDragging ? () => onChangeHoveringBlock(shortcode.uid) : undefined}
            onMouseLeave={onChangeHoveringBlock && !isAnyDragging ? () => onChangeHoveringBlock(false) : undefined}
            {...attributes}
        >
            <span
                className="wprm-template-menu-block-drag-handle"
                {...listeners}
            >
                <Icon
                    type="drag"
                />
            </span>
            <span
                className="wprm-template-menu-block-name"
                onClick={onChangeEditingBlock ? () => onChangeEditingBlock(shortcode.uid) : undefined}
            >
                {shortcode.name}
                {isOverlay && childCount > 0 && (
                    <span className="wprm-template-menu-block-child-count">
                        +{childCount}
                    </span>
                )}
            </span>
            <span className="wprm-template-menu-block-actions">
                <Icon
                    type="pencil"
                    title="Edit Block"
                    onClick={(e) => {
                        e.stopPropagation();
                        if (onChangeEditingBlock) {
                            onChangeEditingBlock(shortcode.uid);
                        }
                    }}
                />
                <Icon
                    type="duplicate"
                    title="Duplicate Block"
                    onClick={(e) => {
                        e.stopPropagation();
                        if (onDuplicateBlock) {
                            onDuplicateBlock(shortcode.uid);
                        }
                    }}
                />
                <Icon
                    type="trash"
                    title="Remove Block"
                    onClick={(e) => {
                        e.stopPropagation();
                        if (onRemoveBlock && confirm('Are you sure you want to delete the "' + shortcode.name + '" block?')) {
                            onRemoveBlock(shortcode.uid);
                        }
                    }}
                />
            </span>
        </div>
    );
});

SortableBlockItem.displayName = 'SortableBlockItem';

export default SortableBlockItem;
