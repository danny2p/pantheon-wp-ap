import React, { Component } from 'react';
import AsyncSelect from 'react-select/async';

import { __wprm } from 'Shared/Translations';
import AjaxWrapper from 'Shared/AjaxWrapper';

export default class SelectPost extends Component {
    getOptions(input) {
        if (!input) {
			return Promise.resolve({ options: [] });
        }

		return AjaxWrapper.call('wprm_search_posts', {
            search: input,
        }).then((data) => {
            // Return empty array if no data or error occurred.
            return data && data.posts_with_id ? data.posts_with_id : [];
        });
    }

    render() {
        return (
            <AsyncSelect
                placeholder={ __wprm( 'Select or start typing to search for a post' ) }
                value={this.props.value}
                onChange={this.props.onValueChange}
                getOptionValue={({id}) => id}
                getOptionLabel={({text}) => text}
                defaultOptions={this.props.options ? this.props.options.concat(wprm_admin.latest_posts) : wprm_admin.latest_posts}
                loadOptions={this.getOptions.bind(this)}
                noOptionsMessage={() => __wprm( 'No posts found' ) }
                clearable={false}
            />
        );
    }
}
