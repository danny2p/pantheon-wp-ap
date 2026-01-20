import React, { Component } from 'react';
import AsyncSelect from 'react-select/async';

import AjaxWrapper from 'Shared/AjaxWrapper';

export default class SettingDropdownRecipe extends Component {
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
        let options = JSON.parse( JSON.stringify( wprm_admin.latest_recipes ) );

        // Optional General Options.
        if ( this.props.setting.hasOwnProperty( 'options' ) ) {
            for (let option in this.props.setting.options) {
                options.unshift({
                    id: option,
                    text: this.props.setting.options[option],
                });
            }
        }

        const isDemoRecipe = 'demo' === this.props.value || 'demo' === this.props.value.id;

        return (
            <div className="wprm-main-container-preview-recipe">
                <AsyncSelect
                    placeholder="Select or start typing to search for a recipe"
                    value={ isDemoRecipe ? options[0] : this.props.value }
                    onChange={this.props.onValueChange}
                    getOptionValue={({id}) => id}
                    getOptionLabel={({text}) => text}
                    defaultOptions={options}
                    loadOptions={this.getOptions.bind(this)}
                    noOptionsMessage={() => "Create a recipe on the Manage page"}
                    clearable={false}
                />
            </div>
        );
    }
}
