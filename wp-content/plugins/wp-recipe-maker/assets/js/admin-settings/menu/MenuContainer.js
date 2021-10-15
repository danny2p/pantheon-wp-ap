import React from 'react';
import PropTypes from 'prop-types'
import { Link } from 'react-scroll'

import Helpers from '../general/Helpers';
import Icon from '../general/Icon';

const MenuContainer = (props) => {
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

    return (
        <div id="wprm-settings-sidebar">
            <div id="wprm-settings-buttons">
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
        </div>
    );
}

MenuContainer.propTypes = {
    structure: PropTypes.array.isRequired,
    settingsChanged: PropTypes.bool.isRequired,
    savingChanges: PropTypes.bool.isRequired,
    onSaveChanges: PropTypes.func.isRequired,
    onCancelChanges: PropTypes.func.isRequired,
}

export default MenuContainer;