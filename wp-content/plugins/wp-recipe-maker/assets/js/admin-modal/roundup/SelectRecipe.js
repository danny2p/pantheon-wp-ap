import React, { Component } from 'react';
import AsyncSelect from 'react-select/async';

import { __wprm } from 'Shared/Translations';
import AjaxWrapper from 'Shared/AjaxWrapper';

export default class SelectRecipe extends Component {
    getOptions(input) {
        if (!input) {
			return Promise.resolve({ options: [] });
        }

		return AjaxWrapper.call('wprm_search_recipes', {
            search: input,
        }).then((data) => {
            // Return empty array if no data or error occurred.
            return data && data.recipes_with_id ? data.recipes_with_id : [];
        });
    }

    render() {
        return (
            <AsyncSelect
                placeholder={ __wprm( 'Select or start typing to search for a recipe' ) }
                value={this.props.value}
                onChange={this.props.onValueChange}
                getOptionValue={({id}) => id}
                getOptionLabel={({text}) => text}
                defaultOptions={this.props.options.concat(wprm_admin.latest_recipes)}
                loadOptions={this.getOptions.bind(this)}
                noOptionsMessage={() => __wprm( 'No recipes found' ) }
                clearable={false}
            />
        );
    }
}
