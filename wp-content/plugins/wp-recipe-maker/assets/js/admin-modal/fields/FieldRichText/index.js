import React, { Component } from 'react';

import RichEditor from './RichEditor';

export default class FieldRichText extends Component {

    constructor(props) {
        super(props);
        
        this.state = {
            errors: 0,
        }
    }

    componentDidCatch( error, info ) {
        // Catched error forces rerender of Slate which fixes common issues (like selection problems).
        this.setState({
            errors: this.state.errors + 1,
        });
    }

    render() {
        return (
            <RichEditor
                { ...this.props }
            />
        )
    }
}
