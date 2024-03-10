import React, { Component, Fragment } from 'react';
import Parser, { domToReact } from 'html-react-parser';

// Functionality for the preview.
import '../../../public/expandable';

// Styles for the preview.
import '../../../../css/public/template_reset.scss';
import '../../../../css/shortcodes/shortcodes.scss';
import '../../../../../../wp-recipe-maker-premium/assets/css/shortcodes/shortcodes.scss';

import Helpers from '../../general/Helpers';
import Loader from 'Shared/Loader';
import Block from './Block';
import Element from './Element';
import AddPatterns from '../../menu/AddPatterns';
import AddBlocks from '../../menu/AddBlocks';
import RemoveBlocks from '../../menu/RemoveBlocks';
import MoveBlocks from '../../menu/MoveBlocks';
import BlockProperties from '../../menu/BlockProperties';
import PreviewRecipe from './PreviewRecipe';
import Shortcodes from '../../general/shortcodes';
import Elements from '../../general/elements';
import Patterns from '../../general/patterns';

const { shortcodeGroups, shortcodeKeysAlphebetically } = Shortcodes;

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
            addingPattern: false,
            addingBlock: false,
            movingBlock: false,
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
        if ( 'shortcode-generator' === this.props.mode ) {
            // Make sure editing block stays on the shortcode.
            if ( this.state.editingBlock !== 0 ) {
                this.onChangeEditingBlock(0);
            } else {
                this.checkHtmlChange();
            }
        } else {
            // If changing to edit blocks mode, reset the editing blocks.
            if ( 'blocks' === this.props.mode && this.props.mode !== prevProps.mode ) {
                this.onChangeEditingBlock(false);
            } else {
                this.checkHtmlChange(); // onChangeEditingBlock forces HTML update, so no need to check.
            }
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
        // Find shortcodes in HTML.
        let shortcodes = [];
        const regex = /\[([^\s\]]*)\s*([^\]]*?)\]|<div class="(wprm-layout-[^\s"]*)\s?(.*?)">/gmi;

        // Loop over all the matches we found and replace in HTML to parse.
        let htmlToParse = html;
        let waitingForClosing = {};

        let match;
        while ((match = regex.exec(html)) !== null) {
            // Handle shortcodes first.
            if ( '[' === match[0].substring(0, 1) ) {
                // Check for attributes in shortcode.
                let shortcode_atts = Helpers.getShortcodeAttributes( match[2] );

                // Get shortcode name.
                let uid = shortcodes.length;
                let id = match[1];
                const name = Helpers.getShortcodeName(id);

                // Check if this is a shortcode that needs a closing tag.
                const isClosingShortcode = '/' === id.substring(0, 1);
                const needsClosingShortcode = Shortcodes.contentShortcodes.includes( id );

                // We have a shortcode that still needs a closing shortcode, only add placeholder comment.
                if ( needsClosingShortcode ) {
                    htmlToParse = htmlToParse.replace(match[0], '<!--wprm-opening-' + uid + '-->');
                    waitingForClosing[ id ] = {
                        match,
                        uid,
                        id,
                        name,
                        attributes: shortcode_atts,
                    }
                    shortcodes[uid] = false; // Placeholder.
                    continue;
                }

                // We have a closing shortcode, check if we have a matching shortcode that's still open.
                if ( isClosingShortcode ) {
                    const openingShortcode = waitingForClosing.hasOwnProperty( id.substring(1) ) ? waitingForClosing[ id.substring(1) ] : false;
                    let content = false;

                    if ( openingShortcode ) {
                        htmlToParse = htmlToParse.replace( match[0], '<!--wprm-closing-' + openingShortcode.uid + '-->' );
                        
                        // Find content in between opening and closing shortcode.
                        const contentRegex = new RegExp( '<!--wprm-opening-' + openingShortcode.uid + '-->(.*)<!--wprm-closing-' + openingShortcode.uid + '-->', 'mis' );
                        let contentMatch;

                        if ((contentMatch = contentRegex.exec(htmlToParse)) !== null) {
                            content = contentMatch[1];

                            // Add placeholder elements in htmlToParse (keep comment for closing element).
                            htmlToParse = htmlToParse.replace( '<!--wprm-opening-' + openingShortcode.uid + '-->', '<wprm-replace-shortcode-with-block uid="' + openingShortcode.uid + '">' );
                            htmlToParse = htmlToParse.replace( '<!--wprm-closing-' + openingShortcode.uid + '-->', '<!--wprm-closing-' + openingShortcode.uid + '--></wprm-replace-shortcode-with-block>' );
                            shortcodes[ openingShortcode.uid ] = {
                                uid: openingShortcode.uid,
                                id: openingShortcode.id,
                                name: openingShortcode.name,
                                attributes: openingShortcode.attributes,
                                content,
                            };
                        }
                    }
                    
                    if ( ! openingShortcode || false === content ) {
                        // No matching opening shortcode or no opening comment found, remove closing shortcode.
                        htmlToParse = htmlToParse.replace(match[0], '');
                    }
                    continue;
                }

                // We have a regular shortcode, add placeholder element and register shortcode.
                htmlToParse = htmlToParse.replace(match[0], '<wprm-replace-shortcode-with-block uid="' + uid + '"></wprm-replace-shortcode-with-block>');
                shortcodes[uid] = {
                    uid,
                    id,
                    name,
                    attributes: shortcode_atts,
                    content: false,
                };
            } else {
                // Get layout element.
                let uid = shortcodes.length;
                let id = match[3];
                let content;
                let classes = match[4] ? match[4].split( ' ' ) : [];
                let name = Helpers.getShortcodeName(id);

                const customClass = classes.find( (c) => ! c.startsWith( 'wprm-' ) );
                if ( customClass ) {
                    name += ' (' + customClass + ')';
                }

                const elementWithUid = match[0].replace( '">', '" uid="' + uid + '">' );

                // Find closing div.
                const remainingHtmlIndex = htmlToParse.indexOf( match[0] ) + match[0].length;
                const remainingHtml = htmlToParse.substring( remainingHtmlIndex );
                const closingDivIndex = this.getIndexOfClosingDiv( remainingHtml );

                // Add comment for closing div.
                if ( -1 === closingDivIndex ) {
                    content = '';
                    htmlToParse = htmlToParse.substring( 0, remainingHtmlIndex + closingDivIndex + 1 ) + '<!--wprm-closing-' + uid + '--></div>' + htmlToParse.substring( remainingHtmlIndex + closingDivIndex + 1 );
                } else {
                    content = remainingHtml.substring( 0, closingDivIndex );
                    htmlToParse = htmlToParse.substring( 0, remainingHtmlIndex + closingDivIndex ) + '<!--wprm-closing-' + uid + '-->' + htmlToParse.substring( remainingHtmlIndex + closingDivIndex );
                }

                htmlToParse = htmlToParse.replace(match[0], elementWithUid);
                shortcodes[uid] = {
                    uid,
                    id,
                    name,
                    classes,
                    content,
                };                
            }
        }

        // Get HTML with shortcodes replaced by blocks.
        let parsedHtml = <Loader/>;
        try {
            const recipeId = this.state.recipe ? this.state.recipe.id : false;

            const parseOptions = {
                replace: function(domNode) {
                    if ( domNode.name == 'wprm-replace-shortcode-with-block' ) {
                        return this.replaceDomNodeWithBlock( domNode, shortcodes, recipeId, parseOptions );
                    }

                    if ( domNode.name == 'div' && domNode.attribs.class && 'wprm-layout-' === domNode.attribs.class.substring( 0, 12 ) ) {
                        return this.replaceDomNodeWithElement( domNode, shortcodes, recipeId, parseOptions );
                    }
                }.bind(this)
            }
            parsedHtml = Parser(htmlToParse, parseOptions);
        } catch ( error ) {
            console.log( 'Error parsing HTML', error );
        }
        return {
            htmlMap: htmlToParse,
            html: parsedHtml,
            shortcodes,
        }
    }

    getIndexOfClosingDiv( html ) {
        let index = -1;
        let depth = 1; // We're already inside the opening div.
        let i = 0;

        while ( i < html.length ) {
            if ( '<div' === html.substring( i, i + 4 ) ) {
                depth++;
            } else if ( '</div' === html.substring( i, i + 5 ) ) {
                depth--;
            }

            if ( 0 === depth ) {
                index = i;
                break;
            }

            i++;
        }

        return index;
    }

    replaceDomNodeWithElement( domNode, shortcodes, recipeId, parseOptions ) {
        const shortcode = shortcodes[ domNode.attribs.uid ];

        if ( ! shortcode ) {
            return null
        }

        return <Element
                    recipeId={ recipeId }
                    shortcode={ shortcode }
                    shortcodes={ shortcodes }
                    onClassesChange={ this.onClassesChange.bind(this) }
                    editingBlock={this.state.editingBlock}
                    onChangeEditingBlock={this.onChangeEditingBlock.bind(this)}
                    hoveringBlock={this.state.hoveringBlock}
                    onChangeHoveringBlock={this.onChangeHoveringBlock.bind(this)}
                    replaceDomNodeWithElement={this.replaceDomNodeWithElement.bind(this)}
                    replaceDomNodeWithBlock={this.replaceDomNodeWithBlock.bind(this)}
                    parseOptions={parseOptions}
                >
                    { false !== shortcode.content ? domToReact( domNode.children, parseOptions ) : null }
                </Element>;
    }

    replaceDomNodeWithBlock( domNode, shortcodes, recipeId, parseOptions ) {
        const shortcode = shortcodes[ domNode.attribs.uid ];

        if ( ! shortcode ) {
            return null
        }

        return <Block
                    mode={ 'shortcode-generator' === this.props.mode ? this.props.mode : null }
                    recipeId={ recipeId }
                    shortcode={ shortcode }
                    shortcodes={ shortcodes }
                    onBlockPropertyChange={ this.onBlockPropertyChange.bind(this) }
                    onBlockPropertiesChange={ this.onBlockPropertiesChange.bind(this) }
                    editingBlock={this.state.editingBlock}
                    onChangeEditingBlock={this.onChangeEditingBlock.bind(this)}
                    hoveringBlock={this.state.hoveringBlock}
                    onChangeHoveringBlock={this.onChangeHoveringBlock.bind(this)}
                    replaceDomNodeWithElement={this.replaceDomNodeWithElement.bind(this)}
                    replaceDomNodeWithBlock={this.replaceDomNodeWithBlock.bind(this)}
                    parseOptions={parseOptions}
                >
                    { false !== shortcode.content ? domToReact( domNode.children, parseOptions ) : null }
                </Block>;
    }

    unparseHtml() {
        let html = this.state.htmlMap;

        for ( let shortcode of this.state.shortcodes ) {
            if ( Elements.layoutElements.includes( shortcode.id ) ) {
                const elementRegex = new RegExp( '<div class="wprm-layout-[^>]*? uid="' + shortcode.uid + '">', 'mis' );
                let elementMatch;
        
                if ((elementMatch = elementRegex.exec(html)) !== null) {
                    // Classes to add.
                    let classes = [
                        shortcode.id,
                    ];
                    if ( shortcode.hasOwnProperty( 'classes' ) && shortcode.classes.length ) {
                        classes = classes.concat( shortcode.classes );
                    }

                    const elementToOutput = '<div class="' + classes.join( ' ' ) + '">';
                    
                    html = html.replace( elementMatch[0], elementToOutput );
                    html = html.replace('<!--wprm-closing-' + shortcode.uid + '-->', '');
                }
                
            } else {
                // Shortcodes, regular or content.
                let fullShortcode = Helpers.getFullShortcode(shortcode);

                if ( false !== shortcode.content ) {
                    const closingShortcode = '[/' + shortcode.id + ']';

                    html = html.replace('<wprm-replace-shortcode-with-block uid="' + shortcode.uid + '">', fullShortcode);
                    html = html.replace('<!--wprm-closing-' + shortcode.uid + '--></wprm-replace-shortcode-with-block>', closingShortcode);
                } else {
                    html = html.replace('<wprm-replace-shortcode-with-block uid="' + shortcode.uid + '"></wprm-replace-shortcode-with-block>', fullShortcode);
                }
            }
        }

        return html;
    }

    onClassesChange(uid, classes) {
        let newState = this.state;
        newState.shortcodes[uid].classes = classes;

        this.setState(newState,
            () => {
                let newHtml = this.unparseHtml();
                this.props.onChangeHTML(newHtml);
            }
        );
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

    onChangeAddingPattern(id) {
        if (id !== this.state.addingPattern) {
            this.setState({
                addingPattern: id,
            });
        }
    }

    onAddPattern( uid, position = 'after' ) {
        const pattern = Patterns.patterns[ this.state.addingPattern ];

        if ( pattern ) {
            if ( pattern.hasOwnProperty( 'html' ) && pattern.html ) {
                this.onAddHTML( pattern.html, uid, position );
            }

            if ( pattern.hasOwnProperty( 'css' ) && pattern.css ) {
                const patternCss = pattern.css.replace( /%template%/g, '.wprm-recipe-template-' + this.props.template.slug );
                const newCSS = this.props.template.style.css + '\n' + patternCss;
                this.props.onChangeCSS( newCSS );
            }
        }
    }

    onChangeAddingBlock(id) {
        if (id !== this.state.addingBlock) {
            this.setState({
                addingBlock: id,
            });
        }
    }

    onAddBlock( uid, position = 'after' ) {
        // Get shortcode to add.
        let shortcode = '[' + this.state.addingBlock + ']';

        if ( Shortcodes.contentShortcodes.includes( this.state.addingBlock ) ) {
            shortcode = '[' + this.state.addingBlock + ']\n[/' + this.state.addingBlock + ']';
        }
        if ( Elements.layoutElements.includes( this.state.addingBlock ) ) {
            shortcode = '<div class="' + this.state.addingBlock + '">\n</div>';
        }

        this.onAddHTML( shortcode, uid, position, ( addedShortcodeUid ) => {
            this.onChangeEditingBlock( addedShortcodeUid );
        });
    }

    onAddHTML( code, uid, position = 'after', callback = false) {
        let htmlMap = this.state.htmlMap;
        let addedShortcodeUid;

        if ( 'start' === uid ) {
            htmlMap = code + '\n' + htmlMap;
            addedShortcodeUid = 0;
        } else {
            const targetIsLayoutElement = this.state.shortcodes[uid] && Elements.layoutElements.includes( this.state.shortcodes[uid].id );
            const targetIsContentShortcode = ! targetIsLayoutElement && this.state.shortcodes[uid] && false !== this.state.shortcodes[uid].content;

            let afterShortcode = targetIsLayoutElement ? '<!--wprm-closing-' + uid + '--></div>' : '<wprm-replace-shortcode-with-block uid="' + uid + '"></wprm-replace-shortcode-with-block>';
            addedShortcodeUid = uid + 1;

            if ( targetIsContentShortcode || targetIsLayoutElement ) {
                if ( 'inside-start' === position ) {
                    afterShortcode = targetIsLayoutElement ? ' uid="' + uid + '">' : '<wprm-replace-shortcode-with-block uid="' + uid + '">';
                } else {
                    if ( targetIsContentShortcode ) {
                        afterShortcode = '<!--wprm-closing-' + uid + '--></wprm-replace-shortcode-with-block>';
                    }

                    // Get htmlMap substr before closing shortcode.
                    const beforeShortcode = htmlMap.substring( 0, htmlMap.indexOf( afterShortcode ) );

                    // Get last uid before closing shortcode.
                    const lastUid = beforeShortcode.match(/uid="(\d+)"/gmi).pop().match(/\d+/gmi).pop();
                    addedShortcodeUid = parseInt( lastUid ) + 1;
                }
            }

            if ( 'inside-end' === position ) {
                htmlMap = htmlMap.replace( afterShortcode, code + '\n' + afterShortcode );
            } else {
                // Default to add after. Works for inside-start as well.
                htmlMap = htmlMap.replace( afterShortcode, afterShortcode + '\n' + code );
            }
        }

        if ( htmlMap !== this.state.htmlMap) {
            this.setState({
                addingPattern: false,
                addingBlock: false,
                hoveringBlock: false,
                htmlMap,
            },
                () => {
                    let newHtml = this.unparseHtml();
                    this.props.onChangeHTML(newHtml);
                    this.props.onChangeMode( 'blocks' );

                    this.setState({
                        addingPattern: false,
                        addingBlock: false,
                        hoveringBlock: false,
                    }, () => {
                        if ( callback ) {
                            callback( addedShortcodeUid );
                        }
                    });
                });
        }
    }

    onRemoveBlock(uid) {
        let htmlMap = this.state.htmlMap;
        htmlMap = htmlMap.replace('<wprm-replace-shortcode-with-block uid="' + uid + '"></wprm-replace-shortcode-with-block>', '');

        // Remove closing shortcode if it exists.
        htmlMap = htmlMap.replace('<wprm-replace-shortcode-with-block uid="' + uid + '">', '');
        htmlMap = htmlMap.replace('<!--wprm-closing-' + uid + '--></wprm-replace-shortcode-with-block>', '');

        // Replace div element if exists.
        const elementRegex = new RegExp( '<div class="wprm-layout-[^>]*? uid="' + uid + '">', 'mis' );
        let elementMatch;

        if ((elementMatch = elementRegex.exec(htmlMap)) !== null) {
            htmlMap = htmlMap.replace( elementMatch[0], '' );
            htmlMap = htmlMap.replace('<!--wprm-closing-' + uid + '--></div>', '');
        }

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

    onChangeMovingBlock(shortcode) {
        this.setState({
            movingBlock: shortcode,
        });
    }

    onMoveBlock( target, position = 'after' ) {
        let htmlMap = this.state.htmlMap;
        const sourceIsLayoutElement = this.state.shortcodes[this.state.movingBlock.uid] && Elements.layoutElements.includes( this.state.shortcodes[this.state.movingBlock.uid].id );
        const sourceIsContentShortcode = ! sourceIsLayoutElement && this.state.shortcodes[this.state.movingBlock.uid] && false !== this.state.shortcodes[this.state.movingBlock.uid].content;

        const targetIsLayoutElement = this.state.shortcodes[target] && Elements.layoutElements.includes( this.state.shortcodes[target].id );
        const targetIsContentShortcode = ! targetIsLayoutElement && this.state.shortcodes[target] && false !== this.state.shortcodes[target].content;

        let shortcode = '<wprm-replace-shortcode-with-block uid="' + this.state.movingBlock.uid + '"></wprm-replace-shortcode-with-block>';

        if ( sourceIsContentShortcode || sourceIsLayoutElement ) {
            // Get full element or shortcode, with everything inside.
            let shortcodeRegex = new RegExp( '<wprm-replace-shortcode-with-block uid="' + this.state.movingBlock.uid + '">.*<!--wprm-closing-' + this.state.movingBlock.uid + '--><\/wprm-replace-shortcode-with-block>', 'mis' );
            if ( sourceIsLayoutElement ) {
                shortcodeRegex = new RegExp( '<div class="wprm-layout-[^>]*? uid="' + this.state.movingBlock.uid + '">.*<!--wprm-closing-' + this.state.movingBlock.uid + '--><\/div>', 'mis' );
            }

            let shortcodeMatch;
            if ((shortcodeMatch = shortcodeRegex.exec(htmlMap)) !== null) {
                shortcode = shortcodeMatch[0];
            }
        }
        
        let targetShortcode = '<wprm-replace-shortcode-with-block uid="' + target + '"></wprm-replace-shortcode-with-block>';

        // Remove from current position.
        htmlMap = htmlMap.replace(shortcode, '');

        // Move to new position.
        if ( 'before' === position || 'inside' === position ) {
            if ( targetIsContentShortcode ) {
                targetShortcode = '<wprm-replace-shortcode-with-block uid="' + target + '">';
            } else if ( targetIsLayoutElement ) {
                const elementRegex = new RegExp( '<div class="wprm-layout-[^>]*? uid="' + target + '">', 'mis' );

                let elementMatch;
                if ((elementMatch = elementRegex.exec(htmlMap)) !== null) {
                    targetShortcode = elementMatch[0];
                } else {
                    if ( 'inside' === position ) {
                        targetShortcode = ' uid="' + target + '">';
                    } else {
                        return; // Did not find the div we want to but the shortcode before, so can't continue.
                    }
                }
            }

            if ( 'inside' === position ) {
                htmlMap = htmlMap.replace(targetShortcode, targetShortcode + '\n' + shortcode);
            } else {
                htmlMap = htmlMap.replace(targetShortcode, shortcode + '\n' + targetShortcode);
            }
        } else {
            if ( targetIsContentShortcode ) {
                targetShortcode = '<!--wprm-closing-' + target + '--></wprm-replace-shortcode-with-block>';
            } else if ( targetIsLayoutElement ) {
                targetShortcode = '<!--wprm-closing-' + target + '--></div>';
            }

            htmlMap = htmlMap.replace(targetShortcode, targetShortcode + '\n' + shortcode);
        }

        if ( htmlMap !== this.state.htmlMap) {
            this.setState({
                movingBlock: false,
                hoveringBlock: false,
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
                                {
                                    'shortcode' === this.props.template.type
                                    &&
                                    <Fragment>
                                        <p>&nbsp;</p>
                                        <div className={`wprm-recipe wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
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
                <AddPatterns>
                {
                    ! this.state.addingPattern
                    ?
                    <Fragment>
                        <p>Select pattern to add:</p>
                        {
                            Object.keys( Patterns.patterns ).map( ( id, i ) => (
                                <div
                                    key={i}
                                    className="wprm-template-menu-block"
                                    onClick={ () => this.onChangeAddingPattern(id) }
                                >{ Patterns.patterns[ id ].label }</div>
                            ) )
                        }
                    </Fragment>
                    :
                    <Fragment>
                        <a href="#" onClick={(e) => {
                            e.preventDefault();
                            this.onChangeAddingPattern(false);
                        }}>Cancel</a>
                        <p>Add "{ Patterns.patterns[ this.state.addingPattern ].label }" after:</p>
                        <div
                            className="wprm-template-menu-block"
                            onClick={ () => this.onAddPattern( 'start' ) }
                        >Start of template</div>
                        {
                            this.state.shortcodes.map((shortcode, i) => {
                                if ( false !== shortcode.content ) {
                                    return (
                                        <div
                                            key={i}
                                            className={ shortcode.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-content wprm-template-menu-block-hover' : 'wprm-template-menu-block wprm-template-menu-block-content' }
                                            onMouseEnter={ () => this.onChangeHoveringBlock(shortcode.uid) }
                                            onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                        >
                                            { shortcode.name }
                                            <div
                                                className="wprm-template-menu-block-inside"
                                                onClick={ () => this.onAddPattern( shortcode.uid, 'inside-start' ) }
                                            >Add inside, as first block</div>
                                            <div
                                                className="wprm-template-menu-block-inside"
                                                onClick={ () => this.onAddPattern( shortcode.uid, 'inside-end' ) }
                                            >Add inside, as last block</div>
                                            <div
                                                className="wprm-template-menu-block-inside"
                                                onClick={ () => this.onAddPattern( shortcode.uid, 'after' ) }
                                            >Add after</div>
                                        </div>
                                    );
                                }

                                // Regular shortcode.
                                return (
                                    <div
                                        key={i}
                                        className={ shortcode.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-hover' : 'wprm-template-menu-block' }
                                        onClick={ () => this.onAddPattern( shortcode.uid ) }
                                        onMouseEnter={ () => this.onChangeHoveringBlock(shortcode.uid) }
                                        onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                    >{ shortcode.name }</div>
                                );
                            })
                        }
                    </Fragment>
                }
                </AddPatterns>
                <AddBlocks>
                {
                    ! this.state.addingBlock
                    ?
                    <Fragment>
                        <p>Select block to add:</p>
                        {
                            Object.keys( shortcodeGroups ).map( ( groupKey, i ) => (
                                <Fragment key={ i }>
                                    <div className="wprm-template-menu-add-block-group">{ shortcodeGroups[ groupKey ].group }</div>
                                    {
                                        shortcodeGroups[ groupKey ].shortcodes.map((id, j) => {

                                            // Make sure shortcode still exists.
                                            if ( ! shortcodeKeysAlphebetically.includes( id ) && ! Elements.layoutElements.includes( id ) ) {
                                                return null;
                                            }

                                            return (
                                                <div
                                                    key={j}
                                                    className="wprm-template-menu-block"
                                                    onClick={ () => this.onChangeAddingBlock(id) }
                                                >{ Helpers.getShortcodeName(id) }</div>
                                            );
                                        })
                                    }
                                </Fragment>
                            ) )
                        }
                    </Fragment>
                    :
                    <Fragment>
                        <a href="#" onClick={(e) => {
                            e.preventDefault();
                            this.onChangeAddingBlock(false);
                        }}>Cancel</a>
                        <p>Add "{ Helpers.getShortcodeName(this.state.addingBlock) }" after:</p>
                        <div
                            className="wprm-template-menu-block"
                            onClick={ () => this.onAddBlock( 'start' ) }
                        >Start of template</div>
                        {
                            this.state.shortcodes.map((shortcode, i) => {
                                if ( false !== shortcode.content ) {
                                    return (
                                        <div
                                            key={i}
                                            className={ shortcode.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-content wprm-template-menu-block-hover' : 'wprm-template-menu-block wprm-template-menu-block-content' }
                                            onMouseEnter={ () => this.onChangeHoveringBlock(shortcode.uid) }
                                            onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                        >
                                            { shortcode.name }
                                            <div
                                                className="wprm-template-menu-block-inside"
                                                onClick={ () => this.onAddBlock( shortcode.uid, 'inside-start' ) }
                                            >Add inside, as first block</div>
                                            <div
                                                className="wprm-template-menu-block-inside"
                                                onClick={ () => this.onAddBlock( shortcode.uid, 'inside-end' ) }
                                            >Add inside, as last block</div>
                                            <div
                                                className="wprm-template-menu-block-inside"
                                                onClick={ () => this.onAddBlock( shortcode.uid, 'after' ) }
                                            >Add after</div>
                                        </div>
                                    );
                                }

                                // Regular shortcode.
                                return (
                                    <div
                                        key={i}
                                        className={ shortcode.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-hover' : 'wprm-template-menu-block' }
                                        onClick={ () => this.onAddBlock( shortcode.uid ) }
                                        onMouseEnter={ () => this.onChangeHoveringBlock(shortcode.uid) }
                                        onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                    >{ shortcode.name }</div>
                                );
                            })
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
                <MoveBlocks>
                {
                        this.state.shortcodes.length <= 1
                        ?
                        <p>There are not enough blocks to move.</p>
                        :
                        <Fragment>
                            {
                                false === this.state.movingBlock
                                ?
                                <Fragment>
                                    <p>Select block to move:</p>
                                    {
                                        this.state.shortcodes.map((shortcode, i) => {
                                            return (
                                                <div
                                                    key={i}
                                                    className={ shortcode.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-hover' : 'wprm-template-menu-block' }
                                                    onClick={ () => {
                                                        this.onChangeMovingBlock(shortcode);
                                                    }}
                                                    onMouseEnter={ () => this.onChangeHoveringBlock(shortcode.uid) }
                                                    onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                                >{ shortcode.name }</div>
                                            );
                                        })
                                    }
                                </Fragment>
                                :
                                <Fragment>
                                    <a href="#" onClick={(e) => {
                                        e.preventDefault();
                                        this.onChangeMovingBlock(false);
                                    }}>Cancel</a>
                                    <p>Move "{ this.state.movingBlock.name }" inside of:</p>
                                    {
                                        this.state.shortcodes.map((shortcode, i) => {
                                            if ( shortcode.uid === this.state.movingBlock.uid || false === shortcode.content ) {
                                                return null;
                                            }
                                            
                                            return (
                                                <div
                                                    key={i}
                                                    className={ shortcode.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-hover' : 'wprm-template-menu-block' }
                                                    onClick={ () => {
                                                        this.onMoveBlock( shortcode.uid, 'inside' );
                                                    }}
                                                    onMouseEnter={ () => this.onChangeHoveringBlock(shortcode.uid) }
                                                    onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                                >{ shortcode.name }</div>
                                            );
                                        })
                                    }
                                    <br/><br/>
                                    <p>Move "{ this.state.movingBlock.name }" before:</p>
                                    {
                                        this.state.shortcodes.map((shortcode, i) => {
                                            if ( shortcode.uid === this.state.movingBlock.uid ) {
                                                return null;
                                            }
                                            
                                            return (
                                                <div
                                                    key={i}
                                                    className={ shortcode.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-hover' : 'wprm-template-menu-block' }
                                                    onClick={ () => {
                                                        this.onMoveBlock( shortcode.uid, 'before' );
                                                    }}
                                                    onMouseEnter={ () => this.onChangeHoveringBlock(shortcode.uid) }
                                                    onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                                >{ shortcode.name }</div>
                                            );
                                        })
                                    }
                                    <br/><br/>
                                    <p>Move "{ this.state.movingBlock.name }" after:</p>
                                    {
                                        this.state.shortcodes.map((shortcode, i) => {
                                            if ( shortcode.uid === this.state.movingBlock.uid ) {
                                                return null;
                                            }

                                            return (
                                                <div
                                                    key={i}
                                                    className={ shortcode.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-hover' : 'wprm-template-menu-block' }
                                                    onClick={ () => {
                                                        this.onMoveBlock( shortcode.uid, 'after' );
                                                    }}
                                                    onMouseEnter={ () => this.onChangeHoveringBlock(shortcode.uid) }
                                                    onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                                >{ shortcode.name }</div>
                                            );
                                        })
                                    }
                                </Fragment>
                            }
                        </Fragment>
                }
                </MoveBlocks>
            </Fragment>
        );
    }
}
