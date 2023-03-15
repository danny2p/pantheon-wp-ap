// Source: https://github.com/ianstormtaylor/slate/blob/master/site/examples/paste-html.js
import { jsx } from 'slate-hyperscript';
import escapeHtml from 'escape-html';
import { Text } from 'slate';

const ELEMENT_TAGS = {
    A: el => ({
        type: 'link',
        url: el.getAttribute('href'),
        newTab: '_blank' === el.getAttribute('target') ? true : false,
        noFollow: el.getAttribute('rel') && el.getAttribute('rel').includes('nofollow') ? true : false,
        sponsored: el.getAttribute('rel') && el.getAttribute('rel').includes('sponsored') ? true : false,
    }),
    P: () => ({ type: 'paragraph' }),
    'WPRM-CODE': el => {
        return {
            type: 'code',
        }
    },
    'WPRM-TEMPERATURE': el => {
        return {
            type: 'temperature',
            icon: el.getAttribute('icon'),
            unit: el.getAttribute('unit'),
            help: el.getAttribute('help'),
        }
    },
    'WPRM-INGREDIENT': el => {
        return {
            type: 'ingredient',
            uid: el.getAttribute('uid'),
            removed: el.getAttribute('removed') && '1' === el.getAttribute('removed') ? true : false,
        }
    },
}

const TEXT_TAGS = {
    EM: () => ({ italic: true }),
    I: () => ({ italic: true }),
    STRONG: () => ({ bold: true }),
    B: () => ({ bold: true }),
    U: () => ({ underline: true }),
    SUB: () => ({ subscript: true }),
    SUP: () => ({ superscript: true }),
}

export const deserialize = ( el, singleLine = false ) => {
    if (el.nodeType === 3) {
        // Return text without newlines.
        return el.textContent.replace(/\r?\n|\r/g, '');
    } else if (el.nodeType !== 1) {
        return null;
    } else if (el.nodeName === 'BR') {
        return '';
    }

    const { nodeName } = el
    let parent = el

    if (
        nodeName === 'PRE' &&
        el.childNodes[0] &&
        el.childNodes[0].nodeName === 'CODE'
    ) {
        parent = el.childNodes[0]
    }

    let children = Array.from(parent.childNodes)
        .map( (child) => deserialize( child, singleLine ) )
        .reduce((acc, val) => acc.concat(val), [])

    if (el.nodeName === 'BODY') {
        return jsx('fragment', {}, children)
    }

    // No paragraphs in singleLine mode.
    if ( singleLine && 'P' === nodeName ) {
        return children;
    }

    if (ELEMENT_TAGS[nodeName]) {
        let attrs = ELEMENT_TAGS[nodeName](el)

        // Special case: Affiliate Link.
        if ( 'A' === nodeName && el.hasAttribute( 'data-eafl-id' ) ) {
            attrs = {
                type: 'affiliate-link',
                url: el.getAttribute('href'),
                id: parseInt( el.getAttribute('data-eafl-id') ),
            };
        }

        let element = jsx('element', attrs, children)

        // Special case: wprm-code.
        if ( 'WPRM-CODE' === nodeName ) {
            element.children = [{ text: el.innerHTML }];
        }

        if ( 0 === element.children.length ) {
            element.children = [{ text: '' }];
        }

        return element;
    }

    if (TEXT_TAGS[nodeName]) {
        const attr = TEXT_TAGS[nodeName](el)

        // Check for potential conflicts in the children Array.
        // Issue: https://github.com/ianstormtaylor/slate/issues/3350
        children = children.map((child) => {
            if ( typeof child === 'string' || Text.isText( child ) ) {
                return child;
            } else {                
                if ( child.hasOwnProperty( 'type' ) && 'link' === child.type ) {
                    console.log( 'Information Lost', child );
                    if ( child.hasOwnProperty( 'children' ) && 1 === child.children.length ) {
                        if ( child.children[0].hasOwnProperty( 'text' ) ) {
                            return child.children[0].text;
                        }
                    }
                }
                return '';
            }
        });

        return children
            .map(child => {
                return jsx(`text`, attr, child)
            })
    }

    return children
}

export const serialize = node => {
    if ( Text.isText( node ) ) {
        let html = escapeHtml(node.text);
        
        if (node.bold) {
            html = `<strong>${html}</strong>`;
        }
        if (node.italic) {
            html = `<em>${html}</em>`;
        }
        if (node.underline) {
            html = `<u>${html}</u>`;
        }
        if (node.subscript) {
            html = `<sub>${html}</sub>`;
        }
        if (node.superscript) {
            html = `<sup>${html}</sup>`;
        }

        return html;
    }
  
    const children = node.children.map(n => serialize(n)).join('');

    switch (node.type) {
        case 'paragraph':
            return `<p>${children}</p>`;
        case 'link':
            let rel = [];

            if ( node.noFollow ) { rel.push( 'nofollow' ) }
            if ( node.sponsored ) { rel.push( 'sponsored' ) }

            return `<a href="${escapeHtml(node.url)}"${ node.newTab ? ' target="_blank"' : ''}${ rel.length > 0 ? ` rel="${ rel.join( ' ' ) }"` : ''}>${children}</a>`;
        case 'affiliate-link':
            return `<a href="${escapeHtml(node.url)}" data-eafl-id="${escapeHtml(node.id)}" class="eafl-link">${children}</a>`;
        case 'code':
            return `<wprm-code>${children}</wprm-code>`;
        case 'temperature':
            let help = node.help;

            help = help.replace(/"/gm, '&quot;');
            help = help.replace(/\]/gm, '&#93;');
            help = help.replace(/>/gm, '&gt;');

            return `<wprm-temperature icon="${ node.icon }" unit="${ node.unit }" help="${ help }">${ children }</wprm-temperature>`;
        case 'ingredient':
            return `<wprm-ingredient uid="${ node.uid }" removed="${ node.removed ? '1' : '0' }">${ children }</wprm-ingredient>`;
        default:
            return children;
    }
}