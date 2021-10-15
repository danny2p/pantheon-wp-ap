import React, { Fragment } from 'react';
 
const Item = (props) => {
    const { item } = props;

    if ( ! item.title ) {
        return null;
    }

    const content = (
        <Fragment>
            <div className="wprm-admin-dashboard-news-item-title-container">
                <div className="wprm-admin-dashboard-news-item-date">{ item.date_formatted }</div>
                <div className="wprm-admin-dashboard-news-item-title">{ item.title }</div>
            </div>
            {
                item.hasOwnProperty( 'label' )
                && <div className={ `wprm-admin-dashboard-news-item-label wprm-admin-dashboard-news-item-label-${ item.label_key }` }>{ item.label }</div>
            }
        </Fragment>
    );

    let classes = [
        'wprm-admin-dashboard-news-item',
    ];

    if ( item.new ) {
        classes.push( 'wprm-admin-dashboard-news-item-new' );
    }

    return (
        <Fragment>
        {
            item.hasOwnProperty( 'url' )
            ?
            <a href={ item.url } target="_blank" className={ classes.join( ' ' ) }>{ content }</a>
            :
            <div className={ classes.join( ' ' ) }>{ content }</div>
        }
        </Fragment>
    );
}
export default Item;