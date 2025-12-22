import React, { useRef, useEffect } from 'react';
import PropTypes from 'prop-types'
import { Link } from 'react-scroll'

import Helpers from '../general/Helpers';
import Icon from '../general/Icon';

const MenuContainer = (props) => {
    const inputRef = useRef(null);
    
    // Sync uncontrolled input with controlled value when searchQuery changes externally
    useEffect(() => {
        if (inputRef.current && inputRef.current.value !== props.searchQuery) {
            inputRef.current.value = props.searchQuery;
        }
    }, [props.searchQuery]);
    
    let menuStructure = [];

    for ( let group of props.structure ) {
        if ( ! Helpers.dependencyMet(group, props.settings ) ) {
            continue;
        }

        if (group.hasOwnProperty('header')) {
            menuStructure.push({
                header: group.header,
            });
        } else {
            menuStructure.push({
                id: group.id,
                name: group.name,
                icon: group.hasOwnProperty( 'icon' ) ? group.icon : false,
            });
        }
    }

    const isSearchActive = props.searchQuery && props.searchQuery.length > 0;

    const clearSearch = () => {
        if (inputRef.current) {
            inputRef.current.value = '';
        }
        props.onSearchChange('');
    };

    return (
        <div id="wprm-settings-sidebar">
            <div id="wprm-settings-buttons">
                <div className="wprm-settings-changes-wrapper">
                    <button
                        className="button button-primary"
                        disabled={props.savingChanges || !props.settingsChanged}
                        onClick={props.onSaveChanges}
                    >{ props.savingChanges ? '...' : 'Save Changes' }</button>
                    <button
                        className="button"
                        disabled={props.savingChanges || !props.settingsChanged}
                        onClick={props.onCancelChanges}
                    >Cancel Changes</button>
                </div>
                <div className="wprm-settings-search-wrapper">
                    <input
                        ref={inputRef}
                        type="text"
                        id="wprm-settings-search"
                        placeholder="Search..."
                        defaultValue={props.searchQuery}
                        onChange={(e) => props.onSearchChange(e.target.value)}
                    />
                    {isSearchActive && (
                        <button
                            type="button"
                            className="wprm-settings-search-clear"
                            onClick={clearSearch}
                            title="Clear search"
                        >
                            ×
                        </button>
                    )}
                </div>
            </div>
            {isSearchActive ? (
                <div id="wprm-settings-menu-search-message">
                    <a href="#" onClick={(e) => { e.preventDefault(); clearSearch(); }}>
                        Clear search
                    </a> to use menu
                </div>
            ) : (
                <div id="wprm-settings-menu">
                    {
                        menuStructure.map((group, i) => {
                            if (group.hasOwnProperty('header')) {
                                return <div className="wprm-settings-menu-header" key={i}>{group.header}</div>
                            } else {
                                return <Link
                                        to={`wprm-settings-group-${group.id}`}
                                        className="wprm-settings-menu-group"
                                        activeClass="active"
                                        spy={true}
                                        offset={-42}
                                        smooth={true}
                                        duration={400}
                                        key={i}
                                    >
                                    { group.icon && <Icon type={group.icon} /> } {group.name}
                                </Link>
                            }
                        })
                    }
                </div>
            )}
        </div>
    );
}

MenuContainer.propTypes = {
    structure: PropTypes.array.isRequired,
    settingsChanged: PropTypes.bool.isRequired,
    savingChanges: PropTypes.bool.isRequired,
    onSaveChanges: PropTypes.func.isRequired,
    onCancelChanges: PropTypes.func.isRequired,
    searchQuery: PropTypes.string.isRequired,
    onSearchChange: PropTypes.func.isRequired,
}

export default MenuContainer;