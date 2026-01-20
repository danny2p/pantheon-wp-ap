import React, { useMemo, useRef, useEffect } from 'react';
import Helpers from '../../general/Helpers';
import Shortcodes from '../../general/shortcodes';
import Elements from '../../general/elements';
import Patterns from '../../general/patterns';

const { shortcodeGroups, shortcodeKeysAlphebetically, getShortcodeId, newShortcodes } = Shortcodes;
const { newPatterns } = Patterns;

// Build patterns as a group
const patternEntries = Object.entries(Patterns.patterns).map(([id, pattern]) => ({
    id,
    name: pattern.name,
    description: pattern.description || '',
    isPattern: true,
    isNew: newPatterns.includes(id),
}));

const combinedGroups = {
    patterns: {
        group: 'Patterns',
        description: 'Reusable block combinations to quickly add common layouts.',
        shortcodes: patternEntries,
    },
    ...shortcodeGroups,
};

const AddBlocksView = ({ onSelectBlock, onSelectPattern, searchQuery = '', scrollToCategory = null, showNewOnly = false, onToggleShowNewOnly = null }) => {
    const groupRefs = useRef({});

    // Filter blocks based on search query and new filter
    const filteredGroups = useMemo(() => {
        const query = searchQuery.trim().toLowerCase();
        const hasSearchQuery = query.length > 0;
        const filtered = {};

        Object.keys(combinedGroups).forEach(groupKey => {
            const group = combinedGroups[groupKey];
            let matchingShortcodes = group.shortcodes;

            // Filter by isNew if showNewOnly is true
            if (showNewOnly) {
                matchingShortcodes = matchingShortcodes.filter(entry => {
                    const id = getShortcodeId(entry);
                    if (entry.isPattern) {
                        return newPatterns.includes(id);
                    }
                    return newShortcodes.includes(id);
                });
            }

            // Filter by search query if present
            if (hasSearchQuery) {
                matchingShortcodes = matchingShortcodes.filter(entry => {
                    const id = getShortcodeId(entry);
                    const name = (entry.isPattern ? entry.name : Helpers.getShortcodeName(id)).toLowerCase();
                    const description = (entry.description || '').toLowerCase();
                    return name.includes(query) || description.includes(query);
                });
            }

            if (matchingShortcodes.length > 0) {
                filtered[groupKey] = {
                    ...group,
                    shortcodes: matchingShortcodes,
                };
            }
        });

        return filtered;
    }, [searchQuery, showNewOnly]);

    // Calculate matching existing blocks when filtering by new only
    const matchingExistingBlocksCount = useMemo(() => {
        if (!showNewOnly) {
            return 0;
        }

        const query = searchQuery.trim().toLowerCase();
        const hasSearchQuery = query.length > 0;
        let count = 0;

        Object.keys(combinedGroups).forEach(groupKey => {
            const group = combinedGroups[groupKey];
            let matchingShortcodes = group.shortcodes;

            // Filter out new blocks (we want existing ones)
            matchingShortcodes = matchingShortcodes.filter(entry => {
                const id = getShortcodeId(entry);
                if (entry.isPattern) {
                    return !newPatterns.includes(id);
                }
                return !newShortcodes.includes(id);
            });

            // Filter by search query if present
            if (hasSearchQuery) {
                matchingShortcodes = matchingShortcodes.filter(entry => {
                    const id = getShortcodeId(entry);
                    const name = (entry.isPattern ? entry.name : Helpers.getShortcodeName(id)).toLowerCase();
                    const description = (entry.description || '').toLowerCase();
                    return name.includes(query) || description.includes(query);
                });
            } else {
                // If no search query, count all existing blocks
                matchingShortcodes = matchingShortcodes.filter(entry => {
                    const id = getShortcodeId(entry);
                    return shortcodeKeysAlphebetically.includes(id) || Elements.layoutElements.includes(id) || entry.isPattern;
                });
            }

            count += matchingShortcodes.length;
        });

        return count;
    }, [showNewOnly, searchQuery]);

    const handleEntryClick = (entry) => {
        const blockId = getShortcodeId(entry);
        if (entry.isPattern && onSelectPattern) {
            onSelectPattern(blockId);
        } else {
            onSelectBlock(blockId);
        }
    };

    const getBlockDescription = (entry) => {
        return entry.description || 'No description available';
    };

    const isBlockAvailable = (entry) => {
        // Patterns are always available
        if (entry.isPattern) {
            return true;
        }
        const id = getShortcodeId(entry);
        return shortcodeKeysAlphebetically.includes(id) || Elements.layoutElements.includes(id);
    };

    // Handle scroll to category
    useEffect(() => {
        if (scrollToCategory && groupRefs.current[scrollToCategory]) {
            const element = groupRefs.current[scrollToCategory];
            const container = element.closest('.wprm-add-blocks-view');
            
            if (container) {
                const offset = 50; // Small offset from top
                const containerRect = container.getBoundingClientRect();
                const elementRect = element.getBoundingClientRect();
                const scrollTop = container.scrollTop || window.pageYOffset;
                const targetScroll = scrollTop + elementRect.top - containerRect.top - offset;

                // Scroll the container if it's scrollable, otherwise scroll window
                if (container.scrollHeight > container.clientHeight) {
                    container.scrollTo({
                        top: targetScroll,
                        behavior: 'smooth'
                    });
                } else {
                    const windowOffset = elementRect.top + window.pageYOffset - offset;
                    window.scrollTo({
                        top: windowOffset,
                        behavior: 'smooth'
                    });
                }
            }
        }
    }, [scrollToCategory]);

    return (
        <div className="wprm-add-blocks-view">
            <div className="wprm-add-blocks-view-header">
                <h2>Add Block to Template</h2>
                <p>Select a block to add to your template. Each block displays different recipe information or functionality.</p>
            </div>

            <div className="wprm-add-blocks-view-content">
                {Object.keys(filteredGroups).length === 0 ? (
                    <div className="wprm-add-blocks-view-empty">
                        {showNewOnly && matchingExistingBlocksCount > 0 ? (
                            <p>
                                {searchQuery.trim() ? (
                                    <>
                                        {matchingExistingBlocksCount} existing {matchingExistingBlocksCount === 1 ? 'block' : 'blocks'} match "{searchQuery}",{' '}
                                        <a 
                                            href="#" 
                                            onClick={(e) => {
                                                e.preventDefault();
                                                if (onToggleShowNewOnly) {
                                                    onToggleShowNewOnly(false);
                                                }
                                            }}
                                            className="wprm-add-blocks-view-empty-link"
                                        >
                                            click to show
                                        </a>
                                    </>
                                ) : (
                                    <>
                                        {matchingExistingBlocksCount} existing {matchingExistingBlocksCount === 1 ? 'block' : 'blocks'} available,{' '}
                                        <a 
                                            href="#" 
                                            onClick={(e) => {
                                                e.preventDefault();
                                                if (onToggleShowNewOnly) {
                                                    onToggleShowNewOnly(false);
                                                }
                                            }}
                                            className="wprm-add-blocks-view-empty-link"
                                        >
                                            click to show
                                        </a>
                                    </>
                                )}
                            </p>
                        ) : (
                            <p>
                                {showNewOnly && searchQuery.trim() 
                                    ? `No new blocks found matching "${searchQuery}"`
                                    : showNewOnly
                                    ? 'No new blocks found'
                                    : `No blocks found matching "${searchQuery}"`}
                            </p>
                        )}
                    </div>
                ) : (
                    Object.keys(filteredGroups).map((groupKey) => {
                        const group = filteredGroups[groupKey];
                        const availableBlocks = group.shortcodes.filter(isBlockAvailable);

                        if (availableBlocks.length === 0) {
                            return null;
                        }

                        return (
                            <div 
                                key={groupKey} 
                                className="wprm-add-blocks-view-group"
                                ref={(el) => { groupRefs.current[groupKey] = el; }}
                                id={`wprm-add-blocks-category-${groupKey}`}
                            >
                                <div className="wprm-add-blocks-view-group-header">
                                    <h3 className="wprm-add-blocks-view-group-title">{group.group}</h3>
                                    {group.description && (
                                        <p className="wprm-add-blocks-view-group-description">{group.description}</p>
                                    )}
                                </div>
                                <div className="wprm-add-blocks-view-grid">
                                    {availableBlocks.map((entry) => {
                                        const blockId = getShortcodeId(entry);
                                        const name = entry.isPattern ? entry.name : Helpers.getShortcodeName(blockId);
                                        const description = getBlockDescription(entry);
                                        const isNew = entry.isPattern 
                                            ? newPatterns.includes(blockId)
                                            : newShortcodes.includes(blockId);

                                        return (
                                            <div
                                                key={blockId}
                                                className={`wprm-add-blocks-view-card${isNew ? ' wprm-add-blocks-view-card-new' : ''}`}
                                                onClick={() => handleEntryClick(entry)}
                                            >
                                                <div className="wprm-add-blocks-view-card-header">
                                                    <h4 className="wprm-add-blocks-view-card-title">
                                                        {name}
                                                        {isNew && (
                                                            <span className="wprm-add-blocks-view-card-new-badge">New</span>
                                                        )}
                                                    </h4>
                                                </div>
                                                <div className="wprm-add-blocks-view-card-body">
                                                    <p className="wprm-add-blocks-view-card-description">{description}</p>
                                                </div>
                                                <div className="wprm-add-blocks-view-card-footer">
                                                    <span className="wprm-add-blocks-view-card-action">Click to add →</span>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        );
                    })
                )}
            </div>
        </div>
    );
};

export default AddBlocksView;

