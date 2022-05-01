import React, { Component, Fragment } from 'react';
import Parser from 'html-react-parser';

import '../../../../css/public/template_reset.scss';
import '../../../../css/shortcodes/shortcodes.scss';
import '../../../../../../wp-recipe-maker-premium/assets/css/shortcodes/shortcodes.scss';

import Helpers from '../../general/Helpers';
import Loader from 'Shared/Loader';
import Block from './Block';
import AddBlocks from '../../menu/AddBlocks';
import RemoveBlocks from '../../menu/RemoveBlocks';
import BlockProperties from '../../menu/BlockProperties';
import PreviewRecipe from './PreviewRecipe';

// Sort shortcodes for "Add Blocks" section.
const generalShortcodeKeys = [
    'wprm-spacer',
    'wprm-text',
    'wprm-link',
    'wprm-image',
    'wprm-call-to-action',
    'wprm-icon',
    'wprm-prevent-sleep',
];
let recipeShortcodeKeys = [];
const shortcodeKeysAlphebetically = Object.keys(wprm_admin_template.shortcodes).sort();

for ( let shortcode of shortcodeKeysAlphebetically ) {
    if ( ! generalShortcodeKeys.includes( shortcode ) ) {
        recipeShortcodeKeys.push( shortcode );
    }
}

export default class PreviewTemplate extends Component {
    constructor(props) {
        super(props);

        let recipe = wprm_admin_template.preview_recipe;
        if ( 'demo' === recipe || 0 === recipe.id ) {
            recipe = {
                id: 'demo',
                text: 'Use WPRM Demo Recipe',
            };
        }

        this.state = {
            recipe,
            width: 600,
            html: '',
            htmlMap: '',
            parsedHtml: '',
            shortcodes: [],
            editingBlock: false,
            addingBlock: false,
            hoveringBlock: false,
            hasError: false,
        }
    }

    componentDidCatch() {
        this.setState({
            hasError: true,
        });
    }

    componentDidMount() {
        this.checkHtmlChange();
    }

    componentDidUpdate(prevProps) {
        // If changing to edit blocks mode, reset the editing blocks.
        if ( 'blocks' === this.props.mode && this.props.mode !== prevProps.mode ) {
            this.onChangeEditingBlock(false);
        } else {
            this.checkHtmlChange(); // onChangeEditingBlock forces HTML update, so no need to check.
        }
    }

    checkHtmlChange() {
        if ( this.props.template.html !== this.state.html ) {
            this.changeHtml();
        }
    }

    changeHtml() {
        const parsed = this.parseHtml(this.props.template.html);

        this.setState({
            html: this.props.template.html,
            htmlMap: parsed.htmlMap,
            parsedHtml: parsed.html,
            shortcodes: parsed.shortcodes,
            hasError: false,
        });
    }

    parseHtml(html) {
        let htmlToParse = html;

        // Remove closing wprm-condition shortcode from preview.
        htmlToParse = htmlToParse.replace( /\[\/wprm\-condition\]/g, '' );

        // Find shortcodes in HTML.
        let shortcodes = [];
        const regex = /\[([^\s\]]*)\s*([^\]]*?)\]/gmi;

        let match;
        while ((match = regex.exec(html)) !== null) {
            // Check for attributes in shortcode.
            let shortcode_atts = {};
            let attributes = match[2].match(/(\w+=\"[^\"]*?\"|\w+=\'[^\']*?\'|\w+=\w*)/gmi);

            if (attributes) {
                for (let i = 0; i < attributes.length; i++) {
                    let attribute = attributes[i];
                    let property = attribute.substring(0, attribute.indexOf('='));
                    let value = attribute.substring(attribute.indexOf('=') + 1);

                    // Trim value if necessary.
                    if ('"' === value[0] || "'" === value[0] ) {
                        value = value.substr(1, value.length-2);
                    }

                    shortcode_atts[property] = value;
                }
            }

            // Get shortcode name.
            let id = match[1];
            const name = Helpers.getShortcodeName(id);

            // Generate UID.
            let uid = shortcodes.length;

            // Replace with HTML tag to parse in next step, save attributes for access.
            htmlToParse = htmlToParse.replace(match[0], '<wprm-replace-shortcode-with-block uid="' + uid + '"></wprm-replace-shortcode-with-block>');
            shortcodes.push({
                uid,
                id,
                name,
                attributes: shortcode_atts,
            });
        }

        // Get HTML with shortcodes replaced by blocks.
        let parsedHtml = <Loader/>;
        try {
            parsedHtml = Parser(htmlToParse, {
                replace: function(domNode) {
                    if (domNode.name == 'wprm-replace-shortcode-with-block') {
                        const recipeId = this.state.recipe ? this.state.recipe.id : false;
    
                        return <Block
                                    recipeId={ recipeId }
                                    shortcode={ shortcodes[ domNode.attribs.uid ] }
                                    shortcodes={ shortcodes }
                                    onBlockPropertyChange={ this.onBlockPropertyChange.bind(this) }
                                    onBlockPropertiesChange={ this.onBlockPropertiesChange.bind(this) }
                                    editingBlock={this.state.editingBlock}
                                    onChangeEditingBlock={this.onChangeEditingBlock.bind(this)}
                                    hoveringBlock={this.state.hoveringBlock}
                                    onChangeHoveringBlock={this.onChangeHoveringBlock.bind(this)}
                                />;
                    }
                }.bind(this)
            });
        } catch ( error ) {}

        return {
            htmlMap: htmlToParse,
            html: parsedHtml,
            shortcodes,
        }
    }

    unparseHtml() {
        let html = this.state.htmlMap;

        for ( let shortcode of this.state.shortcodes ) {
            let fullShortcode = Helpers.getFullShortcode(shortcode);
            html = html.replace('<wprm-replace-shortcode-with-block uid="' + shortcode.uid + '"></wprm-replace-shortcode-with-block>', fullShortcode);
        }

        return html;
    }

    onBlockPropertyChange(uid, property, value) {
        let properties = {};
        properties[property] = value;
        this.onBlockPropertiesChange(uid, properties);
    }

    onBlockPropertiesChange(uid, properties) {
        let newState = this.state;
        newState.shortcodes[uid].attributes = {
            ...newState.shortcodes[uid].attributes,
            ...properties,
        }

        this.setState(newState,
            () => {
                let newHtml = this.unparseHtml();
                this.props.onChangeHTML(newHtml);
            });
    }

    onChangeEditingBlock(uid) {
        if (uid !== this.state.editingBlock) {
            this.setState({
                editingBlock: uid,
                hoveringBlock: false,
            }, this.changeHtml);
            // Force HTML update to trickle down editingBlock prop.
        }
    }

    onChangeHoveringBlock(uid) {
        if (uid !== this.state.hoveringBlock) {
            this.setState({
                hoveringBlock: uid,
            }, this.changeHtml);
            // Force HTML update to trickle down hoveringBlock prop.
        }
    }

    onChangeAddingBlock(id) {
        if (id !== this.state.addingBlock) {
            this.setState({
                addingBlock: id,
            });
        }
    }

    onAddBlockAfter(uid) {
        let htmlMap = this.state.htmlMap;
        const shortcode = '[' + this.state.addingBlock + ']';
        const afterShortcode = '<wprm-replace-shortcode-with-block uid="' + uid + '"></wprm-replace-shortcode-with-block>';
        htmlMap = htmlMap.replace(afterShortcode, afterShortcode + '\n' + shortcode);

        if ( htmlMap !== this.state.htmlMap) {
            this.setState({
                addingBlock: false,
                hoveringBlock: false,
                htmlMap,
            },
                () => {
                    let newHtml = this.unparseHtml();
                    this.props.onChangeHTML(newHtml);
                    this.props.onChangeMode( 'blocks' );

                    this.setState({
                        addingBlock: false,
                        hoveringBlock: false,
                    }, () => {
                        this.onChangeEditingBlock(uid + 1);
                    });
                });
        }
    }

    onRemoveBlock(uid) {
        let htmlMap = this.state.htmlMap;
        htmlMap = htmlMap.replace('<wprm-replace-shortcode-with-block uid="' + uid + '"></wprm-replace-shortcode-with-block>', '');

        if ( htmlMap !== this.state.htmlMap) {
            this.setState({
                htmlMap,
            },
                () => {
                    let newHtml = this.unparseHtml();
                    this.props.onChangeHTML(newHtml);
                });
        }
    }

    render() {
        const parsedHtml = this.state.hasError ? <Loader /> : this.state.parsedHtml;

        if ( 'onboarding' === this.props.mode ) {
            
            return (
                <Fragment>
                    <style>{ Helpers.parseCSS( this.props.template ) }</style>
                    {
                        'recipe' === this.props.template.type
                        &&
                        <div className={`wprm-recipe wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
                    }
                    {
                        'snippet' === this.props.template.type
                        &&
                        <div className={`wprm-recipe wprm-recipe-snippet wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
                    }
                </Fragment>
            );
        }

        return (
            <Fragment>
                <div className="wprm-main-container">
                    <h2 className="wprm-main-container-name">Preview at <input type="number" min="1" value={ this.state.width } onChange={ (e) => { this.setState({ width: e.target.value } ); } } />px</h2>
                    <div className="wprm-main-container-preview">
                        <PreviewRecipe
                            recipe={ this.state.recipe }
                            onRecipeChange={ (recipe) => {
                                if ( recipe !== this.state.recipe ) {
                                    this.setState( {
                                        recipe,
                                        html: '', // Force HTML to update.
                                    });
                                }
                            }}
                        />
                        {
                            this.state.recipe && this.state.recipe.id
                            ?
                            <div
                                className="wprm-main-container-preview-content"
                                style={{
                                    width: `${this.state.width}px`,
                                }}
                            >
                                <style>{ Helpers.parseCSS( this.props.template ) }</style>
                                {
                                    'recipe' === this.props.template.type
                                    &&
                                    <Fragment>
                                        <p>This is an example paragraph that could be appearing before the recipe box, just to give some context to this preview. After this paragraph the recipe box will appear.</p>
                                        <div className={`wprm-recipe wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
                                        <p>This is a paragraph appearing after the recipe box.</p>
                                    </Fragment>
                                }
                                {
                                    'snippet' === this.props.template.type
                                    &&
                                    <Fragment>
                                        <p>&nbsp;</p>
                                        <div className={`wprm-recipe wprm-recipe-snippet wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
                                        <p>This would be the start of your post content, as the recipe snippets should automatically appear above. We'll be adding some example content below to give you a realistic preview.</p>
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In eleifend vitae nisl et pharetra. Sed euismod nisi convallis arcu lobortis commodo. Mauris nec arcu blandit, ultrices nisi sit amet, scelerisque tortor. Mauris vitae odio sed nisl posuere feugiat eu sit amet nunc. Vivamus varius rutrum tortor, ut viverra mi. Pellentesque sed justo eget lectus eleifend consectetur. Curabitur hendrerit purus velit, ut auctor orci fringilla sed. Phasellus commodo luctus nulla, et rutrum risus lobortis in. Aenean ullamcorper, magna congue viverra consequat, libero elit blandit magna, in ultricies quam risus et magna. Aenean viverra lorem leo, eget laoreet quam suscipit viverra. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Quisque sodales dolor mauris. Ut sed tempus erat. Nulla metus diam, luctus ac erat bibendum, placerat maximus nisi. Nullam hendrerit eleifend lobortis.</p>
                                        <p>Proin tempus hendrerit orci, tincidunt bibendum justo tincidunt vel. Morbi porttitor finibus magna non imperdiet. Fusce sollicitudin ex auctor interdum ultricies. Proin efficitur eleifend lacus, dapibus eleifend nibh tempus at. Pellentesque feugiat imperdiet turpis, sed consequat diam tincidunt a. Mauris mollis justo nec tellus aliquam, efficitur scelerisque nunc semper. Morbi rhoncus ultricies congue. Sed semper aliquet interdum.</p>
                                        <p>Nam ultricies, tellus nec vulputate varius, ligula ipsum viverra libero, lacinia ultrices sapien erat id mi. Duis vel dignissim lectus. Aliquam vehicula finibus tortor, cursus fringilla leo sodales ut. Vestibulum nec erat pretium, finibus odio et, porta lorem. Nunc in mi lobortis, aliquet sem sollicitudin, accumsan mi. Nam pretium nibh nunc, vel varius ex sagittis at. Vestibulum ac turpis vitae dui congue iaculis et non massa. Duis sed gravida nunc. Vivamus blandit dapibus orci, eu maximus velit faucibus eu.</p>
                                        <div id={ `wprm-recipe-container-${this.state.recipe.id}` } className="wprm-preview-snippet-recipe-box">
                                            <p>This is an example recipe box.</p>
                                            <p id={ `wprm-recipe-video-container-${this.state.recipe.id}` }>It includes an example video.</p>
                                        </div>
                                        <p>Some more random content could be appearing after the recipe box. Morbi dignissim euismod vestibulum. Interdum et malesuada fames ac ante ipsum primis in faucibus. Vestibulum eu faucibus lectus. Donec sit amet mattis erat, at vulputate elit. Morbi ullamcorper, justo nec porttitor porta, dui lectus euismod est, convallis tempor lorem elit nec leo. Praesent hendrerit auctor risus sed mollis. Integer suscipit arcu at risus efficitur, et interdum arcu fringilla. Aliquam mollis accumsan blandit. Nam vestibulum urna id velit scelerisque, eu commodo urna imperdiet. Mauris sed risus libero. Integer lacinia nec lectus in posuere. Sed feugiat dolor eros, ac scelerisque tellus hendrerit sit amet. Sed nisl lacus, condimentum id orci eu, malesuada mattis sem. Quisque ipsum velit, viverra et magna a, laoreet porta lorem. Praesent porttitor lorem quis quam lobortis, lacinia tincidunt odio sodales.</p>
                                    </Fragment>
                                }
                                {
                                    'roundup' === this.props.template.type
                                    &&
                                    <Fragment>
                                        <h2>Our first recipe</h2>
                                        <p>This is the first example recipe in this recipe roundup. We can have as much information and images as we want here and then end with the roundup template for this particular recipe.</p>
                                        <div className={`wprm-recipe wprm-recipe-roundup-item wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
                                        <h2>Our second recipe</h2>
                                        <p>A roundup would have multiple recipes, so here is another one with some more demo text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In eleifend vitae nisl et pharetra. Sed euismod nisi convallis arcu lobortis commodo.</p>
                                        <p>...</p>
                                    </Fragment>
                                }
                            </div>
                            :
                            <p style={{color: 'darkred', textAlign: 'center'}}>You have to select a recipe to preview the template. Use the dropdown above or set a default recipe to use for the preview on the settings page.</p>
                        }
                    </div>
                </div>
                {
                    false === this.state.editingBlock || this.state.shortcodes.length <= this.state.editingBlock
                    ?
                    <BlockProperties>
                        {
                            this.state.shortcodes.map((shortcode, i) => {
                                return (
                                    <div
                                        key={i}
                                        className={ shortcode.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-hover' : 'wprm-template-menu-block' }
                                        onClick={ () => this.onChangeEditingBlock(shortcode.uid) }
                                        onMouseEnter={ () => this.onChangeHoveringBlock(shortcode.uid) }
                                        onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                    >{ shortcode.name }</div>
                                );
                            })
                        }
                        {
                             ! this.state.shortcodes.length && <p>There are no adjustable blocks.</p>
                        }
                    </BlockProperties>
                    :
                    null
                }
                <AddBlocks>
                {
                    ! this.state.addingBlock
                    ?
                    <Fragment>
                        <p>Select block to add:</p>
                        <div className="wprm-template-menu-add-block-group">General Blocks</div>
                        {
                            generalShortcodeKeys.map((id, i) => {
                                return (
                                    <div
                                        key={i}
                                        className="wprm-template-menu-block"
                                        onClick={ () => this.onChangeAddingBlock(id) }
                                    >{ Helpers.getShortcodeName(id) }</div>
                                );
                            })
                        }
                        <div className="wprm-template-menu-add-block-group">Recipe Blocks</div>
                        {
                            recipeShortcodeKeys.map((id, i) => {
                                return (
                                    <div
                                        key={i}
                                        className="wprm-template-menu-block"
                                        onClick={ () => this.onChangeAddingBlock(id) }
                                    >{ Helpers.getShortcodeName(id) }</div>
                                );
                            })
                        }
                    </Fragment>
                    :
                    <Fragment>
                        <a href="#" onClick={(e) => {
                            e.preventDefault();
                            this.onChangeAddingBlock(false);
                        }}>Cancel</a>
                        <p>Add "{ Helpers.getShortcodeName(this.state.addingBlock) }" after:</p>
                        {
                            this.state.shortcodes.map((shortcode, i) => {
                                return (
                                    <div
                                        key={i}
                                        className={ shortcode.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-hover' : 'wprm-template-menu-block' }
                                        onClick={ () => this.onAddBlockAfter(shortcode.uid) }
                                        onMouseEnter={ () => this.onChangeHoveringBlock(shortcode.uid) }
                                        onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                    >{ shortcode.name }</div>
                                );
                            })
                        }
                        {
                            ! this.state.shortcodes.length && <p>There are no blocks in the Template.</p>
                        }
                    </Fragment>
                }
                </AddBlocks>
                <RemoveBlocks>
                {
                    this.state.shortcodes.map((shortcode, i) => {
                        return (
                            <div
                                key={i}
                                className={ shortcode.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-hover' : 'wprm-template-menu-block' }
                                onClick={ () => {
                                    if (confirm( 'Are you sure you want to delete the "' + shortcode.name + '" block?' )) {
                                        this.onRemoveBlock(shortcode.uid);
                                    }
                                }}
                                onMouseEnter={ () => this.onChangeHoveringBlock(shortcode.uid) }
                                onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                            >{ shortcode.name }</div>
                        );
                    })
                }
                {
                        ! this.state.shortcodes.length && <p>There are no blocks to remove.</p>
                }
                </RemoveBlocks>
            </Fragment>
        );
    }
}
