const { Component } = wp.element;

import Select from 'react-select';


class RecipeSelect extends Component {
    constructor() {
        super( ...arguments );
    }

    render() {        
        return (
            <Select
                // placeholder="Select a recipe"
                // value={value}
                // onChange={this.props.onChangeField}
                // valueKey="id"
                // labelKey="text"
                // loadOptions={this.getOptions.bind(this)}
                // clearable={false}
            />
        );
    }
}

export default RecipeSelect;