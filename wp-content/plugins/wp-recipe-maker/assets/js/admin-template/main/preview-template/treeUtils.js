import Elements from '../../general/elements';
import Shortcodes from '../../general/shortcodes';

/**
 * Check if a shortcode/element can have children
 */
export const canHaveChildren = (shortcode) => {
    if (!shortcode) return false;
    
    // Layout elements can have children
    if (Elements.layoutElements.includes(shortcode.id)) {
        return true;
    }
    
    // Content shortcodes can have children
    if (Shortcodes.contentShortcodes.includes(shortcode.id)) {
        return true;
    }
    
    // Check if it has content property set (meaning it has a closing tag)
    if (shortcode.content !== false && shortcode.content !== undefined) {
        return true;
    }
    
    return false;
};

/**
 * Convert flat shortcodes array (with depth) to tree structure
 * Each node has: { ...shortcode, children: [] }
 */
export const flatToTree = (shortcodes) => {
    if (!shortcodes || shortcodes.length === 0) return [];
    
    const tree = [];
    const stack = [{ children: tree, depth: -1 }];
    
    for (const shortcode of shortcodes) {
        if (!shortcode) continue;
        
        const depth = shortcode.depth !== undefined ? shortcode.depth : 0;
        const node = {
            ...shortcode,
            children: [],
        };
        
        // Pop stack until we find the right parent level
        while (stack.length > 1 && stack[stack.length - 1].depth >= depth) {
            stack.pop();
        }
        
        // Add to parent's children
        stack[stack.length - 1].children.push(node);
        
        // If this can have children, push it to stack
        if (canHaveChildren(shortcode)) {
            stack.push({ ...node, depth });
        }
    }
    
    return tree;
};

/**
 * Convert tree structure back to flat array with correct depth
 */
export const treeToFlat = (tree, depth = 0) => {
    const result = [];
    
    for (const node of tree) {
        // Add the node itself (without children property for the flat version)
        const { children, ...shortcode } = node;
        result.push({
            ...shortcode,
            depth,
        });
        
        // Recursively add children
        if (children && children.length > 0) {
            result.push(...treeToFlat(children, depth + 1));
        }
    }
    
    return result;
};

/**
 * Find a node in the tree by uid
 */
export const findNodeByUid = (tree, uid) => {
    for (const node of tree) {
        if (node.uid === uid) {
            return node;
        }
        if (node.children && node.children.length > 0) {
            const found = findNodeByUid(node.children, uid);
            if (found) return found;
        }
    }
    return null;
};

/**
 * Find parent of a node by uid
 */
export const findParentByUid = (tree, uid, parent = null) => {
    for (const node of tree) {
        if (node.uid === uid) {
            return parent;
        }
        if (node.children && node.children.length > 0) {
            const found = findParentByUid(node.children, uid, node);
            if (found !== undefined) return found;
        }
    }
    return undefined;
};

/**
 * Remove a node from the tree by uid
 */
export const removeNodeByUid = (tree, uid) => {
    return tree.filter(node => {
        if (node.uid === uid) return false;
        if (node.children && node.children.length > 0) {
            node.children = removeNodeByUid(node.children, uid);
        }
        return true;
    });
};

/**
 * Insert a node at a specific position
 * position: 'before', 'after', or 'inside' (as first child)
 */
export const insertNode = (tree, nodeToInsert, targetUid, position) => {
    const result = [];
    
    for (let i = 0; i < tree.length; i++) {
        const node = tree[i];
        
        if (node.uid === targetUid) {
            if (position === 'before') {
                result.push(nodeToInsert);
                result.push(node);
            } else if (position === 'after') {
                result.push(node);
                result.push(nodeToInsert);
            } else if (position === 'inside') {
                // Insert as first child
                result.push({
                    ...node,
                    children: [nodeToInsert, ...(node.children || [])],
                });
            }
        } else {
            // Check if we need to recurse into children
            if (node.children && node.children.length > 0) {
                result.push({
                    ...node,
                    children: insertNode(node.children, nodeToInsert, targetUid, position),
                });
            } else {
                result.push(node);
            }
        }
    }
    
    return result;
};

/**
 * Get all UIDs from the tree in flat order (for SortableContext)
 */
export const getUidsFromTree = (tree) => {
    const uids = [];
    
    for (const node of tree) {
        uids.push(node.uid);
        if (node.children && node.children.length > 0) {
            uids.push(...getUidsFromTree(node.children));
        }
    }
    
    return uids;
};

/**
 * Calculate projected drop position based on cursor position
 * Returns: { targetUid, position: 'before' | 'after' | 'inside', depth }
 */
export const getProjectedDropPosition = (
    tree,
    flatItems,
    activeId,
    overId,
    offsetLeft,
    indentationWidth = 15
) => {
    if (!overId || activeId === overId) return null;
    
    const overIndex = flatItems.findIndex(item => item.uid === overId);
    const activeIndex = flatItems.findIndex(item => item.uid === activeId);
    
    if (overIndex === -1 || activeIndex === -1) return null;
    
    const overItem = flatItems[overIndex];
    const activeItem = flatItems[activeIndex];
    
    // Calculate depth change based on horizontal offset
    const depthChange = Math.round(offsetLeft / indentationWidth);
    const projectedDepth = Math.max(0, (overItem.depth || 0) + depthChange);
    
    // Determine if we're dropping before, after, or inside
    const overCanHaveChildren = canHaveChildren(overItem);
    
    // If dragging right (positive offset) and over item can have children, drop inside
    if (depthChange > 0 && overCanHaveChildren) {
        return {
            targetUid: overItem.uid,
            position: 'inside',
            depth: (overItem.depth || 0) + 1,
        };
    }
    
    // Otherwise, determine based on position relative to over item
    const isBelowOver = activeIndex > overIndex;
    
    if (isBelowOver) {
        // Moving up - drop before the over item
        return {
            targetUid: overItem.uid,
            position: 'before',
            depth: overItem.depth || 0,
        };
    } else {
        // Moving down - drop after the over item
        return {
            targetUid: overItem.uid,
            position: 'after',
            depth: overItem.depth || 0,
        };
    }
};

export default {
    canHaveChildren,
    flatToTree,
    treeToFlat,
    findNodeByUid,
    findParentByUid,
    removeNodeByUid,
    insertNode,
    getUidsFromTree,
    getProjectedDropPosition,
};
