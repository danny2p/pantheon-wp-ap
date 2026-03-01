import React, { Component } from 'react';
import { withRouter } from 'react-router-dom';

import Api from 'Shared/Api';
import Helpers from './general/Helpers';
import Shortcodes from './general/shortcodes';

import Menu from './menu';
import Main from './main';

import '../../css/admin/template/layout.scss';

const isEnabledSetting = ( value, defaultValue = true ) => {
    if ( 'undefined' === typeof value || null === value ) {
        return defaultValue;
    }

    if ( 'boolean' === typeof value ) {
        return value;
    }

    if ( 'number' === typeof value ) {
        return 0 !== value;
    }

    if ( 'string' === typeof value ) {
        const normalized = value.trim().toLowerCase();

        if ( [ '0', 'false', 'off', 'no', '' ].includes( normalized ) ) {
            return false;
        }

        if ( [ '1', 'true', 'on', 'yes' ].includes( normalized ) ) {
            return true;
        }
    }

    return !! value;
};

class App extends Component {

    constructor(props) {
        super(props);

        this.state = {
            mode: 'manage',
            editing: false,
            shortcode: false,
            templates: JSON.parse(JSON.stringify(wprm_admin_template.templates)),
            template: false,
            historyPast: [],
            historyFuture: [],
            historyPastCapped: false,
            codeHistoryResetNotice: false,
            savingTemplate: false,
            sidebarCollapsed: false,
            initializingFromUrl: true,
            editingBlock: false,
            manageTemplateType: false,
            defaultTemplateUsages: wprm_admin_template.default_template_usages || {},
        }
        this.historyEnabled = isEnabledSetting( wprm_admin_template.undo_redo_history, true );
        
        // Track if we're updating URL ourselves to prevent re-parsing
        this.isUpdatingUrl = false;
        this.historyDebounceTimer = null;
        this.historyDebounceOpen = false;
        this.historyDebounceMs = 750;
        this.historyMaxSteps = 20;
        this.samePropertyMergeWindowMs = 5000;
        this.lastTemplatePropertyChange = {
            id: false,
            at: 0,
            baseTemplate: false,
        };
    }

    componentDidMount() {
        window.addEventListener( 'beforeunload', this.beforeWindowClose.bind(this) );
        
        // Parse URL to restore state
        this.parseUrlAndRestoreState();
    }

    componentDidUpdate(prevProps) {
        // If URL changed externally (e.g., browser back/forward), restore state
        // But skip if we're updating the URL ourselves
        if (prevProps.location.pathname !== this.props.location.pathname) {
            if (!this.isUpdatingUrl) {
                this.parseUrlAndRestoreState();
            } else {
                // Reset flag after React processes the URL change
                // Use setTimeout to ensure it happens after all updates
                setTimeout(() => {
                    this.isUpdatingUrl = false;
                }, 0);
            }
        }
    }

    parseUrlAndRestoreState() {
        const path = this.props.location.pathname || '/';
        const previousSessionKey = this.getCurrentSessionKey();
        const nextSessionKey = this.getSessionKeyForPath(path);
        const shouldResetHistory = previousSessionKey !== nextSessionKey;
        const historyResetState = shouldResetHistory ? {
            historyPast: [],
            historyFuture: [],
            historyPastCapped: false,
        } : {};

        if ( shouldResetHistory ) {
            this.closeHistoryDebounceWindow();
            this.resetLastTemplatePropertyChange();
        }
        
        // Parse URL path
        if (path === '/' || path === '/manage' || path.startsWith('/manage/')) {
            // Manage Templates overview
            // Parse: /manage/:type/:slug?
            let manageType = false;
            let manageSlug = false;
            
            if (path.startsWith('/manage/')) {
                const pathParts = path.replace('/manage/', '').split('/');
                const validTypes = ['recipe', 'snippet', 'roundup', 'import'];
                if (pathParts[0] && validTypes.includes(pathParts[0])) {
                    manageType = pathParts[0];
                    if (pathParts[1]) {
                        manageSlug = pathParts[1];
                    }
                }
            }
            
            // Validate that slug exists in templates if provided
            let finalTemplate = false;
            if (manageSlug && this.state.templates.hasOwnProperty(manageSlug)) {
                finalTemplate = manageSlug;
            }
            
            this.setState({
                mode: 'manage',
                editing: false,
                shortcode: false,
                template: finalTemplate ? JSON.parse(JSON.stringify(this.state.templates[finalTemplate])) : false,
                editingBlock: false,
                manageTemplateType: manageType,
                initializingFromUrl: false,
                ...historyResetState,
            });
        } else if (path.startsWith('/shortcode/')) {
            // Shortcode Generator - editing a specific shortcode
            const shortcodeId = path.replace('/shortcode/', '');
            const shortcode = this.findShortcodeById(shortcodeId);
            
            if (shortcode) {
                this.setState({
                    mode: 'shortcode',
                    editing: false,
                    shortcode: shortcode,
                    template: false,
                    editingBlock: false,
                    manageTemplateType: false,
                    initializingFromUrl: false,
                    ...historyResetState,
                });
            } else {
                // Invalid shortcode ID, redirect to shortcode generator
                this.updateUrl('/shortcode');
                this.setState({
                    mode: 'shortcode',
                    editing: false,
                    shortcode: false,
                    template: false,
                    editingBlock: false,
                    manageTemplateType: false,
                    initializingFromUrl: false,
                    ...historyResetState,
                });
            }
        } else if (path === '/shortcode') {
            // Shortcode Generator (no shortcode selected)
            this.setState({
                mode: 'shortcode',
                editing: false,
                shortcode: false,
                template: false,
                editingBlock: false,
                manageTemplateType: false,
                initializingFromUrl: false,
                ...historyResetState,
            });
        } else if (path === '/explorer') {
            // Feature Explorer
            this.setState({
                mode: 'feature-explorer',
                editing: false,
                shortcode: false,
                template: false,
                editingBlock: false,
                manageTemplateType: false,
                initializingFromUrl: false,
                ...historyResetState,
            });
        } else if (path.startsWith('/template/')) {
            // Editing a specific template
            // Parse: /template/:slug/:mode/:blockUid?
            const pathParts = path.replace('/template/', '').split('/');
            const slug = pathParts[0];
            const mode = pathParts[1] || 'properties'; // Default to properties
            const blockUid = pathParts[2] ? parseInt(pathParts[2], 10) : false;
            
            // Validate mode
            const validModes = ['properties', 'blocks', 'add', 'html', 'css'];
            const finalMode = validModes.includes(mode) ? mode : 'properties';
            
            // Only allow block UID for blocks mode
            const finalEditingBlock = ('blocks' === finalMode && blockUid !== false && !isNaN(blockUid)) ? blockUid : false;
            
            if (this.state.templates.hasOwnProperty(slug)) {
                const template = JSON.parse(JSON.stringify(this.state.templates[slug]));
                this.setState({
                    mode: finalMode,
                    editing: true,
                    shortcode: false,
                    template: template,
                    editingBlock: finalEditingBlock,
                    initializingFromUrl: false,
                    ...historyResetState,
                });
            } else {
                // Invalid template slug, redirect to manage
                this.updateUrl('/manage');
                this.setState({
                    mode: 'manage',
                    editing: false,
                    shortcode: false,
                    template: false,
                    editingBlock: false,
                    initializingFromUrl: false,
                    ...historyResetState,
                });
            }
        } else {
            // Unknown path, default to manage
            this.updateUrl('/manage');
            this.setState({
                mode: 'manage',
                editing: false,
                shortcode: false,
                template: false,
                editingBlock: false,
                manageTemplateType: false,
                initializingFromUrl: false,
                ...historyResetState,
            });
        }
    }

    cloneTemplate(template) {
        return JSON.parse(JSON.stringify(template));
    }

    isTemplateEqual(a, b) {
        return JSON.stringify(a) === JSON.stringify(b);
    }

    getCurrentSessionKey() {
        return this.state.editing && this.state.template && this.state.template.slug
            ? `template:${this.state.template.slug}`
            : false;
    }

    getSessionKeyForPath(path) {
        if ( path && path.startsWith('/template/') ) {
            const slug = path.replace('/template/', '').split('/')[0];

            return slug ? `template:${slug}` : false;
        }

        return false;
    }

    scheduleHistoryDebounceClose() {
        if ( this.historyDebounceTimer ) {
            clearTimeout(this.historyDebounceTimer);
        }

        this.historyDebounceTimer = setTimeout(() => {
            this.historyDebounceOpen = false;
            this.historyDebounceTimer = null;
        }, this.historyDebounceMs);
    }

    closeHistoryDebounceWindow() {
        if ( this.historyDebounceTimer ) {
            clearTimeout(this.historyDebounceTimer);
            this.historyDebounceTimer = null;
        }

        this.historyDebounceOpen = false;
    }

    trimHistoryStack(historyStack) {
        if ( historyStack.length > this.historyMaxSteps ) {
            return historyStack.slice( historyStack.length - this.historyMaxSteps );
        }

        return historyStack;
    }

    trimHistoryPast(historyPast, alreadyCapped = false) {
        const cappedNow = historyPast.length > this.historyMaxSteps;

        return {
            historyPast: this.trimHistoryStack(historyPast),
            historyPastCapped: alreadyCapped || cappedNow,
        };
    }

    resetHistory() {
        this.closeHistoryDebounceWindow();
        this.lastTemplatePropertyChange = {
            id: false,
            at: 0,
            baseTemplate: false,
        };
        this.setState({
            historyPast: [],
            historyFuture: [],
            historyPastCapped: false,
        });
    }

    resetLastTemplatePropertyChange() {
        this.lastTemplatePropertyChange = {
            id: false,
            at: 0,
            baseTemplate: false,
        };
    }

    shouldMergeWithPreviousTemplatePropertyChange(id, now = Date.now()) {
        const sameProperty = this.lastTemplatePropertyChange.id === id;
        const withinWindow = ( now - this.lastTemplatePropertyChange.at ) < this.samePropertyMergeWindowMs;
        const hasBaseTemplate = !! this.lastTemplatePropertyChange.baseTemplate;

        return sameProperty && withinWindow && hasBaseTemplate;
    }

    recordHistoryBeforeChange(prevState, { mode = 'immediate', persistUntilBoundary = false } = {}) {
        if ( ! this.historyEnabled ) {
            return {
                historyPast: [],
                historyFuture: [],
                historyPastCapped: false,
            };
        }

        if ( ! prevState.editing || ! prevState.template ) {
            return {
                historyPast: prevState.historyPast,
                historyFuture: prevState.historyFuture,
                historyPastCapped: prevState.historyPastCapped,
            };
        }

        let historyPast = prevState.historyPast;
        let historyPastCapped = prevState.historyPastCapped;
        const currentTemplateSnapshot = this.cloneTemplate(prevState.template);
        const lastHistoryTemplate = historyPast.length ? historyPast[historyPast.length - 1] : false;

        if ( 'debounced' === mode ) {
            if ( ! this.historyDebounceOpen ) {
                if ( ! lastHistoryTemplate || ! this.isTemplateEqual(lastHistoryTemplate, currentTemplateSnapshot) ) {
                    const trimmedHistoryPast = this.trimHistoryPast(historyPast.concat([currentTemplateSnapshot]), historyPastCapped);
                    historyPast = trimmedHistoryPast.historyPast;
                    historyPastCapped = trimmedHistoryPast.historyPastCapped;
                }
                this.historyDebounceOpen = true;
            }

            if ( persistUntilBoundary ) {
                if ( this.historyDebounceTimer ) {
                    clearTimeout(this.historyDebounceTimer);
                    this.historyDebounceTimer = null;
                }
            } else {
                this.scheduleHistoryDebounceClose();
            }
        } else {
            this.closeHistoryDebounceWindow();

            if ( ! lastHistoryTemplate || ! this.isTemplateEqual(lastHistoryTemplate, currentTemplateSnapshot) ) {
                const trimmedHistoryPast = this.trimHistoryPast(historyPast.concat([currentTemplateSnapshot]), historyPastCapped);
                historyPast = trimmedHistoryPast.historyPast;
                historyPastCapped = trimmedHistoryPast.historyPastCapped;
            }
        }

        const trimmedHistoryPast = this.trimHistoryPast(historyPast, historyPastCapped);

        return {
            historyPast: trimmedHistoryPast.historyPast,
            historyFuture: [],
            historyPastCapped: trimmedHistoryPast.historyPastCapped,
        };
    }

    onUndo() {
        if ( ! this.historyEnabled ) {
            return;
        }

        this.closeHistoryDebounceWindow();
        this.resetLastTemplatePropertyChange();

        this.setState((prevState) => {
            if ( ! prevState.template || ! prevState.historyPast.length ) {
                return null;
            }

            const restoredTemplate = prevState.historyPast[ prevState.historyPast.length - 1 ];

            return {
                template: this.cloneTemplate(restoredTemplate),
                historyPast: prevState.historyPast.slice(0, -1),
                historyFuture: this.trimHistoryStack(prevState.historyFuture.concat([ this.cloneTemplate(prevState.template) ])),
                historyPastCapped: prevState.historyPastCapped,
            };
        });
    }

    onRedo() {
        if ( ! this.historyEnabled ) {
            return;
        }

        this.closeHistoryDebounceWindow();
        this.resetLastTemplatePropertyChange();

        this.setState((prevState) => {
            if ( ! prevState.template || ! prevState.historyFuture.length ) {
                return null;
            }

            const restoredTemplate = prevState.historyFuture[ prevState.historyFuture.length - 1 ];
            const trimmedHistoryPast = this.trimHistoryPast(prevState.historyPast.concat([ this.cloneTemplate(prevState.template) ]), prevState.historyPastCapped);

            return {
                template: this.cloneTemplate(restoredTemplate),
                historyPast: trimmedHistoryPast.historyPast,
                historyFuture: prevState.historyFuture.slice(0, -1),
                historyPastCapped: trimmedHistoryPast.historyPastCapped,
            };
        });
    }

    findShortcodeById(shortcodeId) {
        // Try to find shortcode by ID in the current state
        if (this.state.shortcode && this.state.shortcode.id === shortcodeId) {
            return this.state.shortcode;
        }
        
        // If not found, create a basic shortcode object from the ID
        // This handles the case where we're navigating directly to a shortcode URL
        const { shortcodeKeysAlphebetically, contentShortcodes } = Shortcodes;
        
        if (shortcodeKeysAlphebetically.includes(shortcodeId)) {
            const needsClosingShortcode = contentShortcodes.includes(shortcodeId);
            return {
                uid: 0,
                id: shortcodeId,
                name: Helpers.getShortcodeName(shortcodeId),
                attributes: {},
                full: needsClosingShortcode ? `[${shortcodeId}][/${shortcodeId}]` : `[${shortcodeId}]`,
            };
        }
        
        return false;
    }

    updateUrl(path) {
        if (this.props.history) {
            this.isUpdatingUrl = true;
            this.props.history.push(path);
        }
    }
    
    componentWillUnmount() {
        this.closeHistoryDebounceWindow();
        window.removeEventListener( 'beforeunload', this.beforeWindowClose.bind(this) );
    }

    beforeWindowClose(event) {
        if ( this.changesMade() ) {
            return false;
        }
    }

    changesMade() {
        return this.state.editing &&
                ( this.state.template.html !== this.state.templates[this.state.template.slug].html
                || Helpers.parseCSS( this.state.template ) !== Helpers.parseCSS( this.state.templates[this.state.template.slug] ) );
    }

    onChangeEditing(editing) {
        if ( editing !== this.state.editing ) {
            // Scroll to top.
            window.scrollTo(0,0);

            if ( editing ) {
                // Update URL to template editing with current mode
                if (this.state.template && this.state.template.slug) {
                    const mode = this.state.mode || 'properties';
                    this.updateUrl(`/template/${this.state.template.slug}/${mode}`);
                }
                this.setState({
                    editing,
                    mode: 'properties',
                    editingBlock: false, // Reset editing block when starting to edit
                    historyPast: [],
                    historyFuture: [],
                    historyPastCapped: false,
                });
            } else {
                // Update URL to manage overview (preserve type and selected template if any)
                this.updateManageUrl(this.state.manageTemplateType, false);
                this.closeHistoryDebounceWindow();
                this.setState({
                    editing,
                    mode: 'manage',
                    template: false,
                    editingBlock: false,
                    historyPast: [],
                    historyFuture: [],
                    historyPastCapped: false,
                });
            }

            this.resetLastTemplatePropertyChange();
        }
    }

    updateManageUrl(type, slug) {
        if (type) {
            if (slug) {
                this.updateUrl(`/manage/${type}/${slug}`);
            } else {
                this.updateUrl(`/manage/${type}`);
            }
        } else {
            this.updateUrl('/manage');
        }
    }

    onChangeManageTemplateType(type) {
        if (type !== this.state.manageTemplateType) {
            // Clear selected template when changing type
            // Update state first, then URL in callback to avoid race condition
            this.setState({
                manageTemplateType: type,
                template: false,
            }, () => {
                this.updateManageUrl(type, false);
            });
        }
    }

    onChangeMode(mode) {
        // Handle clicking "Edit Blocks" while already in blocks mode and editing a specific block
        // In this case, clear the editing block to show the list of all blocks
        if ( 'blocks' === mode && 'blocks' === this.state.mode && this.state.editingBlock !== false && typeof this.state.editingBlock === 'number' ) {
            // Update URL to blocks list (without block UID)
            if ( this.state.editing && this.state.template && this.state.template.slug ) {
                this.updateUrl(`/template/${this.state.template.slug}/blocks`);
            }
            
            this.setState({
                editingBlock: false,
                // Expand sidebar when clicking on a menu item
                sidebarCollapsed: false,
            });
            return;
        }

        if ( mode !== this.state.mode ) {
            // Scroll to top when going to or coming from HTML/CSS mode.
            if ( 'html' === mode || 'html' === this.state.mode || 'css' === mode || 'css' === this.state.mode ) {
                window.scrollTo(0,0);
            }

            // Update URL for shortcode mode
            if ( 'feature-explorer' === mode ) {
                this.updateUrl('/explorer');
            } else if ( 'shortcode' === mode ) {
                if (this.state.shortcode && this.state.shortcode.id) {
                    this.updateUrl(`/shortcode/${this.state.shortcode.id}`);
                } else {
                    this.updateUrl('/shortcode');
                }
            } else if ( 'manage' === mode && !this.state.editing ) {
                // Preserve template type and selected template when navigating to manage
                this.updateManageUrl(this.state.manageTemplateType, this.state.template ? this.state.template.slug : false);
            } else if ( this.state.editing && this.state.template && this.state.template.slug ) {
                // Update URL with mode when editing a template
                const validModes = ['properties', 'blocks', 'add', 'html', 'css'];
                if (validModes.includes(mode)) {
                    // Reset editing block when switching modes (unless staying in blocks mode)
                    // Allow editingBlock to be 0 (first block) - only exclude false
                    const editingBlock = ('blocks' === mode && this.state.editingBlock !== false && typeof this.state.editingBlock === 'number') ? this.state.editingBlock : false;
                    if (editingBlock !== false) {
                        this.updateUrl(`/template/${this.state.template.slug}/${mode}/${editingBlock}`);
                    } else {
                        this.updateUrl(`/template/${this.state.template.slug}/${mode}`);
                    }
                }
            }

            this.setState((prevState) => {
                // Reset editing block when switching away from blocks mode
                // Allow editingBlock to be 0 (first block) - only exclude false
                const editingBlock = ('blocks' === mode && prevState.editingBlock !== false && typeof prevState.editingBlock === 'number') ? prevState.editingBlock : false;

                return {
                    mode,
                    template: 'feature-explorer' === mode ? false : prevState.template,
                    shortcode: 'feature-explorer' === mode ? false : prevState.shortcode,
                    editingBlock: editingBlock,
                    codeHistoryResetNotice: 'html' === mode || 'css' === mode ? false : prevState.codeHistoryResetNotice,
                    // Expand sidebar when clicking on a menu item
                    sidebarCollapsed: false,
                };
            });
        }
    }

    onChangeEditingBlock(uid) {
        if ( uid !== this.state.editingBlock ) {
            // Update URL when editing block changes (only in blocks mode)
            if ( this.state.editing && this.state.template && this.state.template.slug && 'blocks' === this.state.mode ) {
                // Allow uid 0 (first block) - only exclude false (no block selected)
                if (uid !== false && typeof uid === 'number') {
                    this.updateUrl(`/template/${this.state.template.slug}/blocks/${uid}`);
                } else {
                    this.updateUrl(`/template/${this.state.template.slug}/blocks`);
                }
            }
            
            this.setState({
                editingBlock: uid,
            });
        }
    }

    onToggleSidebar() {
        this.setState({
            sidebarCollapsed: !this.state.sidebarCollapsed,
        });
    }

    onChangeTemplate(slug) {
        // Don't do anything if we're in the middle of saving.
        if ( ! this.state.savingTemplate ) {
            const currentSlug = this.state.template && this.state.template.slug ? this.state.template.slug : false;
            const switchingTemplateWhileEditing = this.state.editing && currentSlug && currentSlug !== slug;

            if ( switchingTemplateWhileEditing ) {
                this.closeHistoryDebounceWindow();
                this.resetLastTemplatePropertyChange();
            }

            if (this.state.templates.hasOwnProperty(slug)) {
                const template = this.cloneTemplate(this.state.templates[slug]); // Important: use deep clone.
                
                // Update state first, then URL in callback to avoid race condition
                this.setState({
                    template: template,
                    editingBlock: false, // Reset editing block when switching templates
                    ...( switchingTemplateWhileEditing ? { historyPast: [], historyFuture: [], historyPastCapped: false } : {} ),
                }, () => {
                    // Update URL if we're editing this template
                    if (this.state.editing) {
                        const mode = this.state.mode || 'properties';
                        this.updateUrl(`/template/${slug}/${mode}`);
                    } else if (this.state.mode === 'manage') {
                        // Update URL in manage mode to include template type and selected template
                        this.updateManageUrl(this.state.manageTemplateType, slug);
                    }
                });
            } else {
                // If template not found and we're editing, go back to manage
                if (this.state.editing) {
                    this.closeHistoryDebounceWindow();
                    this.resetLastTemplatePropertyChange();
                    this.setState({
                        editing: false,
                        mode: 'manage',
                        template: false,
                        editingBlock: false,
                        historyPast: [],
                        historyFuture: [],
                        historyPastCapped: false,
                    }, () => {
                        this.updateManageUrl(this.state.manageTemplateType, false);
                    });
                } else if (this.state.mode === 'manage') {
                    // Update URL in manage mode when deselecting template
                    this.setState({
                        template: false,
                    }, () => {
                        this.updateManageUrl(this.state.manageTemplateType, false);
                    });
                } else {
                    this.setState({
                        template: false,
                    });
                }
            }
        }
    }

    onChangeTemplateProperty(id, value, options = {}) {
        if ( ! this.state.template || ! this.state.template.style || ! this.state.template.style.properties || ! this.state.template.style.properties[id] ) {
            return;
        }

        const historyMode = options.historyMode || 'immediate';
        const historyBoundary = !! options.historyBoundary;
        const historyPersistUntilBoundary = !! options.historyPersistUntilBoundary;
        const now = Date.now();
        const shouldMergeChange = this.historyEnabled && this.shouldMergeWithPreviousTemplatePropertyChange(id, now);
        const mergeBaseTemplate = shouldMergeChange && this.lastTemplatePropertyChange.baseTemplate
            ? this.cloneTemplate(this.lastTemplatePropertyChange.baseTemplate)
            : this.cloneTemplate(this.state.template);

        if ( value === this.state.template.style.properties[id].value ) {
            if ( historyBoundary ) {
                this.closeHistoryDebounceWindow();
            }
            return;
        }

        if ( this.historyEnabled ) {
            this.lastTemplatePropertyChange = {
                id,
                at: now,
                baseTemplate: mergeBaseTemplate,
            };
        } else {
            this.resetLastTemplatePropertyChange();
        }

        this.setState((prevState) => {
            if ( ! prevState.template || ! prevState.template.style || ! prevState.template.style.properties || ! prevState.template.style.properties[id] ) {
                return null;
            }

            if ( value === prevState.template.style.properties[id].value ) {
                return null;
            }

            const template = this.cloneTemplate(prevState.template);
            template.style.properties[id].value = value;

            return {
                ...( shouldMergeChange
                    ? (() => {
                        const lastHistoryTemplate = prevState.historyPast.length ? prevState.historyPast[ prevState.historyPast.length - 1 ] : false;
                        const isAtMergeBase = this.isTemplateEqual(template, mergeBaseTemplate);

                        if ( isAtMergeBase ) {
                            return {
                                historyPast: lastHistoryTemplate && this.isTemplateEqual(lastHistoryTemplate, mergeBaseTemplate)
                                    ? prevState.historyPast.slice(0, -1)
                                    : prevState.historyPast,
                                historyFuture: [],
                                historyPastCapped: prevState.historyPastCapped,
                            };
                        }

                        const trimmedHistoryPast = this.trimHistoryPast(prevState.historyPast.concat([ this.cloneTemplate(mergeBaseTemplate) ]), prevState.historyPastCapped);

                        return {
                            historyPast: lastHistoryTemplate && this.isTemplateEqual(lastHistoryTemplate, mergeBaseTemplate)
                                ? prevState.historyPast
                                : trimmedHistoryPast.historyPast,
                            historyFuture: [],
                            historyPastCapped: lastHistoryTemplate && this.isTemplateEqual(lastHistoryTemplate, mergeBaseTemplate)
                                ? prevState.historyPastCapped
                                : trimmedHistoryPast.historyPastCapped,
                        };
                    })()
                    : this.recordHistoryBeforeChange(prevState, { mode: historyMode, persistUntilBoundary: historyPersistUntilBoundary }) ),
                template,
            };
        }, () => {
            if ( historyBoundary ) {
                this.closeHistoryDebounceWindow();
            }
        });
    }

    onChangeFonts( fonts, options = {} ) {
        if ( ! this.state.template ) {
            return;
        }

        const historyPropertyId = options.historyPropertyId || '__fonts__';
        const historyMode = options.historyMode || 'immediate';
        const historyBoundary = !! options.historyBoundary;
        const now = Date.now();
        const shouldMergeChange = this.historyEnabled && this.shouldMergeWithPreviousTemplatePropertyChange(historyPropertyId, now);
        const mergeBaseTemplate = shouldMergeChange && this.lastTemplatePropertyChange.baseTemplate
            ? this.cloneTemplate(this.lastTemplatePropertyChange.baseTemplate)
            : this.cloneTemplate(this.state.template);

        if ( this.isTemplateEqual(fonts, this.state.template.fonts) ) {
            if ( historyBoundary ) {
                this.closeHistoryDebounceWindow();
            }
            return;
        }

        if ( this.historyEnabled ) {
            this.lastTemplatePropertyChange = {
                id: historyPropertyId,
                at: now,
                baseTemplate: mergeBaseTemplate,
            };
        } else {
            this.resetLastTemplatePropertyChange();
        }

        this.setState((prevState) => {
            if ( ! prevState.template || this.isTemplateEqual(fonts, prevState.template.fonts) ) {
                return null;
            }

            const template = this.cloneTemplate(prevState.template);
            template.fonts = [ ...fonts ];

            return {
                ...( shouldMergeChange
                    ? (() => {
                        const lastHistoryTemplate = prevState.historyPast.length ? prevState.historyPast[ prevState.historyPast.length - 1 ] : false;
                        const isAtMergeBase = this.isTemplateEqual(template, mergeBaseTemplate);

                        if ( isAtMergeBase ) {
                            return {
                                historyPast: lastHistoryTemplate && this.isTemplateEqual(lastHistoryTemplate, mergeBaseTemplate)
                                    ? prevState.historyPast.slice(0, -1)
                                    : prevState.historyPast,
                                historyFuture: [],
                                historyPastCapped: prevState.historyPastCapped,
                            };
                        }

                        const trimmedHistoryPast = this.trimHistoryPast(prevState.historyPast.concat([ this.cloneTemplate(mergeBaseTemplate) ]), prevState.historyPastCapped);

                        return {
                            historyPast: lastHistoryTemplate && this.isTemplateEqual(lastHistoryTemplate, mergeBaseTemplate)
                                ? prevState.historyPast
                                : trimmedHistoryPast.historyPast,
                            historyFuture: [],
                            historyPastCapped: lastHistoryTemplate && this.isTemplateEqual(lastHistoryTemplate, mergeBaseTemplate)
                                ? prevState.historyPastCapped
                                : trimmedHistoryPast.historyPastCapped,
                        };
                    })()
                    : this.recordHistoryBeforeChange(prevState, { mode: historyMode }) ),
                template,
            };
        }, () => {
            if ( historyBoundary ) {
                this.closeHistoryDebounceWindow();
            }
        });
    }

    onChangeHTML(html, options = {}) {
        if ( ! this.state.template ) {
            return;
        }

        const resetHistory = !! options.resetHistory;
        if ( resetHistory ) {
            this.closeHistoryDebounceWindow();
            this.resetLastTemplatePropertyChange();

            this.setState((prevState) => {
                if ( ! prevState.template ) {
                    return null;
                }

                const noHistoryToReset = 0 === prevState.historyPast.length && 0 === prevState.historyFuture.length;
                if ( html === prevState.template.html && noHistoryToReset ) {
                    return null;
                }

                const template = this.cloneTemplate(prevState.template);
                template.html = html;

                return {
                    template,
                    historyPast: [],
                    historyFuture: [],
                    historyPastCapped: false,
                    codeHistoryResetNotice: this.historyEnabled,
                };
            });
            return;
        }

        const historyMode = options.historyMode || 'debounced';
        const historyBoundary = !! options.historyBoundary;
        const historyMergeKey = options.historyPropertyId ? `html:${options.historyPropertyId}` : false;
        const now = Date.now();
        const shouldMergeChange = this.historyEnabled && historyMergeKey && this.shouldMergeWithPreviousTemplatePropertyChange(historyMergeKey, now);
        const mergeBaseTemplate = shouldMergeChange && this.lastTemplatePropertyChange.baseTemplate
            ? this.cloneTemplate(this.lastTemplatePropertyChange.baseTemplate)
            : this.cloneTemplate(this.state.template);

        if ( html === this.state.template.html ) {
            if ( historyBoundary ) {
                this.closeHistoryDebounceWindow();
            }
            return;
        }

        if ( this.historyEnabled && historyMergeKey ) {
            this.lastTemplatePropertyChange = {
                id: historyMergeKey,
                at: now,
                baseTemplate: mergeBaseTemplate,
            };
        } else {
            this.resetLastTemplatePropertyChange();
        }

        this.setState((prevState) => {
            if ( ! prevState.template || html === prevState.template.html ) {
                return null;
            }

            const template = this.cloneTemplate(prevState.template);
            template.html = html;

            return {
                ...( shouldMergeChange
                    ? (() => {
                        const lastHistoryTemplate = prevState.historyPast.length ? prevState.historyPast[ prevState.historyPast.length - 1 ] : false;
                        const isAtMergeBase = this.isTemplateEqual(template, mergeBaseTemplate);

                        if ( isAtMergeBase ) {
                            return {
                                historyPast: lastHistoryTemplate && this.isTemplateEqual(lastHistoryTemplate, mergeBaseTemplate)
                                    ? prevState.historyPast.slice(0, -1)
                                    : prevState.historyPast,
                                historyFuture: [],
                                historyPastCapped: prevState.historyPastCapped,
                            };
                        }

                        const trimmedHistoryPast = this.trimHistoryPast(prevState.historyPast.concat([ this.cloneTemplate(mergeBaseTemplate) ]), prevState.historyPastCapped);

                        return {
                            historyPast: lastHistoryTemplate && this.isTemplateEqual(lastHistoryTemplate, mergeBaseTemplate)
                                ? prevState.historyPast
                                : trimmedHistoryPast.historyPast,
                            historyFuture: [],
                            historyPastCapped: lastHistoryTemplate && this.isTemplateEqual(lastHistoryTemplate, mergeBaseTemplate)
                                ? prevState.historyPastCapped
                                : trimmedHistoryPast.historyPastCapped,
                        };
                    })()
                    : this.recordHistoryBeforeChange(prevState, { mode: historyMode }) ),
                template,
            };
        }, () => {
            if ( historyBoundary ) {
                this.closeHistoryDebounceWindow();
            }
        });
    }

    onChangeCSS(css, options = {}) {
        if ( ! this.state.template || ! this.state.template.style ) {
            return;
        }

        const resetHistory = !! options.resetHistory;
        if ( resetHistory ) {
            this.closeHistoryDebounceWindow();
            this.resetLastTemplatePropertyChange();

            this.setState((prevState) => {
                if ( ! prevState.template || ! prevState.template.style ) {
                    return null;
                }

                const noHistoryToReset = 0 === prevState.historyPast.length && 0 === prevState.historyFuture.length;
                if ( css === prevState.template.style.css && noHistoryToReset ) {
                    return null;
                }

                const template = this.cloneTemplate(prevState.template);
                template.style.css = css;

                return {
                    template,
                    historyPast: [],
                    historyFuture: [],
                    historyPastCapped: false,
                    codeHistoryResetNotice: this.historyEnabled,
                };
            });
            return;
        }

        this.resetLastTemplatePropertyChange();

        const historyMode = options.historyMode || 'debounced';
        const historyBoundary = !! options.historyBoundary;

        if ( css === this.state.template.style.css ) {
            if ( historyBoundary ) {
                this.closeHistoryDebounceWindow();
            }
            return;
        }

        this.setState((prevState) => {
            if ( ! prevState.template || ! prevState.template.style || css === prevState.template.style.css ) {
                return null;
            }

            const template = this.cloneTemplate(prevState.template);
            template.style.css = css;

            return {
                ...this.recordHistoryBeforeChange(prevState, { mode: historyMode }),
                template,
            };
        }, () => {
            if ( historyBoundary ) {
                this.closeHistoryDebounceWindow();
            }
        });
    }

    onChangeShortcode(shortcode) {
        if ( shortcode !== this.state.shortcode ) {
            // Update URL when shortcode changes
            if (shortcode && shortcode.id) {
                this.updateUrl(`/shortcode/${shortcode.id}`);
            } else {
                this.updateUrl('/shortcode');
            }
            
            this.setState({
                shortcode
            });
        }
    }

    onDeleteTemplate(slug) {
        if ( ! this.state.savingTemplate ) {
            this.setState({
                savingTemplate: true,
            });
    
            Api.template.delete(slug).then(deletedSlug => {
                if ( deletedSlug ) {
                    let newState = this.state;

                    newState.savingTemplate = false;
                    newState.template = false;
                    delete newState.templates[deletedSlug];
    
                    // If we were editing the deleted template, go back to manage
                    if (newState.editing && newState.template && newState.template.slug === deletedSlug) {
                        newState.editing = false;
                        newState.mode = 'manage';
                        this.updateManageUrl(newState.manageTemplateType, false);
                    } else if (newState.mode === 'manage' && newState.template && newState.template.slug === deletedSlug) {
                        // If we were viewing the deleted template in manage mode, clear selection
                        newState.template = false;
                        this.updateManageUrl(newState.manageTemplateType, false);
                    }
    
                    this.setState(newState);
                } else {
                    this.setState({
                        savingTemplate: false,
                    });
                }
            });
        }
    }

    onSaveTemplate(template) {
        if ( ! this.state.savingTemplate ) {
            this.setState({
                savingTemplate: true,
            });

            // For new templates without a style property, use the raw CSS
            // For existing templates, parse the CSS to handle style properties
            const parsedTemplate = {
                ...template,
                css: template.style && template.style.css !== undefined 
                    ? Helpers.parseCSS(template)
                    : (template.css || ''),
            }
    
            Api.template.save(parsedTemplate).then(savedTemplate => {
                if ( savedTemplate ) {
                    const slug = savedTemplate.slug;
                    const newTemplates = { ...this.state.templates };
                    if ( slug ) {
                        newTemplates[slug] = savedTemplate;
                    }

                    this.setState({
                        savingTemplate: false,
                        templates: newTemplates,
                    }, () => {
                        // Force refresh of active template to make sure things are synced.
                        // If we're editing, update URL to the new slug with current mode
                        if (this.state.editing && slug) {
                            const mode = this.state.mode || 'properties';
                            // Allow editingBlock to be 0 (first block) - only exclude false
                            const editingBlock = (this.state.mode === 'blocks' && this.state.editingBlock !== false && typeof this.state.editingBlock === 'number') ? this.state.editingBlock : false;
                            if (editingBlock !== false) {
                                this.updateUrl(`/template/${slug}/${mode}/${editingBlock}`);
                            } else {
                                this.updateUrl(`/template/${slug}/${mode}`);
                            }
                        }
                        
                        // Make sure template exists in state before trying to change to it
                        if (this.state.templates.hasOwnProperty(slug)) {
                            this.onChangeTemplate(slug);
                            
                            // Automatically open for editing if this is a blank template (empty html and css)
                            const isBlankTemplate = savedTemplate && 
                                (!savedTemplate.html || savedTemplate.html.trim() === '') && 
                                (!savedTemplate.css || savedTemplate.css.trim() === '');
                            if (isBlankTemplate) {
                                // Use setTimeout to ensure template is loaded before opening for editing
                                setTimeout(() => {
                                    this.onChangeEditing(true);
                                }, 100);
                            }
                        } else {
                            console.error('Template not found in state after saving:', slug, this.state.templates);
                        }
                    });
                } else {
                    this.setState({
                        savingTemplate: false,
                    });
                    alert('Failed to save template. The server did not return a valid template. Please try again.');
                }
            }).catch(error => {
                console.error('Error saving template:', error);
                this.setState({
                    savingTemplate: false,
                });
                alert('An error occurred while saving the template. Please check the console for details.');
            });
        }
    }

    render() {
        return (
            <div>
                <Menu
                    mode={ this.state.mode }
                    editing={ this.state.editing }
                    changesMade={ this.changesMade() }
                    onChangeEditing={ this.onChangeEditing.bind(this) }
                    savingTemplate={ this.state.savingTemplate }
                    onSaveTemplate={ this.onSaveTemplate.bind(this) }
                    onChangeMode={ this.onChangeMode.bind(this) }
                    templates={ this.state.templates }
                    template={ this.state.template }
                    onChangeTemplate={ this.onChangeTemplate.bind(this) }
                    onChangeTemplateProperty={ this.onChangeTemplateProperty.bind(this) }
                    fonts={ this.state.template && this.state.template.fonts ? this.state.template.fonts : [] }
                    onChangeFonts={ this.onChangeFonts.bind(this) }
                    sidebarCollapsed={ this.state.sidebarCollapsed }
                    onToggleSidebar={ this.onToggleSidebar.bind(this) }
                    historyEnabled={ this.historyEnabled }
                    undoAtMax={ this.state.historyPastCapped && this.state.historyPast.length >= this.historyMaxSteps }
                    canUndo={ this.state.historyPast.length > 0 }
                    canRedo={ this.state.historyFuture.length > 0 }
                    undoCount={ this.state.historyPast.length }
                    redoCount={ this.state.historyFuture.length }
                    onUndo={ this.onUndo.bind(this) }
                    onRedo={ this.onRedo.bind(this) }
                    codeHistoryResetNotice={ this.state.codeHistoryResetNotice }
                />
                <Main
                    mode={ this.state.mode }
                    onChangeMode={ this.onChangeMode.bind(this) }
                    editing={ this.state.editing }
                    onChangeEditing={ this.onChangeEditing.bind(this) }
                    savingTemplate={ this.state.savingTemplate }
                    onDeleteTemplate={ this.onDeleteTemplate.bind(this) }
                    onSaveTemplate={ this.onSaveTemplate.bind(this) }
                    templates={ this.state.templates }
                    template={ this.state.template }
                    onChangeTemplate={ this.onChangeTemplate.bind(this) }
                    onChangeHTML={ this.onChangeHTML.bind(this) }
                    onChangeCSS={ this.onChangeCSS.bind(this) }
                    shortcode={ this.state.shortcode }
                    onChangeShortcode={ this.onChangeShortcode.bind(this) }
                    editingBlock={ this.state.editingBlock }
                    onChangeEditingBlock={ this.onChangeEditingBlock.bind(this) }
                    manageTemplateType={ this.state.manageTemplateType }
                    onChangeManageTemplateType={ this.onChangeManageTemplateType.bind(this) }
                    defaultTemplateUsages={ this.state.defaultTemplateUsages }
                />
            </div>
        );
    }
}

export default withRouter(App);
