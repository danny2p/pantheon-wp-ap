import React, { Fragment } from 'react';

import '../../../../css/admin/dashboard/news.scss';

import { __wprm } from 'Shared/Translations';

import Block from '../../layout/Block';
import Item from './Item';
 
const News = (props) => {
    const news = wprm_admin_dashboard.news;

    if ( ! news || 0 === news.length ) {
        return null;
    }

    return (
        <Block
            title={ __wprm( 'News' ) }
        >
            <div className="wprm-admin-dashboard-news-container">
                {
                    news.map( ( item, index ) => {
                        return (
                            <Item
                                item={ item }
                                key={ index }
                            />
                        )
                    } )
                }
            </div>
        </Block>
    );
}
export default News;