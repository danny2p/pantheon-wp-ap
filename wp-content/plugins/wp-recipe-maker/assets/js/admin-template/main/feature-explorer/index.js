import React, { useEffect, useMemo, useRef, useState } from 'react';

import BlockProperties from '../../menu/BlockProperties';
import PreviewTemplate from '../preview-template';
import FEATURE_CATEGORIES from './feature-categories';
import FEATURE_EXPLORER_SNIPPET_TEMPLATE from './snippet-template';
import FEATURE_EXPLORER_TEMPLATE from './template';
import FEATURE_EXPLORER_ROUNDUP_TEMPLATE from './roundup-template';

const FORCED_DEMO_RECIPE = {
    id: 'feature-explorer',
    text: 'Use WPRM Feature Explorer Demo Recipe',
};

const FEATURES = FEATURE_CATEGORIES.reduce((allFeatures, category) => allFeatures.concat(category.features), []);

const cloneTemplate = (template) => JSON.parse(JSON.stringify(template));

const FeatureExplorer = () => {
    const [sidebarHoveredFeatureId, setSidebarHoveredFeatureId] = useState(false);
    const [previewHoveredFeatureId, setPreviewHoveredFeatureId] = useState(false);
    const [previewTooltip, setPreviewTooltip] = useState(false);
    const [selectedFeatureId, setSelectedFeatureId] = useState(false);
    const previewRef = useRef(null);
    const sidebarListRef = useRef(null);

    const snippetTemplate = useMemo(() => cloneTemplate(FEATURE_EXPLORER_SNIPPET_TEMPLATE), []);
    const explorerRecipeTemplate = useMemo(() => cloneTemplate(FEATURE_EXPLORER_TEMPLATE), []);
    const explorerRoundupTemplate = useMemo(() => cloneTemplate(FEATURE_EXPLORER_ROUNDUP_TEMPLATE), []);

    const hoveredFeatureId = sidebarHoveredFeatureId || previewHoveredFeatureId;
    const activeHighlightId = hoveredFeatureId || selectedFeatureId;
    const activeFeature = activeHighlightId ? FEATURES.find((feature) => feature.id === activeHighlightId) : false;

    const previewClasses = [
        'wprm-feature-explorer-preview',
    ];

    const getFeatureFromPreviewTarget = (target) => {
        if (!target || !target.closest) {
            return false;
        }

        for (const feature of FEATURES) {
            const matchingElement = target.closest(feature.highlightSelector);

            if (matchingElement) {
                return {
                    id: feature.id,
                    name: feature.name,
                    element: matchingElement,
                };
            }
        }

        return false;
    };

    const getFeatureIdFromPreviewTarget = (target) => {
        const feature = getFeatureFromPreviewTarget(target);
        return feature ? feature.id : false;
    };

    const scrollToFeatureInPreview = (featureId) => {
        if (!featureId || !previewRef.current) {
            return;
        }

        const feature = FEATURES.find((candidate) => candidate.id === featureId);
        if (!feature) {
            return;
        }

        const recipePreview = previewRef.current.querySelector('.wprm-feature-explorer-recipe-preview');
        let featureElement = recipePreview ? recipePreview.querySelector(feature.highlightSelector) : false;

        // Some features are shown outside the recipe preview (snippet/roundup), so fallback to full explorer preview.
        if (!featureElement) {
            featureElement = previewRef.current.querySelector(feature.highlightSelector);
        }

        if (featureElement && featureElement.scrollIntoView) {
            featureElement.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest',
            });
        }
    };

    const scrollToFeatureInSidebar = (featureId) => {
        if (!featureId || !sidebarListRef.current) {
            return;
        }

        const sidebarFeature = sidebarListRef.current.querySelector(`[data-feature-id="${featureId}"]`);
        if (sidebarFeature && sidebarFeature.scrollIntoView) {
            sidebarFeature.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'nearest',
            });
        }
    };

    const openFeature = (featureId, options = {}) => {
        if (!featureId) {
            return;
        }

        const {
            scrollToPreview = true,
            scrollToSidebar = false,
        } = options;

        setSelectedFeatureId(featureId);

        if (scrollToPreview) {
            scrollToFeatureInPreview(featureId);
        }

        if (scrollToSidebar) {
            scrollToFeatureInSidebar(featureId);
        }
    };

    useEffect(() => {
        if (!previewRef.current) {
            return undefined;
        }

        const previewElement = previewRef.current;
        const highlighted = previewElement.querySelectorAll('.wprm-feature-explorer-highlight-target');
        highlighted.forEach((element) => element.classList.remove('wprm-feature-explorer-highlight-target'));

        if (activeFeature && activeFeature.highlightSelector) {
            const nextHighlighted = previewElement.querySelectorAll(activeFeature.highlightSelector);
            nextHighlighted.forEach((element) => element.classList.add('wprm-feature-explorer-highlight-target'));
        }

        return () => {
            const cleanupHighlighted = previewElement.querySelectorAll('.wprm-feature-explorer-highlight-target');
            cleanupHighlighted.forEach((element) => element.classList.remove('wprm-feature-explorer-highlight-target'));
        };
    }, [activeFeature]);

    return (
        <div className="wprm-main-container wprm-feature-explorer">
            <h2 className="wprm-main-container-name">Feature Explorer</h2>
            <div
                ref={ previewRef }
                className={ previewClasses.join(' ') }
                onMouseMove={ (e) => {
                    const hoveredFeature = getFeatureFromPreviewTarget(e.target);

                    if (!hoveredFeature || !previewRef.current) {
                        if (previewHoveredFeatureId) {
                            setPreviewHoveredFeatureId(false);
                        }

                        if (previewTooltip) {
                            setPreviewTooltip(false);
                        }

                        return;
                    }

                    if (hoveredFeature.id !== previewHoveredFeatureId) {
                        setPreviewHoveredFeatureId(hoveredFeature.id);
                    }

                    const previewRect = previewRef.current.getBoundingClientRect();
                    const hoveredRect = hoveredFeature.element.getBoundingClientRect();
                    const tooltipTop = hoveredRect.top - previewRect.top;
                    const tooltipLeft = hoveredRect.left - previewRect.left + hoveredRect.width / 2;
                    const isTopPlacement = tooltipTop > 84;
                    const clampedLeft = Math.max(36, Math.min(tooltipLeft, previewRect.width - 36));

                    const nextTooltip = {
                        name: hoveredFeature.name,
                        top: tooltipTop,
                        left: clampedLeft,
                        placement: isTopPlacement ? 'top' : 'bottom',
                    };

                    if (
                        !previewTooltip
                        || previewTooltip.name !== nextTooltip.name
                        || previewTooltip.top !== nextTooltip.top
                        || previewTooltip.left !== nextTooltip.left
                        || previewTooltip.placement !== nextTooltip.placement
                    ) {
                        setPreviewTooltip(nextTooltip);
                    }
                } }
                onMouseLeave={ () => {
                    setPreviewHoveredFeatureId(false);
                    setPreviewTooltip(false);
                } }
                onClickCapture={ (e) => {
                    // Prevent links/buttons in the preview from navigating away.
                    e.preventDefault();
                    e.stopPropagation();

                    const clickedFeature = getFeatureIdFromPreviewTarget(e.target);
                    if (clickedFeature) {
                        openFeature(clickedFeature, {
                            scrollToSidebar: true,
                        });
                    }
                } }
            >
                <div className="wprm-feature-explorer-post-content">
                    <p>This preview represents a regular blog post with a recipe card and some additional content. Hover over a feature to see it in action and click one to learn more in the sidebar.</p>
                    <div className="wprm-feature-explorer-snippet-preview">
                        <PreviewTemplate
                            template={ snippetTemplate }
                            mode={ 'onboarding' }
                            forcedRecipe={ FORCED_DEMO_RECIPE }
                            onChangeMode={ () => {} }
                            onChangeHTML={ () => {} }
                        />
                    </div>
                    <p>This is an example food blog post preview showing how snippets and recipes can appear together in real content.</p>
                    <p>Readers often skim first, then jump to recipe details. This explorer highlights a few features that improve that experience.</p>
                    <div className="wprm-feature-explorer-recipe-preview">
                        <PreviewTemplate
                            template={ explorerRecipeTemplate }
                            mode={ 'onboarding' }
                            forcedRecipe={ FORCED_DEMO_RECIPE }
                            onChangeMode={ () => {} }
                            onChangeHTML={ () => {} }
                        />
                    </div>
                    <p>After the recipe card, the post can continue with serving tips, substitutions, or storage suggestions.</p>
                    <p className="wprm-feature-explorer-roundup-intro">We also have our Recipe Roundup feature, which useful for roundup posts that include links to recipes on your own site and/or external sites. This is what one such roundup item could look like, for example:</p>
                    <div className="wprm-feature-explorer-roundup-preview wprm-feature-explorer-roundup-preview-internal">
                        <PreviewTemplate
                            template={ explorerRoundupTemplate }
                            mode={ 'onboarding' }
                            forcedRecipe={ FORCED_DEMO_RECIPE }
                            onChangeMode={ () => {} }
                            onChangeHTML={ () => {} }
                        />
                    </div>
                </div>
                {
                    previewTooltip
                    &&
                    <div
                        className={ `wprm-feature-explorer-preview-tooltip wprm-feature-explorer-preview-tooltip-${previewTooltip.placement}` }
                        style={{
                            top: `${previewTooltip.top}px`,
                            left: `${previewTooltip.left}px`,
                        }}
                    >
                        { previewTooltip.name }
                    </div>
                }
            </div>
            <BlockProperties>
                <p>Highlighted Features</p>
                <div ref={ sidebarListRef } className="wprm-feature-explorer-sidebar-list">
                    {
                        FEATURE_CATEGORIES.map((category) => (
                            <div key={ category.id } className="wprm-feature-explorer-sidebar-category">
                                <div className="wprm-feature-explorer-sidebar-category-title">{ category.name }</div>
                                <div className="wprm-feature-explorer-sidebar-category-items">
                                    {
                                        category.features.map((feature) => {
                                            const classes = [
                                                'wprm-feature-explorer-sidebar-item',
                                            ];
                                            const isSelected = selectedFeatureId === feature.id;

                                            if (isSelected) {
                                                classes.push('wprm-feature-explorer-sidebar-item-selected');
                                            }

                                            if (hoveredFeatureId === feature.id) {
                                                classes.push('wprm-feature-explorer-sidebar-item-hovered');
                                            }

                                            return (
                                                <div key={ feature.id } className="wprm-feature-explorer-sidebar-item-container">
                                                    <button
                                                        type="button"
                                                        className={ classes.join(' ') }
                                                        data-feature-id={ feature.id }
                                                        onMouseEnter={ () => setSidebarHoveredFeatureId(feature.id) }
                                                        onMouseLeave={ () => setSidebarHoveredFeatureId(false) }
                                                        onClick={ () => {
                                                            if (isSelected) {
                                                                setSelectedFeatureId(false);
                                                            } else {
                                                                openFeature(feature.id);
                                                            }
                                                        } }
                                                    >
                                                        <span className="wprm-feature-explorer-sidebar-item-name">{ feature.name }</span>
                                                        {
                                                            feature.isNew
                                                            &&
                                                            <span className="wprm-feature-explorer-sidebar-item-badge">New</span>
                                                        }
                                                    </button>
                                                    {
                                                        isSelected
                                                        &&
                                                        <div className="wprm-feature-explorer-sidebar-item-panel">
                                                            <p>{ feature.explanation }</p>
                                                            {
                                                                feature.demoUrl
                                                                &&
                                                                <p><a href={ feature.demoUrl } target="_blank" rel="noopener noreferrer">View this feature on our demo site</a></p>
                                                            }
                                                            {
                                                                feature.docsUrl
                                                                &&
                                                                <p><a href={ feature.docsUrl } target="_blank" rel="noopener noreferrer">Learn more in our documentation</a></p>
                                                            }
                                                        </div>
                                                    }
                                                </div>
                                            );
                                        })
                                    }
                                </div>
                            </div>
                        ))
                    }
                </div>
            </BlockProperties>
        </div>
    );
};

export default FeatureExplorer;
