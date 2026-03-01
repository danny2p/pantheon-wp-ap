import React, { Fragment } from 'react';
import { __wprm } from 'Shared/Translations';

import '../../../css/admin/template/menu.scss';

import Helpers from '../general/Helpers';
import Icon from '../general/Icon';
import Loader from 'Shared/Loader';
import Tooltip from 'Shared/Tooltip';
import TemplateProperties from './TemplateProperties';

const Menu = (props) => {
    const isCodeEditMode = 'html' === props.mode || 'css' === props.mode;
    const historyEnabled = !! props.historyEnabled;

    return (
        <div id="wprm-template-sidebar" className={props.sidebarCollapsed ? 'collapsed' : ''}>
            <Tooltip content={props.sidebarCollapsed ? 'Expand Sidebar' : ''} placement="right">
                <div 
                    id="wprm-template-sidebar-toggle"
                    onClick={() => props.onToggleSidebar()}
                >
                    <span className="wprm-template-sidebar-toggle-icon"></span>
                    <span className="wprm-template-sidebar-toggle-text">
                        {props.sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'}
                    </span>
                </div>
            </Tooltip>
            {
                props.editing
                &&
                <div id="wprm-template-buttons">
                    <p>Editing template: { props.template.name }</p>
                    {
                        props.savingTemplate
                        ?
                        <Loader/>
                        :
                        <Fragment>
                            <button
                                type="button"
                                className="button button-primary"
                                disabled={ ! props.changesMade }
                                onClick={() => {
                                    if ( confirm( 'Are you sure you want to save your changes?' ) ) {
                                        props.onSaveTemplate({
                                            ...props.template,
                                        });
                                    }
                                }}
                            >{ props.savingTemplate ? '...' : 'Save Changes' }</button>
                            <button
                                type="button"
                                className="button"
                                onClick={() => {
                                    if ( ! props.changesMade || confirm( 'Are you sure you want to cancel your changes?' ) ) {
                                        props.onChangeEditing(false);
                                    }
                                }}
                            >{ props.changesMade ? "Cancel Changes" : "Stop Editing" }</button>
                            {
                                isCodeEditMode
                                ?
                                historyEnabled
                                ?
                                <div className="wprm-template-history-warning">
                                    {
                                        props.codeHistoryResetNotice
                                        ? __wprm( 'Undo/redo history has been reset' )
                                        : __wprm( 'Direct HTML or CSS edits will reset the undo/redo history.' )
                                    }
                                </div>
                                :
                                null
                                :
                                historyEnabled
                                ?
                                <div className="wprm-template-history-controls">
                                    <button
                                        type="button"
                                        className="button"
                                        disabled={ ! props.canUndo }
                                        aria-label="Undo last template change"
                                        onClick={() => props.onUndo()}
                                    >{ props.undoAtMax ? 'Undo (max)' : `Undo (${ props.undoCount })` }</button>
                                    <button
                                        type="button"
                                        className="button"
                                        disabled={ ! props.canRedo }
                                        aria-label="Redo template change"
                                        onClick={() => props.onRedo()}
                                    >Redo ({ props.redoCount })</button>
                                </div>
                                :
                                null
                            }
                        </Fragment>
                    }
                </div>
            }
            <div id="wprm-template-menu">
                {
                    ! props.editing
                    ?
                    <Fragment>
                        <Tooltip content={props.sidebarCollapsed ? 'Manage Templates' : ''} placement="right">
                            <a
                                className={ 'manage' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                                onClick={ (e) => { props.onChangeMode( 'manage' ) } }
                            ><Icon type='manage' /> Manage Templates</a>
                        </Tooltip>
                        <Tooltip content={props.sidebarCollapsed ? 'Feature Explorer' : ''} placement="right">
                            <a
                                className={ 'feature-explorer' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                                onClick={ (e) => { props.onChangeMode( 'feature-explorer' ) } }
                            ><Icon type='patterns' /> Feature Explorer</a>
                        </Tooltip>
                        <Tooltip content={props.sidebarCollapsed ? 'Shortcode Generator' : ''} placement="right">
                            <a
                                className={ 'shortcode' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                                onClick={ (e) => { props.onChangeMode( 'shortcode' ) } }
                            ><Icon type='html' /> Shortcode Generator</a>
                        </Tooltip>
                    </Fragment>
                    :
                    <Fragment>
                        <Tooltip content={props.sidebarCollapsed ? 'Template Properties' : ''} placement="right">
                            <a
                                className={ 'properties' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                                onClick={ (e) => { props.onChangeMode( 'properties' ) } }
                            ><Icon type='properties' /> Template Properties</a>
                        </Tooltip>
                        <Tooltip content={props.sidebarCollapsed ? 'Edit Blocks' : ''} placement="right">
                            <a
                                className={ 'blocks' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                                onClick={ (e) => { props.onChangeMode( 'blocks' ) } }
                            ><Icon type='blocks' /> Edit Blocks</a>
                        </Tooltip>
                        <Tooltip content={props.sidebarCollapsed ? 'Add Blocks' : ''} placement="right">
                            <a
                                className={ 'add' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                                onClick={ (e) => { props.onChangeMode( 'add' ) } }
                            ><Icon type='add' /> Add Blocks</a>
                        </Tooltip>
                        <Tooltip content={props.sidebarCollapsed ? 'Edit HTML' : ''} placement="right">
                            <a
                                className={ 'html' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                                onClick={ (e) => { props.onChangeMode( 'html' ) } }
                            ><Icon type='html' /> Edit HTML</a>
                        </Tooltip>
                        <Tooltip content={props.sidebarCollapsed ? 'Edit CSS' : ''} placement="right">
                            <a
                                className={ 'css' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                                onClick={ (e) => { props.onChangeMode( 'css' ) } }
                            ><Icon type='css' /> Edit CSS</a>
                        </Tooltip>
                    </Fragment>
                }
            </div>
            {
                'properties' === props.mode && props.template
                ?
                <TemplateProperties
                    template={props.template}
                    onChangeTemplateProperty={props.onChangeTemplateProperty}
                    fonts={props.fonts}
                    onChangeFonts={props.onChangeFonts}
                />
                :
                null
            }
            <div
                id="wprm-add-patterns"
                style={{ display: 'patterns' !== props.mode ? 'none' : 'block' }}
                className="wprm-template-properties"
            ></div>
            <div
                id="wprm-add-blocks"
                style={{ display: 'add' !== props.mode ? 'none' : 'block' }}
                className="wprm-template-properties"
            ></div>
            <div
                id="wprm-block-properties"
                style={{ display: 'blocks' !== props.mode && 'shortcode' !== props.mode && 'feature-explorer' !== props.mode ? 'none' : 'block' }}
                className="wprm-template-properties"
            ></div>
        </div>
    );
}

export default Menu;
