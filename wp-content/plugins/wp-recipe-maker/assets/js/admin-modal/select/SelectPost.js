import React, { Component } from 'react';
import AsyncSelect from 'react-select/async';

import { __wprm } from 'Shared/Translations';

export default class SelectPost extends Component {
    getOptions(input) {
        if (!input) {
			return Promise.resolve({ options: [] });
        }

		return fetch(wprm_admin.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: 'action=wprm_search_posts&security=' + wprm_admin.nonce + '&search=' + encodeURIComponent( input ),
                headers: {
                    'Accept': 'application/json, text/plain, */*',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
                },
            })
            .then((response) => response.json())
            .then((json) => {
                return json.data.posts_with_id;
            });
    }

    render() {
        return (
            <AsyncSelect
                placeholder={ __wprm( 'Select or search a post' ) }
                value={this.props.value}
                onChange={this.props.onValueChange}
                getOptionValue={({id}) => id}
                getOptionLabel={({text}) => text}
                loadOptions={this.getOptions.bind(this)}
                noOptionsMessage={() => __wprm( 'No posts found' ) }
                clearable={false}
            />
        );
    }
}
