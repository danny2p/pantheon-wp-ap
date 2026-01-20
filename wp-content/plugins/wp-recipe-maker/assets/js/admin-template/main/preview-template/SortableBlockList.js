import React, { useState, useMemo, useCallback, useRef } from 'react';
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
    DragOverlay,
    MeasuringStrategy,
} from '@dnd-kit/core';
import {
    SortableContext,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';

import SortableBlockItem from './SortableBlockItem';
import { canHaveChildren } from './treeUtils';

const INDENTATION_WIDTH = 15;

/**
 * Check if targetUid is a descendant of sourceUid in the flat shortcodes array.
 * A descendant comes after the source and has a greater depth until we hit
 * an item with depth <= source's depth.
 */
const isDescendant = (shortcodes, sourceUid, targetUid) => {
    const sourceIndex = shortcodes.findIndex(s => s.uid === sourceUid);
    const targetIndex = shortcodes.findIndex(s => s.uid === targetUid);
    
    if (sourceIndex === -1 || targetIndex === -1) return false;
    
    // Target must come after source to be a descendant
    if (targetIndex <= sourceIndex) return false;
    
    const sourceDepth = shortcodes[sourceIndex].depth || 0;
    
    // Check all items between source and target
    for (let i = sourceIndex + 1; i <= targetIndex; i++) {
        const itemDepth = shortcodes[i].depth || 0;
        
        // If we hit an item at the same level or higher than source,
        // we've exited the source's children - target is not a descendant
        if (itemDepth <= sourceDepth) {
            return false;
        }
    }
    
    // Target is within source's descendants
    return true;
};

/**
 * Get all descendants of a given item (children, grandchildren, etc.)
 */
const getDescendants = (shortcodes, sourceUid) => {
    const sourceIndex = shortcodes.findIndex(s => s.uid === sourceUid);
    if (sourceIndex === -1) return [];
    
    const sourceDepth = shortcodes[sourceIndex].depth || 0;
    const descendants = [];
    
    // Collect all items after source that have greater depth
    for (let i = sourceIndex + 1; i < shortcodes.length; i++) {
        const itemDepth = shortcodes[i].depth || 0;
        
        // If we hit an item at the same level or higher than source, stop
        if (itemDepth <= sourceDepth) {
            break;
        }
        
        descendants.push(shortcodes[i]);
    }
    
    return descendants;
};

/**
 * Get the count of all descendants of a given item
 */
const getChildCount = (shortcodes, sourceUid) => {
    return getDescendants(shortcodes, sourceUid).length;
};

/**
 * Remove children of specified items from the list
 */
const removeChildrenOf = (shortcodes, parentUids) => {
    if (!parentUids || parentUids.length === 0) return shortcodes;
    
    const excludedUids = new Set();
    
    for (const parentUid of parentUids) {
        const descendants = getDescendants(shortcodes, parentUid);
        descendants.forEach(d => excludedUids.add(d.uid));
    }
    
    return shortcodes.filter(s => !excludedUids.has(s.uid));
};

const SortableBlockList = ({
    shortcodes,
    hoveringBlock,
    onChangeHoveringBlock,
    onChangeEditingBlock,
    onDuplicateBlock,
    onRemoveBlock,
    onChangeMovingBlock,
    onMoveBlock,
}) => {
    const [activeId, setActiveId] = useState(null);
    const [overId, setOverId] = useState(null);
    const [offsetLeft, setOffsetLeft] = useState(0);
    const [dropPosition, setDropPosition] = useState(null); // 'before', 'after', 'inside'
    const [spacerHeight, setSpacerHeight] = useState(0);
    
    // Map to store refs for each item so we can measure them
    const itemRefs = useRef(new Map());

    // Find the active shortcode
    const activeShortcode = useMemo(() => {
        if (activeId === null) return null;
        return shortcodes.find(s => s.uid === activeId);
    }, [activeId, shortcodes]);

    // Get child count for the active item (for showing in overlay)
    const activeChildCount = useMemo(() => {
        if (activeId === null) return 0;
        return getChildCount(shortcodes, activeId);
    }, [activeId, shortcodes]);

    // Filter out children of the active item when dragging
    // This collapses descendants into the parent visually during drag
    const displayedShortcodes = useMemo(() => {
        if (activeId === null) return shortcodes;
        return removeChildrenOf(shortcodes, [activeId]);
    }, [activeId, shortcodes]);

    // Get flat list of uids for SortableContext (from displayed items only)
    const itemIds = useMemo(() => {
        return displayedShortcodes.map(s => s.uid);
    }, [displayedShortcodes]);

    // Find the over shortcode
    const overShortcode = useMemo(() => {
        if (overId === null) return null;
        return shortcodes.find(s => s.uid === overId);
    }, [overId, shortcodes]);

    // Sensors for drag detection
    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 5, // 5px movement before drag starts
            },
        }),
        useSensor(KeyboardSensor)
    );

    // Calculate drop position based on cursor
    const calculateDropPosition = useCallback((over, delta) => {
        if (!over || !activeShortcode) return null;

        const overItem = shortcodes.find(s => s.uid === over.id);
        if (!overItem) return null;

        // Prevent dropping a parent inside itself or any of its descendants
        if (isDescendant(shortcodes, activeId, over.id)) {
            return null;
        }

        // Check if over item can have children
        const overCanHaveChildren = canHaveChildren(overItem);
        
        // Get horizontal offset
        const horizontalOffset = delta?.x || 0;
        
        // If dragging significantly to the right and over a container, drop inside
        // But only if the active item is not the over item's ancestor
        if (horizontalOffset > INDENTATION_WIDTH && overCanHaveChildren) {
            return 'inside';
        }

        // Determine before/after based on vertical position in the list
        const activeIndex = shortcodes.findIndex(s => s.uid === activeId);
        const overIndex = shortcodes.findIndex(s => s.uid === over.id);

        if (activeIndex < overIndex) {
            // Moving down - drop after
            return 'after';
        } else {
            // Moving up - drop before
            return 'before';
        }
    }, [activeId, activeShortcode, shortcodes]);

    // Handle drag start
    const handleDragStart = useCallback((event) => {
        const { active } = event;
        setActiveId(active.id);
        setOverId(null);
        setDropPosition(null);
        
        const element = itemRefs.current.get(active.id);
        const childCount = getChildCount(shortcodes, active.id);
        
        // Always calculate spacer height to prevent container from shrinking
        // This includes the item's own height plus any children heights
        let totalSpacerHeight = 0;
        
        if (element) {
            // Measure the item's own height
            const itemRect = element.getBoundingClientRect();
            totalSpacerHeight = itemRect.height;
            
            if (childCount > 0) {
                // Get all descendants to measure their total height
                const descendants = getDescendants(shortcodes, active.id);
                const listContainer = element.closest('.wprm-sortable-block-list');
                
                if (listContainer && descendants.length > 0) {
                    let lastChildBottom = null;
                    
                    descendants.forEach(descendant => {
                        let childElement = itemRefs.current.get(descendant.uid);
                        
                        if (!childElement) {
                            childElement = listContainer.querySelector(`[data-uid="${descendant.uid}"]`);
                        }
                        
                        if (childElement) {
                            const childRect = childElement.getBoundingClientRect();
                            
                            if (lastChildBottom === null || childRect.bottom > lastChildBottom) {
                                lastChildBottom = childRect.bottom;
                            }
                        }
                    });
                    
                    if (lastChildBottom !== null) {
                        // Add children height to total spacer height
                        const childrenTotalHeight = lastChildBottom - itemRect.bottom;
                        totalSpacerHeight += childrenTotalHeight;
                    }
                }
            }
        }
        
        // Set spacer height
        setSpacerHeight(totalSpacerHeight);
        
        // Clear hover state when dragging starts to prevent expensive re-renders
        if (onChangeHoveringBlock) {
            onChangeHoveringBlock(false);
        }
    }, [onChangeHoveringBlock, shortcodes]);

    // Handle drag move - calculate drop position
    const handleDragMove = useCallback((event) => {
        const { delta } = event;
        if (delta) {
            setOffsetLeft(delta.x);
        }
    }, []);

    // Handle drag over
    const handleDragOver = useCallback((event) => {
        const { over, delta } = event;
        
        if (over) {
            setOverId(over.id);
            const position = calculateDropPosition(over, delta);
            setDropPosition(position);
        } else {
            setOverId(null);
            setDropPosition(null);
        }
    }, [calculateDropPosition]);

    // Handle drag end - perform the move
    const handleDragEnd = useCallback((event) => {
        const { active, over, delta } = event;

        if (over && active.id !== over.id) {
            const sourceShortcode = shortcodes.find(s => s.uid === active.id);
            const targetUid = over.id;
            
            // Calculate final drop position
            const position = calculateDropPosition(over, delta);

            if (sourceShortcode && position) {
                // First set the moving block
                onChangeMovingBlock(sourceShortcode);
                
                // Then perform the move (using setTimeout to ensure state is set)
                setTimeout(() => {
                    onMoveBlock(targetUid, position);
                }, 0);
            }
        }

        // Reset state
        setActiveId(null);
        setOverId(null);
        setOffsetLeft(0);
        setDropPosition(null);
        setSpacerHeight(0);
    }, [shortcodes, calculateDropPosition, onChangeMovingBlock, onMoveBlock]);

    // Handle drag cancel
    const handleDragCancel = useCallback(() => {
        setActiveId(null);
        setOverId(null);
        setOffsetLeft(0);
        setDropPosition(null);
        setSpacerHeight(0);
    }, []);

    // Measuring configuration for accurate drop zones
    const measuring = {
        droppable: {
            strategy: MeasuringStrategy.Always,
        },
    };

    // Ref callback to store item elements for measurement
    const setItemRef = useCallback((uid) => (node) => {
        if (node) {
            itemRefs.current.set(uid, node);
        } else {
            itemRefs.current.delete(uid);
        }
    }, []);

    // Render drop indicator
    const renderDropIndicator = () => {
        if (!overId || !dropPosition || activeId === overId) return null;

        const overIndex = shortcodes.findIndex(s => s.uid === overId);
        if (overIndex === -1) return null;

        const overItem = shortcodes[overIndex];
        const depth = overItem.depth || 0;
        
        // Calculate indicator depth based on position
        let indicatorDepth = depth;
        if (dropPosition === 'inside') {
            indicatorDepth = depth + 1;
        }

        // Position: before/after/inside
        let indicatorPosition = 'after';
        if (dropPosition === 'before') {
            indicatorPosition = 'before';
        } else if (dropPosition === 'inside') {
            indicatorPosition = 'inside';
        }

        return (
            <div
                className={`wprm-sortable-drop-indicator wprm-sortable-drop-indicator-${indicatorPosition}`}
                style={{
                    marginLeft: `${indicatorDepth * INDENTATION_WIDTH}px`,
                }}
                data-over-uid={overId}
            />
        );
    };

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragStart={handleDragStart}
            onDragMove={handleDragMove}
            onDragOver={handleDragOver}
            onDragEnd={handleDragEnd}
            onDragCancel={handleDragCancel}
            measuring={measuring}
        >
            <SortableContext
                items={itemIds}
                strategy={verticalListSortingStrategy}
            >
                <div className="wprm-sortable-block-list">
                    {displayedShortcodes.map((shortcode, index) => {
                        const depth = shortcode.depth !== undefined ? shortcode.depth : 0;
                        const isOver = shortcode.uid === overId;
                        const showIndicatorBefore = isOver && dropPosition === 'before';
                        const showIndicatorAfter = isOver && dropPosition === 'after';
                        const showIndicatorInside = isOver && dropPosition === 'inside';

                        return (
                            <React.Fragment key={shortcode.uid}>
                                {showIndicatorBefore && renderDropIndicator()}
                                <SortableBlockItem
                                    ref={setItemRef(shortcode.uid)}
                                    shortcode={shortcode}
                                    depth={depth}
                                    isHovering={shortcode.uid === hoveringBlock}
                                    isDragging={shortcode.uid === activeId}
                                    isAnyDragging={activeId !== null}
                                    onChangeHoveringBlock={onChangeHoveringBlock}
                                    onChangeEditingBlock={onChangeEditingBlock}
                                    onDuplicateBlock={onDuplicateBlock}
                                    onRemoveBlock={onRemoveBlock}
                                />
                                {showIndicatorInside && (
                                    <div
                                        className="wprm-sortable-drop-indicator wprm-sortable-drop-indicator-inside"
                                        style={{
                                            marginLeft: `${(depth + 1) * INDENTATION_WIDTH}px`,
                                        }}
                                    />
                                )}
                                {showIndicatorAfter && renderDropIndicator()}
                            </React.Fragment>
                        );
                    })}
                    {/* Spacer at bottom to maintain container height when item is being dragged */}
                    {spacerHeight > 0 && (
                        <div
                            className="wprm-sortable-children-spacer"
                            style={{
                                height: `${spacerHeight}px`,
                                width: '100%',
                                flexShrink: 0,
                            }}
                            aria-hidden="true"
                        />
                    )}
                </div>
            </SortableContext>

            <DragOverlay dropAnimation={null}>
                {activeShortcode ? (
                    <div 
                        className="wprm-drag-overlay-wrapper"
                        style={{
                            // Small offset for visual spacing from cursor
                            transform: 'translate(10px, 10px)',
                        }}
                    >
                        <SortableBlockItem
                            shortcode={activeShortcode}
                            depth={0}
                            isOverlay={true}
                            isDragging={true}
                            childCount={activeChildCount}
                        />
                    </div>
                ) : null}
            </DragOverlay>
        </DndContext>
    );
};

export default SortableBlockList;
