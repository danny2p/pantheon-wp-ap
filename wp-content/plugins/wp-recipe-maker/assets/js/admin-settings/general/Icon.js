import React from 'react';
import SVG from 'react-inlinesvg';

import IconArrows from '../../../icons/settings/arrows.svg';
import IconBook from '../../../icons/settings/book.svg';
import IconBrush from '../../../icons/settings/brush.svg';
import IconButtonClick from '../../../icons/settings/button-click.svg';
import IconChart from '../../../icons/settings/chart.svg';
import IconClock from '../../../icons/settings/clock.svg';
import IconCode from '../../../icons/settings/code.svg';
import IconCog from '../../../icons/settings/cog.svg';
import IconCrane from '../../../icons/settings/crane.svg';
import IconDocApple from '../../../icons/settings/doc-apple.svg';
import IconDoc from '../../../icons/settings/doc.svg';
import IconEdit from '../../../icons/settings/edit.svg';
import IconFiles from '../../../icons/settings/files.svg';
import IconHealth from '../../../icons/settings/health.svg';
import IconImport from '../../../icons/settings/import.svg';
import IconKey from '../../../icons/settings/key.svg';
import IconKnife from '../../../icons/settings/knife.svg';
import IconLetter from '../../../icons/settings/letter.svg';
import IconLink from '../../../icons/settings/link.svg';
import IconList from '../../../icons/settings/list.svg';
import IconLock from '../../../icons/settings/lock.svg';
import IconMeasureApple from '../../../icons/settings/measure-apple.svg';
import IconPainting from '../../../icons/settings/painting.svg';
import IconPlug from '../../../icons/settings/plug.svg';
import IconPrinter from '../../../icons/settings/printer.svg';
import IconQuestion from '../../../icons/settings/question.svg';
import IconSearch from '../../../icons/settings/search.svg';
import IconShare from '../../../icons/settings/share.svg';
import IconSliders from '../../../icons/settings/sliders.svg';
import IconSpeed from '../../../icons/settings/speed.svg';
import IconStar from '../../../icons/settings/star.svg';
import IconSupport from '../../../icons/settings/support.svg';
import IconText from '../../../icons/settings/text.svg';
import IconUndo from '../../../icons/settings/undo.svg';
import IconUp from '../../../icons/settings/up.svg';
import IconWarning from '../../../icons/settings/warning.svg';

const icons = {
    arrows: IconArrows,
    book: IconBook,
    brush: IconBrush,
    'button-click': IconButtonClick,
    chart: IconChart,
    clock: IconClock,
    code: IconCode,
    cog: IconCog,
    crane: IconCrane,
    'doc-apple': IconDocApple,
    doc: IconDoc,
    edit: IconEdit,
    files: IconFiles,
    health: IconHealth,
    import: IconImport,
    key: IconKey,
    knife: IconKnife,
    letter: IconLetter,
    link: IconLink,
    list: IconList,
    lock: IconLock,
    'measure-apple': IconMeasureApple,
    painting: IconPainting,
    plug: IconPlug,
    printer: IconPrinter,
    question: IconQuestion,
    search: IconSearch,
    share: IconShare,
    sliders: IconSliders,
    speed: IconSpeed,
    star: IconStar,
    support: IconSupport,
    text: IconText,
    undo: IconUndo,
    up: IconUp,
    warning: IconWarning,
};

const Icon = (props) => {
    let icon = icons.hasOwnProperty(props.type) ? icons[props.type] : false;

    if ( !icon ) {
        return <span className="wprm-settings-noicon">&nbsp;</span>;
    }

    return (
        <span className='wprm-settings-icon'>
            <SVG
                src={icon}
            />
        </span>
    );
}
export default Icon;