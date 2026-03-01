import React from 'react';

import '../../../css/admin/template/main.scss';

import ManageTemplates from './manage-templates';
import EditTemplate from './edit-template';
import PreviewTemplate from './preview-template';
import ShortcodeGenerator from './shortcode-generator';
import FeatureExplorer from './feature-explorer';

const Main = (props) => {
    return (
        <div id="wprm-template-main" className={`wprm-template-main-${props.mode}`}>
            {
                'manage' === props.mode
                &&
                <ManageTemplates
                    templates={ props.templates }
                    template={ props.template }
                    onChangeEditing={ props.onChangeEditing }
                    onDeleteTemplate={ props.onDeleteTemplate }
                    onChangeTemplate={ props.onChangeTemplate }
                    savingTemplate={ props.savingTemplate }
                    onSaveTemplate={ props.onSaveTemplate }
                    type={ props.manageTemplateType }
                    onChangeType={ props.onChangeManageTemplateType }
                    defaultTemplateUsages={ props.defaultTemplateUsages }
                />
            }
            {
                'manage' !== props.mode && 'shortcode' !== props.mode && 'feature-explorer' !== props.mode && props.template
                &&
                <EditTemplate
                    mode={ props.mode }
                    template={ props.template }
                    onChangeHTML={ props.onChangeHTML }
                    onChangeCSS={ props.onChangeCSS }
                />
            }
            {
                'shortcode' === props.mode
                &&
                <ShortcodeGenerator
                    shortcode={ props.shortcode }
                    onChangeShortcode={ props.onChangeShortcode }
                />
            }
            {
                'feature-explorer' === props.mode
                &&
                <FeatureExplorer
                    templates={ props.templates }
                />
            }
            {
                'feature-explorer' !== props.mode && props.template
                &&
                <PreviewTemplate
                    mode={ props.mode }
                    template={ props.template }
                    onChangeHTML={ props.onChangeHTML }
                    onChangeCSS={ props.onChangeCSS }
                    onChangeMode={ props.onChangeMode }
                    editingBlock={ props.editingBlock }
                    onChangeEditingBlock={ props.onChangeEditingBlock }
                />
            }
        </div>
    );
}

export default Main;
