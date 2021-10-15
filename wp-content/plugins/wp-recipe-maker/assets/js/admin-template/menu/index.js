import React, { Fragment } from 'react';

import '../../../css/admin/template/menu.scss';

import Helpers from '../general/Helpers';
import Icon from '../general/Icon';
import Loader from 'Shared/Loader';
import TemplateProperties from './TemplateProperties';

const Menu = (props) => {
    return (
        <div id="wprm-template-sidebar">
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
                                className="button"
                                onClick={() => {
                                    if ( ! props.changesMade || confirm( 'Are you sure you want to cancel your changes?' ) ) {
                                        props.onChangeEditing(false);
                                    }
                                }}
                            >{ props.changesMade ? "Cancel Changes" : "Stop Editing" }</button>
                        </Fragment>
                    }
                </div>
            }
            <div id="wprm-template-menu">
                {
                    ! props.editing
                    ?
                    <Fragment>
                        <a
                            className={ 'manage' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                            onClick={ (e) => { props.onChangeMode( 'manage' ) } }
                        ><Icon type='manage' /> Manage Templates</a>
                    </Fragment>
                    :
                    <Fragment>
                        <a
                            className={ 'properties' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                            onClick={ (e) => { props.onChangeMode( 'properties' ) } }
                        ><Icon type='properties' /> Template Properties</a>
                        <a
                            className={ 'blocks' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                            onClick={ (e) => { props.onChangeMode( 'blocks' ) } }
                        ><Icon type='blocks' /> Edit Blocks</a>
                        <a
                            className={ 'add' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                            onClick={ (e) => { props.onChangeMode( 'add' ) } }
                        ><Icon type='add' /> Add Blocks</a>
                        <a
                            className={ 'remove' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                            onClick={ (e) => { props.onChangeMode( 'remove' ) } }
                        ><Icon type='remove' /> Remove Blocks</a>
                        <a
                            className={ 'html' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                            onClick={ (e) => { props.onChangeMode( 'html' ) } }
                        ><Icon type='html' /> Edit HTML</a>
                        <a
                            className={ 'css' === props.mode ? "wprm-template-menu-group active" : "wprm-template-menu-group" }
                            onClick={ (e) => { props.onChangeMode( 'css' ) } }
                        ><Icon type='css' /> Edit CSS</a>
                    </Fragment>
                }
            </div>
            {
                'properties' === props.mode && props.template
                ?
                <TemplateProperties
                    template={props.template}
                    onChangeTemplateProperty={props.onChangeTemplateProperty}
                />
                :
                null
            }
            <div
                id="wprm-add-blocks"
                style={{ display: 'add' !== props.mode ? 'none' : 'block' }}
                className="wprm-template-properties"
            ></div>
            <div
                id="wprm-remove-blocks"
                style={{ display: 'remove' !== props.mode ? 'none' : 'block' }}
                className="wprm-template-properties"
            ></div>
            <div
                id="wprm-block-properties"
                style={{ display: 'blocks' !== props.mode ? 'none' : 'block' }}
                className="wprm-template-properties"
            ></div>
        </div>
    );
}

export default Menu;