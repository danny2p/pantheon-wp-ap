import React, { useState, useRef, useEffect } from 'react';
import Modal from 'react-modal';
import SVG from 'react-inlinesvg';

// Set app element for accessibility.
const appElement = document.getElementById( 'wprm-template' );
if ( appElement ) {
    Modal.setAppElement( '#wprm-template' );
}

// Icon categories in display order. Icons not in any category go into "Other".
const categories = [
    {
        label: 'Cooking & Kitchen',
        icons: [ 'chef-hat', 'chef-hat-2', 'cutlery', 'cutlery-2', 'utensils', 'knife', 'pan', 'pot', 'oven', 'oven-mit', 'salt', 'stirring', 'cup', 'ingredients', 'ingredients-2', 'pear', 'carrot', 'flame', 'hot-pepper', 'scale', 'diet-apple', 'people', 'people-2' ],
    },
    {
        label: 'Time & Planning',
        icons: [ 'clock', 'clock-2', 'hourglass', 'calendar', 'calendar-plus' ],
    },
    {
        label: 'Rating & Favorites',
        icons: [ 'star-empty', 'star-full', 'star-alt-empty', 'star-alt-full', 'heart', 'heart-empty', 'heart-full', 'bookmark' ],
    },
    {
        label: 'Shopping',
        icons: [ 'cart', 'cart-alt', 'cart-simple-add', 'basket', 'basket-simple', 'dollar', 'money', 'tag' ],
    },
    {
        label: 'Social Media',
        icons: [ 'facebook', 'instagram', 'pinterest', 'pinterest-2', 'bluesky', 'mastodon', 'tumblr', 'twitter', 'x', 'youtube', 'whatsapp', 'messenger', 'google', 'google-color', 'tiktok', 'reddit' ],
    },
    {
        label: 'Media & Images',
        icons: [ 'camera', 'camera-2', 'camera-no', 'video-camera', 'movie', 'media' ],
    },
    {
        label: 'Communication',
        icons: [ 'airplane', 'email', 'mail', 'chat', 'call', 'contact', 'mobile', 'share' ],
    },
    {
        label: 'Print & Documents',
        icons: [ 'printer', 'printer-2', 'printer-3', 'article', 'book', 'clipboard', 'notes', 'text', 'pencil', 'pencil-2' ],
    },
    {
        label: 'Interface',
        icons: [ 'search', 'plus', 'trash', 'eye', 'lightbulb', 'floppy-disk', 'checkbox-checked', 'checkbox-empty', 'checkmark', 'battery', 'info', 'globe', 'download', 'download-2' ],
    },
    {
        label: 'Arrows',
        icons: [ 'arrow-down', 'arrow-small-down', 'arrow-small-left', 'arrow-small-right', 'arrow-small-up', 'arrows', 'arrows-2', 'triangle-down', 'triangle-left', 'triangle-right', 'triangle-up' ],
    },
];

// Build a set of all categorized icon IDs for quick lookup.
const categorizedIds = new Set( categories.flatMap( ( cat ) => cat.icons ) );

/**
 * Get categories with their icons filtered by search term.
 * Icons not assigned to any category are grouped under "Other".
 */
const getFilteredCategories = ( icons, searchLower ) => {
    const result = [];

    for ( const category of categories ) {
        const filtered = category.icons.filter( ( id ) => {
            if ( ! icons[ id ] ) {
                return false;
            }
            if ( ! searchLower ) {
                return true;
            }
            const name = icons[ id ].name || id;
            return name.toLowerCase().includes( searchLower ) || id.toLowerCase().includes( searchLower );
        } );

        if ( filtered.length > 0 ) {
            result.push( { label: category.label, icons: filtered } );
        }
    }

    // Collect uncategorized icons into "Other".
    const otherIcons = Object.keys( icons ).sort().filter( ( id ) => {
        if ( categorizedIds.has( id ) ) {
            return false;
        }
        if ( ! searchLower ) {
            return true;
        }
        const name = icons[ id ].name || id;
        return name.toLowerCase().includes( searchLower ) || id.toLowerCase().includes( searchLower );
    } );

    if ( otherIcons.length > 0 ) {
        result.push( { label: 'Other', icons: otherIcons } );
    }

    return result;
};

const IconPickerModal = ( { isOpen, onClose, onSelect, currentValue } ) => {
    const [ search, setSearch ] = useState( '' );
    const searchRef = useRef( null );

    // Focus search input when modal opens.
    useEffect( () => {
        if ( isOpen && searchRef.current ) {
            // Small delay to ensure modal is rendered.
            setTimeout( () => {
                if ( searchRef.current ) {
                    searchRef.current.focus();
                }
            }, 100 );
        }

        // Reset search when modal opens.
        if ( isOpen ) {
            setSearch( '' );
        }
    }, [ isOpen ] );

    const icons = wprm_admin_template.icons;
    const searchLower = search.toLowerCase();
    const filteredCategories = getFilteredCategories( icons, searchLower );
    const hasResults = filteredCategories.length > 0;

    return (
        <Modal
            isOpen={ isOpen }
            onRequestClose={ onClose }
            overlayClassName="wprm-icon-picker-modal-overlay"
            className="wprm-icon-picker-modal"
        >
            <div className="wprm-icon-picker-modal-header">
                <h3>Select Icon</h3>
                <button
                    className="wprm-icon-picker-modal-close"
                    onClick={ onClose }
                    aria-label="Close"
                >&times;</button>
            </div>
            <div className="wprm-icon-picker-modal-search">
                <input
                    ref={ searchRef }
                    type="text"
                    placeholder="Search icons..."
                    value={ search }
                    onChange={ ( e ) => setSearch( e.target.value ) }
                />
            </div>
            <div className="wprm-icon-picker-modal-body">
                {
                    ! hasResults
                    &&
                    <p className="wprm-icon-picker-modal-no-results">No icons found.</p>
                }
                {
                    filteredCategories.map( ( category ) => (
                        <div key={ category.label } className="wprm-icon-picker-modal-category">
                            <h4 className="wprm-icon-picker-modal-category-label">{ category.label }</h4>
                            <div className="wprm-icon-picker-modal-grid">
                                {
                                    category.icons.map( ( id ) => {
                                        const icon = icons[ id ];
                                        const isSelected = id === currentValue;
                                        return (
                                            <button
                                                key={ id }
                                                className={ `wprm-icon-picker-modal-icon${ isSelected ? ' wprm-icon-picker-modal-icon-selected' : '' }` }
                                                onClick={ () => onSelect( icon.id ) }
                                                title={ icon.name || id }
                                                type="button"
                                            >
                                                <SVG src={ icon.url } />
                                                <span className="wprm-icon-picker-modal-icon-name">{ icon.name || id }</span>
                                            </button>
                                        );
                                    } )
                                }
                            </div>
                        </div>
                    ) )
                }
            </div>
            <div className="wprm-icon-picker-modal-footer">
                <button
                    type="button"
                    className="button button-secondary button-compact"
                    onClick={ () => {
                        onSelect( '' );
                    } }
                >Clear Icon</button>
                <button
                    type="button"
                    className="button button-secondary button-compact"
                    onClick={ () => {
                        const url = prompt( 'Set a custom URL for the icon' );
                        if ( url ) {
                            onSelect( url );
                        }
                    } }
                >Custom URL</button>
                <button
                    type="button"
                    className="button button-secondary button-compact"
                    onClick={ onClose }
                >Cancel</button>
            </div>
        </Modal>
    );
};

export default IconPickerModal;
