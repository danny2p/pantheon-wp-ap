import React from 'react';
import SVG from 'react-inlinesvg';

import '../../css/admin/shared/icon.scss';
import Tooltip from './Tooltip';

import IconAdjustable from '../../icons/admin/adjustable.svg';
import IconBold from '../../icons/admin/bold.svg';
import IconClock from '../../icons/admin/clock.svg';
import IconClose from '../../icons/admin/close.svg';
import IconCode from '../../icons/admin/code.svg';
import IconCheckboxAlternate from '../../icons/admin/checkbox-alternate.svg';
import IconCheckboxChecked from '../../icons/admin/checkbox-checked.svg';
import IconCheckboxEmpty from '../../icons/admin/checkbox-empty.svg';
import IconCheckmark from '../../icons/admin/checkmark.svg';
import IconDuplicate from '../../icons/admin/duplicate.svg';
import IconDrag from '../../icons/admin/drag.svg';
import IconSplit from '../../icons/admin/split.svg';
import IconSplitThick from '../../icons/admin/split-thick.svg';
import IconEaflLink from '../../icons/admin/eafl-link.svg';
import IconEaflUnlink from '../../icons/admin/eafl-unlink.svg';
import IconEye from '../../icons/admin/eye.svg';
import IconHeading1 from '../../icons/admin/heading-1.svg';
import IconHeading2 from '../../icons/admin/heading-2.svg';
import IconHeading3 from '../../icons/admin/heading-3.svg';
import IconHeading4 from '../../icons/admin/heading-4.svg';
import IconHeading5 from '../../icons/admin/heading-5.svg';
import IconHeading6 from '../../icons/admin/heading-6.svg';
import IconItalic from '../../icons/admin/italic.svg';
import IconLink from '../../icons/admin/link.svg';
import IconLock from '../../icons/admin/lock.svg';
import IconMerge from '../../icons/admin/merge.svg';
import IconMovie from '../../icons/admin/movie.svg';
import IconPencil from '../../icons/admin/pencil.svg';
import IconPhoto from '../../icons/admin/photo.svg';
import IconPlus from '../../icons/admin/plus.svg';
import IconPlusText from '../../icons/admin/plus-text.svg';
import IconPrint from '../../icons/admin/print.svg';
import IconQuestionBox from '../../icons/admin/question-box.svg';
import IconQuestion from '../../icons/admin/question.svg';
import IconReload from '../../icons/admin/reload.svg';
import IconRestore from '../../icons/admin/restore.svg';
import IconSearch from '../../icons/admin/search.svg';
import IconSparks from '../../icons/admin/sparks.svg';
import IconStarEmpty from '../../icons/admin/star-empty.svg';
import IconStarFull from '../../icons/admin/star-full.svg';
import IconSubscript from '../../icons/admin/subscript.svg';
import IconSuperscript from '../../icons/admin/superscript.svg';
import IconStyle from '../../icons/admin/style.svg';
import IconTemperature from '../../icons/admin/temperature.svg';
import IconTrash from '../../icons/admin/trash.svg';
import IconUnderline from '../../icons/admin/underline.svg';
import IconUnlink from '../../icons/admin/unlink.svg';
import IconVideoplayer from '../../icons/admin/videoplayer.svg';
import IconWarning from '../../icons/admin/warning.svg';
 
const icons = {
    adjustable: IconAdjustable,
    bold: IconBold,
    clock: IconClock,
    close: IconClose,
    code: IconCode,
    'checkbox-alternate': IconCheckboxAlternate,
    'checkbox-checked': IconCheckboxChecked,
    'checkbox-empty': IconCheckboxEmpty,
    checkmark: IconCheckmark,
    duplicate: IconDuplicate,
    drag: IconDrag,
    split: IconSplit,
    'split-thick': IconSplitThick,
    'eafl-link': IconEaflLink,
    'eafl-unlink': IconEaflUnlink,
    eye: IconEye,
    'heading-1': IconHeading1,
    'heading-2': IconHeading2,
    'heading-3': IconHeading3,
    'heading-4': IconHeading4,
    'heading-5': IconHeading5,
    'heading-6': IconHeading6,
    italic: IconItalic,
    link: IconLink,
    lock: IconLock,
    merge: IconMerge,
    movie: IconMovie,
    pencil: IconPencil,
    photo: IconPhoto,
    plus: IconPlus,
    'plus-text': IconPlusText,
    print: IconPrint,
    'question-box': IconQuestionBox,
    question: IconQuestion,
    reload: IconReload,
    restore: IconRestore,
    search: IconSearch,
    sparks: IconSparks,
    'star-empty': IconStarEmpty,
    'star-full': IconStarFull,
    style: IconStyle,
    subscript: IconSubscript,
    superscript: IconSuperscript,
    temperature: IconTemperature,
    trash: IconTrash,
    underline: IconUnderline,
    unlink: IconUnlink,
    videoplayer: IconVideoplayer,
    warning: IconWarning,
};

const Icon = (props) => {
    let icon = icons.hasOwnProperty(props.type) ? icons[props.type] : false;

    if ( !icon ) {
        return null;
    }

    let tooltip = props.title;
    let className = props.className ? `wprm-admin-icon ${props.className}` : 'wprm-admin-icon';

    const hidden = props.hasOwnProperty( 'hidden' ) ? props.hidden : false;

    if ( hidden ) {
        tooltip = '';
        className += ' wprm-admin-icon-hidden';
    }

    // Optional custom color.
    let customColor = false;
    if ( props.hasOwnProperty( 'color' ) && '#111111' !== props.color ) {
        customColor = props.color;  
    }

    return (
        <Tooltip content={ tooltip }>
            <span
                className={ className }
                onClick={ hidden ? () => {} : props.onClick }
            >
                <SVG
                    src={ icon }
                    preProcessor={(code) => {
                        if ( customColor && ( '#' === customColor.charAt(0) || 'currentColor' === customColor ) ) {
                            code = code.replaceAll('#111111', customColor );
                        }

                        return code;
                    }}
                />
            </span>
        </Tooltip>
    );
}
export default Icon;
