const {
    SelectControl,
} = wp.components;
const {
    Component,
} = wp.element;

import AsyncSelect from 'react-select/async';

export default class PostSelect extends Component {
    getPosts(search) {
        wp.apiFetch( { path: `/wp/v2/${this.props.type}?search=${ encodeURIComponent( search ) }` } ).then( posts => {
            const result = posts.map((post) => {
                return {
                    value: post.id,
                    label: post.title.rendered,
                }
            });

            return Promise.resolve(result);
        } ).catch( () => {
            return Promise.resolve({ options: [] });
        });
    }

    render() {
        return (
            <AsyncSelect
                className="wprm-shared-post-select"
                // placeholder={this.props.placeholder}
                value={null}
                onChange={this.props.onChange}
                defaultOptions={true}
                loadOptions={this.getPosts.bind(this)}
                clearable={false}
            />
        );
    }
}